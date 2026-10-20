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

    /**
     * Önceki yöntem "Payment::count() + 1" idi: iki tahsilat aynı anda girilirse
     * ikisi de aynı receipt_no'yu hesaplayıp ikincisi unique constraint hatasıyla
     * (500) başarısız oluyordu — kasadaki para alınmış ama kayıt düşmemiş gibi
     * görünüyordu. Veritabanının kendi auto-increment ID'si (gerçekten eşzamanlılığa
     * dayanıklı, çakışması imkânsız) temel alınarak yarış durumu tamamen ortadan
     * kaldırılır: kayıt önce geçici benzersiz bir UUID ile oluşturulur, sonra ID
     * biliniyorken makbuz no'su buna göre güncellenir.
     */
    public static function createWithReceiptNo(array $attributes): self
    {
        $payment = static::create(array_merge($attributes, [
            'receipt_no' => 'TMP-' . \Illuminate\Support\Str::uuid(),
        ]));

        $payment->update([
            'receipt_no' => 'MKB-' . now()->format('Ym') . '-' . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT),
        ]);

        return $payment;
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
