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
        $coaches = User::where('role', 'coach')->where('is_active', true)->where('show_on_website', true)->get();
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

        // Not: "trial_reminder" şablonu ({tarih}/{saat} içerir) deneme dersinden 1 gün
        // önce otomatik hatırlatma içindir (bkz. routes/console.php), bu an için değil —
        // bu yüzden burada o şablona bağlanmıyor, kendi sabit karşılama metnini kullanıyor.
        $msgText = "Merhaba {$lead->parent_name} 👋 Enerjik Cimnastik Spor Kulübü'ne {$lead->student_name} için yaptığınız ücretsiz deneme dersi başvurusu alınmıştır! Uygun grup ve saat planlaması için sizinle iletişime geçiyoruz. 🤸";

        WhatsappMessage::create([
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
    // Telefon numarası TEK BAŞINA giriş için yeterli değildir: numarayı bilen/tahmin
    // eden herkesin çocuğun finansal/sağlık bilgilerine erişebilmesini önlemek için
    // kayıt sırasında aileye verilen 4 haneli PIN de doğrulanır (bkz. Student::portal_pin).
    public function mobilePwa(Request $request)
    {
        $settings = SiteSetting::pluck('value', 'key')->toArray();

        // Çıkış yapılmak istendiyse
        if ($request->boolean('cikis')) {
            session()->forget(['logged_parent_phone', 'logged_parent_pin']);
            return redirect()->route('public.mobile.pwa');
        }

        // Telefon + PIN formdan geldiyse doğrula ve oturuma yaz
        $inputPhone = $request->input('phone');
        $loginError = null;

        if ($inputPhone !== null) {
            $cleanInput = preg_replace('/\D+/', '', $inputPhone);
            $cleanPin = preg_replace('/\D+/', '', (string) $request->input('pin'));

            if (strlen($cleanInput) < 9) {
                $loginError = 'Lütfen geçerli bir cep telefonu numarası girin (Ör: 0533 222 33 44).';
            } elseif (strlen($cleanPin) !== 4) {
                $loginError = 'Lütfen kayıt sırasında size verilen 4 haneli PIN kodunu girin.';
            } else {
                $matched = Student::where(function ($q) use ($cleanInput) {
                    $q->where('parent_phone', 'like', '%' . substr($cleanInput, -9) . '%')
                      ->orWhere('secondary_parent_phone', 'like', '%' . substr($cleanInput, -9) . '%');
                })->where('portal_pin', $cleanPin)->exists();

                if ($matched) {
                    session(['logged_parent_phone' => $inputPhone, 'logged_parent_pin' => $cleanPin]);
                } else {
                    $loginError = 'Telefon numarası veya PIN kodu hatalı. PIN kodunuzu salon sekreterliğinden öğrenebilirsiniz.';
                }
            }
        }

        $activePhone = session('logged_parent_phone');
        $activePin = session('logged_parent_pin');
        $students = collect();

        if ($activePhone && $activePin) {
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
            ])->where(function ($q) use ($cleanPhone) {
                $q->where('parent_phone', 'like', '%' . substr($cleanPhone, -9) . '%')
                  ->orWhere('secondary_parent_phone', 'like', '%' . substr($cleanPhone, -9) . '%');
            })->where('portal_pin', $activePin)->get();
        }

        $sekme = $request->query('sekme', 'cocuklarim'); // cocuklarim, aidat, karne, duyurular
        $announcements = Announcement::whereIn('target_audience', ['all', 'parents'])->latest()->take(6)->get();

        $turkishDays = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $todayName = $turkishDays[now()->dayOfWeekIso] ?? null;

        return view('public.mobile-app', compact(
            'activePhone',
            'loginError',
            'students',
            'sekme',
            'settings',
            'announcements',
            'todayName'
        ));
    }

    // 3. VELİ SALONA GELDİĞİNDE QR OKUTUP YOKLAMA YAPMA EKRANI (/qr-yoklama)
    // Burada da telefon + PIN birlikte doğrulanmadan hiçbir öğrenci bilgisi
    // gösterilmez (bkz. mobilePwa() üstündeki not).
    public function qrCheckinPage(Request $request)
    {
        $loginError = null;
        $queryPhone = $request->query('phone');
        $queryPin = $request->query('pin');

        if ($queryPhone !== null && $queryPin !== null) {
            $cleanInput = preg_replace('/\D+/', '', $queryPhone);
            $cleanPin = preg_replace('/\D+/', '', (string) $queryPin);

            if (strlen($cleanInput) >= 9 && strlen($cleanPin) === 4) {
                $matched = Student::where(function ($q) use ($cleanInput) {
                    $q->where('parent_phone', 'like', '%' . substr($cleanInput, -9) . '%')
                      ->orWhere('secondary_parent_phone', 'like', '%' . substr($cleanInput, -9) . '%');
                })->where('portal_pin', $cleanPin)->exists();

                if ($matched) {
                    session(['logged_parent_phone' => $queryPhone, 'logged_parent_pin' => $cleanPin]);
                } else {
                    $loginError = 'Telefon numarası veya PIN kodu hatalı.';
                }
            } else {
                $loginError = 'Lütfen geçerli bir telefon numarası ve 4 haneli PIN kodu girin.';
            }
        }

        $phone = session('logged_parent_phone', '');
        $pin = session('logged_parent_pin', '');
        $cleanPhone = preg_replace('/\D+/', '', (string) $phone);

        $students = collect();
        if (strlen($cleanPhone) >= 9 && strlen((string) $pin) === 4) {
            $students = Student::with(['branch', 'group.coach', 'group.schedules', 'attendances' => fn($q) => $q->whereDate('attendance_date', today())])
                ->where(function ($q) use ($cleanPhone) {
                    $q->where('parent_phone', 'like', '%' . substr($cleanPhone, -9) . '%')
                      ->orWhere('secondary_parent_phone', 'like', '%' . substr($cleanPhone, -9) . '%');
                })->where('portal_pin', $pin)->get();
        }

        $todayCheckins = Attendance::with(['student', 'group'])
            ->whereDate('attendance_date', today())
            ->where('status', 'present')
            ->latest('updated_at')
            ->take(10)
            ->get();

        $settings = SiteSetting::pluck('value', 'key')->toArray();

        return view('public.qr-checkin', compact('students', 'phone', 'loginError', 'todayCheckins', 'settings'));
    }

    public function submitQrCheckin(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'checkin_type' => 'nullable|in:present,makeup',
        ]);

        // Yoklama yalnızca doğrulanmış (telefon+PIN oturumu açık) bir veli tarafından
        // yapılabilir ve sadece KENDİ çocuğu için — rastgele bir student_id denenemez.
        $phone = preg_replace('/\D+/', '', (string) session('logged_parent_phone', ''));
        $pin = session('logged_parent_pin', '');
        if (strlen($phone) < 9 || strlen((string) $pin) !== 4) {
            return redirect()->route('public.qr.checkin')->with('checkin_error', 'Yoklama yapabilmek için önce telefon numaranız ve PIN kodunuzla giriş yapmalısınız.');
        }

        $student = Student::with('group')
            ->where('id', $validated['student_id'])
            ->where(function ($q) use ($phone) {
                $q->where('parent_phone', 'like', '%' . substr($phone, -9) . '%')
                  ->orWhere('secondary_parent_phone', 'like', '%' . substr($phone, -9) . '%');
            })
            ->where('portal_pin', $pin)
            ->first();

        if (!$student) {
            return redirect()->route('public.qr.checkin')->with('checkin_error', 'Bu sporcu için yoklama yapma yetkiniz yok.');
        }

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

    // Veli Portalı: Ders programını telefon/bilgisayar takvimine (.ics) abonelik
    // olarak ekleme. Haftalık tekrarlı (RRULE) etkinlikler üretir; imzalı URL
    // (Student::calendarUrl()) sayesinde takvim uygulaması oturum açmadan periyodik
    // olarak bu adresi tekrar çekebilir.
    public function calendarIcs(Student $student)
    {
        $turkishToIcalDay = [
            'Pazartesi' => 'MO', 'Salı' => 'TU', 'Çarşamba' => 'WE', 'Perşembe' => 'TH',
            'Cuma' => 'FR', 'Cumartesi' => 'SA', 'Pazar' => 'SU',
        ];
        $turkishDayIso = [
            'Pazartesi' => 1, 'Salı' => 2, 'Çarşamba' => 3, 'Perşembe' => 4,
            'Cuma' => 5, 'Cumartesi' => 6, 'Pazar' => 7,
        ];

        $schedules = $student->group?->schedules ?? collect();

        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Enerjik Cimnastik//Ders Programi//TR', 'CALSCALE:GREGORIAN', 'X-WR-CALNAME:' . $student->full_name . ' - Antrenman Programı'];

        foreach ($schedules as $sch) {
            $isoDay = $turkishDayIso[$sch->day_of_week] ?? null;
            $icalDay = $turkishToIcalDay[$sch->day_of_week] ?? null;
            if (!$isoDay || !$icalDay) {
                continue;
            }

            $firstDate = today();
            while ($firstDate->dayOfWeekIso !== $isoDay) {
                $firstDate = $firstDate->addDay();
            }

            $dtStart = \Illuminate\Support\Carbon::parse($firstDate->toDateString() . ' ' . $sch->start_time, 'Europe/Istanbul')->setTimezone('UTC');
            $dtEnd = \Illuminate\Support\Carbon::parse($firstDate->toDateString() . ' ' . $sch->end_time, 'Europe/Istanbul')->setTimezone('UTC');

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:enerjik-schedule-' . $sch->id . '@enerjikcimnastik';
            $lines[] = 'DTSTAMP:' . now()->setTimezone('UTC')->format('Ymd\THis\Z');
            $lines[] = 'DTSTART:' . $dtStart->format('Ymd\THis\Z');
            $lines[] = 'DTEND:' . $dtEnd->format('Ymd\THis\Z');
            $lines[] = 'RRULE:FREQ=WEEKLY;BYDAY=' . $icalDay;
            $lines[] = 'SUMMARY:🤸 ' . ($student->group?->name ?? 'Antrenman') . ' Dersi';
            $lines[] = 'LOCATION:' . ($sch->area ?? 'Enerjik Cimnastik');
            $lines[] = 'DESCRIPTION:' . $student->full_name . ' için ' . ($student->group?->branch?->name ?? '') . ' antrenmanı.';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="enerjik-cimnastik-ders-programi.ics"',
        ]);
    }

    // SEO: Google/Bing gibi arama motorlarına sitedeki herkese açık sayfaları bildirir.
    // url('/') kullanıldığından (sabit domain yazılmadığından) local/Vercel/Natro
    // ortamlarının hepsinde APP_URL'e göre doğru adresleri üretir.
    public function sitemap()
    {
        $urls = [
            ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => route('public.qr.poster'), 'changefreq' => 'monthly', 'priority' => '0.3'],
        ];

        $xml = view('public.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
