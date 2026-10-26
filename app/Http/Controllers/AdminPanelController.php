<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\Concerns\ManagesAttendance;
use App\Http\Controllers\Admin\Concerns\ManagesCms;
use App\Http\Controllers\Admin\Concerns\ManagesEventsAndDevelopment;
use App\Http\Controllers\Admin\Concerns\ManagesFinance;
use App\Http\Controllers\Admin\Concerns\ManagesGroupsAndSchedules;
use App\Http\Controllers\Admin\Concerns\ManagesLeads;
use App\Http\Controllers\Admin\Concerns\ManagesPackagesAndDiscounts;
use App\Http\Controllers\Admin\Concerns\ManagesStaff;
use App\Http\Controllers\Admin\Concerns\ManagesStudents;
use App\Http\Controllers\Admin\Concerns\ManagesSystem;
use App\Http\Controllers\Admin\Concerns\ManagesWhatsapp;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\DevelopmentRecord;
use App\Models\DiscountRule;
use App\Models\Event;
use App\Models\Faq;
use App\Models\FinancialTransaction;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\NavMenuItem;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Yönetim paneli ana controller'ı.
 *
 * 16 modülün store/update/destroy metodları, bakımı kolaylaştırmak için modül bazlı
 * trait'lere (App\Http\Controllers\Admin\Concerns\*) ayrılmıştır. Her trait, bu
 * sınıftaki getAuthenticatedStaff()/assertCoachOwnsGroup() helper'larını ve index()
 * tarafından render edilen admin.panel view'ını paylaşır.
 */
class AdminPanelController extends Controller
{
    use ManagesLeads;
    use ManagesStudents;
    use ManagesGroupsAndSchedules;
    use ManagesAttendance;
    use ManagesPackagesAndDiscounts;
    use ManagesFinance;
    use ManagesStaff;
    use ManagesWhatsapp;
    use ManagesEventsAndDevelopment;
    use ManagesCms;
    use ManagesSystem;

    protected function getAuthenticatedStaff(): ?User
    {
        // admin.auth middleware'i tüm /panel rotalarında bu attribute'u set eder;
        // burada ekstra bir sorgu atmadan aynı kullanıcıyı tekrar kullanırız.
        $cached = request()->attributes->get('authStaff');
        if ($cached instanceof User) {
            return $cached;
        }

        // Middleware'in kapsamadığı giriş/çıkış ekranları için geriye dönük sorgu.
        $userId = session('admin_user_id');
        if (!$userId) {
            return null;
        }

        return User::whereIn('role', ['super_admin', 'secretary', 'coach'])
            ->where('is_active', true)
            ->find($userId);
    }

