<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DynamicTabPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Test Admin', 'email' => 'admin-tabs@test.local', 'phone' => '5320000002',
            'role' => 'super_admin', 'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'), 'is_active' => true, 'permissions' => [],
        ]);
    }

    protected function makeSecretary(array $permissions = []): User
    {
        return User::create([
            'name' => 'Test Sekreter', 'email' => 'sekreter-tabs@test.local', 'phone' => '5320000003',
            'role' => 'secretary', 'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'), 'is_active' => true, 'permissions' => $permissions,
        ]);
    }

    public function test_secretary_cannot_see_kasa_tab_by_default(): void
    {
        $secretary = $this->makeSecretary();
        $this->assertNotContains('kasa', $secretary->allowedTabs());

        // Controller, yetkisi olmayan bir ?tab= değerini sessizce 'pano'ya
        // düşürür (bkz. AdminPanelController::index), bu yüzden kasa'ya özgü
        // içerik hiç render edilmemiş olmalı.
        $response = $this->withSession(['admin_user_id' => $secretary->id])->get('/panel?tab=kasa');
        $response->assertOk()->assertDontSee('Gelir Kategorileri');
    }

    public function test_super_admin_can_grant_a_secretary_view_access_to_kasa_tab(): void
    {
        $admin = $this->makeAdmin();
        $secretary = $this->makeSecretary();

        $this->withSession(['admin_user_id' => $admin->id])->post(route('admin.staff.permissions', $secretary->id), [
            'role' => 'secretary',
            'name' => $secretary->name,
            'email' => $secretary->email,
            'phone' => $secretary->phone,
            'compensation_type' => 'none',
            'is_active' => '1',
            'perm_view_kasa' => '1',
        ])->assertRedirect();

        $secretary->refresh();
        $this->assertContains('kasa', $secretary->allowedTabs());

        $response = $this->withSession(['admin_user_id' => $secretary->id])->get('/panel?tab=kasa');
        $response->assertOk()->assertSee('Gelir Kategorileri');
    }

    public function test_existing_secretary_account_keeps_its_original_tab_access_untouched(): void
    {
        // Yeni "view_X" anahtarları stored permissions'ta hiç yoksa, davranış
        // bu özellik eklenmeden ÖNCEKİ sabit kodlanmış rol varsayılanlarıyla
        // birebir aynı kalmalı (geriye dönük uyumluluk garantisi).
        $secretary = $this->makeSecretary(['send_whatsapp' => true, 'edit_content' => false]);

        $tabs = $secretary->allowedTabs();
        $this->assertContains('pano', $tabs);
        $this->assertContains('adaylar', $tabs);
        $this->assertContains('mesajlar', $tabs);
        $this->assertNotContains('site_icerigi', $tabs);
        $this->assertNotContains('kasa', $tabs);
        $this->assertNotContains('personel', $tabs);
    }
}
