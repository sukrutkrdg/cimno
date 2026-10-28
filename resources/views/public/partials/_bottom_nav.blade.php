{{--
    Veli Mobil Uygulaması: Paylaşılan Alt Navigasyon Barı

    Hem /mobil (mobile-app.blade.php) hem de /qr-yoklama (qr-checkin.blade.php)
    sayfaları bu partial'ı @include ederek AYNI 5 sekmeyi aynı sırada, aynı
    ikonlarla ve aynı "aktif sekme" vurgusuyla gösterir. Daha önce bu iki sayfa
    birbirinden tamamen farklı (5 sütun vs 3 sütun, farklı ikon/etiket/sıra) alt
    menülere sahipti; bu da veli QR Yoklama'ya dokunduğunda ya da salon
    afişindeki QR'ı okuttuğunda uygulamanın "baştan başlamış" gibi hissettirmesine
    yol açıyordu. Bu dosya o iki kopyanın birleştirilmiş, tek doğru kaynağıdır —
    yeni bir sekme eklenecekse SADECE burası güncellenir.

    Beklenen değişken:
    - $activeTab: 'cocuklarim' | 'karne' | 'duyurular' | 'qr'
      ('qr' değeri yalnızca qr-checkin.blade.php'den gelir ve o an zaten
      /qr-yoklama sayfasında olunduğunu belirtir.)

    Not: "Aidat & Makbuz" sekmesi salon sahibinin isteğiyle kaldırıldı — veli
    ekranında artık borç/ödeme durumu ve makbuz hiç gösterilmiyor.
--}}
@php
    $isOnQrPage = $activeTab === 'qr';
@endphp
<nav class="fixed bottom-0 inset-x-0 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 z-40 shadow-lg">
    <div class="max-w-lg mx-auto grid grid-cols-4 text-center py-2">
        <a href="{{ route('public.mobile.pwa', ['sekme' => 'cocuklarim']) }}" class="flex flex-col items-center gap-0.5 {{ $activeTab === 'cocuklarim' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
            <span class="text-lg">🤸</span>
            <span class="text-[11px]">Öğrencim</span>
        </a>

        @if($isOnQrPage)
            {{-- Zaten /qr-yoklama sayfasındayız: yeniden sayfaya gitmek yerine kamera katmanını yerinde aç. --}}
            <button type="button" onclick="document.getElementById('qrScanBtn')?.click()" class="flex flex-col items-center gap-0.5 text-emerald-600 font-extrabold">
                <span class="text-lg">📷</span>
                <span class="text-[11px]">QR Yoklama</span>
            </button>
        @else
            <a href="{{ route('public.qr.checkin', ['kamera' => 1]) }}" class="flex flex-col items-center gap-0.5 text-emerald-600 font-extrabold">
                <span class="text-lg">📷</span>
                <span class="text-[11px]">QR Yoklama</span>
            </a>
        @endif

        <a href="{{ route('public.mobile.pwa', ['sekme' => 'karne']) }}" class="flex flex-col items-center gap-0.5 {{ $activeTab === 'karne' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
            <span class="text-lg">🏅</span>
            <span class="text-[11px]">Gelişim Karnesi</span>
        </a>
        <a href="{{ route('public.mobile.pwa', ['sekme' => 'duyurular']) }}" class="flex flex-col items-center gap-0.5 {{ $activeTab === 'duyurular' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
            <span class="text-lg">📢</span>
            <span class="text-[11px]">Duyurular</span>
        </a>
    </div>
</nav>
