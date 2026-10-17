<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
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
