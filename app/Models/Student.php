<?php

namespace App\Models;

use App\Support\Phone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class Student extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    // Veli, ders programını telefon/bilgisayar takvimine "abone" olarak ekleyebilsin
    // diye kalıcı (süresiz) imzalı bir bağlantı — takvim uygulamaları bu adresi
    // periyodik olarak kendi başına tekrar çeker, bu yüzden receiptUrl()'deki gibi
    // günlerle sınırlı bir süre burada uygun olmaz.
    public function calendarUrl(): string
    {
        return URL::signedRoute('public.calendar.ics', ['student' => $this->id]);
    }

    protected $casts = [
        'birth_date' => 'date',
        'medical_report_expires_at' => 'date',
        'license_visa_date' => 'date',
        'kvkk_consent' => 'boolean',
        'health_data_consent' => 'boolean',
        'photo_consent' => 'boolean',
        'marketing_consent' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function discountRule()
    {
        return $this->belongsTo(DiscountRule::class);
    }

    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function developmentRecords()
    {
        return $this->hasMany(DevelopmentRecord::class);
    }

    public function siblings()
    {
        return $this->belongsToMany(Student::class, 'student_siblings', 'student_id', 'sibling_student_id');
    }

    /**
     * Veli portalı girişi için 4 haneli PIN üretir/bulur. Aynı telefon numarasına
     * kayıtlı bir kardeş zaten varsa onun PIN'i tekrar kullanılır, böylece aile
     * tek bir PIN ile tüm çocuklarını görebilir.
     */
    public static function portalPinFor(?string $parentPhone): string
    {
        $clean = Phone::normalize($parentPhone);
        if ($clean !== null) {
            $existing = static::where(function ($q) use ($clean) {
                $q->where('parent_phone', $clean)->orWhere('secondary_parent_phone', $clean);
            })->whereNotNull('portal_pin')->value('portal_pin');
            if ($existing) {
                return $existing;
            }
        }

        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
