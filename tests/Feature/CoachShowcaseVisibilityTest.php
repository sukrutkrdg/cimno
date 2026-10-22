<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoachShowcaseVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSuperAdmin(): User
    {
        return User::create([
            'name' => 'Test Yönetici', 'email' => 'yonetici-coach@test.local', 'phone' => '05321234567',
            'role' => 'super_admin', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
            'permissions' => ['all' => true],
        ]);
    }

    protected function makeCoach(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test Antrenör',
            'email' => 'antrenor-' . uniqid() . '@test.local',
            'phone' => '0532' . random_int(1000000, 9999999),
            'role' => 'coach',
            'compensation_type' => 'none',
            'password' => Hash::make('x'),
            'is_active' => true,
            'specialty' => 'Artistik Cimnastik',
            'bio' => 'Deneyimli antrenör.',
        ], $overrides));
    }

    public function test_homepage_only_shows_coaches_with_show_on_website_enabled(): void
    {
        $visible = $this->makeCoach(['name' => 'Görünür Antrenör']);
        $hidden = $this->makeCoach(['name' => 'Gizli Antrenör', 'show_on_website' => false]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Görünür Antrenör');
        $response->assertDontSee('Gizli Antrenör');
    }

    public function test_super_admin_can_toggle_coach_visibility_without_deleting_staff_record(): void
    {
        $admin = $this->makeSuperAdmin();
        $coach = $this->makeCoach()->refresh();
        $this->assertTrue($coach->show_on_website);

        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.coaches.toggle_visibility', $coach->id))
            ->assertRedirect();

        $this->assertFalse($coach->refresh()->show_on_website);
        $this->assertNotNull(User::find($coach->id), 'Personel kaydı silinmemeli, sadece vitrinden kalkmalı.');

        // Tekrar basınca eski haline döner.
        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.coaches.toggle_visibility', $coach->id));
        $this->assertTrue($coach->refresh()->show_on_website);
    }

    public function test_deactivated_staff_coach_does_not_show_even_if_marked_visible(): void
    {
        $this->makeCoach(['name' => 'Ayrılan Antrenör', 'is_active' => false, 'show_on_website' => true]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Ayrılan Antrenör');
    }
}
