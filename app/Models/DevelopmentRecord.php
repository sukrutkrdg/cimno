<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevelopmentRecord extends Model
{
    protected $guarded = [];

    protected $casts = [
        'skills' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    // Salonun basılı "GELİŞİM TAKİP KARNESİ" formuna göre girilmiş (3 kategori × 4
    // kriter, 1-4 ölçeği) mi, yoksa eski 0-100 puanlı formatta mı kaydedildi?
    public function isPdfFormat(): bool
    {
        return $this->phys_flexibility !== null;
    }
}