    /**
     * "only_own_groups" iznine sahip bir antrenör, sadece kendisine atanmış gruplar/
     * öğrenciler üzerinde yazma işlemi yapabilir. index() bu kısıtlamayı sadece
     * görüntülemede uyguluyordu; store/update/destroy rotalarında hiç kontrol yoktu
     * (bir antrenör URL'yi bilerek başka bir grubu/öğrenciyi değiştirebiliyordu).
     * Bu metod ilgili yazma metodlarının en başında çağrılır.
     */
    protected function assertCoachOwnsGroup(?int $groupId): void
    {
        $authUser = $this->getAuthenticatedStaff();
        if (!$authUser || $authUser->role !== 'coach' || !$authUser->hasPermission('only_own_groups', true)) {
            return;
        }

        if (!$groupId || !Group::where('id', $groupId)->where('coach_id', $authUser->id)->exists()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('admin.index')->with('error', 'Bu gruba/öğrenciye erişim yetkiniz bulunmamaktadır.')
            );
        }
    }

    public function loginForm()
    {
        if ($this->getAuthenticatedStaff()) {
            return redirect()->route('admin.index');
        }
        return view('admin.login');
    }

    public function loginSubmit(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'password' => 'required|string',
        ]);

        $identity = strtolower(trim($request->input('identity')));
        $password = $request->input('password');
        $cleanDigits = preg_replace('/\D+/', '', $identity);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : null;

        // yonetici@ ve yonetim@ her ikisini de destekle
        $emailsToMatch = [$identity];
        if ($identity === 'yonetim@enerjikcimnastik.com') {
            $emailsToMatch[] = 'yonetici@enerjikcimnastik.com';
        } elseif ($identity === 'yonetici@enerjikcimnastik.com') {
            $emailsToMatch[] = 'yonetim@enerjikcimnastik.com';
        }

        $user = User::whereIn('role', ['super_admin', 'secretary', 'coach'])
            ->where(function ($q) use ($emailsToMatch, $last10) {
                $q->whereIn('email', $emailsToMatch);
                if ($last10) {
                    $q->orWhere('phone', 'like', '%' . $last10 . '%');
                }
            })
            ->first();

        $passwordValid = $user && Hash::check($password, $user->password);

        if (!$user || !$passwordValid) {
            return back()->withInput()->with('error', 'Giriş bilgileri hatalı! Lütfen e-posta/telefon veya şifrenizi kontrol edin.');
        }

        if (!$user->is_active) {
            return back()->withInput()->with('error', 'Hesabınız pasif durumdadır. Lütfen Salon Yöneticisi ile iletişime geçin.');
        }

        session([
            'admin_user_id' => $user->id,
            'admin_role' => $user->role,
        ]);

        AuditLog::record('yonetici_girisi', "{$user->name} ({$user->roleLabel()}) yönetim paneline giriş yaptı.", 'User', $user->id);

        return redirect()->route('admin.index')->with('success', "Hoş geldiniz {$user->name}! ({$user->roleLabel()}) yetkisiyle giriş yapıldı.");
    }

    public function logout()
    {
        $user = $this->getAuthenticatedStaff();
        if ($user) {
            AuditLog::record('yonetici_cikisi', "{$user->name} yönetim panelinden çıkış yaptı.", 'User', $user->id);
        }
        session()->forget(['admin_user_id', 'admin_role']);
        return redirect()->route('admin.login')->with('success', 'Güvenli çıkış yapıldı.');
    }

    public function index(Request $request)
    {
        $authUser = $this->getAuthenticatedStaff();
        if (!$authUser) {
            return redirect()->route('admin.login');
        }

        $activeTab = $request->query('tab', 'pano');

        // Sadece Süper Yönetici diğer rolleri önizleyebilir; Sekreter ve Antrenör kendi rolüne kilitlidir
        if ($authUser->role === 'super_admin') {
            $currentRole = $request->query('role_view', 'super_admin');
        } else {
            $currentRole = $authUser->role;
        }

        // Yetkili Sekme Kontrolü
        $allowedTabs = $authUser->allowedTabs();
        if ($authUser->role === 'super_admin' && $currentRole !== 'super_admin') {
            $previewUser = new User(['role' => $currentRole]);
            $allowedTabs = $previewUser->allowedTabs();
        }

        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'pano';
        }

        // Her sekmede görünen üst bar rozeti: gönderilmeyi bekleyen WhatsApp mesajı sayısı
        // (yeni öğrenci/PIN yenileme gibi işlemler mesajı otomatik kuyruğa yazar ama
        // göndermek hâlâ elle tetiklenir; bu rozet unutulmasını önlemek içindir).
        $pendingWhatsappCount = WhatsappMessage::where('status', 'queued')->count();

        // 1. Pano İstatistikleri
        $todayCollection = Payment::whereDate('payment_date', today())->sum('amount');
        $monthCollection = Payment::whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount');
        $overdueTotal = Invoice::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->sum('remaining_amount');
        $pendingTotal = Invoice::whereIn('status', ['unpaid', 'partial'])->sum('remaining_amount');
        $activeStudentsCount = Student::where('status', 'active')->count();
        $waitingListCount = Student::where('status', 'waiting_list')->count();
        $newLeadsCount = Lead::whereIn('status', ['new', 'trial_scheduled', 'thinking'])->count();

        // Antrenör sadece kendi gruplarını görsün mü?
        $restrictToCoachId = ($authUser->role === 'coach' && $authUser->hasPermission('only_own_groups', true))
            ? $authUser->id
            : null;

        // Pano: Bugünkü ve Son 7 Günlük Yoklama Özeti (antrenör görünümünde kendi gruplarıyla sınırlı)
        $todayAttendanceCounts = Attendance::whereDate('attendance_date', today())
            ->when($restrictToCoachId, fn($q) => $q->where('coach_id', $restrictToCoachId))
            ->select('status', DB::raw('count(*) as toplam'))
            ->groupBy('status')
            ->pluck('toplam', 'status');
        $todayAttendanceTotal = (int) $todayAttendanceCounts->sum();
        $todayAttendanceRate = $todayAttendanceTotal > 0
            ? round((((int) ($todayAttendanceCounts['present'] ?? 0) + (int) ($todayAttendanceCounts['makeup'] ?? 0)) / $todayAttendanceTotal) * 100, 1)
            : null;

        $weekAttendanceRow = Attendance::whereBetween('attendance_date', [today()->subDays(6), today()])
            ->when($restrictToCoachId, fn($q) => $q->where('coach_id', $restrictToCoachId))
            ->selectRaw("sum(case when status in ('present','makeup') then 1 else 0 end) as gelen, count(*) as toplam")
            ->first();
        $weekAttendanceRate = $weekAttendanceRow && $weekAttendanceRow->toplam > 0
            ? round(($weekAttendanceRow->gelen / $weekAttendanceRow->toplam) * 100, 1)
            : null;

        // Veriler
        $branches = Branch::withCount('students')->orderBy('sort_order')->get();
        $groupsQuery = Group::with(['branch', 'coach', 'schedules'])->withCount('activeStudents');
        if ($restrictToCoachId) {
            $groupsQuery->where('coach_id', $restrictToCoachId);
        }
        $groups = $groupsQuery->get();

        $schedulesQuery = Schedule::with(['group.branch', 'group.coach'])->orderBy('start_time');
        if ($restrictToCoachId) {
            $schedulesQuery->whereHas('group', fn($q) => $q->where('coach_id', $restrictToCoachId));
        }
        $schedules = $schedulesQuery->get();

        // Aday, fatura, tahsilat, kasa hareketi ve WhatsApp kuyruğu zamanla sınırsız büyüyen
        // tablolardır; tüm kayıtları her panel açılışında çekmek performans sorununa yol açar.
        // Tam sayfalama (paginate) tek sayfalı sekme yapısını bozacağından, en güncel N kayda
        // sınırlandırılır (audit log ve yoklamada zaten uygulanan aynı desen).
        $leads = Lead::with(['branch', 'trialGroup'])->latest()->take(200)->get();

        $studentsQuery = Student::with(['branch', 'group', 'package', 'discountRule', 'siblings', 'invoices'])->latest();
        if ($restrictToCoachId) {
            $studentsQuery->whereHas('group', fn($q) => $q->where('coach_id', $restrictToCoachId));
        }
        $students = $studentsQuery->get();

        $packages = Package::all();
        $discountRules = DiscountRule::all();
        $invoices = Invoice::with(['student.group', 'package', 'payments'])->latest('due_date')->take(300)->get();
        $payments = Payment::with(['student', 'invoice', 'cashAccount', 'recorder'])->latest('payment_date')->take(200)->get();
        $cashAccounts = CashAccount::all();
        $transactions = FinancialTransaction::with(['cashAccount', 'relatedUser'])->latest('transaction_date')->take(200)->get();
        $staff = User::whereIn('role', ['super_admin', 'secretary', 'coach'])->withCount('groups')->get();

        // "only_own_groups" antrenörleri yoklama ve gelişim karnesi listelerinde de
        // kendi gruplarıyla sınırlanmalı — aksi hâlde salon genelindeki tüm öğrencilerin
        // yoklama/gelişim/sağlık/devamsızlık bilgilerini görebilirlerdi (bkz. $groups/
        // $students'taki aynı $restrictToCoachId deseni, 157. satır civarı).
        $attendancesQuery = Attendance::with(['group', 'student', 'coach'])->latest('attendance_date');
        if ($restrictToCoachId) {
            $attendancesQuery->where('coach_id', $restrictToCoachId);
        }
        $attendances = $attendancesQuery->take(50)->get();

        $developmentRecordsQuery = DevelopmentRecord::with(['student.group', 'coach'])->latest();
        if ($restrictToCoachId) {
            $developmentRecordsQuery->where('coach_id', $restrictToCoachId);
        }
        $developmentRecords = $developmentRecordsQuery->get();

        $events = Event::with('participants')->latest('event_date')->get();
        $whatsappTemplates = WhatsappTemplate::all();
        $whatsappQueue = WhatsappMessage::with(['template', 'student'])->latest()->take(150)->get();
        $announcements = Announcement::latest()->get();
        $faqs = Faq::orderBy('sort_order')->get();
        $gallery = GalleryItem::with('student')->latest()->get();
        $navMenuItems = NavMenuItem::orderBy('sort_order')->get();
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        $auditLogs = AuditLog::with('user')->latest()->take(60)->get();

        // Dinamik Seçenek Listeleri (Süper Yönetici Tarafından Eklenip Düzenlenebilir & Silinebilir)
        $hallAreas = array_values(array_filter(array_map('trim', explode('|', $settings['custom_hall_areas'] ?? 'Ana Minder|Trampolin Alanı|Ana Minder & Bar Alanı|Bale & Ritmik Salonu'))));
        $ageGroups = array_values(array_filter(array_map('trim', explode('|', $settings['custom_age_groups'] ?? '3-4 Yaş (Oyun)|4-6 Yaş (Minikler)|5-8 Yaş|7-10 Yaş (Yıldızlar)|9-14 Yaş (Gençler)|16+ Yaş (Yetişkin)'))));
        $groupLevels = array_values(array_filter(array_map('trim', explode('|', $settings['custom_group_levels'] ?? 'Başlangıç|Orta Seviye|İleri Seviye|Performans / Yarışmacı|Hobi Grubu'))));
        $financeCategories = array_values(array_filter(array_map('trim', explode('|', $settings['custom_finance_categories'] ?? 'Kira|Elektrik / Su / Doğalgaz|Maaş ve SGK|Antrenör Hakedişi|Ekipman ve Bakım|Temizlik|Reklam (Instagram/Google)|Ürün Satışı (Mayo / Forma)|Lisans ve Yarışma Harcı'))));
        $leadSources = array_values(array_filter(array_map('trim', explode('|', $settings['custom_lead_sources'] ?? 'website|instagram|whatsapp|telefon|tavsiye'))));

        // Özel Uyarı Listeleri (Periyodik İşler) — aynı şekilde antrenör kendi grubuyla sınırlı
        $absentWarnStudentsQuery = Student::with('group')->where('consecutive_absences', '>=', 2)->where('status', 'active');
        if ($restrictToCoachId) {
            $absentWarnStudentsQuery->whereHas('group', fn($q) => $q->where('coach_id', $restrictToCoachId));
        }
        $absentWarnStudents = $absentWarnStudentsQuery->get();

        $expiringMedicalStudentsQuery = Student::with('group')->whereNotNull('medical_report_expires_at')
            ->whereDate('medical_report_expires_at', '<=', now()->addDays(15))
            ->where('status', 'active');
        if ($restrictToCoachId) {
            $expiringMedicalStudentsQuery->whereHas('group', fn($q) => $q->where('coach_id', $restrictToCoachId));
        }
        $expiringMedicalStudents = $expiringMedicalStudentsQuery->get();

        // Antrenör Hakediş Hesabı (Bu Ay Yoklamadan Otomatik)
        // Not: whereMonth() yıl kontrolü yapmadığından önceki yıllarda aynı ayda
        // alınmış yoklamalar da sayıma karışıyordu; whereYear eklendi.
        // lessonsGiven = coach_id'nin GERÇEKTEN yoklama aldığı ayrı gün sayısı; bu
        // ay hiç yoklama alınmamışsa (yeni işe başlama, izin, hiç ders verilmemiş
        // hafta) tahmini taban (grup sayısı × 8) yalnızca o durumda kullanılır —
        // eskiden max() ile taban her zaman GERÇEK sayıyı ezip fazla ödemeye yol açıyordu.
        $lessonsGivenByCoach = Attendance::where('coach_id', '!=', null)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->selectRaw('coach_id, count(distinct attendance_date) as lesson_days')
            ->groupBy('coach_id')
            ->pluck('lesson_days', 'coach_id');

        $groupCountByCoach = Group::where('coach_id', '!=', null)
            ->selectRaw('coach_id, count(*) as grp_count')
            ->groupBy('coach_id')
            ->pluck('grp_count', 'coach_id');

        $coachPayrolls = User::where('role', 'coach')->get()->map(function ($coach) use ($lessonsGivenByCoach, $groupCountByCoach) {
            $lessonsGiven = (int) ($lessonsGivenByCoach[$coach->id] ?? 0);
            $estimatedBaseline = (int) ($groupCountByCoach[$coach->id] ?? 0) * 8;
            $lessonCount = $lessonsGiven > 0 ? $lessonsGiven : $estimatedBaseline;
            $lessonFeeTotal = ($coach->compensation_type === 'per_lesson' || $coach->compensation_type === 'both')
                ? $lessonCount * $coach->per_lesson_rate
                : 0;
            $base = ($coach->compensation_type === 'fixed' || $coach->compensation_type === 'both')
                ? $coach->base_salary
                : 0;
            return [
                'coach' => $coach,
                'lesson_count' => $lessonCount,
                'is_estimated' => $lessonsGiven === 0,
                'base_salary' => $base,
                'lesson_earnings' => $lessonFeeTotal,
                'total_payable' => $base + $lessonFeeTotal,
            ];
        });

        // Yaşlandırma Raporu (0-30, 31-60, 61-90+ gün gecikenler)
        $agingBuckets = [
            '0_30' => Invoice::whereIn('status', ['unpaid', 'partial'])->whereBetween('due_date', [now()->subDays(30), today()])->sum('remaining_amount'),
            '31_60' => Invoice::whereIn('status', ['unpaid', 'partial'])->whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])->sum('remaining_amount'),
            '61_plus' => Invoice::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', now()->subDays(60))->sum('remaining_amount'),
        ];

        // Aylık Gelir-Gider Trendi (Raporlar Grafiği — son 12 ay)
        // Gelir = tahsilat (Payment) + diğer gelir hareketleri (FinancialTransaction
        // direction=income, ör. ürün satışı); Gider = FinancialTransaction direction=expense.
        $monthlyFinanceTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = (clone $monthStart)->endOfMonth();

            $paymentIncome = (float) Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount');
            $otherIncome = (float) FinancialTransaction::where('direction', 'income')->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount');
            $expense = (float) FinancialTransaction::where('direction', 'expense')->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount');

            $monthlyFinanceTrend[] = [
                'label' => $monthStart->translatedFormat('M Y'),
                'gelir' => $paymentIncome + $otherIncome,
                'gider' => $expense,
            ];
        }

        // Yoklama Analiz & Raporları (Çoklu Kriterli Filtre + Grafik Verisi)
        $attFrom = $request->query('att_from', today()->subDays(29)->toDateString());
        $attTo = $request->query('att_to', today()->toDateString());
        $attBranchId = $request->query('att_branch_id');
        $attGroupId = $request->query('att_group_id');
        $attCoachId = $request->query('att_coach_id');

        $attendanceBaseQuery = function () use ($attFrom, $attTo, $attGroupId, $attCoachId, $attBranchId) {
            return Attendance::query()
                ->whereBetween('attendances.attendance_date', [$attFrom, $attTo])
                ->when($attGroupId, fn($q) => $q->where('attendances.group_id', $attGroupId))
                ->when($attCoachId, fn($q) => $q->where('attendances.coach_id', $attCoachId))
                ->when($attBranchId, fn($q) => $q->whereHas('group', fn($gq) => $gq->where('branch_id', $attBranchId)));
        };

        $attendanceStatusCounts = $attendanceBaseQuery()
            ->select('attendances.status', DB::raw('count(*) as toplam'))
            ->groupBy('attendances.status')
            ->pluck('toplam', 'status');

        $attendanceTotal = (int) $attendanceStatusCounts->sum();
        $attendancePresentLike = (int) ($attendanceStatusCounts['present'] ?? 0) + (int) ($attendanceStatusCounts['makeup'] ?? 0);
        $attendanceRate = $attendanceTotal > 0 ? round($attendancePresentLike / $attendanceTotal * 100, 1) : 0;

        $attendanceDailyTrend = $attendanceBaseQuery()
            ->select(
                'attendances.attendance_date',
                DB::raw("sum(case when attendances.status in ('present','makeup') then 1 else 0 end) as gelen"),
                DB::raw('count(*) as toplam')
            )
            ->groupBy('attendances.attendance_date')
            ->orderBy('attendances.attendance_date')
            ->get();

        $attendanceByBranch = $attendanceBaseQuery()
            ->join('groups', 'groups.id', '=', 'attendances.group_id')
            ->join('branches', 'branches.id', '=', 'groups.branch_id')
            ->select(
                'branches.name as branch_name',
                DB::raw("sum(case when attendances.status in ('present','makeup') then 1 else 0 end) as gelen"),
                DB::raw('count(*) as toplam')
            )
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('toplam')
            ->get();

        $attendanceByGroup = $attendanceBaseQuery()
            ->join('groups', 'groups.id', '=', 'attendances.group_id')
            ->select(
                'groups.name as group_name',
                DB::raw("sum(case when attendances.status in ('present','makeup') then 1 else 0 end) as gelen"),
                DB::raw('count(*) as toplam')
            )
            ->groupBy('groups.id', 'groups.name')
            ->orderByDesc('toplam')
            ->get();

        $attendanceTopAbsentees = $attendanceBaseQuery()
            ->where('attendances.status', 'absent')
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->select('students.id as student_id', 'students.full_name', DB::raw('count(*) as devamsizlik'))
            ->groupBy('students.id', 'students.full_name')
            ->orderByDesc('devamsizlik')
            ->take(10)
            ->get();

        return view('admin.panel', compact(
            'authUser',
            'allowedTabs',
            'activeTab',
            'currentRole',
            'pendingWhatsappCount',
            'todayCollection',
            'monthCollection',
            'overdueTotal',
            'pendingTotal',
            'activeStudentsCount',
            'waitingListCount',
            'newLeadsCount',
            'branches',
            'groups',
            'schedules',
            'leads',
            'students',
            'packages',
            'discountRules',
            'invoices',
            'payments',
            'cashAccounts',
            'transactions',
            'staff',
            'attendances',
            'developmentRecords',
            'events',
            'whatsappTemplates',
            'whatsappQueue',
            'announcements',
            'faqs',
            'gallery',
            'navMenuItems',
            'settings',
            'auditLogs',
            'absentWarnStudents',
            'expiringMedicalStudents',
            'coachPayrolls',
            'agingBuckets',
            'monthlyFinanceTrend',
            'attFrom',
            'attTo',
            'attBranchId',
            'attGroupId',
            'attCoachId',
            'attendanceStatusCounts',
            'attendanceTotal',
            'attendanceRate',
            'attendanceDailyTrend',
            'attendanceByBranch',
            'attendanceByGroup',
            'attendanceTopAbsentees',
            'todayAttendanceCounts',
            'todayAttendanceTotal',
            'todayAttendanceRate',
            'weekAttendanceRate',
            'hallAreas',
            'ageGroups',
            'groupLevels',
            'financeCategories',
            'leadSources'
        ));
    }
}
