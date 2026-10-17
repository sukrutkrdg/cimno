<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Enerjik Cimnastik Mobil</title>

    <!-- PWA & iOS / Android Ana Ekran Kısayol Etiketleri -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E1B4B">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Enerjik Cimnastik">
    <link rel="apple-touch-icon" href="/icons/icon.svg">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 pb-24 select-none">

    <!-- Mobil Uygulama Üst Barı (Native App Header) -->
    <header class="bg-gradient-to-r from-indigo-950 via-indigo-900 to-indigo-800 text-white px-4 pt-4 pb-3.5 sticky top-0 z-40 shadow-md">
        <div class="max-w-lg mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2.5">
                <img src="/icons/icon.svg" alt="Logo" class="w-9 h-9 rounded-xl shadow">
                <div>
                    <h1 class="font-extrabold text-sm leading-tight">Enerjik Cimnastik Mobil</h1>
                    <span id="network-badge" class="text-[11px] text-emerald-300 font-semibold">🟢 Çevrimiçi · Senkronize</span>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button onclick="openInstallModal()" class="px-2.5 py-1.5 rounded-xl bg-amber-400 text-slate-950 font-extrabold text-[11px] shadow">
                    📲 Ana Ekrana Ekle
                </button>
                <a href="{{ route('public.qr.poster') }}" class="px-2.5 py-1.5 rounded-xl bg-white/15 text-white font-bold text-[11px]">
                    🏁 QR Afiş
                </a>
            </div>
        </div>
    </header>

    <!-- iOS & Android Ana Ekrana Kısayol Ekleme Bilgi Bandı -->
    <div id="install-banner" class="max-w-lg mx-auto mt-3 px-3">
        <div class="bg-gradient-to-r from-indigo-600 to-rose-600 text-white rounded-2xl p-3.5 shadow flex items-center justify-between gap-3">
            <div class="text-xs">
                <strong class="block text-sm font-extrabold">📱 Telefonunuza Uygulama Olarak Ekleyin!</strong>
                <span class="text-indigo-100 text-[11px]">Mağaza indirmesi gerekmeden iPhone ve Android ana ekranınıza tek dokunuşla kısayol ekleyin.</span>
            </div>
            <button onclick="triggerPwaInstall()" class="shrink-0 px-3.5 py-2 rounded-xl bg-white text-indigo-900 font-extrabold text-xs shadow">
                Kısayol Ekle
            </button>
        </div>
    </div>

    <!-- ANA MOBİL EKRAN İÇERİĞİ -->
    <main class="max-w-lg mx-auto p-3 space-y-4">

        <!-- ==================================================== -->
        <!-- 1. VELİ MOBİL EKRANI                                 -->
        <!-- ==================================================== -->
        @if($rol === 'veli')
            <!-- Veli Hızlı Salon Giriş QR Yoklama Kartı -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-2xl p-4 shadow-md flex items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] font-extrabold uppercase bg-white/20 px-2 py-0.5 rounded-full">Salona Geldiniz mi?</span>
                    <h2 class="text-sm font-extrabold mt-1">📷 QR ile Hızlı Ders Yoklaması Yap</h2>
                    <p class="text-[11px] text-emerald-100">Salondaki QR kodu okutarak veya buradan tek dokunuşla giriş bildirin.</p>
                </div>
                <a href="{{ route('public.qr.checkin', ['phone' => $phone]) }}" class="shrink-0 px-3.5 py-2.5 rounded-xl bg-white text-emerald-900 font-extrabold text-xs shadow">
                    ✅ Yoklama Yap
                </a>
            </div>

            <div class="bg-white rounded-2xl p-3.5 border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('public.mobile.pwa') }}" class="flex items-center justify-between gap-2">
                    <input type="hidden" name="rol" value="veli">
                    <span class="text-xs font-bold text-slate-600">Veli Hesabı:</span>
                    <select name="phone" onchange="this.form.submit()" class="flex-1 bg-slate-100 border border-slate-300 text-slate-900 text-xs rounded-xl px-3 py-2 font-bold">
                        <option value="05332223344" {{ str_contains($phone, '2223344') ? 'selected' : '' }}>Murat Arslan (Elif & Kerem Arslan)</option>
                        <option value="05354445566" {{ str_contains($phone, '4445566') ? 'selected' : '' }}>Zeynep Çelik (Defne Çelik)</option>
                    </select>
                </form>
            </div>

            @foreach($students as $st)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="bg-indigo-900 text-white p-4 flex justify-between items-center">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">{{ $st->branch?->name }}</span>
                            <h2 class="text-lg font-extrabold mt-1">🤸 {{ $st->full_name }}</h2>
                            <p class="text-[11px] text-indigo-200">{{ $st->group?->name }} · Antrenör: {{ $st->group?->coach?->name }}</p>
                        </div>
                        <div class="text-right bg-white/10 px-3 py-1.5 rounded-xl">
                            <span class="text-[10px] text-indigo-200 block">Telafi Hakkı</span>
                            <span class="text-sm font-extrabold text-amber-300">{{ $st->makeup_credits }} Ders</span>
                        </div>
                    </div>

                    <div class="p-4 space-y-4">
                        <!-- Ders Saatleri -->
                        <div>
                            <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2">📅 Haftalık Antrenman Saatleri</h3>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($st->group?->schedules ?? [] as $sch)
                                    <span class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-900 font-bold text-xs">
                                        🗓️ {{ $sch->day_of_week }} {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Ödeme ve Dijital Makbuzlar -->
                        <div>
                            <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2">💳 Aidat & Dijital Makbuzlar</h3>
                            <div class="space-y-2">
                                @foreach($st->invoices as $inv)
                                    <div class="p-3 rounded-xl border {{ $inv->remaining_amount > 0 ? 'bg-rose-50/60 border-rose-200' : 'bg-emerald-50/50 border-emerald-200' }}">
                                        <div class="flex justify-between items-center">
                                            <span class="font-bold text-xs">{{ $inv->period_label }}</span>
                                            <span class="text-xs font-extrabold {{ $inv->remaining_amount > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                                {{ $inv->remaining_amount > 0 ? 'Kalan: ' . number_format($inv->remaining_amount, 0, ',', '.') . ' ₺' : '✅ Ödendi' }}
                                            </span>
                                        </div>
                                        @foreach($inv->payments as $pay)
                                            <div class="mt-2 pt-2 border-t border-slate-200/70 flex justify-between items-center text-[11px]">
                                                <span>🧾 {{ $pay->receipt_no }} ({{ number_format($pay->amount, 0, ',', '.') }} ₺)</span>
                                                <a href="{{ route('public.receipt', $pay->id) }}" class="px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-bold">Makbuz Aç</a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Son Yoklamalar -->
                        <div>
                            <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2">✅ Son Yoklama Kayıtları</h3>
                            <div class="grid grid-cols-2 gap-1.5">
                                @foreach($st->attendances->take(4) as $att)
                                    <div class="p-2 rounded-xl bg-slate-50 border text-[11px] flex justify-between items-center">
                                        <span>{{ $att->attendance_date->format('d.m.Y') }}</span>
                                        <span class="font-bold {{ $att->status === 'present' ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $att->status === 'present' ? 'Geldi' : 'Gelmedi' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Gelişim Karnesi -->
                        @foreach($st->developmentRecords as $dev)
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs">
                                <div class="font-extrabold text-amber-950">🏅 {{ $dev->term_label }} ({{ $dev->current_level }})</div>
                                <div class="grid grid-cols-4 gap-1 text-center my-2 text-[11px]">
                                    <div class="bg-white p-1 rounded border">Esn: <strong>{{ $dev->flexibility_score }}</strong></div>
                                    <div class="bg-white p-1 rounded border">Kuv: <strong>{{ $dev->strength_score }}</strong></div>
                                    <div class="bg-white p-1 rounded border">Den: <strong>{{ $dev->balance_score }}</strong></div>
                                    <div class="bg-white p-1 rounded border">Dis: <strong>{{ $dev->discipline_score }}</strong></div>
                                </div>
                                <p class="text-[11px] text-slate-700 italic">"{{ $dev->coach_comment }}"</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Kulüp IBAN ve Hızlı WhatsApp -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-2">
                <div class="text-xs font-extrabold text-indigo-600">🏦 Havale / EFT İçin Kulüp IBAN Bilgisi</div>
                <div class="text-xs font-bold">{{ $settings['iban_holder'] ?? '' }}</div>
                <div class="text-xs font-mono bg-slate-100 p-2 rounded-xl select-all">{{ $settings['iban_number'] ?? '' }}</div>
                <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '905321001001' }}?text={{ urlencode('Merhaba, mobil uygulamadan yazıyorum.') }}" target="_blank" class="block text-center w-full py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">
                    💬 Salon WhatsApp Hattına Yaz
                </a>
            </div>
        @endif

        <!-- ==================================================== -->
        <!-- 2. ANTRENÖR MOBİL EKRANI (ÇEVRİMDIŞI YOKLAMA)        -->
        <!-- ==================================================== -->
        @if($rol === 'antrenor')
            <div class="bg-indigo-950 text-white rounded-2xl p-4 shadow">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-[11px] text-amber-300 font-bold uppercase">Antrenör Mobil Modülü</span>
                        <h2 class="text-base font-extrabold">🎽 Tek Dokunuşla Grup Yoklaması</h2>
                    </div>
                    <button onclick="syncOfflineQueue()" class="px-3 py-1.5 rounded-xl bg-emerald-500 text-slate-950 font-extrabold text-xs">
                        🔄 Senkronize Et
                    </button>
                </div>
                <p class="text-[11px] text-indigo-200 mt-1">İnternet kesilse bile yoklamalar telefon hafızasına kaydedilir ve bağlantı geldiğinde otomatik gönderilir.</p>
                <div id="offline-status" class="text-[11px] text-amber-300 font-bold mt-1 hidden"></div>
            </div>

            @foreach($groups as $grp)
                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-3">
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <span class="text-[11px] font-bold text-indigo-600">{{ $grp->branch?->name }} · {{ $grp->area }}</span>
                            <h3 class="font-extrabold text-sm">{{ $grp->name }}</h3>
                        </div>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100">{{ $grp->activeStudents->count() }}/{{ $grp->capacity }}</span>
                    </div>

                    <div class="space-y-2">
                        @foreach($grp->activeStudents as $st)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center gap-2">
                                <div>
                                    <div class="font-bold text-xs">{{ $st->full_name }}</div>
                                    <div class="text-[10px] text-slate-500">Telafi Hakkı: {{ $st->makeup_credits }} · Beden: {{ $st->uniform_size ?? '-' }}</div>
                                </div>
                                <div class="flex gap-1.5">
                                    <button onclick="markMobileAttendance({{ $grp->id }}, {{ $st->id }}, 'present', this)" class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold">
                                        ✅ Geldi
                                    </button>
                                    <button onclick="markMobileAttendance({{ $grp->id }}, {{ $st->id }}, 'absent', this)" class="px-3 py-1.5 rounded-xl bg-rose-100 text-rose-700 text-xs font-bold">
                                        ❌ Yok
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        <!-- ==================================================== -->
        <!-- 3. YÖNETİCİ / SEKRETER MOBİL EKRANI                  -->
        <!-- ==================================================== -->
        @if($rol === 'yonetici')
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] text-slate-500 font-bold">Bu Ayki Tahsilat</span>
                    <div class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ number_format($stats['month_collection'], 0, ',', '.') }} ₺</div>
                </div>
                <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-sm">
                    <span class="text-[11px] text-slate-500 font-bold">Bekleyen Aidat</span>
                    <div class="text-xl font-extrabold text-rose-600 mt-0.5">{{ number_format($stats['overdue_total'], 0, ',', '.') }} ₺</div>
                </div>
            </div>

            <!-- Mobil Hızlı Tahsilat -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <h3 class="font-extrabold text-sm mb-2.5">⚡ Hızlı Aidat Tahsilatı Gir</h3>
                <form action="{{ route('admin.payments.store') }}" method="POST" class="space-y-2.5 text-xs">
                    @csrf
                    <select name="invoice_id" class="w-full border rounded-xl px-3 py-2.5 bg-slate-50 font-bold">
                        @foreach($overdueInvoices as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->student?->full_name }} ({{ number_format($inv->remaining_amount, 0, ',', '.') }} ₺)</option>
                        @endforeach
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="amount" value="1220" required class="border rounded-xl px-3 py-2 font-bold" placeholder="Tutar (₺)">
                        <select name="payment_method" class="border rounded-xl px-3 py-2 bg-white font-bold">
                            <option value="cash">Nakit</option>
                            <option value="pos">POS (Kart)</option>
                            <option value="bank_transfer">Havale/EFT</option>
                        </select>
                    </div>
                    <input type="hidden" name="cash_account_id" value="{{ $cashAccounts->first()?->id ?? 1 }}">
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-extrabold">
                        🧾 Tahsilatı Kaydet & Makbuz Üret
                    </button>
                </form>
            </div>

            <!-- WhatsApp Business Hazır Cevap Kuyruğu -->
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-2.5">
                <div class="flex justify-between items-center">
                    <h3 class="font-extrabold text-sm text-emerald-800">💬 WhatsApp Business Hazır Mesajlar ({{ $whatsappQueue->count() }})</h3>
                    <a href="{{ route('admin.index', ['tab' => 'mesajlar']) }}" class="text-[11px] font-bold text-indigo-600 underline">Tam Panel</a>
                </div>
                @foreach($whatsappQueue as $msg)
                    <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="font-extrabold text-xs">{{ $msg->recipient_name }}</span>
                            <span class="text-[10px] font-mono bg-white px-2 py-0.5 rounded border font-bold">{{ $msg->template?->shortcut ?? '/mesaj' }}</span>
                        </div>
                        <p class="text-[11px] text-slate-700 leading-snug">{{ $msg->message_text }}</p>
                        <a href="{{ route('admin.whatsapp.send', $msg->id) }}" target="_blank" class="block text-center w-full py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold shadow-sm">
                            💬 WhatsApp Business'ta Aç & Gönder →
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

    </main>

    <!-- iOS & Android Ana Ekrana Ekleme Rehberi Modalı -->
    <div id="install-modal" class="fixed inset-0 bg-slate-950/75 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-extrabold">📲 Telefon Ana Ekranına Kısayol Ekle</h3>
                <button onclick="closeInstallModal()" class="text-slate-400 font-extrabold text-lg">✕</button>
            </div>
            <p class="text-xs text-slate-600">App Store veya Google Play'e gerek kalmadan uygulamamızı 5 saniyede telefon ekranınıza ekleyebilirsiniz:</p>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5 text-xs">
                <div class="font-extrabold text-indigo-900">🍎 iPhone / iPad (Safari) İçin:</div>
                <p>1. Ekranın altındaki <strong>Paylaş (Yukarı Oklu Kare ⬆️)</strong> simgesine dokunun.</p>
                <p>2. Menüden <strong>"Ana Ekrana Ekle" (Add to Home Screen)</strong> seçeneğine basıp sağ üstten <strong>Ekle</strong> deyin.</p>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5 text-xs">
                <div class="font-extrabold text-emerald-900">🤖 Android (Chrome) İçin:</div>
                <p>1. Sağ üstteki <strong>Üç Nokta (⋮)</strong> menüsüne dokunun.</p>
                <p>2. <strong>"Ana ekrana ekle"</strong> veya <strong>"Uygulamayı yükle"</strong> seçeneğine dokunun.</p>
            </div>

            <button onclick="closeInstallModal()" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-extrabold text-xs">
                Anladım, Teşekkürler
            </button>
        </div>
    </div>

    <!-- Alt Mobil Uygulama Navigasyon Barı (Bottom Tab Bar) -->
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-40 shadow-lg">
        <div class="max-w-lg mx-auto grid grid-cols-4 text-center py-2">
            <a href="{{ route('public.mobile.pwa', ['rol' => 'veli']) }}" class="flex flex-col items-center gap-0.5 {{ $rol === 'veli' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
                <span class="text-lg">👨‍👩‍👧</span>
                <span class="text-[11px]">Veli Ekranı</span>
            </a>
            <a href="{{ route('public.mobile.pwa', ['rol' => 'antrenor']) }}" class="flex flex-col items-center gap-0.5 {{ $rol === 'antrenor' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
                <span class="text-lg">🎽</span>
                <span class="text-[11px]">Antrenör Yoklama</span>
            </a>
            <a href="{{ route('public.mobile.pwa', ['rol' => 'yonetici']) }}" class="flex flex-col items-center gap-0.5 {{ $rol === 'yonetici' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
                <span class="text-lg">💬</span>
                <span class="text-[11px]">Yönetim & WA</span>
            </a>
            <a href="{{ route('admin.index') }}" class="flex flex-col items-center gap-0.5 text-slate-500 font-semibold">
                <span class="text-lg">⚙️</span>
                <span class="text-[11px]">Tam Panel</span>
            </a>
        </div>
    </nav>

    <script>
        // 1. PWA Service Worker Kaydı
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        // 2. Android Chrome Otomatik Kurulum İstemi + iOS Rehberi
        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        function triggerPwaInstall() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(() => { deferredPrompt = null; });
            } else {
                openInstallModal();
            }
        }

        function openInstallModal() {
            const m = document.getElementById('install-modal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeInstallModal() {
            const m = document.getElementById('install-modal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        // 3. Antrenör Çevrimdışı (Offline) Yoklama ve Otomatik Senkronizasyon
        async function markMobileAttendance(groupId, studentId, status, btn) {
            const record = {
                group_id: groupId,
                student_id: studentId,
                status: status,
                attendance_date: new Date().toISOString().slice(0, 10)
            };

            const queue = JSON.parse(localStorage.getItem('enerjik_offline_attendance') || '[]');
            queue.push(record);
            localStorage.setItem('enerjik_offline_attendance', JSON.stringify(queue));

            btn.textContent = status === 'present' ? '✓ Kaydedildi' : '✗ Kaydedildi';
            await syncOfflineQueue();
        }

        async function syncOfflineQueue() {
            const queue = JSON.parse(localStorage.getItem('enerjik_offline_attendance') || '[]');
            const badge = document.getElementById('offline-status');
            if (queue.length === 0) return;

            try {
                const res = await fetch('{{ route("public.mobile.attendance.sync") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ records: queue })
                });
                if (res.ok) {
                    localStorage.removeItem('enerjik_offline_attendance');
                    if (badge) {
                        badge.classList.remove('hidden');
                        badge.textContent = `✅ ${queue.length} yoklama sunucuya senkronize edildi.`;
                    }
                }
            } catch (err) {
                if (badge) {
                    badge.classList.remove('hidden');
                    badge.textContent = `📶 Çevrimdışı mod: ${queue.length} yoklama telefona kaydedildi, internet gelince gönderilecek.`;
                }
            }
        }

        window.addEventListener('online', syncOfflineQueue);
    </script>
</body>
</html>
