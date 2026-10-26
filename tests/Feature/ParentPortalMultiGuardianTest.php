<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPortalMultiGuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_primary_and_secondary_guardian_can_log_in_and_see_the_student(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-veli', 'age_range' => '4-12', 'is_active' => true]);
        $student = Student::create([
            'full_name' => 'Çift Velili Öğrenci',
            'branch_id' => $branch->id,
            'parent_name' => 'Anne Veli',
            'parent_phone' => '5331112233',
            'secondary_parent_name' => 'Baba Veli',
            'secondary_parent_phone' => '5449998877',
            'portal_pin' => '7777',
            'status' => 'active',
        ]);

        // Birincil veli kendi telefonu ve ortak PIN ile girer.
        $this->get('/mobil?phone=05331112233&pin=7777')
            ->assertOk()
            ->assertSee('Çift Velili Öğrenci');

        // Birinci oturumu kapatıp, ikincil veli (farklı telefon, aynı PIN) de aynı öğrenciyi görebilmeli.
        $this->get('/mobil?cikis=1');
        $this->get('/mobil?phone=05449998877&pin=7777')
            ->assertOk()
            ->assertSee('Çift Velili Öğrenci');

        // Oturumu tekrar kapatıp alakasız bir telefon + doğru PIN ile giriş reddedilmeli.
        $this->get('/mobil?cikis=1');
        $this->get('/mobil?phone=05550001122&pin=7777')
            ->assertOk()
            ->assertSee('Telefon numarası veya PIN kodu hatalı');

        $this->assertNotNull($student->secondary_parent_phone);
    }
}
