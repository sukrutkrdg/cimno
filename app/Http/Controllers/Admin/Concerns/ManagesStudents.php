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
use Illuminate\Support\Facades\DB;

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
            'secondary_parent_name' => 'nullable|string|max:120',
            'secondary_parent_phone' => 'nullable|string|max:30',
            'emergency_contact_name' => 'nullable|string|max:120',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'health_notes' => 'nullable|string',
            'medical_report_expires_at' => 'nullable|date',
            'license_no' => 'nullable|string|max:60',
            'uniform_size' => 'nullable|string|max:40',
            'initial_payment_amount' => 'nullable|numeric|min:0',
            'cash_account_id' => 'nullable|exists:cash_accounts,id',
        ]);

        // Bu işlem sırasıyla Student + 1-2 WhatsappMessage + Invoice + Payment + kasa
        // bakiyesi artışı yazar. Transaction olmadan, ör. Payment ekleme bir DB kısıtı
        // yüzünden yarıda başarısız olursa, öğrenci kaydı + borç kalır ama tahsilat/kasa
        // güncellemesi hiç olmaz ya da tam tersi. Tek bir transaction ile ya hepsi ya hiçbiri.
        $this->assertCoachOwnsGroup((int) $validated['group_id']);

        [$student, $paid] = DB::transaction(function () use ($validated, $request) {
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
                'portal_pin' => Student::portalPinFor($validated['parent_phone']),
                'parent_name' => $validated['parent_name'],
                'parent_phone' => $validated['parent_phone'],
                'secondary_parent_name' => $validated['secondary_parent_name'] ?? null,
                'secondary_parent_phone' => $validated['secondary_parent_phone'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? $validated['parent_name'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? $validated['parent_phone'],
                'health_notes' => $validated['health_notes'] ?? 'Spor yapmasında engel yoktur.',
                'medical_report_expires_at' => $validated['medical_report_expires_at'] ?? now()->addYear()->toDateString(),
                'license_no' => $validated['license_no'] ?? null,
                'uniform_size' => $validated['uniform_size'] ?? null,
                'status' => 'active',
                // HTML checkbox'lar işaretlenmediğinde hiç gönderilmez; varsayılanı true
                // yapmak, personelin kutuyu işaretlemeyi unutmasını "rıza verildi" olarak
                // kaydeder — KVKK/sağlık/foto izni gibi özel nitelikli veriler için bu
                // tersine bir varsayılandır. Rıza yalnızca kutu fiilen işaretlenmişse geçerli.
                'kvkk_consent' => $request->boolean('kvkk_consent', false),
                'health_data_consent' => $request->boolean('health_data_consent', false),
                'photo_consent' => $request->boolean('photo_consent', false),
                'marketing_consent' => $request->boolean('marketing_consent', false),
            ]);

            // Veli Portalı Giriş Bilgisi (Telefon + PIN) — hem birincil hem (varsa)
            // ikincil veliye kendi telefonuyla ayrı ayrı WhatsApp ile gönderilir.
            $this->queuePortalMessageForGuardians($student, fn (string $name, string $phone) =>
                "Sayın {$name}, {$student->full_name} kaydı için veli portalı giriş bilgileriniz: Telefon: {$phone}, PIN: {$student->portal_pin}. Giriş: " . url('/mobil')
            );

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

                $payment = Payment::createWithReceiptNo([
                    'invoice_id' => $invoice->id,
                    'student_id' => $student->id,
                    'cash_account_id' => $cashAcc->id,
                    'amount' => $paid,
                    'payment_method' => $cashAcc->type === 'pos' ? 'pos' : ($cashAcc->type === 'bank' ? 'bank_transfer' : 'cash'),
                    'payment_date' => today(),
                    'description' => "{$student->full_name} İlk Kayıt Tahsilatı",
                ]);
                $receiptNo = $payment->receipt_no;
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

            return [$student, $paid];
        });

        $guardianNote = !empty($student->secondary_parent_phone)
            ? 'Her iki velinin de giriş bilgisi/PIN mesajı WhatsApp kuyruğuna eklendi'
            : 'Veli giriş bilgisi/PIN mesajı WhatsApp kuyruğuna eklendi';

        return redirect()->route('admin.index', ['tab' => 'ogrenciler'])->with('success', "Öğrenci kaydı ({$student->full_name}), aidat planı ve ilk tahsilat makbuzu başarıyla oluşturuldu! {$guardianNote} — unutmadan Mesajlar sekmesinden gönderin.");
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
        $pinRegenerated = $request->boolean('regenerate_pin');
        $newPin = $pinRegenerated
            ? str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT)
            : ($student->portal_pin ?: Student::portalPinFor($student->parent_phone));

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
            'secondary_parent_name' => $request->input('secondary_parent_name', $student->secondary_parent_name),
            'secondary_parent_phone' => $request->input('secondary_parent_phone', $student->secondary_parent_phone),
            'uniform_size' => $request->input('uniform_size', $student->uniform_size),
            'license_no' => $request->input('license_no', $student->license_no),
            'makeup_credits' => (int) $request->input('makeup_credits', $student->makeup_credits),
            'medical_report_expires_at' => $request->input('medical_report_expires_at') ?: $student->medical_report_expires_at,
            'health_notes' => $request->input('health_notes', $student->health_notes),
            'kvkk_consent' => $request->boolean('kvkk_consent', $student->kvkk_consent),
            'health_data_consent' => $request->boolean('health_data_consent', $student->health_data_consent),
            'photo_consent' => $request->boolean('photo_consent', $student->photo_consent),
            'status' => $request->input('status', $student->status),
            'portal_pin' => $newPin,
        ]);

        // Yeni PIN veliye iletilmezse veli portalına giremez kalır; otomatik bildir.
        // PIN her iki veli için de ortak olduğundan, ikinci veli varsa o da ayrıca bilgilendirilir.
        if ($pinRegenerated) {
            $this->queuePortalMessageForGuardians($student, fn (string $name, string $phone) =>
                "Sayın {$name}, {$student->full_name} için veli portalı PIN kodunuz yenilendi. Yeni PIN: {$newPin} (Telefon: {$phone}). Giriş: " . url('/mobil')
            );
        }

        AuditLog::record('ogrenci_guncellendi', "{$student->full_name} sporcu/veli bilgileri güncellendi.", 'Student', $student->id);
        return redirect()->route('admin.index', ['tab' => 'ogrenciler'])->with('success', "'{$student->full_name}' sporcu bilgileri başarıyla güncellendi!" . ($pinRegenerated ? ' Yeni PIN mesajı WhatsApp kuyruğuna eklendi — unutmadan Mesajlar sekmesinden gönderin.' : ''));
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

    /**
     * Hem birincil hem (varsa) ikincil veliye, kendi adı/telefonuyla aynı mesaj
     * kalıbını ayrı ayrı WhatsApp kuyruğuna ekler — her iki veli de aynı PIN'i
     * paylaştığından yalnızca hitap ve telefon numarası değişir (bkz.
     * PublicSiteController@mobilePwa'daki telefon+PIN eşleştirme sorgusu).
     *
     * @param  \Closure(string $name, string $phone): string  $messageFor
     */
    private function queuePortalMessageForGuardians(Student $student, \Closure $messageFor): void
    {
        WhatsappMessage::create([
            'student_id' => $student->id,
            'recipient_name' => $student->parent_name,
            'recipient_phone' => $student->parent_phone,
            'message_text' => $messageFor($student->parent_name, $student->parent_phone),
            'status' => 'queued',
        ]);

        if (!empty($student->secondary_parent_phone)) {
            $secondaryName = $student->secondary_parent_name ?: 'İkinci Veli';
            WhatsappMessage::create([
                'student_id' => $student->id,
                'recipient_name' => $secondaryName,
                'recipient_phone' => $student->secondary_parent_phone,
                'message_text' => $messageFor($secondaryName, $student->secondary_parent_phone),
                'status' => 'queued',
            ]);
        }
    }
}
