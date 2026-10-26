<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_parent_can_subscribe_and_unsubscribe(): void
    {
        $this->withSession(['logged_parent_phone' => '05331112233', 'logged_parent_pin' => '1234'])
            ->postJson('/push-abone', [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-1',
                'keys' => ['p256dh' => 'test-public-key', 'auth' => 'test-auth-token'],
            ])
            ->assertOk()
            ->assertJson(['status' => 'subscribed']);

        // Oturumdaki ham değer (0'lı) ne olursa olsun, depolanan parent_phone
        // kanonik 10 haneli (0'sız) biçime normalize edilmiş olmalı.
        $this->assertDatabaseHas('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-1',
            'parent_phone' => '5331112233',
        ]);

        $this->withSession(['logged_parent_phone' => '05331112233'])
            ->postJson('/push-abone/iptal', ['endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-1'])
            ->assertOk()
            ->assertJson(['status' => 'unsubscribed']);

        $this->assertDatabaseMissing('push_subscriptions', ['endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-1']);
    }

    public function test_subscribing_without_a_logged_in_session_is_rejected(): void
    {
        $this->postJson('/push-abone', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/anon-endpoint',
            'keys' => ['p256dh' => 'x', 'auth' => 'y'],
        ])->assertForbidden();

        $this->assertDatabaseMissing('push_subscriptions', ['endpoint' => 'https://fcm.googleapis.com/fcm/send/anon-endpoint']);
    }

    public function test_resubscribing_the_same_endpoint_updates_rather_than_duplicates(): void
    {
        PushSubscription::create([
            'parent_phone' => '05331112233', 'endpoint' => 'https://fcm.googleapis.com/fcm/send/dup',
            'public_key' => 'old-key', 'auth_token' => 'old-auth',
        ]);

        $this->withSession(['logged_parent_phone' => '05331112233'])
            ->postJson('/push-abone', [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/dup',
                'keys' => ['p256dh' => 'new-key', 'auth' => 'new-auth'],
            ])->assertOk();

        $this->assertSame(1, PushSubscription::where('endpoint', 'https://fcm.googleapis.com/fcm/send/dup')->count());
        $this->assertSame('new-key', PushSubscription::where('endpoint', 'https://fcm.googleapis.com/fcm/send/dup')->first()->public_key);
    }
}
