<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
            'show_on_website' => 'boolean',
            'base_salary' => 'decimal:2',
            'per_lesson_rate' => 'decimal:2',
        ];
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'coach_id');
    }

    public function children()
    {
        return $this->hasMany(Student::class, 'parent_user_id');
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Süper Yönetici (Salon Sahibi)',
            'secretary' => 'Sekreter (Ön Büro)',
            'coach' => 'Antrenör (Eğitmen)',
            'parent' => 'Veli',
            default => $this->role,
        };
    }

    public function hasPermission(string $perm, bool $default = false): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }
        // Mali, yedekleme ve yetki işlemleri yalnızca super_admin'e aittir
        if (in_array($perm, ['collect_payment', 'view_expenses', 'view_reports'], true)) {
            return false;
        }
        $perms = is_array($this->permissions) ? $this->permissions : [];
        if (array_key_exists($perm, $perms)) {
            return (bool) $perms[$perm];
        }
        if ($this->role === 'secretary') {
            return in_array($perm, [
                'send_whatsapp',
                'edit_content',
                'take_attendance',
                'add_development',
            ], true) ? true : $default;
        }
        if ($this->role === 'coach') {
            return in_array($perm, [
                'only_own_groups',
                'take_attendance',
                'add_development',
            ], true) ? true : $default;
        }
        return $default;
    }

    public function canSeeFinance(): bool
    {
        return $this->role === 'super_admin';
    }

    // Sol menüdeki her modül için, bu personelin stored `permissions` JSON'ında
    // açık bir "view_<tab>" anahtarı varsa o karar geçerlidir (hasPermission()'ın
    // array_key_exists kontrolü sayesinde); yoksa aşağıdaki $defaults, BU METODUN
    // ÖNCEKİ (rol bazlı sabit kodlanmış) davranışını birebir korur — hiçbir mevcut
    // hesabın erişimi, süper yönetici elle bir "view_X" kutusunu değiştirmeden
    // sessizce değişmez.
    public function allowedTabs(): array
    {
        if (!in_array($this->role, ['super_admin', 'secretary', 'coach'], true)) {
            return ['pano'];
        }

        $defaults = [
            'pano' => true,
            'adaylar' => $this->role === 'secretary',
            'ogrenciler' => true,
            'gruplar' => true,
            'takvim' => true,
            'yoklama' => $this->role === 'secretary' || $this->hasPermission('take_attendance', true),
            'paketler' => false,
            'aidat' => false,
            'kasa' => false,
            'personel' => false,
            'mesajlar' => $this->role === 'secretary' && $this->hasPermission('send_whatsapp', true),
            'etkinlikler' => true,
            'gelisim' => $this->role === 'secretary' || $this->hasPermission('add_development', true),
            'raporlar' => false,
            'site_icerigi' => $this->role === 'secretary' && $this->hasPermission('edit_content', true),
            'ayarlar' => false,
        ];

        $allowed = [];
        foreach ($defaults as $tab => $default) {
            if ($this->hasPermission("view_{$tab}", $default)) {
                $allowed[] = $tab;
            }
        }
        return $allowed;
    }
}

