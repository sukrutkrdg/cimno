<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\FinancialTransaction;
use App\Models\Movement;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EditWithoutDeletingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Test Admin', 'email' => 'admin-duzenle@test.local', 'phone' => '5320000006',
            'role' => 'super_admin', 'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'), 'is_active' => true, 'permissions' => [],
        ]);
        $this->branch = Branch::create([
            'name' => 'Artistik', 'slug' => 'artistik', 'age_range' => '4-12 Yaş', 'level' => 'Başlangıç',
        ]);
        $this->withSession(['admin_user_id' => $this->admin->id]);
    }

    public function test_movement_name_and_branch_can_be_edited(): void
    {
        $movement = Movement::create(['name' => 'Takla', 'is_active' => true]);

        $this->post(route('admin.movements.update', $movement->id), [
            'name' => 'Öne Takla',
            'branch_id' => $this->branch->id,
        ])->assertRedirect();

        $movement->refresh();
        $this->assertSame('Öne Takla', $movement->name);
        $this->assertSame($this->branch->id, $movement->branch_id);
    }

    public function test_movement_can_be_moved_back_to_all_branches(): void
    {
        $movement = Movement::create(['name' => 'Amut', 'branch_id' => $this->branch->id, 'is_active' => true]);

        $this->post(route('admin.movements.update', $movement->id), ['name' => 'Amut', 'branch_id' => ''])
            ->assertRedirect();

        $this->assertNull($movement->fresh()->branch_id);
    }

    public function test_groups_tab_offers_edit_forms_for_branches_and_movements(): void
    {
        $movement = Movement::create(['name' => 'Takla', 'is_active' => true]);

        $this->get('/panel?tab=gruplar')->assertOk()
            ->assertSee(route('admin.branches.update', $this->branch->id))
            ->assertSee(route('admin.movements.update', $movement->id));
    }

    public function test_students_tab_offers_an_edit_form_for_branches(): void
    {
        $this->get('/panel?tab=ogrenciler')->assertOk()
            ->assertSee(route('admin.branches.update', $this->branch->id));
    }

    public function test_quick_edit_form_keeps_a_hidden_branch_marked_as_hidden(): void
    {
        $this->branch->update(['is_active' => false]);

        $this->get('/panel?tab=gruplar')->assertOk()
            ->assertSeeInOrder([route('admin.branches.update', $this->branch->id), 'name="hide_on_site" value="1" checked'], false);
    }

    public function test_clearing_a_required_branch_field_is_rejected_instead_of_crashing(): void
    {
        $this->post(route('admin.branches.update', $this->branch->id), ['name' => 'Artistik', 'age_range' => ''])
            ->assertSessionHasErrors('age_range');

        $this->assertSame('4-12 Yaş', $this->branch->fresh()->age_range);
    }

    public function test_renaming_an_income_category_leaves_expenses_with_the_same_name_alone(): void
    {
        SiteSetting::create(['key' => 'custom_income_categories', 'value' => 'Ortak|Aidat']);
        $income = FinancialTransaction::create(['direction' => 'income', 'category' => 'Ortak', 'title' => 'G', 'amount' => 10, 'transaction_date' => today()]);
        $expense = FinancialTransaction::create(['direction' => 'expense', 'category' => 'Ortak', 'title' => 'C', 'amount' => 10, 'transaction_date' => today()]);

        $this->post(route('admin.options.update'), [
            'option_key' => 'custom_income_categories',
            'operation' => 'rename',
            'old_option' => 'Ortak',
            'new_option' => 'Yeni Ad',
            'return_tab' => 'kasa',
        ])->assertRedirect();

        $this->assertSame('Yeni Ad', $income->fresh()->category);
        $this->assertSame('Ortak', $expense->fresh()->category);
    }

    public function test_renaming_to_an_existing_option_shows_an_error_and_changes_nothing(): void
    {
        SiteSetting::create(['key' => 'custom_hall_areas', 'value' => 'Salon A|Salon B']);

        $this->post(route('admin.options.update'), [
            'option_key' => 'custom_hall_areas',
            'operation' => 'rename',
            'old_option' => 'Salon A',
            'new_option' => 'Salon B',
        ])->assertSessionHas('error');

        $this->assertSame('Salon A|Salon B', SiteSetting::where('key', 'custom_hall_areas')->value('value'));
    }
}
