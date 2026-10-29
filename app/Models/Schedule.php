<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Komple Takvim: bu ders saatinde çalışılacak hareketler.
    public function movements()
    {
        return $this->belongsToMany(Movement::class, 'schedule_movement');
    }
}
