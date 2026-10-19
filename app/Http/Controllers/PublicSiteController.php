<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    // 1. NORMAL VATANDAŞIN GÖRECEĞİ KURUMSAL WEB SİTESİ
    public function home()
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        $branches = Branch::where('is_active', true)->orderBy('sort_order')->get();
        $groups = Group::with(['branch', 'coach', 'schedules'])->withCount('activeStudents')->where('is_active', true)->get();
        $packages = Package::where('is_active', true)->where('show_on_site', true)->get();
        $coaches = User::where('role', 'coach')->where('is_active', true)->get();
        $announcements = Announcement::whereIn('target_audience', ['all', 'public_site'])->orderByDesc('is_pinned')->latest()->take(4)->get();
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->get();
        $gallery = GalleryItem::where('photo_consent_verified', true)->latest()->take(6)->get();

        return view('public.home', compact(
            'settings',
            'branches',
            'groups',
            'packages',
            'coaches',
            'announcements',
            'faqs',
            'gallery'
        ));
    }

    // Online Ön Kayıt / Ücretsiz Deneme Dersi Başvurusu
    public function submitTrialLead(Request $request)
    {
        $validated = $request->validate([
            'parent_name' => 'required|string|max:120',
            'parent_phone' => 'required|string|max:30',
            'student_name' => 'required|string|max:120',
            'student_age' => 'required|integer|min:2|max:60',
            'branch_id' => 'nullable|exists:branches,id',
            'preferred_days' => 'nullable|string|max:120',
            'kvkk_approved' => 'accepted',
        ]);

        $lead = Lead::create([
            'parent_name' => $validated['parent_name'],
            'parent_phone' => $validated['parent_phone'],
            'student_name' => $validated['student_name'],
            'student_age' => $validated['student_age'],
            'branch_id' => $validated['branch_id'] ?? null,
            'preferred_days' => $validated['preferred_days'] ?? 'Belirtilmedi',
            'source' => 'website',
            'status' => 'new',
            'notes' => 'Web sitesi online ön kayıt formundan geldi.',
            'kvkk_approved' => true,
            'kvkk_approved_at' => now(),
        ]);

        $tpl = WhatsappTemplate::where('trigger_event', 'trial_reminder')->first();
        $msgText = "Merhaba {$lead->parent_name} 👋 Enerjik Cimnastik Spor Kulübü'ne {$lead->student_name} için yaptığınız ücretsiz deneme dersi başvurusu alınmıştır! Uygun grup ve saat planlaması için sizinle iletişime geçiyoruz. 🤸";

        WhatsappMessage::create([
            'template_id' => $tpl?->id,
            'recipient_name' => $lead->parent_name . ' (Aday - ' . $lead->student_name . ')',
            'recipient_phone' => $lead->parent_phone,
            'message_text' => $msgText,
            'status' => 'queued',
        ]);

        AuditLog::record('aday_on_kayit', "Web sitesinden yeni deneme dersi talebi: {$lead->student_name} (Veli: {$lead->parent_name})", 'Lead', $lead->id);

        return redirect('/#on-kayit')->with('trial_success', 'Başvurunuz başarıyla alındı! Ön büromuz en kısa sürede WhatsApp ve telefon üzerinden deneme dersi saatinizi onaylayacaktır.');
    }

    public function parentPortal(Request $request)
    {
        return redirect()->route('public.mobile.pwa', $request->all());
    }

    // 2. VELİ GİRİŞİ VE VELİYE ÖZEL MOBİL UYGULAMA EKRANI (/mobil)
    public function mobilePwa(Request $request)
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();

        // Çıkış yapılmak istendiyse
        if ($request->boolean('cikis')) {
            session()->forget('logged_parent_phone');
            return redirect()->route('public.mobile.pwa');
        }

        // Telefon numarası formdan veya URL'den geldiyse doğrula
        $inputPhone = $request->input('phone');
        $loginError = null;

        if ($inputPhone !== null) {
            $cleanInput = preg_replace('/\D+/', '', $inputPhone);
            if (strlen($cleanInput) >= 9) {
                $matchedCount = Student::where('parent_phone', 'like', '%' . substr($cleanInput, -9) . '%')
                    ->orWhere('secondary_parent_phone', 'like', '%' . substr($cleanInput, -9) . '%')
                    ->count();

                if ($matchedCount > 0) {
                    session(['logged_parent_phone' => $inputPhone]);
                } else {
                    $loginError = 'Bu telefon numarasına bağlı aktif bir öğrenci kaydı bulunamadı. Lütfen salon sekreterliğimizle iletişime geçin.';
                }
            } else {
                $loginError = 'Lütfen geçerli bir cep telefonu numarası girin (Ör: 0533 222 33 44).';
            }
        }

        $activePhone = $inputPhone && !$loginError ? $inputPhone : session('logged_parent_phone');
        $students = collect();
        $availableMakeupGroups = collect();

        if ($activePhone) {
            $cleanPhone = preg_replace('/\D+/', '', $activePhone);
            $students = Student::with([
                'branch',
                'group.schedules',
                'group.coach',
                'package',
                'discountRule',
                'attendances' => fn($q) => $q->latest('attendance_date')->take(10),
                'invoices.payments',
                'developmentRecords.coach',
            ])->where('parent_phone', 'like', '%' . substr($cleanPhone, -9) . '%')
              ->orWhere('secondary_parent_phone', 'like', '%' . substr($cleanPhone, -9) . '%')
              ->get();

            // Telafi sekmesindeysek, kapasitesi olan (örneğin < 15 öğrenci) diğer grupları çek
            if (request()->query('sekme') === 'telafi' && $students->sum('makeup_credits') > 0) {
                $availableMakeupGroups = \App\Models\Group::with(['schedules', 'coach'])
                    ->where('is_active', true)
                    ->withCount('activeStudents')
                    ->having('active_students_count', '<', 15)
                    ->get();
            }
        }

        $sekme = $request->query('sekme', 'cocuklarim'); // cocuklarim, aidat, karne, duyurular
        $announcements = Announcement::whereIn('target_audience', ['all', 'parents'])->latest()->take(6)->get();

        return view('public.mobile-app', compact('students', 'activePhone', 'loginError', 'sekme', 'announcements', 'settings', 'availableMakeupGroups'));
            'activePhone',
            'loginError',
            'students',
            'sekme',
            'settings',
            'announcements'
        ));
    }

    // 3. VELİ SALONA GELDİĞİNDE QR OKUTUP YOKLAMA YAPMA EKRANI (/qr-yoklama)
    public function qrCheckinPage(Request $request)
    {
        $phone = $request->query('phone', session('logged_parent_phone', ''));
        $cleanPhone = preg_replace('/\D+/', '', (string) $phone);
        $studentId = $request->query('student_id');

        $students = collect();
        if ($studentId) {
            $students = Student::with(['branch', 'group.coach', 'group.schedules', 'attendances' => fn($q) => $q->whereDate('attendance_date', today())])
                ->where('id', $studentId)
                ->get();
        } elseif (strlen($cleanPhone) >= 9) {
            $students = Student::with(['branch', 'group.coach', 'group.schedules', 'attendances' => fn($q) => $q->whereDate('attendance_date', today())])
                ->where('parent_phone', 'like', '%' . substr($cleanPhone, -9) . '%')
                ->orWhere('secondary_parent_phone', 'like', '%' . substr($cleanPhone, -9) . '%')
                ->get();
        }

        $todayCheckins = Attendance::with(['student', 'group'])
            ->whereDate('attendance_date', today())
            ->where('status', 'present')
            ->latest('updated_at')
            ->take(10)
            ->get();

        $settings = SiteSetting::pluck('value', 'key')->toArray();

        return view('public.qr-checkin', compact('students', 'phone', 'todayCheckins', 'settings'));
    }

    public function submitKioskCheckin(Request $request)
    {
        $qrData = $request->input('qr_data'); // Örn: ST-ID-5

        if (!$qrData || !str_starts_with($qrData, 'ST-ID-')) {
            return response()->json(['error' => 'Geçersiz QR Kod formatı.'], 400);
        }

        $studentId = str_replace('ST-ID-', '', $qrData);
        $student = Student::with('group')->find($studentId);

        if (!$student) {
            return response()->json(['error' => 'Öğrenci bulunamadı.'], 404);
        }

        $groupId = $student->group_id ?? \App\Models\Group::first()?->id;

        Attendance::updateOrCreate(
            [
                'group_id' => $groupId,
                'student_id' => $student->id,
                'attendance_date' => today()->toDateString(),
            ],
            [
                'coach_id' => $student->group?->coach_id ?? User::where('role', 'coach')->value('id'),
                'status' => 'present',
                'note' => 'Kiosk Modu: Öğrenci özel QR kodu ile salon giriş yaptı (' . now()->format('H:i') . ')',
            ]
        );

        $student->update(['consecutive_absences' => 0]);

        return response()->json([
            'success' => true,
            'message' => "Hoş geldin, {$student->full_name}! Yoklaman başarıyla alındı.",
            'student_name' => $student->full_name
        ]);
    }

    public function submitQrCheckin(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'checkin_type' => 'nullable|in:present,makeup',
        ]);

        $student = Student::with('group')->findOrFail($validated['student_id']);
        $groupId = $student->group_id ?? Group::first()?->id;
        $status = $validated['checkin_type'] ?? 'present';

        Attendance::updateOrCreate(
            [
                'group_id' => $groupId,
                'student_id' => $student->id,
                'attendance_date' => today()->toDateString(),
            ],
            [
                'coach_id' => $student->group?->coach_id ?? User::where('role', 'coach')->value('id'),
                'status' => $status,
                'note' => 'Veli Salon Giriş QR Kodu ile yoklama yaptı (' . now()->format('H:i') . ')',
            ]
        );

        $student->update(['consecutive_absences' => 0]);
        if ($status === 'makeup' && $student->makeup_credits > 0) {
            $student->decrement('makeup_credits');
        }

        AuditLog::record(
            'veli_qr_yoklama',
            "Veli QR okutarak salon giriş yoklaması yaptı: {$student->full_name} (Grup: {$student->group?->name}, Saat: " . now()->format('H:i') . ")",
            'Student',
            $student->id
        );

        return redirect()->route('public.qr.checkin', ['phone' => $student->parent_phone])
            ->with('checkin_success', "✅ {$student->full_name} için bugünkü (" . today()->format('d.m.Y') . " - Saat " . now()->format('H:i') . ") salon giriş yoklaması başarıyla alındı! Antrenör ekranına iletildi. İyi antrenmanlar dileriz! 🤸");
    }

    public function reserveMakeup(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'group_id' => 'required|exists:groups,id',
            'attendance_date' => 'required|date|after_or_equal:today',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        if ($student->makeup_credits <= 0) {
            return back()->with('loginError', 'Kullanılabilir telafi hakkınız bulunmamaktadır.');
        }

        // Aynı gün ve aynı grupta mükerrer telafi kaydı kontrolü
        $exists = Attendance::where('student_id', $student->id)
            ->where('group_id', $validated['group_id'])
            ->where('attendance_date', $validated['attendance_date'])
            ->exists();

        if ($exists) {
            return back()->with('loginError', 'Bu tarihteki gruba zaten telafi kaydınız bulunmaktadır.');
        }

        // Telafi hakkını 1 azalt ve randevu oluştur
        $student->decrement('makeup_credits');

        $group = \App\Models\Group::with('coach')->find($validated['group_id']);

        Attendance::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'attendance_date' => $validated['attendance_date'],
            'coach_id' => $group->coach_id,
            'status' => 'makeup',
            'note' => 'Veli self-service telafi rezervasyonu.',
        ]);

        return back()->with('loginError', '✅ Telafi randevunuz başarıyla oluşturuldu! Belirtilen gün ve saatte salonda olabilirsiniz.')->withInput(['phone' => $student->parent_phone]);
    }

    public function syncMobileAttendance(Request $request)
    {
        $records = $request->input('records', []);
        $coachId = User::where('role', 'coach')->value('id');
        foreach ($records as $rec) {
            Attendance::updateOrCreate(
                [
                    'group_id' => $rec['group_id'],
                    'student_id' => $rec['student_id'],
                    'attendance_date' => $rec['attendance_date'] ?? today()->toDateString(),
                ],
                [
                    'coach_id' => $coachId,
                    'status' => $rec['status'] ?? 'present',
                ]
            );
        }
        return response()->json(['success' => true, 'count' => count($records)]);
    }

    public function qrPoster()
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        return view('public.qr-poster', compact('settings'));
    }

    public function receiptView(Payment $payment)
    {
        $payment->load(['student.branch', 'student.group', 'invoice', 'cashAccount', 'recorder']);
        $settings = SiteSetting::pluck('value', 'key')->toArray();

        return view('public.receipt', compact('payment', 'settings'));
    }
}
