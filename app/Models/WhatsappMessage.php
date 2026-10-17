<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(WhatsappTemplate::class, 'template_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getWaMeUrlAttribute(): string
    {
        $digits = preg_replace('/\D+/', '', $this->recipient_phone);
        if (str_starts_with($digits, '0')) {
            $digits = '90' . substr($digits, 1);
        } elseif (!str_starts_with($digits, '90') && strlen($digits) === 10) {
            $digits = '90' . $digits;
        }
        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($this->message_text);
    }
}
