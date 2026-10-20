<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\CashAccount;
use App\Models\DiscountRule;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Student;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;

trait ManagesStudents
{
    // MODÜL 3: Yeni Öğrenci Kaydı + İlk Tahsilat (Kabul Kriteri #1)
    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:120',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:Kız,Erkek,Belirtilmedi',
            'branch_id' => 'required|exists:branches,id',
            'group_id' => 'required|exists:groups,id',
            'package_id' => 'required|exists:packages,id',
            'discount_rule_id' => 'nullable|exists:discount_rules,id',
            'parent_name' => 'required|string|max:120',
            'parent_phone' => 'required|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:120',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'health_notes' => 'nullable|string',
            'medical_report_expires_at' => 'nullable|date',
            'license_no' => 'nullable|string|max:60',
            'uniform_size' => 'nullable|string|max:40',
            'initial_payment_amount' => 'nullable|numeric|min:0',
            'cash_account_id' => 'nullable|exists:cash_accounts,id',
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $discountRule = !empty($validated['discount_rule_id']) ? DiscountRule::find($validated['discount_rule_id']) : null;

        $student = Student::create([
            'full_name' => $validated['full_name'],
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'],
            'branch_id' => $validated['branch_id'],
            'group_id' => $validated['group_id'],
            'package_id' => $validated['package_id'],
            'discount_rule_id' => $validated['discount_rule_id'] ?? null,
            'parent_name' => $validated['parent_name'],
            'parent_phone' => $validated['parent_phone'],
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? $validated['parent_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? $validated['parent_phone'],
            'health_notes' => $validated['health_notes'] ?? 'Spor yapmasında engel yoktur.',
            'medical_report_expires_at' => $validated['medical_report_expires_at'] ?? now()->addYear()->toDateString(),
            'license_no' => $validated['license_no'] ?? null,
            'uniform_size' => $validated['uniform_size'] ?? null,
            'status' => 'active',
            'kvkk_consent' => $request->boolean('kvkk_consent', true),
            'health_data_consent' => $request->boolean('health_data_consent', true),
            'photo_consent' => $request->boolean('photo_consent', true),
            'marketing_consent' => $request->boolean('marketing_consent', false),
        ]);

        // Otomatik Borç Hesaplama
        $gross = (float) $package->price;
        $discAmount = 0;
        if ($discountRule) {
            $discAmount = $discountRule->percentage > 0
                ? round($gross * ($discountRule->percentage / 100), 2)
                : (float) $discountRule->fixed_amount;
        }
        $net = max(0, $gross - $discAmount);
        $paid = (float) ($validated['initial_payment_amount'] ?? 0);
        $paid = min($net, $paid);
        $remaining = $net - $paid;

        $invoice = Invoice::create([
            'student_id' => $student->id,
            'package_id' => $package->id,
            'period_label' => now()->format('m/Y') . ' Dönem Aidatı',
            'gross_amount' => $gross,
            'discount_amount' => $discAmount,
            'net_amount' => $net,
            'paid_amount' => $paid,
            'remaining_amount' => $remaining,
            'due_date' => today(),
            'status' => $remaining <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ]);

        if ($paid > 0) {
            $cashAcc = !empty($validated['cash_account_id'])
                ? CashAccount::find($validated['cash_account_id'])
                : CashAccount::first();

            $receiptNo = 'MKB-' . now()->format('Ym') . '-' . str_pad((string) (Payment::count() + 1), 4, '0', STR_PAD_LEFT);
            $payment = Payment::create([
                'receipt_no' => $receiptNo,
                'invoice_id' => $invoice->id,
                'student_id' => $student->id,
                'cash_account_id' => $cashAcc->id,
                'amount' => $paid,
                'payment_method' => $cashAcc->type === 'pos' ? 'pos' : ($cashAcc->type === 'bank' ? 'bank_transfer' : 'cash'),
                'payment_date' => today(),
                'description' => "{$student->full_name} İlk Kayıt Tahsilatı",
            ]);
            $cashAcc->increment('balance', $paid);

            // Makbuz WhatsApp Mesajı Kuyruğa Ekle
            WhatsappMessage::create([
                'student_id' => $student->id,
                'recipient_name' => $student->parent_name,
                'recipient_phone' => $student->parent_phone,
                'message_text' => "Sayın {$student->parent_name}, sporcumuz {$student->full_name} kesin kaydı tamamlanmış ve {$paid} TL ödemeniz alınmıştır (Makbuz No: {$receiptNo}). Dijital makbuzunuz: " . $payment->receiptUrl(),
                'status' => 'queued',
            ]);
        }

        AuditLog::record('ogrenci_eklendi', "Yeni öğrenci kaydı açıldı: {$student->full_name} (İlk Tahsilat: {$paid} TL)", 'Student', $student->id);

        return redirect()->route('admin.index', ['tab' => 'ogrenciler'])->with('success', "Öğrenci kaydı ({$student->full_name}), aidat planı ve ilk tahsilat makbuzu başarıyla oluşturuldu!");
    }

