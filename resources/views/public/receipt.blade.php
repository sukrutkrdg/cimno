<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tahsilat Makbuzu {{ $payment->receipt_no }} | Enerjik Cimnastik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-6">
    <div class="max-w-xl mx-auto bg-white rounded-2xl border border-slate-300 p-8 shadow-lg">
        <div class="flex justify-between items-start border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">{{ $settings['club_name'] ?? 'Enerjik Cimnastik Spor Kulübü' }}</h1>
                <p class="text-xs text-slate-500 mt-1">{{ $settings['address'] ?? '' }} · Tel: {{ $settings['phone'] ?? '' }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold uppercase text-indigo-600 block">Dijital Tahsilat Makbuzu</span>
                <span class="text-lg font-extrabold font-mono">{{ $payment->receipt_no }}</span>
                <span class="text-xs text-slate-500 block">Tarih: {{ $payment->payment_date->format('d.m.Y') }}</span>
            </div>
        </div>

        <div class="py-6 space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-500">Sporcu Adı Soyadı:</span>
                <span class="font-bold">{{ $payment->student?->full_name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-500">Veli Adı Soyadı:</span>
                <span class="font-bold">{{ $payment->student?->parent_name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-500">Branş / Grup:</span>
                <span class="font-semibold">{{ $payment->student?->branch?->name }} - {{ $payment->student?->group?->name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-500">Açıklama / Dönem:</span>
                <span class="font-semibold">{{ $payment->description }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-500">Ödeme Yöntemi / Kasa:</span>
                <span class="font-semibold uppercase">{{ $payment->payment_method }} ({{ $payment->cashAccount?->name }})</span>
            </div>
            <div class="flex justify-between items-center py-3 bg-emerald-50 px-4 rounded-xl border border-emerald-200">
                <span class="font-bold text-emerald-900">Tahsil Edilen Tutar:</span>
                <span class="text-2xl font-extrabold text-emerald-700">{{ number_format($payment->amount, 2, ',', '.') }} ₺</span>
            </div>
            @if($payment->invoice)
                <div class="flex justify-between text-xs text-slate-500 pt-1">
                    <span>İlgili Dönem Toplam Borç: {{ number_format($payment->invoice->net_amount, 2, ',', '.') }} ₺</span>
                    <span>Kalan Bakiye: <strong>{{ number_format($payment->invoice->remaining_amount, 2, ',', '.') }} ₺</strong></span>
                </div>
            @endif
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-between items-center print:hidden">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold">🖨️ Yazdır / PDF Kaydet</button>
            <a href="https://wa.me/90{{ substr(preg_replace('/\D+/', '', $payment->student?->parent_phone ?? ''), -10) }}?text={{ urlencode('Sayın ' . $payment->student?->parent_name . ', ' . $payment->receipt_no . ' numaralı ' . number_format($payment->amount, 2, ',', '.') . ' TL tutarındaki tahsilat makbuzunuz: ' . url()->current()) }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold">
                💬 WhatsApp ile Veliye Gönder
            </a>
        </div>
    </div>
</body>
</html>
