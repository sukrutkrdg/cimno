<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Veli bildirisi: "/mobil"'de QR Yoklama'ya dokununca veya salon afişini
// okutunca alt menü değişiyor, sanki uygulama baştan başlıyor gibi hissettiriyor.
// Kök sebep: /mobil ve /qr-yoklama birbirinden tamamen farklı (5 vs 3 sütunlu,
// farklı ikon/etiket/sıralı) alt navigasyon barlarına sahipti. Bu testler, artık
// her iki sayfanın AYNI paylaşılan partial'dan (public.partials._bottom_nav) gelen
// aynı 5 sekmeyi gösterdiğini ve aktif sekme vurgusunun doğru çalıştığını kanıtlar.
class ParentNavConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function makeLoggedInStudent(string $phone, string $pin, string $slugSuffix): Student
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-nav-' . $slugSuffix, 'age_range' => '4-12', 'is_active' => true]);
        $coach = User::create([
            'name' => 'Antrenör', 'email' => 'antrenor-nav-' . $slugSuffix . '@test.local', 'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
        ]);
        $group = Group::create(['branch_id' => $branch->id, 'coach_id' => $coach->id, 'name' => 'Nav Grubu ' . $slugSuffix, 'capacity' => 10, 'is_active' => true]);

        return Student::create([
            'full_name' => 'Nav Öğrencisi ' . $slugSuffix, 'branch_id' => $branch->id, 'group_id' => $group->id,
            'parent_name' => 'Nav Veli ' . $slugSuffix, 'parent_phone' => $phone, 'portal_pin' => $pin, 'status' => 'active',
        ]);
    }

    public function test_mobil_and_qr_yoklama_show_the_exact_same_five_bottom_nav_tabs_when_logged_in(): void
    {
        $this->makeLoggedInStudent('5331119001', '1001', 'a');
        $session = ['logged_parent_phone' => '5331119001', 'logged_parent_pin' => '1001'];

        $mobil = $this->withSession($session)->get('/mobil')->assertOk();
        $qr = $this->withSession($session)->get('/qr-yoklama')->assertOk();

        foreach (['Öğrencim', 'QR Yoklama', 'Aidat & Makbuz', 'Gelişim Karnesi', 'Duyurular'] as $label) {
            $mobil->assertSee($label, false);
            $qr->assertSee($label, false);
        }

        // Önceden /qr-yoklama'da hiç bulunmayan, /mobil'e özel sekmeler artık her ikisinde de var.
        $qr->assertSee('Gelişim Karnesi')->assertSee('Duyurular')->assertSee('Aidat & Makbuz', false);

        // Önceki /qr-yoklama menüsüne özel, /mobil'de karşılığı olmayan nav sekmesi artık
        // hiçbir yerde yok ("Antrenör" metni antrenör adı etiketi olarak başka yerde geçerli
        // biçimde geçtiğinden, eski nav sekmesine özgü 🎽 ikonunu kontrol ediyoruz).
        $mobil->assertDontSee('Veli Ekranı')->assertDontSee('🎽');
        $qr->assertDontSee('Veli Ekranı')->assertDontSee('🎽');
    }

    public function test_bottom_nav_is_hidden_on_both_pages_when_the_parent_is_not_logged_in(): void
    {
        $this->get('/mobil')->assertOk()->assertDontSee('Gelişim Karnesi')->assertDontSee('Aidat & Makbuz', false);
        $this->get('/qr-yoklama')->assertOk()->assertDontSee('Gelişim Karnesi')->assertDontSee('Aidat & Makbuz', false);
    }

    public function test_active_tab_highlighting_reflects_which_page_the_parent_is_actually_on(): void
    {
        $this->makeLoggedInStudent('5331119002', '1002', 'b');
        $session = ['logged_parent_phone' => '5331119002', 'logged_parent_pin' => '1002'];

        // /mobil (varsayılan sekme=cocuklarim): "Öğrencim" sekmesi indigo ile aktif vurgulanmalı.
        $mobilHtml = $this->withSession($session)->get('/mobil')->assertOk()->getContent();
        $this->assertStringContainsString('text-indigo-600 font-extrabold', $mobilHtml);

        // /qr-yoklama: "Öğrencim" artık aktif değil, çünkü o an gerçekten o sayfada değiliz —
        // orada tek "aktif" görünen sekme QR Yoklama'nın kendisi (emerald vurgusu).
        $qrHtml = $this->withSession($session)->get('/qr-yoklama')->assertOk()->getContent();
        $this->assertStringNotContainsString('text-indigo-600 font-extrabold', $qrHtml);
        $this->assertStringContainsString('text-emerald-600 font-extrabold', $qrHtml);
    }

    public function test_tapping_qr_yoklama_from_mobil_lands_already_logged_in_with_no_relogin_prompt(): void
    {
        $this->makeLoggedInStudent('5331119003', '1003', 'c');
        $session = ['logged_parent_phone' => '5331119003', 'logged_parent_pin' => '1003'];

        // Alt barın QR Yoklama linki artık ?phone= taşımıyor, sadece ?kamera=1 (bkz. partials/_bottom_nav.blade.php).
        $response = $this->withSession($session)->get('/qr-yoklama?kamera=1');

        $response->assertOk()
            ->assertSee('Giriş Yapıldı')
            ->assertSee('Nav Veli c')
            ->assertDontSee('1. Adım: Veli Telefon Numaranız');
    }

    public function test_visiting_mobil_with_only_a_phone_query_param_does_not_show_a_spurious_pin_error_for_an_already_logged_in_parent(): void
    {
        // Bu, giderilen gerçek bir hataydı: paylaşılan navigasyon barından "phone"
        // query param'ı PIN'siz geldiğinde, mobilePwa() bunu geçersiz bir yeni giriş
        // denemesi sayıp zaten oturumu açık veliye "PIN girin" hatası gösteriyordu.
        $this->makeLoggedInStudent('5331119004', '1004', 'd');
        $session = ['logged_parent_phone' => '5331119004', 'logged_parent_pin' => '1004'];

        $this->withSession($session)->get('/mobil?phone=5331119004')
            ->assertOk()
            ->assertSee('Nav Veli d')
            ->assertDontSee('Lütfen kayıt sırasında size verilen 4 haneli PIN kodunu girin.');
    }

    public function test_logging_out_from_qr_yoklama_also_logs_the_parent_out_of_mobil(): void
    {
        $this->makeLoggedInStudent('5331119005', '1005', 'e');
        $session = ['logged_parent_phone' => '5331119005', 'logged_parent_pin' => '1005'];

        $this->withSession($session)->get('/qr-yoklama?cikis=1')->assertRedirect(route('public.qr.checkin'));
        $this->assertNull(session('logged_parent_phone'));

        $this->get('/mobil')->assertOk()->assertSee('Veli Girişi')->assertDontSee('Nav Veli e');
    }

    public function test_logging_out_from_mobil_also_logs_the_parent_out_of_qr_yoklama(): void
    {
        $this->makeLoggedInStudent('5331119006', '1006', 'f');
        $session = ['logged_parent_phone' => '5331119006', 'logged_parent_pin' => '1006'];

        $this->withSession($session)->get('/mobil?cikis=1')->assertRedirect(route('public.mobile.pwa'));
        $this->assertNull(session('logged_parent_phone'));

        $this->get('/qr-yoklama')->assertOk()->assertSee('1. Adım: Veli Telefon Numaranız')->assertDontSee('Nav Veli f');
    }
}
