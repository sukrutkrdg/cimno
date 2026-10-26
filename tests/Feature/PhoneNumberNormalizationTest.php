<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Package;
use App\Models\Student;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhoneNumberNormalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'admin-phone@test.local',
            'phone' => '5320000000',
            'role' => 'super_admin',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => [],
        ]);
    }

    public function test_phone_normalize_strips_leading_zero_and_country_code(): void
    {
        $this->assertSame('5332223344', Phone::normalize('0533 222 33 44'));
        $this->assertSame('5332223344', Phone::normalize('+90 533 222 33 44'));
        $this->assertSame('5332223344', Phone::normalize('905332223344'));
        $this->assertSame('5332223344', Phone::normalize('5332223344'));
        $this->assertNull(Phone::normalize('12345'));
        $this->assertNull(Phone::normalize(null));
    }

    public function test_public_trial_lead_form_stores_phone_without_leading_zero(): void
    {
        $this->post('/on-kayit', [
            'parent_name' => 'Test Veli',
            'parent_phone' => '0533 222 33 44',
            'student_name' => 'Test Çocuk',
            'student_age' => 6,
            'kvkk_approved' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('leads', ['parent_name' => 'Test Veli', 'parent_phone' => '5332223344']);
    }

    public function test_public_trial_lead_form_rejects_an_invalid_phone_number(): void
    {
        $response = $this->from('/#on-kayit')->post('/on-kayit', [
            'parent_name' => 'Test Veli',
            'parent_phone' => '12345',
            'student_name' => 'Test Çocuk',
            'student_age' => 6,
            'kvkk_approved' => '1',
        ]);

        $response->assertSessionHasErrors('parent_phone');
        $this->assertDatabaseMissing('leads', ['parent_name' => 'Test Veli']);
    }

    public function test_admin_student_form_stores_guardian_phones_without_leading_zero(): void
    {
        $admin = $this->makeAdmin();
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-phone', 'age_range' => '4-12', 'is_active' => true]);
        $group = Group::create(['branch_id' => $branch->id, 'name' => 'Grup', 'capacity' => 10, 'is_active' => true]);
        $package = Package::create(['name' => 'Paket', 'price' => 100, 'lessons_count' => 8, 'is_active' => true, 'show_on_site' => true]);

        $this->withSession(['admin_user_id' => $admin->id])
            ->post('/panel/ogrenciler', [
                'full_name' => 'Test Öğrenci',
                'gender' => 'Belirtilmedi',
                'branch_id' => $branch->id,
                'group_id' => $group->id,
                'package_id' => $package->id,
                'parent_name' => 'Test Veli',
                'parent_phone' => '0533 222 33 44',
                'secondary_parent_phone' => '0544 555 66 77',
            ])->assertRedirect();

        $student = Student::where('full_name', 'Test Öğrenci')->firstOrFail();
        $this->assertSame('5332223344', $student->parent_phone);
        $this->assertSame('5445556677', $student->secondary_parent_phone);
    }

    public function test_admin_student_form_rejects_an_invalid_guardian_phone(): void
    {
        $admin = $this->makeAdmin();
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-phone-2', 'age_range' => '4-12', 'is_active' => true]);
        $group = Group::create(['branch_id' => $branch->id, 'name' => 'Grup', 'capacity' => 10, 'is_active' => true]);
        $package = Package::create(['name' => 'Paket', 'price' => 100, 'lessons_count' => 8, 'is_active' => true, 'show_on_site' => true]);

        $response = $this->withSession(['admin_user_id' => $admin->id])
            ->from('/panel?tab=ogrenciler')
            ->post('/panel/ogrenciler', [
                'full_name' => 'Geçersiz Telefon Öğrenci',
                'gender' => 'Belirtilmedi',
                'branch_id' => $branch->id,
                'group_id' => $group->id,
                'package_id' => $package->id,
                'parent_name' => 'Test Veli',
                'parent_phone' => '123',
            ]);

        $response->assertSessionHasErrors('parent_phone');
        $this->assertDatabaseMissing('students', ['full_name' => 'Geçersiz Telefon Öğrenci']);
    }
}