    // MODÜL 3: Mevcut Öğrenciyi Düzenle
    public function updateStudent(Request $request, Student $student)
    {
        $this->assertCoachOwnsGroup($student->group_id);
        $request->validate([
            'gender' => 'nullable|in:Kız,Erkek,Belirtilmedi',
            'branch_id' => 'nullable|exists:branches,id',
            'group_id' => 'nullable|exists:groups,id',
            'package_id' => 'nullable|exists:packages,id',
            'discount_rule_id' => 'nullable|exists:discount_rules,id',
            'makeup_credits' => 'nullable|integer|min:0',
            'medical_report_expires_at' => 'nullable|date',
            'status' => 'nullable|in:active,frozen,left,waiting_list',
        ]);
        if ($request->filled('group_id')) {
            $this->assertCoachOwnsGroup((int) $request->input('group_id'));
        }
        $student->update([
            'full_name' => $request->input('full_name', $student->full_name),
            'birth_date' => $request->input('birth_date') ?: $student->birth_date,
            'gender' => $request->input('gender', $student->gender),
            'branch_id' => $request->input('branch_id') ?: $student->branch_id,
            'group_id' => $request->input('group_id') ?: null,
            'package_id' => $request->input('package_id') ?: null,
            'discount_rule_id' => $request->input('discount_rule_id') ?: null,
            'parent_name' => $request->input('parent_name', $student->parent_name),
            'parent_phone' => $request->input('parent_phone', $student->parent_phone),
            'uniform_size' => $request->input('uniform_size', $student->uniform_size),
            'license_no' => $request->input('license_no', $student->license_no),
            'makeup_credits' => (int) $request->input('makeup_credits', $student->makeup_credits),
            'medical_report_expires_at' => $request->input('medical_report_expires_at') ?: $student->medical_report_expires_at,
            'health_notes' => $request->input('health_notes', $student->health_notes),
            'photo_consent' => $request->boolean('photo_consent', $student->photo_consent),
            'status' => $request->input('status', $student->status),
        ]);
        AuditLog::record('ogrenci_guncellendi', "{$student->full_name} sporcu/veli bilgileri güncellendi.", 'Student', $student->id);
        return redirect()->route('admin.index', ['tab' => 'ogrenciler'])->with('success', "'{$student->full_name}' sporcu bilgileri başarıyla güncellendi!");
    }

    // Öğrenci Durum Değiştirme (Dondurma / Ayrılma / Arşivleme)
    public function updateStudentStatus(Request $request, Student $student)
    {
        $this->assertCoachOwnsGroup($student->group_id);
        $request->validate([
            'status' => 'required|in:active,frozen,left,waiting_list',
        ]);
        $student->update([
            'status' => $request->input('status', 'frozen'),
            'leave_reason' => $request->input('leave_reason'),
        ]);
        AuditLog::record('ogrenci_durum_degisti', "{$student->full_name} durumu '{$student->status}' olarak güncellendi.", 'Student', $student->id);
        return back()->with('success', "{$student->full_name} kaydı güncellendi.");
    }

    public function destroyStudent(Request $request, Student $student)
    {
        // Yetki kontrolü route middleware'inde (admin.permission:delete_student) merkezi olarak yapılır.
        $name = $student->full_name;
        $student->delete();

        AuditLog::record('ogrenci_silindi', "{$name} sporcu ve veli giriş kaydı silindi.", 'Student', $student->id);

        return redirect()->route('admin.index', ['tab' => 'ogrenciler'])->with('success', "{$name} sporcu/veli kaydı başarıyla silindi.");
    }
}
