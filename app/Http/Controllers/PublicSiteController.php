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
        return redirect()->route('public.mobile.pwa', ['rol' => 'veli', 'phone' => $request->query('phone', '05332223344')]);
    }

    // Tarayıcıdan Açılan Mobil Uygulama Ekranı (Ana Ekrana Kısayol Olarak Eklenir)
    public function mobilePwa(Request $request)
    {
        $rol = $request->query('rol', 'veli'); // veli, antrenor, yonetici
        $phone = $request->query('phone', '05332223344');
        $cleanPhone = preg_replace('/\D+/', '', $phone);

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

        $groups = Group::with(['branch', 'coach', 'schedules', 'activeStudents'])->where('is_active', true)->get();
        $overdueInvoices = Invoice::with('student.group')->whereIn('status', ['unpaid', 'partial'])->orderBy('due_date')->get();
        $cashAccounts = CashAccount::all();
        $whatsappQueue = WhatsappMessage::with(['template', 'student'])->where('status', 'queued')->latest()->get();
        $leads = Lead::with('branch')->whereIn('status', ['new', 'trial_scheduled', 'thinking'])->latest()->get();
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        $announcements = Announcement::latest()->take(6)->get();

        $stats = [
            'today_collection' => Payment::whereDate('payment_date', today())->sum('amount'),
            'month_collection' => Payment::whereMonth('payment_date', now()->month)->sum('amount'),
            'overdue_total' => $overdueInvoices->sum('remaining_amount'),
            'active_students' => Student::where('status', 'active')->count(),
        ];

        return view('public.mobile-app', compact(
            'rol',
            'phone',
            'students',
            'groups',
            'overdueInvoices',
            'cashAccounts',
            'whatsappQueue',
            'leads',
            'settings',
            'announcements',
            'stats'
        ));
    }

    // VELİ SALONA GELDİĞİNDE QR OKUTUP YOKLAMA YAPMA EKRANI (/qr-yoklama)
    public function qrCheckinPage(Request $request)
    {
        $phone = $request->query('phone', '05332223344');
        $cleanPhone = preg_replace('/\D+/', '', $phone);
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

        // Üst üste devamsızlık sayacını sıfırla; telafi kullandıysa telafi hakkını düş
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
