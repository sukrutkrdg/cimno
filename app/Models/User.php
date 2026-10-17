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
        $perms = is_array($this->permissions) ? $this->permissions : [];
        if (array_key_exists($perm, $perms)) {
            return (bool) $perms[$perm];
        }
        // Rol bazlı varsayılan yetkiler
        if ($this->role === 'secretary') {
            return in_array($perm, [
                'collect_payment',
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
        if ($this->role === 'super_admin') {
            return true;
        }
        if ($this->role === 'secretary') {
            return $this->hasPermission('collect_payment', true);
        }
        return false;
    }

    public function allowedTabs(): array
    {
        if ($this->role === 'super_admin') {
            return [
                'pano', 'adaylar', 'ogrenciler', 'gruplar', 'yoklama',
                'paketler', 'aidat', 'kasa', 'personel', 'mesajlar',
                'etkinlikler', 'gelisim', 'raporlar', 'site_icerigi', 'ayarlar',
            ];
        }

        if ($this->role === 'secretary') {
            $tabs = ['pano', 'adaylar', 'ogrenciler', 'gruplar', 'yoklama', 'etkinlikler', 'gelisim'];
            if ($this->hasPermission('collect_payment', true)) {
                $tabs[] = 'paketler';
                $tabs[] = 'aidat';
            }
            if ($this->hasPermission('view_expenses', false)) {
                $tabs[] = 'kasa';
            }
            if ($this->hasPermission('send_whatsapp', true)) {
                $tabs[] = 'mesajlar';
            }
            if ($this->hasPermission('edit_content', true)) {
                $tabs[] = 'site_icerigi';
            }
            if ($this->hasPermission('view_reports', false)) {
                $tabs[] = 'raporlar';
            }
            return $tabs;
        }

        if ($this->role === 'coach') {
            $tabs = ['pano', 'ogrenciler', 'gruplar'];
            if ($this->hasPermission('take_attendance', true)) {
                $tabs[] = 'yoklama';
            }
            $tabs[] = 'etkinlikler';
            if ($this->hasPermission('add_development', true)) {
                $tabs[] = 'gelisim';
            }
            return $tabs;
        }

        return ['pano'];
    }
}

