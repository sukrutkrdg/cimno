<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPortalRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_tab_navigation_without_phone_or_pin_is_never_throttled(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-limit', 'age_range' => '4-12', 'is_active' => true]);
        Student::create([
            'full_name' => 'Limit Testi Öğrenci',
            'branch_id' => $branch->id,
            'parent_name' => 'Limit Veli',
            'parent_phone' => '05330000001',
            'portal_pin' => '1234',
            'status' => 'active',
        ]);

        $this->get('/mobil?phone=05330000001&pin=1234')->assertOk();

        // Giriş yapıldıktan sonra sekmeler arasında phone/pin gönderilmeden onlarca kez
        // gezinmek (eski kod bunu paylaşılan 'no-phone' kovasına düşürüp 10 dakikada
        // 8 istekten sonra TÜM ziyaretçileri 429'a düşürüyordu) artık hiç sınırlanmamalı.
        for ($i = 0; $i < 20; $i++) {
            $this->get('/mobil?sekme=aidat')->assertOk();
            $this->get('/mobil?sekme=karne')->assertOk();
        }
    }

    public function test_repeated_wrong_pin_attempts_for_the_same_phone_are_still_throttled(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-limit-2', 'age_range' => '4-12', 'is_active' => true]);
        Student::create([
            'full_name' => 'Limit Testi Öğrenci 2',
            'branch_id' => $branch->id,
            'parent_name' => 'Limit Veli 2',
            'parent_phone' => '05330000002',
            'portal_pin' => '5678',
            'status' => 'active',
        ]);

        // Aynı telefon için 8 yanlış PIN denemesi limite ulaşır, 9.'su 429 döner.
        for ($i = 0; $i < 8; $i++) {
            $this->get('/mobil?phone=05330000002&pin=0000')
                ->assertOk()
                ->assertSee('Telefon numarası veya PIN kodu hatalı');
        }

        $this->get('/mobil?phone=05330000002&pin=0000')->assertStatus(429);
    }
}
