<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\CashAccount;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;

trait ManagesFinance
{
    // MODÜL 8: Tahsilat Girişi & Makbuz Üretimi + WhatsApp Gönderimi
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'cash_account_id' => 'required|exists:cash_accounts,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,pos,bank_transfer,online',
            'description' => 'nullable|string|max:200',
        ]);

        $invoice = Invoice::with('student')->findOrFail($validated['invoice_id']);
        $cashAccount = CashAccount::findOrFail($validated['cash_account_id']);
        $amount = (float) $validated['amount'];

        $payment = Payment::createWithReceiptNo([
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'cash_account_id' => $cashAccount->id,
            'recorded_by' => $this->getAuthenticatedStaff()?->id,
            'amount' => $amount,
            'payment_method' => $validated['payment_method'],
            'payment_date' => today(),
            'description' => $validated['description'] ?? ($invoice->student->full_name . ' - ' . $invoice->period_label),
        ]);
        $receiptNo = $payment->receipt_no;

        $newPaid = $invoice->paid_amount + $amount;
        $newRemaining = max(0, $invoice->net_amount - $newPaid);
        $invoice->update([
            'paid_amount' => $newPaid,
            'remaining_amount' => $newRemaining,
            'status' => $newRemaining <= 0 ? 'paid' : 'partial',
        ]);

        $cashAccount->increment('balance', $amount);

        // WhatsApp Business Makbuz Mesajı Hazırla
        $msgText = "Sayın {$invoice->student->parent_name}, sporcumuz {$invoice->student->full_name} için {$amount} TL tutarındaki ödemeniz alınmıştır (Makbuz No: {$receiptNo}, Kalan Borç: {$newRemaining} TL). Dijital Makbuz: " . $payment->receiptUrl();
        WhatsappMessage::create([
            'student_id' => $invoice->student_id,
            'recipient_name' => $invoice->student->parent_name,
            'recipient_phone' => $invoice->student->parent_phone,
            'message_text' => $msgText,
            'status' => 'queued',
        ]);

        AuditLog::record('tahsilat_girildi', "{$invoice->student->full_name} için {$amount} TL tahsilat girildi ({$receiptNo}).", 'Payment', $payment->id);

        return redirect()->route('admin.index', ['tab' => 'aidat'])->with('success', "Tahsilat ({$receiptNo}) kaydedildi, kasa bakiyesi güncellendi ve WhatsApp makbuz mesajı hazırlandı!");
    }

    public function destroyPayment(Payment $payment)
    {
        // Silme işlemi kasa bakiyesini ve ilgili aidat borcunun kalan tutarını
        // GERİ ALMALI, aksi hâlde bakiye kalıcı olarak yanlış kalır.
        $payment->cashAccount?->decrement('balance', $payment->amount);

        $invoice = $payment->invoice;
        if ($invoice) {
            $newPaid = max(0, $invoice->paid_amount - $payment->amount);
            $newRemaining = max(0, $invoice->net_amount - $newPaid);
            $invoice->update([
                'paid_amount' => $newPaid,
                'remaining_amount' => $newRemaining,
                'status' => $newRemaining <= 0 ? 'paid' : ($newPaid > 0 ? 'partial' : 'unpaid'),
            ]);
        }

        $payment->delete();
        AuditLog::record('tahsilat_silindi', "Tahsilat makbuz kaydı silindi, kasa bakiyesi ve aidat borcu geri alındı ({$payment->amount} TL).", 'Payment', $payment->id);
        return redirect()->route('admin.index', ['tab' => 'aidat'])->with('success', 'Tahsilat kaydı silindi, kasa bakiyesi ve borç durumu güncellendi.');
    }

    // Aylık Otomatik Aidat Borçlandırma Motoru
    public function generateMonthlyInvoices(Request $request)
    {
        $periodLabel = $request->input('period_label', now()->addMonth()->format('m/Y') . ' Aidatı');
        $activeStudents = Student::with(['package', 'discountRule'])->where('status', 'active')->get();
        $createdCount = 0;

        foreach ($activeStudents as $st) {
            if (!$st->package) {
                continue;
            }
            $exists = Invoice::where('student_id', $st->id)->where('period_label', $periodLabel)->exists();
            if ($exists) {
                continue;
            }
            $gross = (float) $st->package->price;
            $disc = 0;
            if ($st->discountRule) {
                $disc = $st->discountRule->percentage > 0
                    ? round($gross * ($st->discountRule->percentage / 100), 2)
                    : (float) $st->discountRule->fixed_amount;
            }
            $net = max(0, $gross - $disc);

            Invoice::create([
                'student_id' => $st->id,
                'package_id' => $st->package_id,
                'period_label' => $periodLabel,
                'gross_amount' => $gross,
                'discount_amount' => $disc,
                'net_amount' => $net,
                'paid_amount' => 0,
                'remaining_amount' => $net,
                'due_date' => now()->startOfMonth()->addMonth()->toDateString(),
                'status' => 'unpaid',
            ]);
            $createdCount++;
        }

        AuditLog::record('otomatik_borclandirma', "{$periodLabel} dönemi için {$createdCount} aktif öğrenciye otomatik aidat borcu açıldı.");

        return redirect()->route('admin.index', ['tab' => 'aidat'])->with('success', "{$periodLabel} dönemi için {$createdCount} aktif öğrenciye indirim kuralları uygulanarak otomatik borç kaydı açıldı!");
    }

    // MODÜL 8: Tekil Aidat / Borç Ekle & Düzenle
    public function storeSingleInvoice(Request $request)
    {
        $student = Student::findOrFail($request->input('student_id'));
        $net = (float) $request->input('net_amount', 3200);
        $inv = Invoice::create([
            'student_id' => $student->id,
            'package_id' => $student->package_id,
            'period_label' => $request->input('period_label', now()->format('m/Y') . ' Aidatı'),
            'gross_amount' => $net,
            'discount_amount' => 0,
            'net_amount' => $net,
            'paid_amount' => 0,
            'remaining_amount' => $net,
            'due_date' => $request->input('due_date', today()->addDays(5)->toDateString()),
            'status' => 'unpaid',
        ]);
        AuditLog::record('tekil_borc_acildi', "{$student->full_name} için '{$inv->period_label}' ({$net} TL) borç açıldı.", 'Invoice', $inv->id);
        return redirect()->route('admin.index', ['tab' => 'aidat'])->with('success', "{$student->full_name} için '{$inv->period_label}' borç kaydı açıldı!");
    }

    public function updateInvoice(Request $request, Invoice $invoice)
    {
        $request->validate([
            'net_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);
        $net = (float) $request->input('net_amount', $invoice->net_amount);
        $paid = (float) $request->input('paid_amount', $invoice->paid_amount);
        $rem = max(0, $net - $paid);
        $invoice->update([
            'period_label' => $request->input('period_label', $invoice->period_label),
            'gross_amount' => $net,
            'net_amount' => $net,
            'paid_amount' => $paid,
            'remaining_amount' => $rem,
            'due_date' => $request->input('due_date', $invoice->due_date?->toDateString()),
            'status' => $rem <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ]);
        AuditLog::record('aidat_guncellendi', "Aidat borç kaydı (#{$invoice->id}) güncellendi.", 'Invoice', $invoice->id);
        return redirect()->route('admin.index', ['tab' => 'aidat'])->with('success', 'Aidat borç kaydı güncellendi!');
    }

    public function destroyInvoice(Invoice $invoice)
    {
        Payment::where('invoice_id', $invoice->id)->delete();
        $invoice->delete();
        AuditLog::record('aidat_silindi', 'Aidat borç kaydı silindi.');
        return redirect()->route('admin.index', ['tab' => 'aidat'])->with('success', 'Aidat borç kaydı silindi.');
    }

    // MODÜL 9: Gelir-Gider ve Ürün Satışı Ekleme
    public function storeFinancialTransaction(Request $request)
    {
        $validated = $request->validate([
            'direction' => 'required|in:income,expense',
            'category' => 'required|string|max:80',
            'cash_account_id' => 'required|exists:cash_accounts,id',
            'title' => 'required|string|max:160',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'is_recurring' => 'nullable|boolean',
        ]);

        $tx = FinancialTransaction::create([
            'direction' => $validated['direction'],
            'category' => $validated['category'],
            'cash_account_id' => $validated['cash_account_id'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        $acc = CashAccount::find($validated['cash_account_id']);
        if ($acc) {
            if ($validated['direction'] === 'income') {
                $acc->increment('balance', $validated['amount']);
            } else {
                $acc->decrement('balance', $validated['amount']);
            }
        }

        AuditLog::record('kasa_hareketi', "{$validated['direction']} kaydı: {$validated['title']} ({$validated['amount']} TL)", 'FinancialTransaction', $tx->id);

        return redirect()->route('admin.index', ['tab' => 'kasa'])->with('success', 'Gelir/Gider hareketi kaydedildi ve ilgili kasa/banka bakiyesi güncellendi.');
    }

    public function updateTransaction(Request $request, FinancialTransaction $transaction)
    {
        $request->validate([
            'direction' => 'nullable|in:income,expense',
            'cash_account_id' => 'nullable|exists:cash_accounts,id',
            'amount' => 'nullable|numeric|min:0',
            'transaction_date' => 'nullable|date',
        ]);

        // Eski tutarın eski kasa hesabı üzerindeki etkisini geri al, güncellemeyi
        // yap, sonra yeni tutarı yeni (veya aynı) kasa hesabına uygula. Aksi hâlde
        // bir hareketi düzenlemek bakiyeyi kalıcı olarak yanlış bırakır.
        if ($transaction->direction === 'income') {
            $transaction->cashAccount?->decrement('balance', $transaction->amount);
        } else {
            $transaction->cashAccount?->increment('balance', $transaction->amount);
        }

        $newDirection = $request->input('direction', $transaction->direction);
        $newAmount = (float) $request->input('amount', $transaction->amount);
        $newCashAccountId = $request->input('cash_account_id', $transaction->cash_account_id);

        $transaction->update([
            'direction' => $newDirection,
            'category' => $request->input('category', $transaction->category),
            'title' => $request->input('title', $transaction->title),
            'amount' => $newAmount,
            'transaction_date' => $request->input('transaction_date', $transaction->transaction_date?->toDateString()),
            'cash_account_id' => $newCashAccountId,
        ]);

        $newAccount = CashAccount::find($newCashAccountId);
        if ($newAccount) {
            $newDirection === 'income' ? $newAccount->increment('balance', $newAmount) : $newAccount->decrement('balance', $newAmount);
        }

        AuditLog::record('kasa_hareketi_guncellendi', "Kasa hareketi güncellendi: {$transaction->title}", 'FinancialTransaction', $transaction->id);
        return redirect()->route('admin.index', ['tab' => 'kasa'])->with('success', "'{$transaction->title}' kasa hareketi güncellendi, bakiye yeniden hesaplandı!");
    }

    public function destroyTransaction(FinancialTransaction $transaction)
    {
        $title = $transaction->title;

        // Silme işlemi kasa bakiyesindeki etkisini GERİ ALMALI.
        if ($transaction->direction === 'income') {
            $transaction->cashAccount?->decrement('balance', $transaction->amount);
        } else {
            $transaction->cashAccount?->increment('balance', $transaction->amount);
        }

        $transaction->delete();
        AuditLog::record('kasa_hareketi_silindi', "Kasa hareketi silindi, bakiye geri alındı: {$title} ({$transaction->amount} TL)");
        return redirect()->route('admin.index', ['tab' => 'kasa'])->with('success', "'{$title}' kasa hareketi silindi, bakiye güncellendi.");
    }

    // MODÜL 9: Kasa / Banka / POS Hesabı Ekle, Düzenle, Sil
    public function storeCashAccount(Request $request)
    {
        $request->validate([
            'type' => 'nullable|in:cash,bank,pos,online_pos',
            'balance' => 'nullable|numeric',
        ]);
        $acc = CashAccount::create([
            'name' => $request->input('name', 'Yeni Hesap'),
            'type' => $request->input('type', 'bank'),
            'iban' => $request->input('iban'),
            'balance' => (float) $request->input('balance', 0),
        ]);
        AuditLog::record('kasa_hesabi_eklendi', "Yeni kasa/banka hesabı eklendi: {$acc->name}", 'CashAccount', $acc->id);
        return redirect()->route('admin.index', ['tab' => 'kasa'])->with('success', "'{$acc->name}' hesabı eklendi!");
    }

    public function updateCashAccount(Request $request, CashAccount $cashAccount)
    {
        $request->validate([
            'type' => 'nullable|in:cash,bank,pos,online_pos',
            'balance' => 'nullable|numeric',
        ]);
        $cashAccount->update([
            'name' => $request->input('name', $cashAccount->name),
            'type' => $request->input('type', $cashAccount->type),
            'iban' => $request->input('iban', $cashAccount->iban),
            'balance' => (float) $request->input('balance', $cashAccount->balance),
        ]);
        AuditLog::record('kasa_hesabi_guncellendi', "Kasa/banka hesabı güncellendi: {$cashAccount->name}", 'CashAccount', $cashAccount->id);
        return redirect()->route('admin.index', ['tab' => 'kasa'])->with('success', "'{$cashAccount->name}' hesabı güncellendi!");
    }

    public function destroyCashAccount(CashAccount $cashAccount)
    {
        // payments.cash_account_id FK'si cascadeOnDelete olduğundan bu hesabı silmek
        // o hesaba ait TÜM tahsilat makbuzlarını da (soft-delete'i atlayarak) kalıcı
        // olarak siler. Geçmiş ödeme kaydı varken silmeye izin vermiyoruz.
        if (Payment::where('cash_account_id', $cashAccount->id)->exists()) {
            return redirect()->route('admin.index', ['tab' => 'kasa'])->with('error', "'{$cashAccount->name}' hesabına ait tahsilat/makbuz kayıtları olduğu için silinemez. Bunun yerine hesabı pasif/kullanım dışı bırakabilirsiniz.");
        }

        $name = $cashAccount->name;
        FinancialTransaction::where('cash_account_id', $cashAccount->id)->delete();
        $cashAccount->delete();
        AuditLog::record('kasa_hesabi_silindi', "Kasa/banka hesabı silindi: {$name}");
        return redirect()->route('admin.index', ['tab' => 'kasa'])->with('success', "'{$name}' hesabı silindi.");
    }

    // MODÜL 14: Aylık Gelir-Gider Raporunu Excel (CSV) Olarak İndirme (Kabul Kriteri #7 - Yalnız Süper Yönetici)
    public function exportFinancialReportCsv()
    {
        // Yetki kontrolü route middleware'inde (admin.permission:role:super_admin) merkezi olarak yapılır.
        $filename = 'enerjik-cimnastik-gelir-gider-raporu-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel Turkish characters
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Tarih', 'Tur', 'Kategori / Makbuz No', 'Aciklama / Ogrenci', 'Hesap', 'Tutar (TL)'], ';');

            foreach (Payment::with(['student', 'cashAccount'])->get() as $p) {
                fputcsv($out, [
                    $p->payment_date->format('d.m.Y'),
                    'GELIR (Tahsilat)',
                    $p->receipt_no,
                    $p->student?->full_name . ' - ' . $p->description,
                    $p->cashAccount?->name,
                    number_format((float) $p->amount, 2, ',', '.'),
                ], ';');
            }

            foreach (FinancialTransaction::with('cashAccount')->get() as $tx) {
                fputcsv($out, [
                    $tx->transaction_date->format('d.m.Y'),
                    $tx->direction === 'income' ? 'GELIR (Diger)' : 'GIDER',
                    $tx->category,
                    $tx->title,
                    $tx->cashAccount?->name,
                    ($tx->direction === 'expense' ? '-' : '') . number_format((float) $tx->amount, 2, ',', '.'),
                ], ';');
            }

            fclose($out);
        };

        AuditLog::record('rapor_indirildi', 'Aylık Gelir-Gider Excel (CSV) raporu indirildi.');
        return response()->stream($callback, 200, $headers);
    }
}
