<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function participants()
    {
        return $this->belongsToMany(Student::class, 'event_participants')
            ->withPivot(['fee_paid', 'parent_approved'])
            ->withTimestamps();
    }
}
