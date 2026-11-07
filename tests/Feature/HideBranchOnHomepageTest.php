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

    public function test_hidden_branch_stays_available_in_the_admin_panel(): void
    {
        $this->branch->update(['is_active' => false]);

        $this->withSession(['admin_user_id' => $this->admin->id])
            ->get('/panel?tab=site_icerigi')
            ->assertOk()
            ->assertSee('Yarisma Bransi');
    }
}
