<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DynamicOptionListsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'admin-options@test.local',
            'phone' => '5320000001',
            'role' => 'super_admin',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => [],
        ]);
    }

    public function test_admin_can_add_a_custom_hall_area(): void
    {
        $admin = $this->makeAdmin();

        $this->withSession(['admin_user_id' => $admin->id])
            ->post(route('admin.options.update'), [
                'option_key' => 'custom_hall_areas',
                'operation' => 'add',
                'new_option' => 'Yeni Spor Salonu',
                'return_tab' => 'gruplar',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('site_settings', ['key' => 'custom_hall_areas']);
        $value = \App\Models\SiteSetting::where('key', 'custom_hall_areas')->value('value');
        $this->assertStringContainsString('Yeni Spor Salonu', $value);
    }

    public function test_admin_can_add_and_remove_an_income_category_independently_of_expense_categories(): void
    {
        $admin = $this->makeAdmin();
        $session = $this->withSession(['admin_user_id' => $admin->id]);

        $session->post(route('admin.options.update'), [
            'option_key' => 'custom_income_categories',
            'operation' => 'add',
            'new_option' => 'Yaz Kampı Geliri',
            'return_tab' => 'kasa',
        ])->assertRedirect();

        $incomeValue = \App\Models\SiteSetting::where('key', 'custom_income_categories')->value('value');
        $this->assertStringContainsString('Yaz Kampı Geliri', $incomeValue);
        $expenseValue = \App\Models\SiteSetting::where('key', 'custom_expense_categories')->value('value');
        $this->assertNull($expenseValue, 'Gelir kategorisi eklemek gider listesini etkilememeli.');

        $this->withSession(['admin_user_id' => $admin->id])->post(route('admin.options.update'), [
            'option_key' => 'custom_income_categories',
            'operation' => 'remove',
            'remove_option' => 'Yaz Kampı Geliri',
            'return_tab' => 'kasa',
        ])->assertRedirect();

        $incomeValue = \App\Models\SiteSetting::where('key', 'custom_income_categories')->value('value');
        $this->assertStringNotContainsString('Yaz Kampı Geliri', $incomeValue);
    }

    public function test_kasa_tab_renders_separate_income_and_expense_category_lists(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->withSession(['admin_user_id' => $admin->id])->get('/panel?tab=kasa');

        $response->assertOk()
            ->assertSee('Gelir Kategorileri')
            ->assertSee('Gider Kategorileri')
            ->assertSee('Aidat / Ders Ücreti') // varsayılan gelir kategorisi
            ->assertSee('Antrenör Hakedişi'); // varsayılan gider kategorisi
    }
}
