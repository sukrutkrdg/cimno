<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WhatsappMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PendingWhatsappBadgeTest extends TestCase
{
    use RefreshDatabase;

    protected function makeSuperAdmin(): User
    {
        return User::create([
            'name' => 'Test Yönetici', 'email' => 'yonetici-badge@test.local', 'phone' => '05321234567',
            'role' => 'super_admin', 'compensation_type' => 'none', 'password' => Hash::make('x'), 'is_active' => true,
            'permissions' => ['all' => true],
        ]);
    }

    public function test_header_shows_pending_whatsapp_badge_when_messages_are_queued(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->withSession(['admin_user_id' => $admin->id])
            ->get('/panel')
            ->assertOk()
            ->assertDontSee('Bekleyen Mesaj');

        WhatsappMessage::create([
            'recipient_name' => 'Test Veli',
            'recipient_phone' => '05330001111',
            'message_text' => 'Test mesajı',
            'status' => 'queued',
        ]);

        $this->withSession(['admin_user_id' => $admin->id])
            ->get('/panel')
            ->assertOk()
            ->assertSee('1 Bekleyen Mesaj');
    }
}
