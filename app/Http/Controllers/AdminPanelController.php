<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Str;

class AdminPanelController extends Controller
{
    protected function getAuthenticatedStaff(): ?User
    {
        $userId = session('admin_user_id');
        if (!$userId) {
            return null;
        }
        $user = User::whereIn('role', ['super_admin', 'secretary', 'coach'])
            ->where('is_active', true)
            ->find($userId);

        return $user;
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

        $identity = trim($request->input('identity'));
        $password = $request->input('password');
        $cleanDigits = preg_replace('/\D+/', '', $identity);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : null;

        $user = User::whereIn('role', ['super_admin', 'secretary', 'coach'])
            ->where(function ($q) use ($identity, $last10) {
                $q->where('email', strtolower($identity));
                if ($last10) {
                    $q->orWhere('phone', 'like', '%' . $last10 . '%');
                }
            })
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
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

    public function updateStaffPermissions(Request $request, User $user)
    {
        $authUser = $this->getAuthenticatedStaff();
        if (!$authUser || $authUser->role !== 'super_admin') {
            return redirect()->route('admin.index')->with('error', 'Bu işlem yalnızca Süper Yönetici (Salon Sahibi) tarafından yapılabilir!');
        }

        $role = $request->input('role', $user->role);
        if (!in_array($role, ['super_admin', 'secretary', 'coach'], true)) {
            $role = $user->role;
        }

        $permissions = [
            'collect_payment' => $request->boolean('perm_collect_payment'),
            'view_expenses' => $request->boolean('perm_view_expenses'),
            'view_reports' => $request->boolean('perm_view_reports'),
            'send_whatsapp' => $request->boolean('perm_send_whatsapp'),
            'edit_content' => $request->boolean('perm_edit_content'),
            'delete_student' => $request->boolean('perm_delete_student'),
            'only_own_groups' => $request->boolean('perm_only_own_groups'),
            'take_attendance' => $request->boolean('perm_take_attendance'),
            'add_development' => $request->boolean('perm_add_development'),
        ];

        $updateData = [
            'role' => $role,
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $permissions,
        ];

        if ($request->filled('new_password')) {
            $updateData['password'] = Hash::make($request->input('new_password'));
        }

        $user->update($updateData);

        AuditLog::record('yetki_guncellendi', "{$user->name} personelinin rol ({$user->roleLabel()}) ve erişim yetkileri güncellendi.", 'User', $user->id);

        return redirect()->route('admin.index', ['tab' => 'personel'])->with('success', "{$user->name} için rol, erişim yetkileri ve giriş ayarları başarıyla güncellendi!");
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

        // 1. Pano İstatistikleri
        $todayCollection = Payment::whereDate('payment_date', today())->sum('amount');
        $monthCollection = Payment::whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount');
        $overdueTotal = Invoice::whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->sum('remaining_amount');
        $pendingTotal = Invoice::whereIn('status', ['unpaid', 'partial'])->sum('remaining_amount');
        $activeStudentsCount = Student::where('status', 'active')->count();
        $newLeadsCount = Lead::whereIn('status', ['new', 'trial_scheduled', 'thinking'])->count();

        // Antrenör sadece kendi gruplarını görsün mü?
        $restrictToCoachId = ($authUser->role === 'coach' && $authUser->hasPermission('only_own_groups', true))
            ? $authUser->id
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

        $leads = Lead::with(['branch', 'trialGroup'])->latest()->get();

        $studentsQuery = Student::with(['branch', 'group', 'package', 'discountRule', 'siblings', 'invoices'])->latest();
        if ($restrictToCoachId) {
            $studentsQuery->whereHas('group', fn($q) => $q->where('coach_id', $restrictToCoachId));
        }
        $students = $studentsQuery->get();

        $packages = Package::all();
        $discountRules = DiscountRule::all();
        $invoices = Invoice::with(['student.group', 'package', 'payments'])->latest('due_date')->get();
        $payments = Payment::with(['student', 'invoice', 'cashAccount', 'recorder'])->latest('payment_date')->get();
        $cashAccounts = CashAccount::all();
        $transactions = FinancialTransaction::with(['cashAccount', 'relatedUser'])->latest('transaction_date')->get();
        $staff = User::whereIn('role', ['super_admin', 'secretary', 'coach'])->withCount('groups')->get();
        $attendances = Attendance::with(['group', 'student', 'coach'])->latest('attendance_date')->take(50)->get();
        $developmentRecords = DevelopmentRecord::with(['student.group', 'coach'])->latest()->get();
        $events = Event::with('participants')->latest('event_date')->get();
        $whatsappTemplates = WhatsappTemplate::all();
        $whatsappQueue = WhatsappMessage::with(['template', 'student'])->latest()->get();
        $announcements = Announcement::latest()->get();
        $faqs = Faq::orderBy('sort_order')->get();
        $gallery = GalleryItem::with('student')->latest()->get();
        $settings = SiteSetting::pluck('value', 'key')->toArray();
        $auditLogs = AuditLog::with('user')->latest()->take(60)->get();

        // Özel Uyarı Listeleri (Periyodik İşler)
        $absentWarnStudents = Student::with('group')->where('consecutive_absences', '>=', 2)->where('status', 'active')->get();
        $expiringMedicalStudents = Student::with('group')->whereNotNull('medical_report_expires_at')
            ->whereDate('medical_report_expires_at', '<=', now()->addDays(15))
            ->where('status', 'active')
            ->get();

        // Antrenör Hakediş Hesabı (Bu Ay Yoklamadan Otomatik)
        $coachPayrolls = User::where('role', 'coach')->get()->map(function ($coach) {
            $lessonsGiven = Attendance::where('coach_id', $coach->id)
                ->whereMonth('attendance_date', now()->month)
                ->distinct()
                ->count('attendance_date');
            // En azından programdaki aktif derslerinden örnek baz hesap
            $lessonCount = max($lessonsGiven, $coach->groups()->count() * 8);
            $lessonFeeTotal = ($coach->compensation_type === 'per_lesson' || $coach->compensation_type === 'both')
                ? $lessonCount * $coach->per_lesson_rate
                : 0;
            $base = ($coach->compensation_type === 'fixed' || $coach->compensation_type === 'both')
                ? $coach->base_salary
                : 0;
            return [
                'coach' => $coach,
                'lesson_count' => $lessonCount,
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

        return view('admin.panel', compact(
            'authUser',
            'allowedTabs',
            'activeTab',
            'currentRole',
            'todayCollection',
            'monthCollection',
            'overdueTotal',
            'pendingTotal',
            'activeStudentsCount',
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
            'settings',
            'auditLogs',
            'absentWarnStudents',
            'expiringMedicalStudents',
            'coachPayrolls',
            'agingBuckets'
        ));
    }

    // MODÜL 2: Adayı Tek Tıkla Kesin Kayda Dönüştür
    public function convertLeadToStudent(Request $request, Lead $lead)
    {
        $group = $lead->trialGroup ?? Group::where('branch_id', $lead->branch_id)->first() ?? Group::first();
        $package = Package::where('billing_cycle', 'monthly')->first() ?? Package::first();

        $student = Student::create([
            'full_name' => $lead->student_name,
            'birth_date' => now()->subYears($lead->student_age)->format('Y-m-d'),
            'branch_id' => $lead->branch_id ?? $group?->branch_id,
            'group_id' => $group?->id,
            'package_id' => $package?->id,
            'parent_name' => $lead->parent_name,
            'parent_phone' => $lead->parent_phone,
            'medical_report_expires_at' => now()->addYear()->toDateString(),
            'billing_day' => 1,
            'status' => 'active',
            'kvkk_consent' => true,
            'health_data_consent' => true,
            'photo_consent' => true,
        ]);

        if ($package) {
            Invoice::create([
                'student_id' => $student->id,
                'package_id' => $package->id,
                'period_label' => now()->translatedFormat('F Y') . ' İlk Kayıt Aidatı',
                'gross_amount' => $package->price,
                'discount_amount' => 0,
                'net_amount' => $package->price,
                'paid_amount' => 0,
                'remaining_amount' => $package->price,
                'due_date' => today()->addDays(3),
                'status' => 'unpaid',
            ]);
        }

        $lead->update(['status' => 'converted']);

        WhatsappMessage::create([
            'student_id' => $student->id,
            'recipient_name' => $student->parent_name,
            'recipient_phone' => $student->parent_phone,
            'message_text' => "Hoş geldiniz Sayın {$student->parent_name}! 🌟 Sporcumuz {$student->full_name} kesin kaydı '{$group?->name}' grubuna yapılmıştır. Veli portalı ve mobil uygulamamıza telefon numaranızla ({$student->parent_phone}) giriş yapabilirsiniz. 🤸",
            'status' => 'queued',
        ]);

        AuditLog::record('aday_kayda_donusturuldu', "Aday {$lead->student_name} tek tıkla kesin kayda dönüştürüldü ve ilk aidat borcu açıldı.", 'Student', $student->id);

        return redirect()->route('admin.index', ['tab' => 'ogrenciler'])->with('success', "{$student->full_name} başarıyla kesin kayda dönüştürüldü, ilk aidat borcu açıldı ve WhatsApp hoş geldin mesajı kuyruğa eklendi!");
    }

    // MODÜL 3: Yeni Öğrenci Kaydı + İlk Tahsilat (Kabul Kriteri #1)
    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:120',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:Kız,Erkek,Belirtilmedi',
            'branch_id' => 'required|exists:branches,id',
            'group_id' => 'required|exists:groups,id',
            'package_id' => 'required|exists:packages,id',
            'discount_rule_id' => 'nullable|exists:discount_rules,id',
            'parent_name' => 'required|string|max:120',
            'parent_phone' => 'required|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:120',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'health_notes' => 'nullable|string',
            'medical_report_expires_at' => 'nullable|date',
            'license_no' => 'nullable|string|max:60',
            'uniform_size' => 'nullable|string|max:40',
            'initial_payment_amount' => 'nullable|numeric|min:0',
            'cash_account_id' => 'nullable|exists:cash_accounts,id',
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $discountRule = !empty($validated['discount_rule_id']) ? DiscountRule::find($validated['discount_rule_id']) : null;

        $student = Student::create([
            'full_name' => $validated['full_name'],
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'],
            'branch_id' => $validated['branch_id'],
            'group_id' => $validated['group_id'],
            'package_id' => $validated['package_id'],
            'discount_rule_id' => $validated['discount_rule_id'] ?? null,
            'parent_name' => $validated['parent_name'],
            'parent_phone' => $validated['parent_phone'],
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? $validated['parent_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? $validated['parent_phone'],
            'health_notes' => $validated['health_notes'] ?? 'Spor yapmasında engel yoktur.',
            'medical_report_expires_at' => $validated['medical_report_expires_at'] ?? now()->addYear()->toDateString(),
            'license_no' => $validated['license_no'] ?? null,
            'uniform_size' => $validated['uniform_size'] ?? null,
            'status' => 'active',
            'kvkk_consent' => $request->boolean('kvkk_consent', true),
            'health_data_consent' => $request->boolean('health_data_consent', true),
            'photo_consent' => $request->boolean('photo_consent', true),
            'marketing_consent' => $request->boolean('marketing_consent', false),
        ]);

        // Otomatik Borç Hesaplama
        $gross = (float) $package->price;
        $discAmount = 0;
        if ($discountRule) {
            $discAmount = $discountRule->percentage > 0
                ? round($gross * ($discountRule->percentage / 100), 2)
                : (float) $discountRule->fixed_amount;
        }
        $net = max(0, $gross - $discAmount);
        $paid = (float) ($validated['initial_payment_amount'] ?? 0);
        $paid = min($net, $paid);
        $remaining = $net - $paid;

        $invoice = Invoice::create([
            'student_id' => $student->id,
            'package_id' => $package->id,
            'period_label' => now()->format('m/Y') . ' Dönem Aidatı',
            'gross_amount' => $gross,
            'discount_amount' => $discAmount,
            'net_amount' => $net,
            'paid_amount' => $paid,
            'remaining_amount' => $remaining,
            'due_date' => today(),
            'status' => $remaining <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ]);

        if ($paid > 0) {
            $cashAcc = !empty($validated['cash_account_id'])
                ? CashAccount::find($validated['cash_account_id'])
                : CashAccount::first();

            $receiptNo = 'MKB-' . now()->format('Ym') . '-' . str_pad((string) (Payment::count() + 1), 4, '0', STR_PAD_LEFT);
            $payment = Payment::create([
                'receipt_no' => $receiptNo,
                'invoice_id' => $invoice->id,
                'student_id' => $student->id,
                'cash_account_id' => $cashAcc->id,
                'amount' => $paid,
                'payment_method' => $cashAcc->type === 'pos' ? 'pos' : ($cashAcc->type === 'bank' ? 'bank_transfer' : 'cash'),
                'payment_date' => today(),
                'description' => "{$student->full_name} İlk Kayıt Tahsilatı",
            ]);
            $cashAcc->increment('balance', $paid);

            // Makbuz WhatsApp Mesajı Kuyruğa Ekle
            WhatsappMessage::create([
                'student_id' => $student->id,
                'recipient_name' => $student->parent_name,
                'recipient_phone' => $student->parent_phone,
                'message_text' => "Sayın {$student->parent_name}, sporcumuz {$student->full_name} kesin kaydı tamamlanmış ve {$paid} TL ödemeniz alınmıştır (Makbuz No: {$receiptNo}). Dijital makbuzunuz: " . url("/makbuz/{$payment->id}"),
                'status' => 'queued',
            ]);
        }

        AuditLog::record('ogrenci_eklendi', "Yeni öğrenci kaydı açıldı: {$student->full_name} (İlk Tahsilat: {$paid} TL)", 'Student', $student->id);

        return redirect()->route('admin.index', ['tab' => 'ogrenciler'])->with('success', "Öğrenci kaydı ({$student->full_name}), aidat planı ve ilk tahsilat makbuzu başarıyla oluşturuldu!");
    }

    // Öğrenci Durum Değiştirme (Dondurma / Ayrılma / Arşivleme)
    public function updateStudentStatus(Request $request, Student $student)
    {
        $student->update([
            'status' => $request->input('status', 'frozen'),
            'leave_reason' => $request->input('leave_reason'),
        ]);
        AuditLog::record('ogrenci_durum_degisti', "{$student->full_name} durumu '{$student->status}' olarak güncellendi.", 'Student', $student->id);
        return back()->with('success', "{$student->full_name} kaydı güncellendi.");
    }

    // MODÜL 4 & 5: Yeni Grup Açıp Ders Programına Ekleme (Kabul Kriteri #2)
    public function storeGroupAndSchedule(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'coach_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:120',
            'level' => 'required|string|max:80',
            'age_group' => 'required|string|max:60',
            'area' => 'required|string|max:80',
            'capacity' => 'required|integer|min:1|max:50',
            'day_of_week' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // Alan ve saat çakışma kontrolü
        $conflict = Schedule::where('day_of_week', $validated['day_of_week'])
            ->where('area', $validated['area'])
            ->where('is_cancelled', false)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })->first();

        $group = Group::create([
            'branch_id' => $validated['branch_id'],
            'coach_id' => $validated['coach_id'] ?? null,
            'name' => $validated['name'],
            'level' => $validated['level'],
            'age_group' => $validated['age_group'],
            'area' => $validated['area'],
            'capacity' => $validated['capacity'],
            'is_active' => true,
        ]);

        Schedule::create([
            'group_id' => $group->id,
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'area' => $validated['area'],
        ]);

        AuditLog::record('grup_ve_program_eklendi', "Yeni grup ({$group->name}) ve ders programı ({$validated['day_of_week']} {$validated['start_time']}) eklendi.", 'Group', $group->id);

        $msg = "Yeni grup '{$group->name}' açıldı ve haftalık ders programına eklendi.";
        if ($conflict) {
            $msg .= " (Uyarı: {$validated['area']} alanında aynı saatte başka bir ders çakışması tespit edildi, lütfen kontrol edin.)";
        }

        return redirect()->route('admin.index', ['tab' => 'gruplar'])->with('success', $msg);
    }

    // MODÜL 6: Grup Bazlı Tek Dokunuşla Yoklama Kaydı
    public function storeAttendance(Request $request)
    {
        $groupId = $request->input('group_id');
        $date = $request->input('attendance_date', today()->toDateString());
        $statuses = $request->input('statuses', []);
        $notes = $request->input('notes', []);

        foreach ($statuses as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'group_id' => $groupId,
                    'student_id' => $studentId,
                    'attendance_date' => $date,
                ],
                [
                    'coach_id' => User::where('role', 'coach')->value('id'),
                    'status' => $status,
                    'note' => $notes[$studentId] ?? null,
                ]
            );

            $student = Student::find($studentId);
            if ($student) {
                if ($status === 'absent') {
                    $student->increment('consecutive_absences');
                    if ($student->consecutive_absences >= 2) {
                        WhatsappMessage::create([
                            'student_id' => $student->id,
                            'recipient_name' => $student->parent_name,
                            'recipient_phone' => $student->parent_phone,
                            'message_text' => "Merhaba {$student->parent_name}, sporcumuz {$student->full_name} üst üste {$student->consecutive_absences} antrenmana katılamamıştır. Her şeyin yolunda olduğunu umuyoruz 🙏 Telafi planlaması için bize yazabilirsiniz.",
                            'status' => 'queued',
                        ]);
                    }
                } elseif ($status === 'excused') {
                    $student->increment('makeup_credits');
                } elseif ($status === 'present') {
                    $student->update(['consecutive_absences' => 0]);
                } elseif ($status === 'makeup' && $student->makeup_credits > 0) {
                    $student->decrement('makeup_credits');
                }
            }
        }

        AuditLog::record('yoklama_alindi', "Grup #{$groupId} için {$date} tarihli yoklama kaydedildi.", 'Group', (int) $groupId);

        return redirect()->route('admin.index', ['tab' => 'yoklama'])->with('success', 'Grup yoklaması kaydedildi, telafi hakları güncellendi ve devamsızlık uyarıları WhatsApp kuyruğuna eklendi.');
    }

