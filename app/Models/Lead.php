<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'trial_date' => 'date',
        'kvkk_approved' => 'boolean',
        'kvkk_approved_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function trialGroup()
    {
        return $this->belongsTo(Group::class, 'trial_group_id');
    }
}
