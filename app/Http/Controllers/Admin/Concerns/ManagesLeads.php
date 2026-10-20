<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Student;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;

trait ManagesLeads
{
    // MODÜL 2: Yeni Aday Ekle & Mevcut Adayı Düzenle
    public function storeLead(Request $request)
    {
        $lead = Lead::create([
            'student_name' => $request->input('student_name', 'Yeni Aday'),
            'student_age' => (int) $request->input('student_age', 6),
            'parent_name' => $request->input('parent_name', ''),
            'parent_phone' => $request->input('parent_phone', ''),
            'branch_id' => $request->input('branch_id') ?: null,
            'trial_group_id' => $request->input('trial_group_id') ?: null,
            'source' => $request->input('source', 'website'),
            'status' => $request->input('status', 'new'),
            'notes' => $request->input('notes'),
        ]);
        AuditLog::record('aday_eklendi', "Yeni deneme/ön kayıt adayı eklendi: {$lead->student_name}", 'Lead', $lead->id);
        return redirect()->route('admin.index', ['tab' => 'adaylar'])->with('success', "'{$lead->student_name}' aday listesine eklendi!");
    }

    public function updateLead(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'student_name' => 'nullable|string|max:120',
            'student_age' => 'nullable|integer|min:1|max:80',
            'parent_name' => 'nullable|string|max:120',
            'parent_phone' => 'nullable|string|max:30',
            'branch_id' => 'nullable|exists:branches,id',
            'trial_group_id' => 'nullable|exists:groups,id',
            'source' => 'nullable|string|max:60',
            'status' => 'nullable|in:new,trial_scheduled,thinking,converted,not_converted',
            'notes' => 'nullable|string',
        ]);

        $lead->update([
            'student_name' => $validated['student_name'] ?? $lead->student_name,
            'student_age' => isset($validated['student_age']) ? (int) $validated['student_age'] : $lead->student_age,
            'parent_name' => $validated['parent_name'] ?? $lead->parent_name,
            'parent_phone' => $validated['parent_phone'] ?? $lead->parent_phone,
            'branch_id' => $validated['branch_id'] ?? $lead->branch_id,
            'trial_group_id' => $validated['trial_group_id'] ?? $lead->trial_group_id,
            'source' => $validated['source'] ?? $lead->source,
            'status' => $validated['status'] ?? $lead->status,
            'notes' => $validated['notes'] ?? $lead->notes,
        ]);
        AuditLog::record('aday_guncellendi', "Aday bilgileri güncellendi: {$lead->student_name}", 'Lead', $lead->id);
        return redirect()->route('admin.index', ['tab' => 'adaylar'])->with('success', "'{$lead->student_name}' aday kaydı güncellendi!");
    }

    // MODÜL 2: Adayı Tek Tıkla Kesin Kayda Dönüştür
    public function convertLeadToStudent(Request $request, Lead $lead)
    {
        $group = $lead->trialGroup ?? Group::where('branch_id', $lead->branch_id)->first() ?? Group::first();
        $package = Package::where('billing_cycle', 'monthly')->first() ?? Package::first();

        $student = Student::create([
            'full_name' => $lead->student_name,
            'birth_date' => now()->subYears($lead->student_age)->format('Y-m-d'),
            'branch_id' => $lead->branch_id ?? $group?->branch_id,
            'group_id' => $group?->id,
            'package_id' => $package?->id,
            'parent_name' => $lead->parent_name,
            'parent_phone' => $lead->parent_phone,
            'portal_pin' => Student::portalPinFor($lead->parent_phone),
            'medical_report_expires_at' => now()->addYear()->toDateString(),
            'billing_day' => 1,
            'status' => 'active',
            'kvkk_consent' => true,
            'health_data_consent' => true,
            'photo_consent' => true,
        ]);

        if ($package) {
            Invoice::create([
                'student_id' => $student->id,
                'package_id' => $package->id,
                'period_label' => now()->translatedFormat('F Y') . ' İlk Kayıt Aidatı',
                'gross_amount' => $package->price,
                'discount_amount' => 0,
                'net_amount' => $package->price,
                'paid_amount' => 0,
                'remaining_amount' => $package->price,
                'due_date' => today()->addDays(3),
                'status' => 'unpaid',
            ]);
        }

        $lead->update(['status' => 'converted']);

        WhatsappMessage::create([
            'student_id' => $student->id,
            'recipient_name' => $student->parent_name,
            'recipient_phone' => $student->parent_phone,
            'message_text' => "Hoş geldiniz Sayın {$student->parent_name}! 🌟 Sporcumuz {$student->full_name} kesin kaydı '{$group?->name}' grubuna yapılmıştır. Veli portalı giriş bilgileriniz — Telefon: {$student->parent_phone}, PIN: {$student->portal_pin}. Giriş: " . url('/mobil'),
            'status' => 'queued',
        ]);

        AuditLog::record('aday_kayda_donusturuldu', "Aday {$lead->student_name} tek tıkla kesin kayda dönüştürüldü ve ilk aidat borcu açıldı.", 'Student', $student->id);

        return redirect()->route('admin.index', ['tab' => 'ogrenciler'])->with('success', "{$student->full_name} başarıyla kesin kayda dönüştürüldü, ilk aidat borcu açıldı ve WhatsApp hoş geldin mesajı kuyruğa eklendi!");
    }

    public function destroyLead(Lead $lead)
    {
        $name = $lead->student_name;
        $lead->delete();
        AuditLog::record('aday_silindi', "Deneme/ön kayıt adayı silindi: {$name}");
        return redirect()->route('admin.index', ['tab' => 'adaylar'])->with('success', "'{$name}' deneme/ön kayıt talebi silindi.");
    }
}
