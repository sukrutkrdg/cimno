<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salon Giriş QR Yoklama & Mobil Kısayol Afişi | Enerjik Cimnastik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Veli/ziyaretçi teması: tek sıcak marka rengi (bordo-eflatun), Mindbody'nin
        // "tek güçlü renk" refleksinden esinlenildi. Durum renkleri (emerald/amber/
        // rose) değiştirilmedi — sadece "indigo/purple" marka rengi güncellendi.
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#fdf2f6', 100: '#fce7ee', 200: '#fbd0dd', 300: '#f4a3c0',
                            400: '#e8689a', 500: '#c23572', 600: '#9d174d', 700: '#831440',
                            800: '#6b1035', 900: '#4a0a24', 950: '#2e0616',
                        },
                        purple: {
                            50: '#fdf2f6', 100: '#fbe4ec', 200: '#f4c2d4', 300: '#e592ae',
                            400: '#c85c82', 500: '#a13a5c', 600: '#7d2945', 700: '#5f1f35',
                            800: '#451726', 900: '#2e0f19', 950: '#1a0810',
                        },
                    },
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 p-6">
    <div class="max-w-4xl mx-auto bg-white rounded-3xl border-2 border-purple-700 p-8 shadow-xl text-center space-y-5">
        <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-24 h-24 rounded-full object-cover mx-auto border-4 border-purple-600 shadow-lg">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-purple-100 text-purple-900 text-xs font-extrabold">
            🟣 {{ $settings['club_name'] ?? 'Tekirdağ Enerjik Cimnastik Spor Kulübü' }}
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900 leading-tight">
            Salon Giriş QR Yoklama & Mobil Uygulama Kısayolu
        </h1>
        <p class="text-sm text-slate-600 max-w-2xl mx-auto">
            Bu afişi salon girişine veya danışma bankosuna asabilirsiniz. Veliler telefon kamerasıyla QR kodu okutarak hem <strong>günlük ders yoklamasını</strong> yapabilir hem de <strong>mobil kısayolu</strong> telefon ekranlarına ekleyebilirler.
        </p>

        <!-- İki Yönlü QR Kod Kartları -->
        <div class="grid md:grid-cols-2 gap-6 pt-2">
            <!-- 1. Salon Giriş QR Yoklama -->
            <div class="p-6 rounded-3xl border-2 border-emerald-500 bg-emerald-50/40 space-y-3">
                <span class="px-3 py-1 rounded-full bg-emerald-600 text-white text-xs font-extrabold uppercase">Her Ders Girişinde Okutun</span>
                <h2 class="text-xl font-extrabold text-emerald-950">📷 1. Salon Giriş QR Yoklama</h2>
                <p class="text-xs text-slate-600">Salona geldiğinizde kameranızla okutun ve tek dokunuşla <strong>"✅ Salona Geldi"</strong> yoklamasını onaylayın.</p>
                <div class="inline-block p-4 bg-white rounded-2xl border border-emerald-200 shadow-sm">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode(url('/qr-yoklama?kaynak=afis')) }}" alt="QR Yoklama" class="w-48 h-48 mx-auto">
                    <span class="block text-[11px] font-mono font-bold text-emerald-700 mt-2">{{ url('/qr-yoklama') }}</span>
                </div>
                <p class="text-[11px] text-slate-500">📱 Veli ekranındaki <strong>"Kamerayı Aç ve Salon QR Afişini Okut"</strong> butonuyla bu afişi doğrudan uygulama içinden okutabilirsiniz.</p>
                <div>
                    <a href="{{ route('public.qr.checkin', ['kamera' => 1]) }}" class="inline-block px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold print:hidden">
                        📷 QR Yoklama Ekranını Aç →
                    </a>
                </div>
            </div>

            <!-- 2. Mobil Uygulama Ana Ekran Kısayolu -->
            <div class="p-6 rounded-3xl border-2 border-indigo-500 bg-indigo-50/40 space-y-3">
                <span class="px-3 py-1 rounded-full bg-indigo-600 text-white text-xs font-extrabold uppercase">Telefonunuza 1 Kez Ekleyin</span>
                <h2 class="text-xl font-extrabold text-indigo-950">📲 2. Mobil Uygulama Kısayolu</h2>
                <p class="text-xs text-slate-600">Ders programı, aidat makbuzları ve gelişim karnesi için okutup <strong>"Ana Ekrana Ekle"</strong> deyin.</p>
                <div class="inline-block p-4 bg-white rounded-2xl border border-indigo-200 shadow-sm">
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
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                <div class="font-extrabold text-sm text-slate-900">🍎 iPhone (iOS Safari) Kısayol Rehberi</div>
                <p>1. QR kodu kamerayla açın.</p>
                <p>2. Alttaki <strong>Paylaş (⬆️)</strong> tuşuna basın ve <strong>"Ana Ekrana Ekle"</strong> seçeneğine dokunun.</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                <div class="font-extrabold text-sm text-slate-900">🤖 Android (Chrome) Kısayol Rehberi</div>
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
</body>
</html>
