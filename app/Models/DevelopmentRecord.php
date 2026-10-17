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
}
