<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LeadSourceManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeStaff(string $role, array $permissions = []): User
    {
        return User::create([
            'name' => 'Test ' . $role, 'email' => $role . '-kaynak@test.local', 'phone' => '5320000007',
            'role' => $role, 'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'), 'is_active' => true, 'permissions' => $permissions,
        ]);
    }

    private function makeLead(string $source): Lead
    {
        return Lead::create([
            'parent_name' => 'Veli', 'parent_phone' => '5320000008', 'student_name' => 'Aday',
            'student_age' => 6, 'source' => $source, 'status' => 'new',
        ]);
    }

    private function renameSource(User $admin, string $old, string $new): void
    {
        $this->withSession(['admin_user_id' => $admin->id])->post(route('admin.options.update'), [
            'option_key' => 'custom_lead_sources',
            'operation' => 'rename',
            'old_option' => $old,
            'new_option' => $new,
            'return_tab' => 'adaylar',
        ])->assertRedirect();
    }

    private function submitPublicForm(): void
    {
        $this->post(route('public.trial.submit'), [
            'parent_name' => 'Site Velisi', 'parent_phone' => '5321234567', 'student_name' => 'Site Adayi',
            'student_age' => 7, 'kvkk_approved' => '1',
        ])->assertRedirect();
    }

    public function test_renaming_a_source_updates_the_list_and_existing_leads(): void
    {
        $lead = $this->makeLead('instagram');

        $this->renameSource($this->makeStaff('super_admin'), 'instagram', 'Instagram Reklam');

        $this->assertSame('Instagram Reklam', $lead->fresh()->source);
        $this->assertContains('Instagram Reklam', explode('|', SiteSetting::where('key', 'custom_lead_sources')->value('value')));
    }

    public function test_public_form_uses_the_new_name_after_website_source_is_renamed_twice(): void
    {
        $admin = $this->makeStaff('super_admin');

        $this->renameSource($admin, 'website', 'Web Sitesi');
        $this->submitPublicForm();
        $this->assertSame('Web Sitesi', Lead::where('student_name', 'Site Adayi')->value('source'));

        $this->renameSource($admin, 'Web Sitesi', 'Site');
        $this->assertSame('Site', Lead::where('student_name', 'Site Adayi')->value('source'));
    }

    public function test_renaming_another_source_does_not_change_the_public_form_source(): void
    {
        $this->renameSource($this->makeStaff('super_admin'), 'telefon', 'Telefon Araması');

        $this->submitPublicForm();

        $this->assertSame('website', Lead::where('student_name', 'Site Adayi')->value('source'));
    }

    public function test_lead_edit_form_keeps_a_source_that_was_removed_from_the_list(): void
    {
        $this->makeLead('Eski Kaynak');

        $this->withSession(['admin_user_id' => $this->makeStaff('super_admin')->id])
            ->get('/panel?tab=adaylar')
            ->assertOk()
            ->assertSee('<option value="Eski Kaynak" selected>', false);
    }

    public function test_management_card_is_hidden_from_staff_who_cannot_edit_content(): void
    {
        $secretary = $this->makeStaff('secretary', ['edit_content' => false]);

        $this->withSession(['admin_user_id' => $secretary->id])
            ->get('/panel?tab=adaylar')
            ->assertOk()
            ->assertDontSee('Aday Kaynaklarını Yönet');
    }

    public function test_management_card_is_shown_to_super_admin(): void
    {
        $this->withSession(['admin_user_id' => $this->makeStaff('super_admin')->id])
            ->get('/panel?tab=adaylar')
            ->assertOk()
            ->assertSee('Aday Kaynaklarını Yönet');
    }
}
