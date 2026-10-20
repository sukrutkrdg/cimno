<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class Payment extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'date',
    ];

    /**
     * Dijital makbuz linki imzalıdır: ardışık ID tahmin edilerek başka bir ailenin
     * ödeme/finansal bilgisine erişilmesini önler. Link 180 gün geçerlidir.
     */
    public function receiptUrl(): string
    {
        return URL::temporarySignedRoute('public.receipt', now()->addDays(180), ['payment' => $this->id]);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
