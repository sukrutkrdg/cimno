<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kullanıcılar ve Roller
        $admin = User::create([
            'name' => 'Şükrü Küçük (Salon Sahibi)',
            'email' => 'yonetici@enerjikcimnastik.com',
            'phone' => '05321001001',
            'role' => 'super_admin',
            'compensation_type' => 'none',
            'password' => Hash::make('enerjik2026'),
            'api_token' => 'token-super-admin-enerjik-2026',
            'permissions' => ['all' => true],
        ]);

        $secretary = User::create([
            'name' => 'Ayşe Yılmaz (Ön Büro / Sekreter)',
            'email' => 'sekreter@enerjikcimnastik.com',
            'phone' => '05321001002',
            'role' => 'secretary',
            'compensation_type' => 'fixed',
            'base_salary' => 28500,
            'password' => Hash::make('enerjik2026'),
            'api_token' => 'token-secretary-enerjik-2026',
            'permissions' => [
                'view_expenses' => false,
                'view_profit' => false,
                'manage_students' => true,
                'collect_payments' => true,
                'send_whatsapp' => true,
            ],
        ]);

        $coach1 = User::create([
            'name' => 'Burak Demir (Baş Antrenör)',
            'email' => 'burak@enerjikcimnastik.com',
            'phone' => '05321001003',
            'role' => 'coach',
            'specialty' => 'Artistik Cimnastik & Trampolin (2. Kademe Antrenör)',
            'bio' => 'Eski Milli Cimnastikçi, 10 yıllık çocuk ve performans grubu antrenörlüğü deneyimi.',
            'compensation_type' => 'both',
            'base_salary' => 22000,
            'per_lesson_rate' => 450,
            'password' => Hash::make('enerjik2026'),
            'api_token' => 'token-coach1-enerjik-2026',
        ]);

        $coach2 = User::create([
            'name' => 'Selin Kaya (Ritmik & Okul Öncesi Antrenörü)',
            'email' => 'selin@enerjikcimnastik.com',
            'phone' => '05321001004',
            'role' => 'coach',
            'specialty' => 'Ritmik Cimnastik & Okul Öncesi Motor Gelişim',
            'bio' => 'BESYO mezunu, Ritmik Cimnastik uzman antrenörü ve çocuk gelişimi eğitmeni.',
            'compensation_type' => 'per_lesson',
            'base_salary' => 0,
            'per_lesson_rate' => 600,
            'password' => Hash::make('enerjik2026'),
            'api_token' => 'token-coach2-enerjik-2026',
        ]);

        $parent1 = User::create([
            'name' => 'Murat Arslan (Veli)',
            'email' => 'murat.arslan@ornek.com',
            'phone' => '05332223344',
            'role' => 'parent',
            'otp_code' => '123456',
            'password' => Hash::make('enerjik2026'),
            'api_token' => 'token-parent1-enerjik-2026',
        ]);

        $parent2 = User::create([
            'name' => 'Zeynep Çelik (Veli)',
            'email' => 'zeynep.celik@ornek.com',
            'phone' => '05354445566',
            'role' => 'parent',
            'otp_code' => '123456',
            'password' => Hash::make('enerjik2026'),
            'api_token' => 'token-parent2-enerjik-2026',
        ]);

        // 2. Branşlar
        $bMini = Branch::create([
            'name' => 'Okul Öncesi Temel Cimnastik',
            'slug' => 'okul-oncesi-temel-cimnastik',
            'age_range' => '3 - 5 Yaş',
            'level' => 'Başlangıç / Oyun Temelli Motor Gelişim',
            'description' => 'Çocukların esneklik, denge, koordinasyon ve özgüven gelişimini oyunlarla destekleyen temel cimnastik programı.',
            'benefits' => 'Duruş bozukluklarını önler, ince ve kaba motor becerileri geliştirir, grup disiplini kazandırır.',
            'icon' => '🌟',
            'sort_order' => 1,
        ]);

        $bArtistik = Branch::create([
            'name' => 'Artistik Cimnastik',
            'slug' => 'artistik-cimnastik',
            'age_range' => '6 - 14 Yaş',
            'level' => 'Başlangıç · Orta · Performans (Lisanslı)',
            'description' => 'Yer minderinde, denge aletinde, atlama masasında ve barda kuvvet, çeviklik ve akrobasi temelli olimpik branş eğitimi.',
            'benefits' => 'Üst düzey kas kuvveti, esneklik, disiplin ve Türkiye Cimnastik Federasyonu yarışmalarına hazırlık.',
            'icon' => '🤸',
            'sort_order' => 2,
        ]);

        $bRitmik = Branch::create([
            'name' => 'Ritmik Cimnastik',
            'slug' => 'ritmik-cimnastik',
            'age_range' => '5 - 13 Yaş',
            'level' => 'Başlangıç · Orta · Yarışma Grubu',
            'description' => 'Müzik, bale temeli ve çember, top, kurdele, ip, lobut aletleriyle estetik ve esnekliği birleştiren branş.',
            'benefits' => 'Ritim duygusu, zarafet, üst düzey esneklik ve sahne özgüveni sağlar.',
            'icon' => '🎀',
            'sort_order' => 3,
        ]);

        $bTrampolin = Branch::create([
            'name' => 'Trampolin & Akrobasi',
            'slug' => 'trampolin-ve-akrobasi',
            'age_range' => '6 - 16 Yaş',
            'level' => 'Temel Sıçrama · İleri Akrobasi',
            'description' => 'Profesyonel olimpik trampolin üzerinde havada yön bulma, koordinasyon ve akrobatik dönüş çalışmaları.',
            'benefits' => 'Patlayıcı kuvvet, vestibüler denge ve yüksek enerji kontrolü kazandırır.',
            'icon' => '🚀',
            'sort_order' => 4,
        ]);

        $bYetiskin = Branch::create([
            'name' => 'Yetişkin Mobilite & Cimnastik Kondisyon',
            'slug' => 'yetiskin-mobilite-kondisyon',
            'age_range' => '18+ Yaş',
            'level' => 'Tüm Seviyeler',
            'description' => 'Yetişkinler için amut (handstand), esneklik, core kuvveti ve eklem hareketliliği antrenmanları.',
            'benefits' => 'Masa başı ağrılarını azaltır, fonksiyonel güç ve esneklik sağlar.',
            'icon' => '💪',
            'sort_order' => 5,
        ]);

        // 3. Paketler ve İndirim Kuralları
        $pkgMonthly = Package::create([
            'name' => 'Aylık Standart Paket (Haftada 2 Ders / Ayda 8 Ders)',
            'billing_cycle' => 'monthly',
            'lessons_count' => 8,
            'price' => 3200,
            'registration_fee' => 500,
            'description' => 'Haftada 2 gün 60 dakika grup antrenmanı, gelişim karnesi ve telafi dersi hakkı dahildir.',
        ]);

        $pkgQuarter = Package::create([
            'name' => '3 Aylık Dönem Paketi (24 Ders)',
            'billing_cycle' => 'quarterly',
            'lessons_count' => 24,
            'price' => 8600,
            'registration_fee' => 0,
            'description' => '3 aylık avantajlı dönem paketi. Kayıt ücreti alınmaz, %10 dönem avantajı içerir.',
        ]);

        $pkgPerf = Package::create([
            'name' => 'Performans & Yarışma Grubu (Haftada 4 Ders / Ayda 16 Ders)',
            'billing_cycle' => 'monthly',
            'lessons_count' => 16,
            'price' => 5200,
            'registration_fee' => 500,
            'description' => 'Lisanslı yarışmacı ve performans adayları için yoğunlaştırılmış antrenman paketi.',
        ]);

        $discSibling = DiscountRule::create([
            'name' => 'Kardeş İndirimi (%15)',
            'type' => 'sibling',
            'percentage' => 15,
            'fixed_amount' => 0,
        ]);

        $discUpfront = DiscountRule::create([
            'name' => 'Peşin Ödeme İndirimi (%10)',
            'type' => 'upfront',
            'percentage' => 10,
            'fixed_amount' => 0,
        ]);

        $discScholar = DiscountRule::create([
            'name' => 'Milli / Yarışmacı Sporcu Bursu (%50)',
            'type' => 'scholarship',
            'percentage' => 50,
            'fixed_amount' => 0,
        ]);

        // 4. Gruplar ve Ders Programı
        $grpMiniA = Group::create([
            'branch_id' => $bMini->id,
            'coach_id' => $coach2->id,
            'name' => 'Minik Yıldızlar A (3-5 Yaş)',
            'level' => 'Başlangıç',
            'age_group' => '3-5 Yaş',
            'area' => 'Ana Minder',
            'capacity' => 12,
        ]);

        $grpArtistikA = Group::create([
            'branch_id' => $bArtistik->id,
            'coach_id' => $coach1->id,
            'name' => 'Artistik Temel Grup B (6-9 Yaş)',
            'level' => 'Temel & Orta',
            'age_group' => '6-9 Yaş',
            'area' => 'Ana Minder & Bar Alanı',
            'capacity' => 12,
        ]);

        $grpRitmikA = Group::create([
            'branch_id' => $bRitmik->id,
            'coach_id' => $coach2->id,
            'name' => 'Ritmik Kuğular (6-11 Yaş)',
            'level' => 'Orta Seviye',
            'age_group' => '6-11 Yaş',
            'area' => 'Bale & Ritmik Salonu',
            'capacity' => 10,
        ]);

        $grpTrampolin = Group::create([
            'branch_id' => $bTrampolin->id,
            'coach_id' => $coach1->id,
            'name' => 'Trampolin Akrobasi Grubu',
            'level' => 'Orta - İleri',
            'age_group' => '8-14 Yaş',
            'area' => 'Trampolin Alanı',
            'capacity' => 8,
        ]);

        // Ders Programı
        Schedule::insert([
            ['group_id' => $grpMiniA->id, 'day_of_week' => 'Cumartesi', 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'area' => 'Ana Minder', 'is_cancelled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpMiniA->id, 'day_of_week' => 'Pazar', 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'area' => 'Ana Minder', 'is_cancelled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpArtistikA->id, 'day_of_week' => 'Salı', 'start_time' => '17:00:00', 'end_time' => '18:15:00', 'area' => 'Ana Minder & Bar Alanı', 'is_cancelled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpArtistikA->id, 'day_of_week' => 'Perşembe', 'start_time' => '17:00:00', 'end_time' => '18:15:00', 'area' => 'Ana Minder & Bar Alanı', 'is_cancelled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpRitmikA->id, 'day_of_week' => 'Çarşamba', 'start_time' => '17:30:00', 'end_time' => '18:45:00', 'area' => 'Bale & Ritmik Salonu', 'is_cancelled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpRitmikA->id, 'day_of_week' => 'Cumartesi', 'start_time' => '13:00:00', 'end_time' => '14:15:00', 'area' => 'Bale & Ritmik Salonu', 'is_cancelled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpTrampolin->id, 'day_of_week' => 'Cumartesi', 'start_time' => '15:00:00', 'end_time' => '16:15:00', 'area' => 'Trampolin Alanı', 'is_cancelled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpTrampolin->id, 'day_of_week' => 'Pazar', 'start_time' => '15:00:00', 'end_time' => '16:15:00', 'area' => 'Trampolin Alanı', 'is_cancelled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 5. Öğrenciler
        $st1 = Student::create([
            'full_name' => 'Elif Arslan',
            'birth_date' => '2018-05-14',
            'gender' => 'Kız',
            'branch_id' => $bArtistik->id,
            'group_id' => $grpArtistikA->id,
            'package_id' => $pkgMonthly->id,
            'parent_name' => 'Murat Arslan',
            'parent_phone' => '05332223344',
            'secondary_parent_name' => 'Esra Arslan',
            'secondary_parent_phone' => '05332223345',
            'parent_user_id' => $parent1->id,
            'emergency_contact_name' => 'Esra Arslan (Anne)',
            'emergency_contact_phone' => '05332223345',
            'health_notes' => 'Bilinen kronik rahatsızlığı yok. Spor yapabilir raporu tam.',
            'medical_report_expires_at' => now()->addMonths(8)->toDateString(),
            'license_no' => 'TCF-34-2026-0891',
            'license_visa_date' => now()->addMonths(10)->toDateString(),
            'uniform_size' => '8-9 Yaş (134 cm)',
            'makeup_credits' => 1,
            'consecutive_absences' => 0,
            'billing_day' => 1,
            'status' => 'active',
            'kvkk_consent' => true,
            'health_data_consent' => true,
            'photo_consent' => true,
            'marketing_consent' => true,
        ]);

        $st2 = Student::create([
            'full_name' => 'Kerem Arslan',
            'birth_date' => '2021-09-20',
            'gender' => 'Erkek',
            'branch_id' => $bMini->id,
            'group_id' => $grpMiniA->id,
            'package_id' => $pkgMonthly->id,
            'discount_rule_id' => $discSibling->id,
            'parent_name' => 'Murat Arslan',
            'parent_phone' => '05332223344',
            'parent_user_id' => $parent1->id,
            'emergency_contact_name' => 'Esra Arslan (Anne)',
            'emergency_contact_phone' => '05332223345',
            'health_notes' => 'Mevsimsel polen alerjisi mevcut.',
            'medical_report_expires_at' => now()->addDays(12)->toDateString(), // 15 günden az kaldı uyarısı için!
            'uniform_size' => '5-6 Yaş (116 cm)',
            'makeup_credits' => 0,
            'consecutive_absences' => 0,
            'billing_day' => 1,
            'status' => 'active',
            'kvkk_consent' => true,
            'health_data_consent' => true,
            'photo_consent' => true,
            'marketing_consent' => true,
        ]);

        // Kardeş bağlantısı
        $st1->siblings()->attach($st2->id);
        $st2->siblings()->attach($st1->id);

        $st3 = Student::create([
            'full_name' => 'Defne Çelik',
            'birth_date' => '2017-10-17', // Bugün doğum günü!
            'gender' => 'Kız',
            'branch_id' => $bRitmik->id,
            'group_id' => $grpRitmikA->id,
            'package_id' => $pkgMonthly->id,
            'parent_name' => 'Zeynep Çelik',
            'parent_phone' => '05354445566',
            'parent_user_id' => $parent2->id,
            'emergency_contact_name' => 'Hakan Çelik (Baba)',
            'emergency_contact_phone' => '05354445567',
            'health_notes' => 'Engel durumu yok.',
            'medical_report_expires_at' => now()->addMonths(5)->toDateString(),
            'license_no' => 'TCF-34-2026-1042',
            'license_visa_date' => now()->addMonths(6)->toDateString(),
            'uniform_size' => '9-10 Yaş (140 cm)',
            'makeup_credits' => 2,
            'consecutive_absences' => 2, // 2+ ders kaçıranlar listesi için!
            'billing_day' => 1,
            'status' => 'active',
            'kvkk_consent' => true,
            'health_data_consent' => true,
            'photo_consent' => false, // Fotoğraf izni vermeyen öğrenci uyarısı için!
            'marketing_consent' => true,
        ]);

        $st4 = Student::create([
            'full_name' => 'Poyraz Yıldırım',
            'birth_date' => '2015-03-11',
            'gender' => 'Erkek',
            'branch_id' => $bTrampolin->id,
            'group_id' => $grpTrampolin->id,
            'package_id' => $pkgQuarter->id,
            'discount_rule_id' => $discUpfront->id,
            'parent_name' => 'Serkan Yıldırım',
            'parent_phone' => '05426667788',
            'emergency_contact_name' => 'Serkan Yıldırım',
            'emergency_contact_phone' => '05426667788',
            'medical_report_expires_at' => now()->addMonths(9)->toDateString(),
            'uniform_size' => '11-12 Yaş',
            'status' => 'active',
            'kvkk_consent' => true,
            'health_data_consent' => true,
            'photo_consent' => true,
        ]);

        // 6. Adaylar (Ön Kayıt & Deneme Dersi Talepleri)
        Lead::create([
            'parent_name' => 'Gökhan Özkan',
            'parent_phone' => '05329998877',
            'student_name' => 'Nil Özkan',
            'student_age' => 5,
            'branch_id' => $bMini->id,
            'trial_group_id' => $grpMiniA->id,
            'trial_date' => now()->addDay()->toDateString(),
            'preferred_days' => 'Hafta sonu sabah',
            'source' => 'instagram',
            'status' => 'trial_scheduled',
            'notes' => 'Cumartesi 10:00 deneme dersine katılacak, WhatsApp hatırlatması hazır.',
            'kvkk_approved' => true,
            'kvkk_approved_at' => now()->subDay(),
        ]);

        Lead::create([
            'parent_name' => 'Melis Karaca',
            'parent_phone' => '05443332211',
            'student_name' => 'Mert Karaca',
            'student_age' => 7,
            'branch_id' => $bArtistik->id,
            'preferred_days' => 'Salı - Perşembe akşam',
            'source' => 'website',
            'status' => 'new',
            'notes' => 'Web sitesinden ücretsiz deneme dersi formu doldurdu.',
            'kvkk_approved' => true,
            'kvkk_approved_at' => now()->subHours(3),
        ]);

        Lead::create([
            'parent_name' => 'Derya Şahin',
            'parent_phone' => '05301112233',
            'student_name' => 'Ada Şahin',
            'student_age' => 8,
            'branch_id' => $bRitmik->id,
            'trial_group_id' => $grpRitmikA->id,
            'trial_date' => now()->subDays(2)->toDateString(),
            'preferred_days' => 'Çarşamba - Cumartesi',
            'source' => 'referral',
            'status' => 'thinking',
            'notes' => 'Deneme dersine katıldı, çok memnun kaldı, babasıyla görüşüp kesin kayıt için dönecek.',
            'kvkk_approved' => true,
            'kvkk_approved_at' => now()->subDays(4),
        ]);

        // 7. Kasa, Banka ve POS Hesapları
        $accCash = CashAccount::create([
            'name' => 'Salon Nakit Kasa',
            'type' => 'cash',
            'balance' => 14500,
        ]);

        $accPos = CashAccount::create([
            'name' => 'Salon Fiziki POS (Garanti BBVA)',
            'type' => 'pos',
            'balance' => 42800,
        ]);

        $accBank = CashAccount::create([
            'name' => 'Kurumsal Banka Hesabı (Havale / EFT)',
            'type' => 'bank',
            'iban' => 'TR45 0006 2000 1230 0006 2999 01',
            'balance' => 68400,
        ]);

        // 8. Aidat Borçları (Invoices) ve Tahsilatlar (Payments)
        $inv1 = Invoice::create([
            'student_id' => $st1->id,
            'package_id' => $pkgMonthly->id,
            'period_label' => 'Ekim 2026 Aidatı',
            'gross_amount' => 3200,
            'discount_amount' => 0,
            'net_amount' => 3200,
            'paid_amount' => 3200,
            'remaining_amount' => 0,
            'due_date' => '2026-10-01',
            'status' => 'paid',
        ]);

        Payment::create([
            'receipt_no' => 'MKB-202610-0001',
            'invoice_id' => $inv1->id,
            'student_id' => $st1->id,
            'cash_account_id' => $accPos->id,
            'recorded_by' => $secretary->id,
            'amount' => 3200,
            'payment_method' => 'pos',
            'payment_date' => '2026-10-02',
            'description' => 'Elif Arslan Ekim 2026 Aidat Tahsilatı',
        ]);

        $inv2 = Invoice::create([
            'student_id' => $st2->id,
            'package_id' => $pkgMonthly->id,
            'period_label' => 'Ekim 2026 Aidatı (%15 Kardeş İndirimli)',
            'gross_amount' => 3200,
            'discount_amount' => 480,
            'net_amount' => 2720,
            'paid_amount' => 1500,
            'remaining_amount' => 1220,
            'due_date' => '2026-10-05',
            'status' => 'partial',
            'notes' => '1.500 TL nakit alındı, kalan 1.220 TL ayın 20 sinde ödenecek.',
        ]);

        Payment::create([
            'receipt_no' => 'MKB-202610-0002',
            'invoice_id' => $inv2->id,
            'student_id' => $st2->id,
            'cash_account_id' => $accCash->id,
            'recorded_by' => $secretary->id,
            'amount' => 1500,
            'payment_method' => 'cash',
            'payment_date' => '2026-10-05',
            'description' => 'Kerem Arslan Ekim 2026 Kısmi Tahsilat',
        ]);

        $inv3 = Invoice::create([
            'student_id' => $st3->id,
            'package_id' => $pkgMonthly->id,
            'period_label' => 'Ekim 2026 Aidatı',
            'gross_amount' => 3200,
            'discount_amount' => 0,
            'net_amount' => 3200,
            'paid_amount' => 0,
            'remaining_amount' => 3200,
            'due_date' => '2026-10-07',
            'status' => 'unpaid',
            'notes' => 'Vadesi 10 gün geçti - Gecikme hatırlatması kuyrukta.',
        ]);

        $inv4 = Invoice::create([
            'student_id' => $st4->id,
            'package_id' => $pkgQuarter->id,
            'period_label' => 'Ekim-Kasım-Aralık 2026 Dönem Paketi',
            'gross_amount' => 8600,
            'discount_amount' => 860,
            'net_amount' => 7740,
            'paid_amount' => 7740,
            'remaining_amount' => 0,
            'due_date' => '2026-10-01',
            'status' => 'paid',
        ]);

        Payment::create([
            'receipt_no' => 'MKB-202610-0003',
            'invoice_id' => $inv4->id,
            'student_id' => $st4->id,
            'cash_account_id' => $accBank->id,
            'recorded_by' => $admin->id,
            'amount' => 7740,
            'payment_method' => 'bank_transfer',
            'payment_date' => '2026-10-01',
            'description' => 'Poyraz Yıldırım 3 Aylık Peşin Havale Tahsilatı',
        ]);

        // 9. Gelir-Gider ve Ürün Satışı
        FinancialTransaction::insert([
            [
                'direction' => 'expense',
                'category' => 'Kira',
                'cash_account_id' => $accBank->id,
                'related_user_id' => null,
                'title' => 'Ekim 2026 Salon Kira Ödemesi',
                'amount' => 35000,
                'transaction_date' => '2026-10-01',
                'is_recurring' => true,
                'notes' => 'Sabit aylık salon kirası',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'direction' => 'expense',
                'category' => 'Elektrik / Su / Doğalgaz',
                'cash_account_id' => $accBank->id,
                'related_user_id' => null,
                'title' => 'Ekim Ayı Elektrik, Su ve İklimlendirme Faturaları',
                'amount' => 6450,
                'transaction_date' => '2026-10-10',
                'is_recurring' => false,
                'notes' => 'Otomatik ödeme',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'direction' => 'income',
                'category' => 'Ürün Satışı (Mayo / Forma)',
                'cash_account_id' => $accCash->id,
                'related_user_id' => null,
                'title' => 'Enerjik Cimnastik Kulüp Mayosu Satışı (3 Adet)',
                'amount' => 2850,
                'transaction_date' => '2026-10-14',
                'is_recurring' => false,
                'notes' => 'Yeni kayıt öğrencileri mayo teslimi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 10. Yoklama Kayıtları
        Attendance::insert([
            ['group_id' => $grpArtistikA->id, 'student_id' => $st1->id, 'coach_id' => $coach1->id, 'attendance_date' => now()->subDays(5)->toDateString(), 'status' => 'present', 'note' => 'Çember hareketinde çok başarılıydı.', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpArtistikA->id, 'student_id' => $st1->id, 'coach_id' => $coach1->id, 'attendance_date' => now()->subDays(2)->toDateString(), 'status' => 'present', 'note' => 'Amutta bekleme süresi 10 saniyeye çıktı.', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpMiniA->id, 'student_id' => $st2->id, 'coach_id' => $coach2->id, 'attendance_date' => now()->subDays(3)->toDateString(), 'status' => 'present', 'note' => 'Denge tahtası parkurunu yardımsız tamamladı.', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpRitmikA->id, 'student_id' => $st3->id, 'coach_id' => $coach2->id, 'attendance_date' => now()->subDays(6)->toDateString(), 'status' => 'absent', 'note' => 'Üst üste 1. devamsızlık', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => $grpRitmikA->id, 'student_id' => $st3->id, 'coach_id' => $coach2->id, 'attendance_date' => now()->subDays(3)->toDateString(), 'status' => 'absent', 'note' => 'Üst üste 2. devamsızlık - Veli aranacak', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 11. Gelişim Karnesi Kayıtları
        DevelopmentRecord::create([
            'student_id' => $st1->id,
            'coach_id' => $coach1->id,
            'term_label' => '2026 Güz Dönemi (Ekim Değerlendirmesi)',
            'current_level' => 'Artistik Seviye 2 (Yarışma Adayı)',
            'flexibility_score' => 92,
            'strength_score' => 88,
            'balance_score' => 90,
            'coordination_score' => 94,
            'discipline_score' => 98,
            'height_cm' => 131.5,
            'weight_kg' => 27.4,
            'skills' => [
                'Öne / Arkaya Takla' => 'Tamamlandı (Mükemmel)',
                'Köprü ve Kalkış' => 'Tamamlandı (Mükemmel)',
                'Çember (Cartwheel)' => 'Tamamlandı (Mükemmel)',
                'Amut (Handstand)' => 'Gelişiyor (Desteksiz 8 sn)',
                'Denge Aleti Yürüyüş & Sıçrama' => 'Tamamlandı',
            ],
            'coach_comment' => 'Elif bu dönem hem esneklik hem de odaklanma açısından grubun örnek sporcularından biri oldu. Bahar dönemi il içi teşvik müsabakalarına hazırlanabilir.',
        ]);

        DevelopmentRecord::create([
            'student_id' => $st2->id,
            'coach_id' => $coach2->id,
            'term_label' => '2026 Güz Dönemi (Ekim Değerlendirmesi)',
            'current_level' => 'Minik Yıldızlar Seviye 1',
            'flexibility_score' => 85,
            'strength_score' => 80,
            'balance_score' => 84,
            'coordination_score' => 88,
            'discipline_score' => 90,
            'height_cm' => 112.0,
            'weight_kg' => 19.5,
            'skills' => [
                'Tek Ayak Denge' => 'Tamamlandı',
                'Öne Yuvarlanma (Takla)' => 'Tamamlandı',
                'Koordinasyon Merdiveni' => 'Tamamlandı',
                'Sıçrama ve Çift Ayak Düşüş' => 'Gelişiyor',
            ],
            'coach_comment' => 'Kerem grup uyumunu çok hızlı yakaladı, komut takibi ve enerjisi harika.',
        ]);

        // 12. Etkinlikler
        $evShow = Event::create([
            'title' => '29 Ekim Cumhuriyet Kupası & Salon İçi Gösteri',
            'type' => 'show',
            'event_date' => '2026-10-29',
            'location' => 'Enerjik Cimnastik Ana Salon',
            'fee' => 450,
            'quota' => 60,
            'description' => 'Tüm gruplarımızın ailelerine açık madalya töreni ve cimnastik gösterisi.',
        ]);
        $evShow->participants()->attach($st1->id, ['fee_paid' => true, 'parent_approved' => true]);
        $evShow->participants()->attach($st2->id, ['fee_paid' => true, 'parent_approved' => true]);

        // 13. WhatsApp Business Hazır Cevapları ve Şablon Kuralları
        $tplTrial = WhatsappTemplate::create([
            'shortcut' => '/deneme',
            'title' => 'Deneme Dersi Hatırlatması (1 Gün Önce)',
            'trigger_event' => 'trial_reminder',
            'category' => 'utility',
            'days_offset' => -1,
            'send_time' => '19:00:00',
            'message_template' => 'Merhaba {veli_adi} 👋 Enerjik Cimnastik Spor Kulübü olarak yarın ({tarih}) saat {saat} grubumuzda {ogrenci_adi} ile ücretsiz deneme dersimizde buluşmayı heyecanla bekliyoruz! 🤸 Rahat bir eşofman, temiz çorap ve su matarası getirmeniz yeterlidir. Konum ve sorularınız için bu mesajı yanıtlayabilirsiniz.',
        ]);

        $tplDueApproaching = WhatsappTemplate::create([
            'shortcut' => '/aidat_hatirlatma',
            'title' => 'Aidat Vade Yaklaşıyor (3 Gün Önce)',
            'trigger_event' => 'due_approaching',
            'category' => 'utility',
            'days_offset' => -3,
            'send_time' => '10:30:00',
            'message_template' => 'Sayın {veli_adi}, sporcumuz {ogrenci_adi} için {donem} aidat ödemesinin ({tutar} TL) son ödeme tarihi {vade} dir. Ödemenizi salonumuzda nakit/kart ile veya IBAN hesabımıza açıklamaya "{ogrenci_adi}" yazarak gerçekleştirebilirsiniz. Teşekkür ederiz. 🙏',
        ]);

        $tplDueOverdue = WhatsappTemplate::create([
            'shortcut' => '/aidat_gecikme',
            'title' => 'Geciken Aidat Hatırlatması (Vadeden 3 ve 10 Gün Sonra)',
            'trigger_event' => 'due_overdue',
            'category' => 'utility',
            'days_offset' => 3,
            'send_time' => '11:00:00',
            'message_template' => 'Merhaba Sayın {veli_adi}, sporcumuz {ogrenci_adi} ait {donem} döneminden kalan {tutar} TL aidat bakiyenizin vade tarihi ({vade}) geçmiştir. Ödeme yaptıysanız bu mesajı dikkate almayınız; dekontunuzu bu mesaj üzerinden paylaşabilirsiniz. İyi günler dileriz.',
        ]);

        $tplReceipt = WhatsappTemplate::create([
            'shortcut' => '/makbuz',
            'title' => 'Ödeme Alındı & Tahsilat Makbuzu',
            'trigger_event' => 'payment_received',
            'category' => 'utility',
            'days_offset' => 0,
            'send_time' => '09:00:00',
            'message_template' => 'Sayın {veli_adi}, sporcumuz {ogrenci_adi} için {tutar} TL tutarındaki ödemeniz alınmıştır (Makbuz No: {makbuz_no}). Dijital makbuzunuzu Veli Panelinden görüntüleyebilirsiniz. Enerjik Cimnastik ailesi olarak teşekkür ederiz! 🌟',
        ]);

        $tplAbsence = WhatsappTemplate::create([
            'shortcut' => '/devamsizlik',
            'title' => 'Üst Üste 2 Ders Devamsızlık Uyarısı',
            'trigger_event' => 'absence_warning',
            'category' => 'utility',
            'days_offset' => 0,
            'send_time' => '20:00:00',
            'message_template' => 'Merhaba {veli_adi}, sporcumuz {ogrenci_adi} son 2 antrenmanımıza katılamamıştır. Her şeyin yolunda olduğunu umuyoruz 🙏 Raporlu veya izinli durumlarda telafi dersi planlaması için bizimle iletişime geçebilirsiniz.',
        ]);

        $tplMedical = WhatsappTemplate::create([
            'shortcut' => '/rapor_bitis',
            'title' => 'Sağlık Raporu Yenileme Hatırlatması (15 Gün Önce)',
            'trigger_event' => 'medical_report_expiring',
            'category' => 'utility',
            'days_offset' => -15,
            'send_time' => '12:00:00',
            'message_template' => 'Sayın {veli_adi}, sporcumuz {ogrenci_adi} ait "Spor Yapabilir Sağlık Raporu" süresi {vade} tarihinde dolacaktır. Antrenmanlara kesintisiz devam edebilmesi için yenilenen raporu sekreterliğimize veya WhatsApp hattımıza iletmenizi rica ederiz.',
        ]);

        $tplBirthday = WhatsappTemplate::create([
            'shortcut' => '/dogum_gunu',
            'title' => 'Doğum Günü Kutlaması',
            'trigger_event' => 'birthday',
            'category' => 'marketing',
            'days_offset' => 0,
            'send_time' => '09:30:00',
            'message_template' => 'İyi ki doğdun {ogrenci_adi}! 🎂🎉 Enerjik Cimnastik ailesi olarak sağlıklı, mutlu, esnek ve başarılarla dolu nice yaşlar diliyoruz! 🤸‍♀️✨',
        ]);

        // 14. Günlük WhatsApp Business Tıkla-Gönder Kuyruğu Örnekleri
        WhatsappMessage::create([
            'template_id' => $tplDueOverdue->id,
            'student_id' => $st3->id,
            'recipient_name' => 'Zeynep Çelik',
            'recipient_phone' => '05354445566',
            'message_text' => 'Merhaba Sayın Zeynep Çelik, sporcumuz Defne Çelik ait Ekim 2026 Aidatı döneminden kalan 3.200,00 TL aidat bakiyenizin vade tarihi (07.10.2026) geçmiştir. Ödeme yaptıysanız bu mesajı dikkate almayınız; dekontunuzu bu mesaj üzerinden paylaşabilirsiniz. İyi günler dileriz.',
            'status' => 'queued',
        ]);

        WhatsappMessage::create([
            'template_id' => $tplAbsence->id,
            'student_id' => $st3->id,
            'recipient_name' => 'Zeynep Çelik',
            'recipient_phone' => '05354445566',
            'message_text' => 'Merhaba Zeynep Çelik, sporcumuz Defne Çelik son 2 antrenmanımıza katılamamıştır. Her şeyin yolunda olduğunu umuyoruz 🙏 Raporlu veya izinli durumlarda telafi dersi planlaması için bizimle iletişime geçebilirsiniz.',
            'status' => 'queued',
        ]);

        WhatsappMessage::create([
            'template_id' => $tplMedical->id,
            'student_id' => $st2->id,
            'recipient_name' => 'Murat Arslan',
            'recipient_phone' => '05332223344',
            'message_text' => 'Sayın Murat Arslan, sporcumuz Kerem Arslan ait "Spor Yapabilir Sağlık Raporu" süresi ' . now()->addDays(12)->format('d.m.Y') . ' tarihinde dolacaktır. Antrenmanlara kesintisiz devam edebilmesi için yenilenen raporu sekreterliğimize veya WhatsApp hattımıza iletmenizi rica ederiz.',
            'status' => 'queued',
        ]);

        WhatsappMessage::create([
            'template_id' => $tplBirthday->id,
            'student_id' => $st3->id,
            'recipient_name' => 'Zeynep Çelik (Defne Çelik)',
            'recipient_phone' => '05354445566',
            'message_text' => 'İyi ki doğdun Defne Çelik! 🎂🎉 Enerjik Cimnastik ailesi olarak sağlıklı, mutlu, esnek ve başarılarla dolu nice yaşlar diliyoruz! 🤸‍♀️✨',
            'status' => 'queued',
        ]);

        WhatsappMessage::create([
            'template_id' => $tplTrial->id,
            'student_id' => null,
            'recipient_name' => 'Gökhan Özkan (Aday Veli - Nil Özkan)',
            'recipient_phone' => '05329998877',
            'message_text' => 'Merhaba Gökhan Özkan 👋 Enerjik Cimnastik Spor Kulübü olarak yarın (' . now()->addDay()->format('d.m.Y') . ') saat 10:00 grubumuzda Nil Özkan ile ücretsiz deneme dersimizde buluşmayı heyecanla bekliyoruz! 🤸 Rahat bir eşofman, temiz çorap ve su matarası getirmeniz yeterlidir.',
            'status' => 'queued',
        ]);

        // 15. Duyurular, SSS, Galeri ve Site Ayarları
        Announcement::insert([
            [
                'title' => '29 Ekim Cumhuriyet Gösterisi Provaları Başlıyor!',
                'content' => '29 Ekim Perşembe günü saat 14:00 te yapılacak salon içi gösterimiz için tüm gruplarımızın kulüp mayolarıyla antrenmana katılmasını rica ederiz.',
                'target_audience' => 'all',
                'is_pinned' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Kış Dönemi Hafta Sonu Yeni Kontenjanları Açıldı',
                'content' => '3-5 yaş Okul Öncesi ve 6-9 yaş Artistik Cimnastik gruplarımızda sınırlı sayıda kontenjanımız açılmıştır. Kardeş kayıtlarında %15 indirim uygulanmaktadır.',
                'target_audience' => 'public_site',
                'is_pinned' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Faq::insert([
            [
                'question' => 'Deneme dersine gelirken ne getirmeliyiz?',
                'answer' => 'Esnek ve rahat bir eşofman/tayt, temiz bir çorap ve su matarası yeterlidir. İlk deneme dersimiz ücretsizdir ve antrenörlerimiz çocuğunuzun yaş/seviye değerlendirmesini ders sonunda sizinle paylaşır.',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Kaçırılan dersler için telafi hakkı var mı?',
                'answer' => 'Evet, önceden bilgi verilen veya sağlık raporlu devamsızlıklarda ayda 2 derse kadar telafi hakkı tanımlanır ve uygun bir grupta telafi yapılır.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Sağlık raporu ve KVKK onayı zorunlu mu?',
                'answer' => 'Evet, Türkiye Cimnastik Federasyonu ve spor kulübü mevzuatı gereği kesin kayıtta "Spor Yapmasında Sakınca Yoktur" sağlık beyanı/raporu ve KVKK aydınlatma onayı alınmaktadır.',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        GalleryItem::insert([
            [
                'title' => 'Artistik Cimnastik Denge Aleti Çalışması',
                'category' => 'Antrenman',
                'image_url' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80',
                'student_id' => $st1->id,
                'photo_consent_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Okul Öncesi Motor Gelişim Parkuru',
                'category' => 'Antrenman',
                'image_url' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=800&q=80',
                'student_id' => $st2->id,
                'photo_consent_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Yıl Sonu Madalya ve Gösteri Törenimiz',
                'category' => 'Gösteri',
                'image_url' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=800&q=80',
                'student_id' => null,
                'photo_consent_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $settings = [
            ['key' => 'club_name', 'value' => 'Enerjik Cimnastik Spor Kulübü', 'group' => 'general'],
            ['key' => 'hero_title', 'value' => 'Çocuğunuzun Enerjisini Esneklik, Özgüven ve Disipline Dönüştürüyoruz!', 'group' => 'general'],
            ['key' => 'hero_subtitle', 'value' => '3-16 yaş arası çocuklar ve yetişkinler için federasyon onaylı antrenörler eşliğinde maksimum 12 kişilik gruplarda güvenli cimnastik eğitimi.', 'group' => 'general'],
            ['key' => 'city_district', 'value' => 'Tekirdağ / Süleymanpaşa & Çorlu', 'group' => 'seo'],
            ['key' => 'seo_title', 'value' => 'Enerjik Cimnastik | Çocuk & Artistik & Ritmik Cimnastik Kursu', 'group' => 'seo'],
            ['key' => 'phone', 'value' => '0532 100 10 01', 'group' => 'contact'],
            ['key' => 'whatsapp_number', 'value' => '905321001001', 'group' => 'contact'],
            ['key' => 'address', 'value' => 'Atatürk Mah. Olimpiyat Cad. No: 24/A Enerjik Plaza', 'group' => 'contact'],
            ['key' => 'working_hours', 'value' => 'Salı - Pazar: 09:30 - 20:30 (Pazartesi İdari İzin)', 'group' => 'contact'],
            ['key' => 'iban_holder', 'value' => 'Enerjik Cimnastik Spor Kulübü Ltd. Şti.', 'group' => 'finance'],
            ['key' => 'iban_number', 'value' => 'TR45 0006 2000 1230 0006 2999 01', 'group' => 'finance'],
            ['key' => 'kvkk_text', 'value' => '6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, Enerjik Cimnastik Spor Kulübü olarak veri sorumlusu sıfatıyla; reşit olmayan sporcularımızın ve velilerimizin kimlik, iletişim, sağlık beyanı ve görsel kayıt verilerini yalnızca kulüp faaliyetlerinin yürütülmesi, acil durum güvenliği ve Türkiye Cimnastik Federasyonu lisans süreçleri kapsamında işliyor, üçüncü kişilerle paylaşmıyoruz.', 'group' => 'kvkk'],
        ];
        foreach ($settings as $s) {
            SiteSetting::create($s);
        }

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'sistem_kurulumu',
            'entity_type' => 'System',
            'entity_id' => 1,
            'description' => 'Enerjik Cimnastik tüm 16 modülü, örnek verileri ve WhatsApp Business hazır cevap şablonları ile başlatıldı.',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
