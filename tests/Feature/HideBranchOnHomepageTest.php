<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HideBranchOnHomepageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin', 'email' => 'admin-brans@test.local', 'phone' => '5320000005',
            'role' => 'super_admin', 'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'), 'is_active' => true, 'permissions' => [],
        ]);
        $this->branch = Branch::create([
            'name' => 'Yarisma Bransi', 'slug' => 'yarisma-bransi', 'age_range' => '6-12 Yaş',
            'level' => 'Performans', 'is_active' => true,
        ]);
        Group::create([
            'branch_id' => $this->branch->id, 'name' => 'Yarisma Grubu A',
            'capacity' => 10, 'is_active' => true,
        ]);
    }

    private function saveBranch(array $extra = []): void
    {
        $this->withSession(['admin_user_id' => $this->admin->id])
            ->post(route('admin.branches.update', $this->branch->id), array_merge([
                'name' => $this->branch->name,
                'return_tab' => 'site_icerigi',
            ], $extra))
            ->assertRedirect();
    }

    public function test_visible_branch_appears_on_the_homepage(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<option value="' . $this->branch->id . '">Yarisma Bransi', false)
            ->assertSee('Yarisma Grubu A');
    }

    public function test_hidden_branch_and_its_groups_disappear_from_the_homepage(): void
    {
        $this->saveBranch(['hide_on_site' => '1']);

        $this->assertDatabaseHas('branches', ['id' => $this->branch->id, 'is_active' => false]);
        $this->get('/')->assertOk()
            ->assertDontSee('Yarisma Bransi')
            ->assertDontSee('Yarisma Grubu A');
    }

    public function test_unticking_the_box_brings_the_branch_back(): void
    {
        $this->branch->update(['is_active' => false]);

        $this->saveBranch();

        $this->get('/')->assertOk()->assertSee('Yarisma Bransi');
    }

    public function test_groups_previously_switched_off_are_visible_again_while_their_branch_is_visible(): void
    {
        Group::where('branch_id', $this->branch->id)->update(['show_in_trial_signup' => false]);

        $this->get('/')->assertOk()
            ->assertSee('Yarisma Grubu A')
            ->assertSee('Yarisma Bransi');
    }

    public function test_saving_a_group_does_not_need_the_removed_checkbox(): void
    {
        $group = Group::where('branch_id', $this->branch->id)->first();

        $this->withSession(['admin_user_id' => $this->admin->id])
            ->get('/panel?tab=gruplar')
            ->assertOk()
            ->assertDontSee('name="show_in_trial_signup"', false);

        $this->withSession(['admin_user_id' => $this->admin->id])
            ->post(route('admin.groups.update', $group->id), [
                'name' => 'Yarisma Grubu Yeni',
                'branch_id' => $this->branch->id,
                'capacity' => 10,
            ])
            ->assertRedirect();

        $this->get('/')->assertSee('Yarisma Grubu Yeni');
    }

    public function test_groups_of_a_hidden_branch_cannot_be_picked_for_a_trial_lesson(): void
    {
        $visibleBranch = Branch::create(['name' => 'Acik Brans', 'slug' => 'acik-brans', 'age_range' => '4-6 Yaş', 'level' => 'Başlangıç', 'is_active' => true]);
        Group::create(['branch_id' => $visibleBranch->id, 'name' => 'Acik Grup', 'capacity' => 10, 'is_active' => true]);
        $this->branch->update(['is_active' => false]);

        $response = $this->withSession(['admin_user_id' => $this->admin->id])->get('/panel?tab=adaylar');

        $eligibleNames = $response->viewData('trialEligibleGroups')->pluck('name')->all();
        $this->assertContains('Acik Grup', $eligibleNames);
        $this->assertNotContains('Yarisma Grubu A', $eligibleNames);
    }

    public function test_hidden_branch_stays_available_in_the_admin_panel(): void
    {
        $this->branch->update(['is_active' => false]);

        $this->withSession(['admin_user_id' => $this->admin->id])
            ->get('/panel?tab=site_icerigi')
            ->assertOk()
            ->assertSee('Yarisma Bransi');
    }
}