    // MODÜL 7: Paket Fiyatlarını Toplu Güncelleme (Kabul Kriteri #3)
    public function bulkUpdatePackagePrices(Request $request)
    {
        $percentage = (float) $request->input('percentage', 0);
        $fixedAdd = (float) $request->input('fixed_amount', 0);

        foreach (Package::all() as $pkg) {
            $newPrice = $pkg->price;
            if ($percentage != 0) {
                $newPrice = round($newPrice * (1 + ($percentage / 100)), -1);
            }
            if ($fixedAdd != 0) {
                $newPrice += $fixedAdd;
            }
            $pkg->update(['price' => max(0, $newPrice)]);
        }

        AuditLog::record('toplu_fiyat_guncelleme', "Paket fiyatları toplu güncellendi (Oran: %{$percentage}, Ek Tutar: {$fixedAdd} TL).");

        return redirect()->route('admin.index', ['tab' => 'paketler'])->with('success', 'Tüm paket fiyatları toplu olarak güncellendi ve ön yüz web sitesine yansıtıldı!');
    }

    // MODÜL 8: Tahsilat Girişi & Makbuz Üretimi + WhatsApp Gönderimi
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'cash_account_id' => 'required|exists:cash_accounts,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,pos,bank_transfer,online',
            'description' => 'nullable|string|max:200',
        ]);

        $invoice = Invoice::with('student')->findOrFail($validated['invoice_id']);
        $cashAccount = CashAccount::findOrFail($validated['cash_account_id']);
        $amount = (float) $validated['amount'];

        $receiptNo = 'MKB-' . now()->format('Ym') . '-' . str_pad((string) (Payment::count() + 1), 4, '0', STR_PAD_LEFT);

        $payment = Payment::create([
            'receipt_no' => $receiptNo,
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'cash_account_id' => $cashAccount->id,
            'recorded_by' => User::first()?->id,
            'amount' => $amount,
            'payment_method' => $validated['payment_method'],
            'payment_date' => today(),
            'description' => $validated['description'] ?? ($invoice->student->full_name . ' - ' . $invoice->period_label),
        ]);

        $newPaid = $invoice->paid_amount + $amount;
        $newRemaining = max(0, $invoice->net_amount - $newPaid);
        $invoice->update([
            'paid_amount' => $newPaid,
            'remaining_amount' => $newRemaining,
            'status' => $newRemaining <= 0 ? 'paid' : 'partial',
        ]);

        $cashAccount->increment('balance', $amount);

        // WhatsApp Business Makbuz Mesajı Hazırla
        $msgText = "Sayın {$invoice->student->parent_name}, sporcumuz {$invoice->student->full_name} için {$amount} TL tutarındaki ödemeniz alınmıştır (Makbuz No: {$receiptNo}, Kalan Borç: {$newRemaining} TL). Dijital Makbuz: " . url("/makbuz/{$payment->id}");
        WhatsappMessage::create([
            'student_id' => $invoice->student_id,
            'recipient_name' => $invoice->student->parent_name,
            'recipient_phone' => $invoice->student->parent_phone,
            'message_text' => $msgText,
            'status' => 'queued',
        ]);

        AuditLog::record('tahsilat_girildi', "{$invoice->student->full_name} için {$amount} TL tahsilat girildi ({$receiptNo}).", 'Payment', $payment->id);

        return redirect()->route('admin.index', ['tab' => 'aidat'])->with('success', "Tahsilat ({$receiptNo}) kaydedildi, kasa bakiyesi güncellendi ve WhatsApp makbuz mesajı hazırlandı!");
    }

    // Aylık Otomatik Aidat Borçlandırma Motoru
    public function generateMonthlyInvoices(Request $request)
    {
        $periodLabel = $request->input('period_label', now()->addMonth()->format('m/Y') . ' Aidatı');
        $activeStudents = Student::with(['package', 'discountRule'])->where('status', 'active')->get();
        $createdCount = 0;

        foreach ($activeStudents as $st) {
            if (!$st->package) {
                continue;
            }
            $exists = Invoice::where('student_id', $st->id)->where('period_label', $periodLabel)->exists();
            if ($exists) {
                continue;
            }
            $gross = (float) $st->package->price;
            $disc = 0;
            if ($st->discountRule) {
                $disc = $st->discountRule->percentage > 0
                    ? round($gross * ($st->discountRule->percentage / 100), 2)
                    : (float) $st->discountRule->fixed_amount;
            }
            $net = max(0, $gross - $disc);

            Invoice::create([
                'student_id' => $st->id,
                'package_id' => $st->package_id,
                'period_label' => $periodLabel,
                'gross_amount' => $gross,
                'discount_amount' => $disc,
                'net_amount' => $net,
                'paid_amount' => 0,
                'remaining_amount' => $net,
                'due_date' => now()->startOfMonth()->addMonth()->toDateString(),
                'status' => 'unpaid',
            ]);
            $createdCount++;
        }

        AuditLog::record('otomatik_borclandirma', "{$periodLabel} dönemi için {$createdCount} aktif öğrenciye otomatik aidat borcu açıldı.");

        return redirect()->route('admin.index', ['tab' => 'aidat'])->with('success', "{$periodLabel} dönemi için {$createdCount} aktif öğrenciye indirim kuralları uygulanarak otomatik borç kaydı açıldı!");
    }

    // MODÜL 9: Gelir-Gider ve Ürün Satışı Ekleme
    public function storeFinancialTransaction(Request $request)
    {
        $validated = $request->validate([
            'direction' => 'required|in:income,expense',
            'category' => 'required|string|max:80',
            'cash_account_id' => 'required|exists:cash_accounts,id',
            'title' => 'required|string|max:160',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'is_recurring' => 'nullable|boolean',
        ]);

        $tx = FinancialTransaction::create([
            'direction' => $validated['direction'],
            'category' => $validated['category'],
            'cash_account_id' => $validated['cash_account_id'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        $acc = CashAccount::find($validated['cash_account_id']);
        if ($acc) {
            if ($validated['direction'] === 'income') {
                $acc->increment('balance', $validated['amount']);
            } else {
                $acc->decrement('balance', $validated['amount']);
            }
        }

        AuditLog::record('kasa_hareketi', "{$validated['direction']} kaydı: {$validated['title']} ({$validated['amount']} TL)", 'FinancialTransaction', $tx->id);

        return redirect()->route('admin.index', ['tab' => 'kasa'])->with('success', 'Gelir/Gider hareketi kaydedildi ve ilgili kasa/banka bakiyesi güncellendi.');
    }

    // MODÜL 10: Personel / Sekreter / Antrenör Hesabı Açma ve Kapatma (Kabul Kriteri #5)
    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:30',
            'role' => 'required|in:super_admin,secretary,coach',
            'specialty' => 'nullable|string|max:120',
            'compensation_type' => 'required|in:fixed,per_lesson,both,none',
            'base_salary' => 'nullable|numeric|min:0',
            'per_lesson_rate' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'specialty' => $validated['specialty'] ?? null,
            'compensation_type' => $validated['compensation_type'],
            'base_salary' => $validated['base_salary'] ?? 0,
            'per_lesson_rate' => $validated['per_lesson_rate'] ?? 0,
            'password' => Hash::make('enerjik2026'),
            'api_token' => 'token-' . Str::random(24),
            'is_active' => true,
            'permissions' => [
                'view_expenses' => $request->boolean('perm_view_expenses'),
                'view_profit' => $request->boolean('perm_view_profit'),
                'collect_payments' => true,
            ],
        ]);

        AuditLog::record('personel_eklendi', "Yeni personel hesabı açıldı: {$user->name} ({$user->roleLabel()})", 'User', $user->id);

        return redirect()->route('admin.index', ['tab' => 'personel'])->with('success', "{$user->name} ({$user->roleLabel()}) hesabı başarıyla açıldı!");
    }

    public function toggleStaffStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $durum = $user->is_active ? 'aktif edildi' : 'kapatıldı (pasife alındı)';
        AuditLog::record('personel_durum_degisti', "{$user->name} kullanıcı hesabı {$durum}.", 'User', $user->id);
        return redirect()->route('admin.index', ['tab' => 'personel'])->with('success', "{$user->name} hesabı {$durum}.");
    }

    // MODÜL 11: WhatsApp Business Hazır Cevap & Kural Güncelleme (Kabul Kriteri #4)
    public function updateWhatsappTemplate(Request $request, WhatsappTemplate $template)
    {
        $template->update([
            'title' => $request->input('title', $template->title),
            'shortcut' => $request->input('shortcut', $template->shortcut),
            'send_time' => $request->input('send_time', $template->send_time),
            'days_offset' => (int) $request->input('days_offset', $template->days_offset),
            'message_template' => $request->input('message_template', $template->message_template),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record('whatsapp_sablon_guncellendi', "WhatsApp hazır cevap şablonu güncellendi: {$template->title} ({$template->shortcut})", 'WhatsappTemplate', $template->id);

        return redirect()->route('admin.index', ['tab' => 'mesajlar'])->with('success', "'{$template->title}' mesaj şablonu ve gönderim saati başarıyla güncellendi!");
    }

    // Günlük Otomatik WhatsApp Hatırlatma Kuyruğu Üretici
    public function generateDailyWhatsappQueue()
    {
        $count = 0;
        $overdueInvoices = Invoice::with('student')->whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->get();
        $tplOverdue = WhatsappTemplate::where('trigger_event', 'due_overdue')->where('is_active', true)->first();

        if ($tplOverdue) {
            foreach ($overdueInvoices as $inv) {
                if (!$inv->student) {
                    continue;
                }
                $text = str_replace(
                    ['{veli_adi}', '{ogrenci_adi}', '{donem}', '{tutar}', '{vade}'],
                    [$inv->student->parent_name, $inv->student->full_name, $inv->period_label, number_format((float) $inv->remaining_amount, 2, ',', '.'), $inv->due_date->format('d.m.Y')],
                    $tplOverdue->message_template
                );
                WhatsappMessage::create([
                    'template_id' => $tplOverdue->id,
                    'student_id' => $inv->student->id,
                    'recipient_name' => $inv->student->parent_name,
                    'recipient_phone' => $inv->student->parent_phone,
                    'message_text' => $text,
                    'status' => 'queued',
                ]);
                $count++;
            }
        }

        return redirect()->route('admin.index', ['tab' => 'mesajlar'])->with('success', "{$count} adet yeni otomatik WhatsApp Business hazır mesajı kuyruğa eklendi. 'WhatsApp ile Gönder' butonuna tıklayarak tek dokunuşla gönderebilirsiniz.");
    }

    public function markWhatsappSent(WhatsappMessage $message)
    {
        $message->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        return redirect()->away($message->wa_me_url);
    }

    // MODÜL 13: Gelişim Karnesi Kaydı
    public function storeDevelopmentRecord(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'term_label' => 'required|string|max:100',
            'current_level' => 'required|string|max:100',
            'flexibility_score' => 'required|integer|min:0|max:100',
            'strength_score' => 'required|integer|min:0|max:100',
            'balance_score' => 'required|integer|min:0|max:100',
            'coordination_score' => 'required|integer|min:0|max:100',
            'discipline_score' => 'required|integer|min:0|max:100',
            'coach_comment' => 'nullable|string',
        ]);

        DevelopmentRecord::create([
            'student_id' => $validated['student_id'],
            'coach_id' => User::where('role', 'coach')->value('id'),
            'term_label' => $validated['term_label'],
            'current_level' => $validated['current_level'],
            'flexibility_score' => $validated['flexibility_score'],
            'strength_score' => $validated['strength_score'],
            'balance_score' => $validated['balance_score'],
            'coordination_score' => $validated['coordination_score'],
            'discipline_score' => $validated['discipline_score'],
            'skills' => [
                'Temel Duruş ve Esneklik' => 'Başarılı',
                'Takla & Çember' => 'Başarılı',
                'Amut & Denge' => 'Gelişiyor',
            ],
            'coach_comment' => $validated['coach_comment'],
        ]);

        return redirect()->route('admin.index', ['tab' => 'gelisim'])->with('success', 'Öğrenci gelişim karnesi oluşturuldu ve veli ekranında yayınlandı!');
    }

    // MODÜL 15: Site İçeriği, Duyuru ve Fotoğraf Güncelleme (Kabul Kriteri #6)
    public function updateSiteSettings(Request $request)
    {
        foreach ($request->except(['_token']) as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        AuditLog::record('site_icerigi_guncellendi', 'Web sitesi içerik ve iletişim ayarları güncellendi.');
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Web sitesi metinleri, iletişim ve SEO ayarları anında güncellendi!');
    }

    public function storeAnnouncement(Request $request)
    {
        Announcement::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'target_audience' => $request->input('target_audience', 'all'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);
        AuditLog::record('duyuru_eklendi', "Yeni duyuru yayınlandı: " . $request->input('title'));
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Yeni duyuru sitede ve mobil uygulamada yayınlandı!');
    }

    public function storeGalleryItem(Request $request)
    {
        $studentId = $request->input('student_id');
        if ($studentId) {
            $st = Student::find($studentId);
            if ($st && !$st->photo_consent) {
                return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('error', "KVKK UYARISI: {$st->full_name} adlı öğrencinin velisi fotoğraf/video paylaşım onayı VERMEMİŞTİR! Galeriye eklenemedi.");
            }
        }

        GalleryItem::create([
            'title' => $request->input('title'),
            'category' => $request->input('category', 'Antrenman'),
            'image_url' => $request->input('image_url'),
            'student_id' => $studentId ?: null,
            'photo_consent_verified' => true,
        ]);

        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Fotoğraf KVKK izin kontrolünden geçerek galeriye eklendi!');
    }

    // MODÜL 14: Aylık Gelir-Gider Raporunu Excel (CSV) Olarak İndirme (Kabul Kriteri #7)
    public function exportFinancialReportCsv()
    {
        $filename = 'enerjik-cimnastik-gelir-gider-raporu-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel Turkish characters
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Tarih', 'Tur', 'Kategori / Makbuz No', 'Aciklama / Ogrenci', 'Hesap', 'Tutar (TL)'], ';');

            foreach (Payment::with(['student', 'cashAccount'])->get() as $p) {
                fputcsv($out, [
                    $p->payment_date->format('d.m.Y'),
                    'GELIR (Tahsilat)',
                    $p->receipt_no,
                    $p->student?->full_name . ' - ' . $p->description,
                    $p->cashAccount?->name,
                    number_format((float) $p->amount, 2, ',', '.'),
                ], ';');
            }

            foreach (FinancialTransaction::with('cashAccount')->get() as $tx) {
                fputcsv($out, [
                    $tx->transaction_date->format('d.m.Y'),
                    $tx->direction === 'income' ? 'GELIR (Diger)' : 'GIDER',
                    $tx->category,
                    $tx->title,
                    $tx->cashAccount?->name,
                    ($tx->direction === 'expense' ? '-' : '') . number_format((float) $tx->amount, 2, ',', '.'),
                ], ';');
            }

            fclose($out);
        };

        AuditLog::record('rapor_indirildi', 'Aylık Gelir-Gider Excel (CSV) raporu indirildi.');
        return response()->stream($callback, 200, $headers);
    }

    // MODÜL 16: Veritabanı Yedeğini İndirme (Kabul Kriteri #8)
    public function downloadDatabaseBackup()
    {
        $tables = [
            'users' => User::all(),
            'branches' => Branch::all(),
            'packages' => Package::all(),
            'discount_rules' => DiscountRule::all(),
            'groups' => Group::all(),
            'schedules' => Schedule::all(),
            'leads' => Lead::all(),
            'students' => Student::all(),
            'attendances' => Attendance::all(),
            'cash_accounts' => CashAccount::all(),
            'invoices' => Invoice::all(),
            'payments' => Payment::all(),
            'financial_transactions' => FinancialTransaction::all(),
            'development_records' => DevelopmentRecord::all(),
            'events' => Event::all(),
            'whatsapp_templates' => WhatsappTemplate::all(),
            'whatsapp_messages' => WhatsappMessage::all(),
            'site_settings' => SiteSetting::all(),
            'audit_logs' => AuditLog::all(),
        ];

        $backupPayload = [
            'club' => 'Enerjik Cimnastik Spor Kulübü',
            'generated_at' => now()->toIso8601String(),
            'database' => config('database.connections.mysql.database'),
            'tables' => $tables,
        ];

        AuditLog::record('yedek_indirildi', 'Tam veritabanı yedeği yönetici tarafından indirildi.');

        $filename = 'enerjik-cimnastik-tam-yedek-' . now()->format('Y-m-d-His') . '.json';
        return response()->streamDownload(function () use ($backupPayload) {
            echo json_encode($backupPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json; charset=UTF-8']);
    }
}
