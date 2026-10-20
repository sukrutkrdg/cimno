<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Salon Giriş QR Yoklama | Enerjik Cimnastik</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E1B4B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/icons/logo.jpg">
    <link rel="icon" type="image/jpeg" href="/icons/logo.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen pb-24">

    <!-- Üst Başlık -->
    <header class="bg-gradient-to-r from-emerald-700 via-teal-700 to-indigo-900 text-white px-4 py-4 shadow-md sticky top-0 z-40">
        <div class="max-w-lg mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2.5">
                <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow">
                <div>
                    <h1 class="font-extrabold text-sm sm:text-base leading-none">Salon Giriş QR Yoklama</h1>
                    <span class="text-[11px] text-emerald-200 font-semibold">{{ today()->format('d.m.Y') }} · Tekirdağ Enerjik Cimnastik</span>
                </div>
            </div>
            <a href="{{ route('public.mobile.pwa') }}" class="px-3 py-1.5 rounded-xl bg-white/15 text-white text-xs font-bold">
                📱 Mobil Ekran
            </a>
        </div>
    </header>

    <main class="max-w-lg mx-auto p-3.5 space-y-4">

        @if(session('checkin_success'))
            <div class="p-4 rounded-2xl bg-emerald-600 text-white shadow-lg space-y-1 animate-pulse">
                <div class="text-base font-extrabold">🎉 Yoklama Başarıyla Alındı!</div>
                <p class="text-xs text-emerald-50">{{ session('checkin_success') }}</p>
            </div>
        @endif

        <!-- Kamera ile Salon QR Afişini Okutma -->
        <button type="button" id="qrScanBtn" class="w-full py-4 rounded-2xl bg-gradient-to-r from-rose-600 to-orange-600 text-white font-extrabold text-sm shadow-lg shadow-rose-600/25 flex items-center justify-center gap-2">
            📷 Kamerayı Aç ve Salon QR Afişini Okut
        </button>

        <!-- Veli Telefon Seçimi / Girişi (Tarayıcıda Hatırlanır) -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-[11px] font-bold uppercase text-emerald-700">1. Adım: Veli Telefon Numaranız</span>
                    <h2 class="text-sm font-extrabold">Kayıtlı Sporcularınızı Görüntüleyin</h2>
                </div>
                <span class="text-xl">📲</span>
            </div>

            <form method="GET" action="{{ route('public.qr.checkin') }}" class="space-y-2.5" id="phone-lookup-form">
                <div class="flex gap-2">
                    <input type="tel" name="phone" id="parent-phone-input" value="{{ $phone }}" placeholder="05XX XXX XX XX" class="flex-1 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm font-bold">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-extrabold">
                        Sporcumu Bul
                    </button>
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <span class="text-[11px] text-slate-400">Hızlı Demo Veli Seç:</span>
                    <a href="{{ route('public.qr.checkin', ['phone' => '05332223344']) }}" class="text-[11px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">Murat Arslan (Elif & Kerem)</a>
                    <a href="{{ route('public.qr.checkin', ['phone' => '05354445566']) }}" class="text-[11px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">Zeynep Çelik (Defne)</a>
                </div>
            </form>
        </div>

        <!-- Sporcu Kartları ve Tek Dokunuşla QR Yoklama Butonu -->
        <div class="space-y-3">
            @forelse($students as $st)
                @php
                    $todayAtt = $st->attendances->first();
                @endphp
                <div class="bg-white rounded-2xl border-2 {{ $todayAtt ? 'border-emerald-500' : 'border-indigo-200' }} p-4 shadow-sm space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[11px] font-bold text-indigo-600">{{ $st->branch?->name }} · {{ $st->group?->name }}</span>
                            <h3 class="text-lg font-extrabold text-slate-900">🤸 {{ $st->full_name }}</h3>
                            <p class="text-xs text-slate-500">Antrenör: {{ $st->group?->coach?->name ?? '-' }} · Telafi Hakkı: <strong>{{ $st->makeup_credits }} Ders</strong></p>
                        </div>
                        @if($todayAtt)
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold">
                                ✅ Bugün Geldi ({{ $todayAtt->updated_at->format('H:i') }})
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-extrabold">
                                ⏳ Yoklama Bekliyor
                            </span>
                        @endif
                    </div>

                    @if($todayAtt)
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-semibold">
                            ✔ {{ $st->full_name }} bugün saat {{ $todayAtt->updated_at->format('H:i') }} itibarıyla derse giriş yaptı olarak kaydedildi.
                        </div>
                    @else
                        <form action="{{ route('public.qr.checkin.submit') }}" method="POST" class="qr-checkin-form grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $st->id }}">
                            <button type="submit" name="checkin_type" value="present" class="w-full py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/25">
                                ✅ Salona Geldi (Yoklama Yap)
                            </button>
                            @if($st->makeup_credits > 0)
                                <button type="submit" name="checkin_type" value="makeup" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs">
                                    🔄 Telafi Dersi Olarak Giriş Yap ({{ $st->makeup_credits }} Hak)
                                </button>
                            @endif
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-6 rounded-2xl bg-white border border-slate-200 text-center text-xs text-slate-500">
                    Lütfen yukarıya salonda kayıtlı cep telefonu numaranızı yazıp <strong>"Sporcumu Bul"</strong> butonuna dokunun.
                </div>
            @endforelse
        </div>

        <!-- Bugün QR ile Salona Giriş Yapan Son Sporcular -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2.5">🟢 Bugün Yoklaması Alınan Sporcular</h3>
            <div class="space-y-1.5">
                @forelse($todayCheckins as $chk)
                    <div class="flex justify-between items-center text-xs py-1.5 px-2.5 rounded-lg bg-slate-50">
                        <span class="font-bold">{{ $chk->student?->full_name }} <span class="text-slate-400 font-normal">({{ $chk->group?->name }})</span></span>
                        <span class="text-emerald-700 font-bold">✅ {{ $chk->updated_at->format('H:i') }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Bugün henüz giriş kaydı yok.</p>
                @endforelse
            </div>
        </div>

    </main>

    <!-- Kamera ile QR Okutma Tam Ekran Katmanı -->
    <div id="qrScanOverlay" class="fixed inset-0 z-[60] bg-black hidden flex-col">
        <div class="flex items-center justify-between p-3 bg-black/70">
            <span class="text-white text-xs font-extrabold">📷 Salon Giriş QR Kodunu Okutun</span>
            <button type="button" id="qrScanClose" class="text-white text-xl font-extrabold px-3 leading-none">✕</button>
        </div>
        <div class="relative flex-1 flex items-center justify-center overflow-hidden bg-black">
            <video id="qrScanVideo" class="absolute inset-0 w-full h-full object-cover" playsinline muted autoplay></video>
            <div class="relative w-56 h-56 border-4 border-emerald-400 rounded-2xl" style="box-shadow:0 0 0 2000px rgba(0,0,0,0.5)"></div>
        </div>
        <div id="qrScanStatus" class="p-4 bg-black/85 text-center text-emerald-300 text-xs font-bold min-h-[3.5rem] leading-relaxed">
            Kamera açılıyor, lütfen izin verin...
        </div>
    </div>
    <canvas id="qrScanCanvas" class="hidden"></canvas>

    <!-- Alt Mobil Bar -->
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-40 shadow-lg">
        <div class="max-w-lg mx-auto grid grid-cols-3 text-center py-2">
            <a href="{{ route('public.mobile.pwa', ['rol' => 'veli', 'phone' => $phone]) }}" class="flex flex-col items-center gap-0.5 text-slate-500 font-semibold">
                <span class="text-lg">👨‍👩‍👧</span>
                <span class="text-[11px]">Veli Ekranı</span>
            </a>
            <a href="{{ route('public.qr.checkin', ['phone' => $phone]) }}" class="flex flex-col items-center gap-0.5 text-emerald-600 font-extrabold">
                <span class="text-lg">📷</span>
                <span class="text-[11px]">QR Yoklama</span>
            </a>
            <a href="{{ route('public.mobile.pwa', ['rol' => 'antrenor']) }}" class="flex flex-col items-center gap-0.5 text-slate-500 font-semibold">
                <span class="text-lg">🎽</span>
                <span class="text-[11px]">Antrenör</span>
            </a>
        </div>
    </nav>

    <script>
        // Veli telefon numarasını tarayıcı hafızasında tut, her gelişte tekrar yazmasın!
        const input = document.getElementById('parent-phone-input');
        if (input && input.value) {
            localStorage.setItem('enerjik_parent_phone', input.value);
        }
    </script>

    <!-- Kamera ile Canlı QR Okuma (jsQR) -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
    (function () {
        const openBtn = document.getElementById('qrScanBtn');
        const overlay = document.getElementById('qrScanOverlay');
        const closeBtn = document.getElementById('qrScanClose');
        const video = document.getElementById('qrScanVideo');
        const canvas = document.getElementById('qrScanCanvas');
        const statusEl = document.getElementById('qrScanStatus');

        // Salon QR afişinin (qr-afis) ürettiği linkte bulunması gereken doğrulama işareti.
        const EXPECTED_PATH = '/qr-yoklama';
        const EXPECTED_MARKER = 'kaynak=afis';

        let mediaStream = null;
        let scanning = false;
        let rafId = null;

        async function openScanner() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                statusEl.textContent = '⚠️ Bu tarayıcı kamera erişimini desteklemiyor. Lütfen Chrome veya Safari kullanın.';
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                return;
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            statusEl.textContent = 'Kamera açılıyor, lütfen izin verin...';

            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = mediaStream;
                await video.play();
                scanning = true;
                statusEl.textContent = 'Salon girişindeki QR afişini çerçeve içine getirin.';
                rafId = requestAnimationFrame(tick);
            } catch (err) {
                statusEl.textContent = '⚠️ Kameraya erişilemedi. Tarayıcı izinlerini ve HTTPS bağlantısını kontrol edin.';
            }
        }

        function closeScanner() {
            scanning = false;
            if (rafId) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }
            if (mediaStream) {
                mediaStream.getTracks().forEach(function (t) { t.stop(); });
                mediaStream = null;
            }
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }

        function tick() {
            if (!scanning) return;

            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = window.jsQR ? window.jsQR(imageData.data, imageData.width, imageData.height) : null;

                if (code && code.data) {
                    handleScan(code.data);
                    return;
                }
            }

            rafId = requestAnimationFrame(tick);
        }

        function handleScan(text) {
            const isValid = text.indexOf(EXPECTED_PATH) !== -1 && text.indexOf(EXPECTED_MARKER) !== -1;

            if (!isValid) {
                statusEl.textContent = '⚠️ Bu QR kodu salon girişi için geçerli değil. Lütfen salondaki orijinal afişi okutun.';
                rafId = requestAnimationFrame(tick);
                return;
            }

            statusEl.textContent = '✅ Salon QR kodu doğrulandı! Yoklamanız işleniyor...';
            scanning = false;
            if (mediaStream) {
                mediaStream.getTracks().forEach(function (t) { t.stop(); });
                mediaStream = null;
            }
            submitPendingCheckins();
        }

        async function submitPendingCheckins() {
            const forms = document.querySelectorAll('form.qr-checkin-form');
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.content : '';

            if (forms.length === 0) {
                statusEl.textContent = '✅ Salon QR kodu doğrulandı! Lütfen telefon numaranızı girip sporcunuzu bulun.';
                setTimeout(closeScanner, 2200);
                return;
            }

            for (const form of forms) {
                const data = new FormData(form);
                data.set('checkin_type', 'present');
                try {
                    await fetch(form.action, {
                        method: 'POST',
                        body: data,
                        headers: { 'X-CSRF-TOKEN': token },
                        credentials: 'same-origin',
                    });
                } catch (e) {
                    // Sessizce yut; sayfa yenilendiğinde gerçek durum zaten görünecek.
                }
            }

            statusEl.textContent = '🎉 Yoklamanız alındı! Sayfa yenileniyor...';
            setTimeout(function () { window.location.reload(); }, 1200);
        }

        if (openBtn) openBtn.addEventListener('click', openScanner);
        if (closeBtn) closeBtn.addEventListener('click', closeScanner);

        // Veli ekranından "?kamera=1" ile gelindiyse kamerayı otomatik aç.
        if (new URLSearchParams(window.location.search).get('kamera') === '1') {
            openScanner();
        }
    })();
    </script>
</body>
</html>
