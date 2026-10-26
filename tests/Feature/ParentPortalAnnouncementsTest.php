<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentPortalAnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_announcements_tab_shows_parent_facing_announcements(): void
    {
        $branch = Branch::create(['name' => 'Test Branş', 'slug' => 'test-brans-duyuru', 'age_range' => '4-12', 'is_active' => true]);
        Student::create([
            'full_name' => 'Duyuru Testi Öğrenci',
            'branch_id' => $branch->id,
            'parent_name' => 'Duyuru Veli',
            'parent_phone' => '5330000003',
            'portal_pin' => '1234',
            'status' => 'active',
        ]);
        Announcement::create(['title' => 'Kış Dönemi Kontenjanları', 'content' => 'Yeni kontenjanlar açıldı.', 'target_audience' => 'parents']);
        Announcement::create(['title' => 'Site İçin Duyuru', 'content' => 'Bu sadece public_site hedefli.', 'target_audience' => 'coaches']);

        $this->get('/mobil?phone=5330000003&pin=1234&sekme=duyurular')
            ->assertOk()
            ->assertSee('Güncel Duyurular')
            ->assertSee('Kış Dönemi Kontenjanları')
            ->assertDontSee('Site İçin Duyuru');
    }

    public function test_announcements_also_appear_on_the_overview_tab_but_not_other_tabs(): void
    {
        $branch = Branch::create(['name' => 'Test Branş 2', 'slug' => 'test-brans-duyuru-2', 'age_range' => '4-12', 'is_active' => true]);
        Student::create([
            'full_name' => 'Duyuru Testi Öğrenci 2',
            'branch_id' => $branch->id,
            'parent_name' => 'Duyuru Veli 2',
            'parent_phone' => '5330000004',
            'portal_pin' => '5678',
            'status' => 'active',
        ]);
        Announcement::create(['title' => 'Genel Duyuru X', 'content' => 'İçerik', 'target_audience' => 'all']);

        $this->get('/mobil?phone=5330000004&pin=5678&sekme=cocuklarim')
            ->assertOk()->assertSee('Genel Duyuru X');

        $this->get('/mobil?phone=5330000004&pin=5678&sekme=aidat')
            ->assertOk()->assertDontSee('Genel Duyuru X');
    }
}
