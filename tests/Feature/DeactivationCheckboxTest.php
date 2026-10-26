<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeactivationCheckboxTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'admin-deactivate@test.local',
            'phone' => '5320000002',
            'role' => 'super_admin',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => [],
        ]);
    }

    public function test_unchecking_kural_aktif_actually_deactivates_a_whatsapp_template(): void
    {
        $admin = $this->makeAdmin();
        $tpl = WhatsappTemplate::create([
            'shortcut' => '/test_sablon',
            'title' => 'Test Şablon',
            'trigger_event' => 'custom',
            'send_time' => '10:00:00',
            'message_template' => 'Merhaba {veli_adi}',
            'is_active' => true,
        ]);

        // Formda checkbox işaretsizse is_active alanı hiç gönderilmez.
        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.whatsapp.template.update', $tpl->id), [
                'title' => $tpl->title,
                'shortcut' => $tpl->shortcut,
                'send_time' => '10:00',
                'days_offset' => 0,
                'message_template' => $tpl->message_template,
            ])
            ->assertRedirect();

        $this->assertFalse($tpl->refresh()->is_active);
    }

    public function test_unchecking_aktif_actually_deactivates_a_staff_account(): void
    {
        $admin = $this->makeAdmin();
        $secretary = User::create([
            'name' => 'Test Sekreter',
            'email' => 'secretary-deactivate@test.local',
            'phone' => '5330000002',
            'role' => 'secretary',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => [],
        ]);

        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.staff.permissions', $secretary->id), [
                'role' => 'secretary',
            ])
            ->assertRedirect();

        $this->assertFalse($secretary->refresh()->is_active);
    }
}
