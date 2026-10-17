<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;
    protected $guarded = [];

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
}
