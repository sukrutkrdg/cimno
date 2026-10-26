<?php

namespace Tests\Feature;

use App\Models\NavMenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NavMenuManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.local',
            'phone' => '0532 000 00 00',
            'role' => 'super_admin',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => [],
        ]);
    }

    public function test_homepage_renders_the_seeded_default_menu_items_in_order(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Branşlarımız', 'Ders Programı', 'Paket & Ücretler', 'Antrenörler', 'Galeri', 'SSS']);
    }

    public function test_admin_can_add_a_new_menu_item_and_it_appears_on_the_homepage(): void
    {
        $admin = $this->makeAdmin();

        $this->withSession(['admin_user_id' => $admin->id])
            ->post('/panel/menu', [
                'label' => 'İletişim',
                'link_type' => 'url',
                'target' => '/veli-portali',
                'placement' => 'header',
                'sort_order' => 99,
            ])
            ->assertRedirect(route('admin.index', ['tab' => 'site_icerigi']));

        $this->assertDatabaseHas('nav_menu_items', ['label' => 'İletişim', 'target' => '/veli-portali']);
        $this->get('/')->assertSee('İletişim');
    }

    public function test_admin_can_hide_a_menu_item_and_it_disappears_from_the_homepage(): void
    {
        $admin = $this->makeAdmin();
        $item = NavMenuItem::create([
            'label' => 'Kaldırılacak Menü',
            'link_type' => 'anchor',
            'target' => 'test-bolum',
            'sort_order' => 50,
            'is_active' => true,
        ]);

        $this->get('/')->assertSee('Kaldırılacak Menü');

        // is_active gönderilmezse (checkbox işaretsiz) öğe gizlenmeli.
        $this->withSession(['admin_user_id' => $admin->id])
            ->post("/panel/menu/{$item->id}/guncelle", [
                'label' => $item->label,
                'link_type' => 'anchor',
                'target' => 'test-bolum',
                'placement' => 'header',
                'sort_order' => 50,
            ])
            ->assertRedirect(route('admin.index', ['tab' => 'site_icerigi']));

        $this->assertFalse($item->refresh()->is_active);
        $this->get('/')->assertDontSee('Kaldırılacak Menü');
    }

    public function test_footer_placement_renders_in_the_footer_not_the_header_nav(): void
    {
        NavMenuItem::create([
            'label' => 'Footer Linki',
            'link_type' => 'url',
            'target' => '/veli-portali',
            'placement' => 'footer',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        NavMenuItem::create([
            'label' => 'Header Linki',
            'link_type' => 'anchor',
            'target' => 'branslar',
            'placement' => 'header',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/');
        $response->assertOk()->assertSee('Footer Linki')->assertSee('Header Linki');

        $html = $response->getContent();
        $footerStart = strpos($html, '<footer');
        $this->assertNotFalse($footerStart);
        // "Footer Linki" sadece <footer> içinde, "Header Linki" ondan önce geçmeli.
        $this->assertGreaterThan($footerStart, strpos($html, 'Footer Linki'));
        $this->assertLessThan($footerStart, strpos($html, 'Header Linki'));
    }

    public function test_admin_can_delete_a_menu_item(): void
    {
        $admin = $this->makeAdmin();
        $item = NavMenuItem::create([
            'label' => 'Silinecek Menü',
            'link_type' => 'anchor',
            'target' => 'silinecek',
            'sort_order' => 60,
            'is_active' => true,
        ]);

        $this->withSession(['admin_user_id' => $admin->id])
            ->post("/panel/menu/{$item->id}/sil")
            ->assertRedirect(route('admin.index', ['tab' => 'site_icerigi']));

        $this->assertDatabaseMissing('nav_menu_items', ['id' => $item->id]);
    }

    public function test_secretary_without_edit_content_permission_cannot_manage_the_menu(): void
    {
        $secretary = User::create([
            'name' => 'Test Sekreter',
            'email' => 'secretary@test.local',
            'phone' => '0533 000 00 00',
            'role' => 'secretary',
            'compensation_type' => 'none',
            'password' => Hash::make('gercek-sifre-123'),
            'is_active' => true,
            'permissions' => ['edit_content' => false],
        ]);

        $this->withSession(['admin_user_id' => $secretary->id])
            ->post('/panel/menu', ['label' => 'Yasak Menü', 'link_type' => 'anchor', 'target' => 'yasak'])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseMissing('nav_menu_items', ['label' => 'Yasak Menü']);
    }
}
