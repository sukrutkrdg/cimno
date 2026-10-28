<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Veli Girişi & Mobil Panel | Tekirdağ Enerjik Cimnastik</title>

    <!-- PWA & iOS / Android Ana Ekran Kısayol Etiketleri -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E1B4B">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Enerjik Cimnastik">
    <link rel="apple-touch-icon" href="/icons/logo.jpg">
    <link rel="icon" type="image/jpeg" href="/icons/logo.jpg">

    @php
        $brandPalette = \App\Support\ColorPalette::fromHex($settings['brand_color'] ?? null);
        $accentPalette = \App\Support\ColorPalette::fromHex($settings['brand_color'] ?? null, 0.6);
    @endphp
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('enerjik_theme');
                var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (dark) document.documentElement.classList.add('dark');
            } catch (e) {}
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Veli/ziyaretçi teması: tek sıcak marka rengi (bordo-eflatun), panel ayarlarından
        // seçilen tek bir hex renkten otomatik üretilir (bkz. App\Support\ColorPalette).
        // Durum renkleri (emerald/amber/rose) değiştirilmedi.
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '{{ $brandPalette['50'] }}', 100: '{{ $brandPalette['100'] }}', 200: '{{ $brandPalette['200'] }}', 300: '{{ $brandPalette['300'] }}',
                            400: '{{ $brandPalette['400'] }}', 500: '{{ $brandPalette['500'] }}', 600: '{{ $brandPalette['600'] }}', 700: '{{ $brandPalette['700'] }}',
                            800: '{{ $brandPalette['800'] }}', 900: '{{ $brandPalette['900'] }}', 950: '{{ $brandPalette['950'] }}',
                        },
                        purple: {
                            50: '{{ $accentPalette['50'] }}', 100: '{{ $accentPalette['100'] }}', 200: '{{ $accentPalette['200'] }}', 300: '{{ $accentPalette['300'] }}',
                            400: '{{ $accentPalette['400'] }}', 500: '{{ $accentPalette['500'] }}', 600: '{{ $accentPalette['600'] }}', 700: '{{ $accentPalette['700'] }}',
                            800: '{{ $accentPalette['800'] }}', 900: '{{ $accentPalette['900'] }}', 950: '{{ $accentPalette['950'] }}',
                        },
                    },
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html, body { max-width: 100%; overflow-x: hidden; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 pb-24 transition-colors">

    <!-- Üst Uygulama Barı -->
    <header class="bg-gradient-to-r from-indigo-950 via-purple-900 to-indigo-900 text-white px-4 py-3.5 sticky top-0 z-40 shadow-md">
        <div class="max-w-lg mx-auto flex justify-between items-center">
            <a href="/" class="flex items-center gap-2.5">
                <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-10 h-10 rounded-full object-cover border-2 border-purple-300 shadow">
                <div>
                    <h1 class="font-extrabold text-sm leading-tight">Enerjik Cimnastik</h1>
                    <span class="text-[11px] text-purple-200 font-semibold">👨‍👩‍👧 Veli Mobil Takip Sistemi</span>
                </div>
            </a>
            <div class="flex items-center gap-1.5">
                <button onclick="enerjikToggleTheme()" class="px-2.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-[13px]" aria-label="Tema Değiştir">
                    <span class="dark:hidden">🌙</span><span class="hidden dark:inline">☀️</span>
                </button>
                @if($activePhone && $students->isNotEmpty())
                    <button id="pushToggleBtn" onclick="enerjikPush.toggle()" class="px-2.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-[11px] font-bold" aria-label="Ders Saati Bildirimleri">
                        🔔 <span id="pushToggleLabel">Bildirimler</span>
                    </button>
                @endif
                <button onclick="enerjikPwa.install()" class="px-2.5 py-1.5 rounded-xl bg-amber-400 text-slate-950 font-extrabold text-[11px] shadow">
                    📲 Ana Ekrana Ekle
                </button>
                @if($activePhone && $students->isNotEmpty())
                    <a href="{{ route('public.mobile.pwa', ['cikis' => 1]) }}" class="px-2.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-[11px]">
                        Çıkış
                    </a>
                @else
                    <a href="/" class="px-2.5 py-1.5 rounded-xl bg-white/15 text-white font-bold text-[11px]">
                        ← Site
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="max-w-lg mx-auto p-3.5 space-y-4">

        <!-- ==================================================== -->
        <!-- DURUM A: VELİ HENÜZ GİRİŞ YAPMADIYSA (GİRİŞ EKRANI)  -->
        <!-- ==================================================== -->
        @if(!$activePhone || $students->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-lg space-y-5 mt-2">
                <div class="text-center space-y-2">
                    <img src="/icons/logo.jpg" alt="Logo" class="w-20 h-20 rounded-full object-cover mx-auto border-4 border-purple-600 shadow-md">
                    <span class="inline-block text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300">
                        Öğrenciye Bağlı Veli Sistemi
                    </span>
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">👨‍👩‍👧 Veli Girişi</h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Çocuğunuzun kulübümüze kesin kaydı yapıldığında <strong>cep telefonu numaranız ve 4 haneli PIN kodunuz</strong> WhatsApp ile tarafınıza gönderilir. Bu ikisini birlikte girerek öğrencinizin ders programını, yoklamasını, aidat durumunu ve gelişim karnesini görüntüleyebilirsiniz.
                    </p>
                </div>

                @if($loginError)
                    <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-300 text-xs font-bold">
                        ⚠️ {{ $loginError }}
                    </div>
                @endif

                <form method="GET" action="{{ route('public.mobile.pwa') }}" class="space-y-3">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 mb-1">Salonda Kayıtlı Cep Telefonu Numaranız</label>
                        <input type="tel" name="phone" required placeholder="05XX XXX XX XX" class="w-full rounded-2xl border-2 border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:border-indigo-600 focus:outline-none px-4 py-3 text-base font-extrabold tracking-wide">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 mb-1">4 Haneli PIN Kodunuz</label>
                        <input type="tel" inputmode="numeric" maxlength="4" name="pin" required placeholder="••••" class="w-full rounded-2xl border-2 border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 focus:border-indigo-600 focus:outline-none px-4 py-3 text-base font-extrabold tracking-[0.3em] text-center">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">PIN kodunuzu bilmiyorsanız salon sekreterliğimizden öğrenebilirsiniz.</p>
                    </div>
                    <button type="submit" class="w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm shadow-lg shadow-indigo-600/25 transition">
                        🔓 Giriş Yap ve Öğrencimi Göster
                    </button>
                </form>

                <!-- Telefon Ana Ekranına Kısayol Ekleme Kutusu -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950 dark:to-purple-950 border border-indigo-100 dark:border-indigo-900 flex items-center justify-between gap-3">
                    <div class="text-xs">
                        <strong class="block text-indigo-950 dark:text-indigo-200 font-extrabold">📲 Telefon Ekranına Kısayol Atın</strong>
                        <span class="text-slate-600 dark:text-slate-400 text-[11px]">Uygulama gibi tek dokunuşla açmak için ana ekranınıza ekleyin.</span>
                    </div>
                    <button onclick="enerjikPwa.install()" class="shrink-0 px-3 py-2 rounded-xl bg-indigo-600 text-white font-extrabold text-xs">
                        Kısayol Ekle
                    </button>
                </div>

                @if(app()->environment(['local', 'testing']))
                    <!-- GÜVENLİK: Gerçek görünümlü veli adı/telefonu içerdiği için yalnızca
                         local/testing ortamında görünür; production'da KVKK gereği gösterilmez. -->
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-2">
                        <span class="block text-[11px] font-extrabold uppercase text-slate-400">⚠️ Sadece Geliştirme Ortamı: Örnek Veli Girişi</span>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">PIN: 8178 (Murat Arslan) · PIN: 4219 (Zeynep Çelik)</p>
                    </div>
                @endif
            </div>
        @else

        <!-- ==================================================== -->
        <!-- DURUM B: VELİ GİRİŞ YAPTIYSA (ÖĞRENCİYE ÖZEL EKRAN)  -->
        <!-- ==================================================== -->
            <!-- Hoş Geldiniz & Hızlı QR Yoklama Kartı -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-2xl p-4 shadow-md flex items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] font-extrabold uppercase bg-white/20 px-2 py-0.5 rounded-full">Hoş Geldiniz, Sayın {{ $students->first()->parent_name }}</span>
                    <h2 class="text-sm font-extrabold mt-1">📷 Salona Geldiniz mi? QR Yoklama</h2>
                    <p class="text-[11px] text-emerald-100">Salona girişte tek dokunuşla bugünkü ders yoklamasını onaylayın.</p>
                </div>
                <a href="{{ route('public.qr.checkin', ['kamera' => 1]) }}" class="shrink-0 px-3.5 py-2.5 rounded-xl bg-white text-emerald-900 font-extrabold text-xs shadow">
                    📷 Kamerayı Aç
                </a>
            </div>

            <!-- Veliye Bağlı Öğrenciler (Kardeşse Alt Alta veya Sekmeli Görünür) -->
            @foreach($students as $st)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="bg-indigo-900 text-white p-4 flex justify-between items-center">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full">{{ $st->branch?->name }}</span>
                            <h2 class="text-lg font-extrabold mt-1">🤸 {{ $st->full_name }}</h2>
                            <p class="text-[11px] text-indigo-200">{{ $st->group?->name }} · Antrenör: {{ $st->group?->coach?->name ?? 'Kulüp Antrenörü' }}</p>
                        </div>
                        <div class="text-right bg-white/10 px-3 py-1.5 rounded-xl">
                            <span class="text-[10px] text-indigo-200 block">Telafi Hakkı</span>
                            <span class="text-sm font-extrabold text-amber-300">{{ $st->makeup_credits }} Ders</span>
                        </div>
                    </div>

                    <div class="p-4 space-y-4">
                        @if($sekme === 'cocuklarim' || $sekme === 'tumu')
                            @php
                                $todaySchedules = ($st->group?->schedules ?? collect())->where('day_of_week', $todayName);
                            @endphp
                            @if($todaySchedules->isNotEmpty())
                                <!-- Bugünkü Ders Hatırlatması -->
                                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-amber-400 to-orange-400 text-slate-950 shadow-md">
                                    <div class="font-extrabold text-sm">🔥 Bugün Dersiniz Var!</div>
                                    <div class="text-xs font-bold mt-0.5">
                                        @foreach($todaySchedules as $sch)
                                            ⏰ {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} ({{ $sch->area }})@if(!$loop->last), @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Ders Saatleri -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="text-xs font-extrabold uppercase text-slate-500">📅 Haftalık Antrenman Saatleri (Takvim)</h3>
                                    @if(($st->group?->schedules ?? collect())->isNotEmpty())
                                        <a href="{{ $st->calendarUrl() }}" class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 underline">📲 Takvime Ekle</a>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($st->group?->schedules ?? [] as $sch)
                                        @php $isToday = $sch->day_of_week === $todayName; @endphp
                                        <span class="px-3 py-1.5 rounded-xl font-bold text-xs {{ $isToday ? 'bg-emerald-500 text-white ring-2 ring-emerald-300' : 'bg-indigo-50 dark:bg-indigo-950 text-indigo-900 dark:text-indigo-200' }}">
                                            {{ $isToday ? '🔥' : '🗓️' }} {{ $sch->day_of_week }} {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} ({{ $sch->area }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Son Yoklamalar -->
                            <div>
                                <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2">✅ Son Yoklama Kayıtları</h3>
                                <div class="grid grid-cols-2 gap-1.5">
                                    @forelse($st->attendances->take(4) as $att)
                                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 border dark:border-slate-700 text-[11px] flex justify-between items-center">
                                            <span>{{ $att->attendance_date->format('d.m.Y') }}</span>
                                            <span class="font-bold {{ $att->status === 'present' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                {{ $att->status === 'present' ? '✅ Geldi' : '❌ Gelmedi' }}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="text-xs text-slate-400 col-span-2">Henüz yoklama kaydı bulunmuyor.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endif

                        @if($sekme === 'cocuklarim' || $sekme === 'aidat')
                            <!-- Ödeme ve Dijital Makbuzlar -->
                            <div>
                                <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2">💳 Aidat Durumu & Dijital Makbuzlar</h3>
                                <div class="space-y-2">
                                    @foreach($st->invoices as $inv)
                                        <div class="p-3 rounded-xl border {{ $inv->remaining_amount > 0 ? 'bg-rose-50/60 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900' : 'bg-emerald-50/50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900' }}">
                                            <div class="flex justify-between items-center">
                                                <span class="font-bold text-xs">{{ $inv->period_label }}</span>
                                                <span class="text-xs font-extrabold {{ $inv->remaining_amount > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400' }}">
                                                    {{ $inv->remaining_amount > 0 ? 'Kalan: ' . number_format($inv->remaining_amount, 0, ',', '.') . ' ₺' : '✅ Ödendi' }}
                                                </span>
                                            </div>
                                            @foreach($inv->payments as $pay)
                                                <div class="mt-2 pt-2 border-t border-slate-200/70 dark:border-slate-700 flex justify-between items-center text-[11px]">
                                                    <span>🧾 {{ $pay->receipt_no }} ({{ number_format($pay->amount, 0, ',', '.') }} ₺)</span>
                                                    <a href="{{ $pay->receiptUrl() }}" class="px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-bold">Makbuz PDF</a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($sekme === 'cocuklarim' || $sekme === 'karne')
                            <!-- Gelişim Karnesi -->
                            @foreach($st->developmentRecords as $dev)
                                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 text-xs">
                                    <div class="font-extrabold text-amber-950 dark:text-amber-300">🏅 {{ $dev->term_label }} ({{ $dev->current_level }})</div>
                                    <div class="grid grid-cols-4 gap-1 text-center my-2 text-[11px]">
                                        <div class="bg-white dark:bg-slate-800 dark:border-slate-700 p-1 rounded border">Esn: <strong>{{ $dev->flexibility_score }}</strong></div>
                                        <div class="bg-white dark:bg-slate-800 dark:border-slate-700 p-1 rounded border">Kuv: <strong>{{ $dev->strength_score }}</strong></div>
                                        <div class="bg-white dark:bg-slate-800 dark:border-slate-700 p-1 rounded border">Den: <strong>{{ $dev->balance_score }}</strong></div>
                                        <div class="bg-white dark:bg-slate-800 dark:border-slate-700 p-1 rounded border">Dis: <strong>{{ $dev->discipline_score }}</strong></div>
                                    </div>
                                    <p class="text-[11px] text-slate-700 dark:text-slate-300 italic">"{{ $dev->coach_comment }}"</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach

            @if($sekme === 'cocuklarim' || $sekme === 'duyurular')
                <!-- Duyurular -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                    <h3 class="text-xs font-extrabold uppercase text-slate-500">📢 Güncel Duyurular</h3>
                    @forelse($announcements as $ann)
                        <div class="p-3 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900">
                            <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">{{ $ann->created_at->format('d.m.Y') }}{{ $ann->is_pinned ? ' · 📌 Sabitlenmiş' : '' }}</span>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white mt-0.5">{{ $ann->title }}</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">{{ $ann->content }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Şu anda güncel bir duyuru bulunmuyor.</p>
                    @endforelse
                </div>
            @endif

            <!-- Hızlı WhatsApp -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
                <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '905388993159' }}?text={{ urlencode('Merhaba, veli ekranından yazıyorum.') }}" target="_blank" class="block text-center w-full py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">
                    💬 Salon WhatsApp Hattına Yaz
                </a>
            </div>
        @endif

    </main>

    <!-- Alt Mobil Uygulama Navigasyon Barı (Yalnız Veli Menüsü) -->
    @if($activePhone && $students->isNotEmpty())
        @include('public.partials._bottom_nav', ['activeTab' => $sekme])
    @endif

    <script>
        function enerjikToggleTheme() {
            var html = document.documentElement;
            var isDark = html.classList.toggle('dark');
            try { localStorage.setItem('enerjik_theme', isDark ? 'dark' : 'light'); } catch (e) {}
        }
    </script>

    @if($activePhone && $students->isNotEmpty())
        <script>
            // Ders saati yaklaşıyor Web Push bildirimi aboneliği. VAPID_PUBLIC_KEY
            // (gizli olmayan, adının gereği "public" anahtar) sunucu tarafında
            // .env'de tanımlıysa burada bastırılır; tanımlı değilse buton pasif kalır.
            window.enerjikPush = (function () {
                const VAPID_PUBLIC_KEY = @json(config('services.vapid.public_key'));
                const btn = document.getElementById('pushToggleBtn');
                const label = document.getElementById('pushToggleLabel');

                function urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const raw = window.atob(base64);
                    return Uint8Array.from([...raw].map((c) => c.charCodeAt(0)));
                }

                function csrfToken() {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    return meta ? meta.content : '';
                }

                function setLabel(subscribed) {
                    if (!btn || !label) return;
                    label.textContent = subscribed ? 'Açık' : 'Bildirimler';
                    btn.classList.toggle('bg-emerald-500', subscribed);
                    btn.classList.toggle('bg-white/15', !subscribed);
                }

                async function getSubscription() {
                    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return null;
                    const reg = await navigator.serviceWorker.ready;
                    return reg.pushManager.getSubscription();
                }

                async function refreshButtonState() {
                    const sub = await getSubscription();
                    setLabel(!!sub);
                }

                async function subscribe() {
                    if (!VAPID_PUBLIC_KEY) {
                        alert('Bildirim sistemi henüz kurulmadı, lütfen salon yönetimine bildirin.');
                        return;
                    }
                    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                        alert('Bu tarayıcı bildirimleri desteklemiyor.');
                        return;
                    }
                    try {
                        const permission = await Notification.requestPermission();
                        if (permission !== 'granted') return;

                        const reg = await navigator.serviceWorker.ready;
                        const sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
                        });

                        await fetch('{{ route('public.push.subscribe') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                            credentials: 'same-origin',
                            body: JSON.stringify(sub.toJSON()),
                        });
                        setLabel(true);
                    } catch (e) {
                        console.error('Push aboneliği başarısız:', e);
                    }
                }

                async function unsubscribe() {
                    const sub = await getSubscription();
                    if (!sub) { setLabel(false); return; }
                    await fetch('{{ route('public.push.unsubscribe') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                        credentials: 'same-origin',
                        body: JSON.stringify({ endpoint: sub.endpoint }),
                    });
                    await sub.unsubscribe();
                    setLabel(false);
                }

                async function toggle() {
                    const sub = await getSubscription();
                    if (sub) {
                        await unsubscribe();
                    } else {
                        await subscribe();
                    }
                }

                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', () => {
                        navigator.serviceWorker.ready.then(refreshButtonState).catch(() => {});
                    });
                }

                return { toggle, subscribe, unsubscribe };
            })();
        </script>
    @endif

    @include('partials.pwa-install-prompt')
</body>
</html>
