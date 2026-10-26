<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;

trait ManagesWhatsapp
{
    // MODÜL 11: Yeni WhatsApp Hazır Cevap Şablonu Ekle / Sil & Kuyruğa Özel Mesaj Ekle
    public function storeWhatsappTemplate(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:160',
            'shortcut' => 'required|string|max:60|unique:whatsapp_templates,shortcut',
            'days_offset' => 'nullable|integer',
            'send_time' => 'nullable|date_format:H:i',
            'message_template' => 'nullable|string',
        ]);
        $tpl = WhatsappTemplate::create([
            'title' => $validated['title'] ?? 'Yeni Hazır Cevap',
            'shortcut' => $validated['shortcut'],
            'trigger_event' => $request->input('trigger_event', 'custom'),
            'days_offset' => (int) ($validated['days_offset'] ?? 0),
            'send_time' => $validated['send_time'] ?? '10:00',
            'message_template' => $validated['message_template'] ?? 'Merhaba {veli_adi}, ',
            'is_active' => true,
        ]);
        AuditLog::record('whatsapp_sablon_eklendi', "Yeni WhatsApp şablonu eklendi: {$tpl->title}", 'WhatsappTemplate', $tpl->id);
        return redirect()->route('admin.index', ['tab' => 'mesajlar'])->with('success', "'{$tpl->title}' şablonu eklendi!");
    }

    // MODÜL 11: WhatsApp Business Hazır Cevap & Kural Güncelleme (Kabul Kriteri #4)
    public function updateWhatsappTemplate(Request $request, WhatsappTemplate $template)
    {
        $request->validate([
            'title' => 'nullable|string|max:160',
            'shortcut' => 'nullable|string|max:60|unique:whatsapp_templates,shortcut,' . $template->id,
            'days_offset' => 'nullable|integer',
            'send_time' => 'nullable|date_format:H:i',
            'message_template' => 'nullable|string',
        ]);
        $template->update([
            'title' => $request->input('title', $template->title),
            'shortcut' => $request->input('shortcut', $template->shortcut),
            'send_time' => $request->input('send_time', $template->send_time),
            'days_offset' => (int) $request->input('days_offset', $template->days_offset),
            'message_template' => $request->input('message_template', $template->message_template),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record('whatsapp_sablon_guncellendi', "WhatsApp hazır cevap şablonu güncellendi: {$template->title} ({$template->shortcut})", 'WhatsappTemplate', $template->id);

        return redirect()->route('admin.index', ['tab' => 'mesajlar'])->with('success', "'{$template->title}' mesaj şablonu ve gönderim saati başarıyla güncellendi!");
    }

    public function destroyWhatsappTemplate(WhatsappTemplate $template)
    {
        $title = $template->title;
        $template->delete();
        AuditLog::record('whatsapp_sablon_silindi', "WhatsApp şablonu silindi: {$title}");
        return redirect()->route('admin.index', ['tab' => 'mesajlar'])->with('success', "'{$title}' şablonu silindi.");
    }

    public function storeWhatsappMessage(Request $request)
    {
        $validated = $request->validate([
            'recipient_name' => 'nullable|string|max:120',
            'recipient_phone' => 'required|string|max:30',
            'message_text' => 'required|string',
        ]);
        $message = WhatsappMessage::create([
            'recipient_name' => $validated['recipient_name'] ?? 'Veli',
            'recipient_phone' => $validated['recipient_phone'],
            'message_text' => $validated['message_text'],
            'status' => 'queued',
        ]);
        AuditLog::record('whatsapp_mesaj_eklendi', "{$message->recipient_name} için manuel WhatsApp mesajı kuyruğa eklendi.", 'WhatsappMessage', $message->id);
        return redirect()->route('admin.index', ['tab' => 'mesajlar'])->with('success', 'Yeni mesaj gönderim kuyruğuna eklendi!');
    }

    // Günlük Otomatik WhatsApp Hatırlatma Kuyruğu Üretici
    public function generateDailyWhatsappQueue()
    {
        $count = 0;
        $overdueInvoices = Invoice::with('student')->whereIn('status', ['unpaid', 'partial'])->whereDate('due_date', '<', today())->get();
        $tplOverdue = WhatsappTemplate::where('trigger_event', 'due_overdue')->where('is_active', true)->first();

        if ($tplOverdue) {
            foreach ($overdueInvoices as $inv) {
                if (!$inv->student) {
                    continue;
                }
                // Bu buton elle birden çok kez tıklanabilir; aynı gün içinde aynı
                // öğrenci için ikinci bir hatırlatma kuyruğa eklenmesini önler.
                $alreadyQueuedToday = WhatsappMessage::where('template_id', $tplOverdue->id)
                    ->where('student_id', $inv->student->id)
                    ->whereDate('created_at', today())
                    ->exists();
                if ($alreadyQueuedToday) {
                    continue;
                }

                $text = str_replace(
                    ['{veli_adi}', '{ogrenci_adi}', '{donem}', '{tutar}', '{vade}'],
                    [$inv->student->parent_name, $inv->student->full_name, $inv->period_label, number_format((float) $inv->remaining_amount, 2, ',', '.'), $inv->due_date->format('d.m.Y')],
                    $tplOverdue->message_template
                );
                WhatsappMessage::create([
                    'template_id' => $tplOverdue->id,
                    'student_id' => $inv->student->id,
                    'recipient_name' => $inv->student->parent_name,
                    'recipient_phone' => $inv->student->parent_phone,
                    'message_text' => $text,
                    'status' => 'queued',
                ]);
                $count++;
            }
        }

        return redirect()->route('admin.index', ['tab' => 'mesajlar'])->with('success', "{$count} adet yeni otomatik WhatsApp Business hazır mesajı kuyruğa eklendi. 'WhatsApp ile Gönder' butonuna tıklayarak tek dokunuşla gönderebilirsiniz.");
    }

    public function markWhatsappSent(WhatsappMessage $message)
    {
        $message->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        return redirect()->away($message->wa_me_url);
    }

    public function destroyWhatsappMessage(WhatsappMessage $message)
    {
        $recipientName = $message->recipient_name;
        $message->delete();
        AuditLog::record('whatsapp_mesaj_silindi', "{$recipientName} için kuyruktaki WhatsApp mesajı silindi.");
        return redirect()->route('admin.index', ['tab' => 'mesajlar'])->with('success', 'WhatsApp kuyruk mesajı silindi.');
    }
}
