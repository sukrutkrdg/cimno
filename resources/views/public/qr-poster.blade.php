<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Giriş QR Yoklama & Mobil Kısayol Afişi | Enerjik Cimnastik</title>
    @php
        $brandPalette = \App\Support\ColorPalette::fromHex($settings['brand_color'] ?? null);
        $accentPalette = \App\Support\ColorPalette::fromHex($settings['brand_color'] ?? null, 0.6);
    @endphp
    <!-- Sayfa boyanmadan önce tema tercihini uygula: beyaz ekranın karanlık temaya "çakması" önlenir. -->
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 p-6 print:bg-white transition-colors">
    <div class="max-w-4xl mx-auto print:hidden flex justify-end mb-3">
        <button onclick="enerjikToggleTheme()" class="px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold shadow-sm">
            <span class="dark:hidden">🌙 Karanlık Tema</span><span class="hidden dark:inline">☀️ Aydınlık Tema</span>
        </button>
    </div>
    <div class="max-w-4xl mx-auto bg-white dark:bg-slate-900 print:bg-white rounded-3xl border-2 border-purple-700 p-8 shadow-xl text-center space-y-5">
        <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-24 h-24 rounded-full object-cover mx-auto border-4 border-purple-600 shadow-lg">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-purple-100 dark:bg-purple-950 print:bg-purple-100 text-purple-900 dark:text-purple-300 print:text-purple-900 text-xs font-extrabold">
            🟣 {{ $settings['club_name'] ?? 'Tekirdağ Enerjik Cimnastik Spor Kulübü' }}
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white print:text-slate-900 leading-tight">
            Salon Giriş QR Yoklama & Mobil Uygulama Kısayolu
        </h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 print:text-slate-600 max-w-2xl mx-auto">
            Bu afişi salon girişine veya danışma bankosuna asabilirsiniz. Veliler telefon kamerasıyla QR kodu okutarak hem <strong>günlük ders yoklamasını</strong> yapabilir hem de <strong>mobil kısayolu</strong> telefon ekranlarına ekleyebilirler.
        </p>

        <!-- İki Yönlü QR Kod Kartları -->
        <div class="grid md:grid-cols-2 gap-6 pt-2">
            <!-- 1. Salon Giriş QR Yoklama -->
            <div class="p-6 rounded-3xl border-2 border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/30 print:bg-emerald-50/40 space-y-3">
                <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-extrabold uppercase">Her Ders Girişinde Okutun</span>
                <h2 class="text-xl font-extrabold text-emerald-950 dark:text-emerald-300 print:text-emerald-950">📷 1. Salon Giriş QR Yoklama</h2>
                <p class="text-xs text-slate-600 dark:text-slate-400 print:text-slate-600">Salona geldiğinizde kameranızla okutun ve tek dokunuşla <strong>"✅ Salona Geldi"</strong> yoklamasını onaylayın.</p>
                <div class="inline-block p-4 bg-white print:bg-white rounded-2xl border border-emerald-200 shadow-sm">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode(url('/qr-yoklama?kaynak=afis')) }}" alt="QR Yoklama" class="w-48 h-48 mx-auto">
                    <span class="block text-[11px] font-mono font-bold text-emerald-700 mt-2">{{ url('/qr-yoklama') }}</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 print:text-slate-500">📱 Veli ekranındaki <strong>"Kamerayı Aç ve Salon QR Afişini Okut"</strong> butonuyla bu afişi doğrudan uygulama içinden okutabilirsiniz.</p>
                <div>
                    <a href="{{ route('public.qr.checkin', ['kamera' => 1]) }}" class="inline-block px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold print:hidden">
                        📷 QR Yoklama Ekranını Aç →
                    </a>
                </div>
            </div>

            <!-- 2. Mobil Uygulama Ana Ekran Kısayolu -->
            <div class="p-6 rounded-3xl border-2 border-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30 print:bg-indigo-50/40 space-y-3">
                <span class="px-3 py-1 rounded-full bg-indigo-600 text-white text-xs font-extrabold uppercase">Telefonunuza 1 Kez Ekleyin</span>
                <h2 class="text-xl font-extrabold text-indigo-950 dark:text-indigo-300 print:text-indigo-950">📲 2. Mobil Uygulama Kısayolu</h2>
                <p class="text-xs text-slate-600 dark:text-slate-400 print:text-slate-600">Ders programı, aidat makbuzları ve gelişim karnesi için okutup <strong>"Ana Ekrana Ekle"</strong> deyin.</p>
                <div class="inline-block p-4 bg-white print:bg-white rounded-2xl border border-indigo-200 shadow-sm">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode(url('/mobil')) }}" alt="Mobil Uygulama QR" class="w-48 h-48 mx-auto">
                    <span class="block text-[11px] font-mono font-bold text-indigo-700 mt-2">{{ url('/mobil') }}</span>
                </div>
                <div>
                    <a href="{{ route('public.mobile.pwa') }}" class="inline-block px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-extrabold print:hidden">
                        📱 Mobil Uygulamayı Aç →
                    </a>
                </div>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4 text-left text-xs pt-2">
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 print:bg-slate-50 border border-slate-200 dark:border-slate-700 space-y-1">
                <div class="font-extrabold text-sm text-slate-900 dark:text-white print:text-slate-900">🍎 iPhone (iOS Safari) Kısayol Rehberi</div>
                <p>1. QR kodu kamerayla açın.</p>
                <p>2. Alttaki <strong>Paylaş (⬆️)</strong> tuşuna basın ve <strong>"Ana Ekrana Ekle"</strong> seçeneğine dokunun.</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 print:bg-slate-50 border border-slate-200 dark:border-slate-700 space-y-1">
                <div class="font-extrabold text-sm text-slate-900 dark:text-white print:text-slate-900">🤖 Android (Chrome) Kısayol Rehberi</div>
                <p>1. QR kodu kamerayla açın.</p>
                <p>2. Üstteki <strong>"Ana Ekrana Ekle"</strong> butonuna veya sağ üst <strong>(⋮) -> Uygulamayı Yükle</strong>'ye dokunun.</p>
            </div>
        </div>

        <div class="pt-2 flex justify-center gap-3 print:hidden">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-extrabold shadow">
                🖨️ Salon Girişi İçin Afişi Yazdır
            </button>
            <a href="{{ route('admin.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-extrabold">
                ⚙️ Yönetim Paneline Dön
            </a>
        </div>
    </div>

    <script>
        function enerjikToggleTheme() {
            var html = document.documentElement;
            var isDark = html.classList.toggle('dark');
            try { localStorage.setItem('enerjik_theme', isDark ? 'dark' : 'light'); } catch (e) {}
        }
    </script>
</body>
</html>
