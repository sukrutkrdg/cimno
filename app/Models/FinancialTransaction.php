<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'transaction_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}
