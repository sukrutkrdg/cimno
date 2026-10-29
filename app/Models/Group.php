<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    // Baş antrenörün (coach_id) dışında, gruba ek olarak atanmış yardımcı
    // antrenörler. Sayıları parametriktir (yönetim panelinden "+ Antrenör Ekle"
    // ile çoğaltılır), coach_id değiştirilmeden eklenir/çıkarılır.
    public function assistantCoaches()
    {
        return $this->belongsToMany(User::class, 'group_coach', 'group_id', 'user_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function activeStudents()
    {
        return $this->hasMany(Student::class)->where('status', 'active');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
