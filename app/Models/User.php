<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = ['id'];

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
}
