<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli (15 Modül) | Enerjik Cimnastik</title>
    <link rel="icon" type="image/jpeg" href="/icons/logo.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Yönetim paneli teması: tek sakin aksan (teal/lacivert). Durum renkleri
        // (emerald/amber/rose) değiştirilmedi — sadece "indigo/purple" marka
        // rengi, dünyadaki kulüp yazılımlarındaki (iClassPro, Gymdesk) sakin
        // paletle uyumlu hâle getirildi. 14 tab partial'ı (resources/views/admin/tabs)
        // bu view içine dahil edildiği için renk ayarını tek bir yerde tanımlamak yeterli.
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4',
                            400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e',
                            800: '#115e59', 900: '#134e4a', 950: '#042f2e',
                        },
                        purple: {
                            50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
                            400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155',
                            800: '#1e293b', 900: '#0f172a', 950: '#020617',
                        },
                    },
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen flex flex-col">

    <!-- Üst Yönetim Barı -->
    <header class="bg-slate-900 text-white px-4 py-3 flex flex-wrap justify-between items-center gap-3 sticky top-0 z-50 shadow">
        <div class="flex items-center gap-3">
            <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-10 h-10 rounded-full object-cover border-2 border-purple-400 shadow">
            <div>
                <h1 class="font-extrabold text-sm sm:text-base leading-none">Tekirdağ Enerjik Cimnastik · Yönetim Paneli</h1>
                <span class="text-[11px] text-slate-400">Tek Veritabanı · 15 Modül · WhatsApp Business Hazır Cevap Motoru</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Giriş Yapan Kullanıcı Rozeti -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="font-extrabold text-white">{{ $authUser->name }}</span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-purple-600/40 text-purple-200 border border-purple-500/40 font-bold">
                    {{ $authUser->roleLabel() }}
                </span>
            </div>

            @if($authUser->role === 'super_admin')
                <!-- Rol Görünüm Seçici (Sadece Süper Yönetici Diğer Rolleri Test Edebilir) -->
                <form method="GET" action="{{ route('admin.index') }}" class="flex items-center gap-1.5">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <span class="text-[11px] text-slate-400 hidden sm:inline">Görünüm:</span>
                    <select name="role_view" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-amber-300 text-xs rounded-lg px-2.5 py-1.5 font-bold">
                        <option value="super_admin" {{ $currentRole === 'super_admin' ? 'selected' : '' }}>👑 Süper Yönetici (Tam Yetki)</option>
                        <option value="secretary" {{ $currentRole === 'secretary' ? 'selected' : '' }}>🗂️ Sekreter Görünümü Testi</option>
                        <option value="coach" {{ $currentRole === 'coach' ? 'selected' : '' }}>🎽 Antrenör Görünümü Testi</option>
                    </select>
                </form>
            @endif

            @if(in_array('mesajlar', $allowedTabs, true) && $pendingWhatsappCount > 0)
                <a href="{{ route('admin.index', ['tab' => 'mesajlar', 'role_view' => $currentRole]) }}" class="flex items-center gap-1.5 text-xs font-extrabold px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400 animate-pulse">
                    💬 {{ $pendingWhatsappCount }} Bekleyen Mesaj
                </a>
            @endif

            <a href="{{ route('public.home') }}" target="_blank" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700">🌐 Veli Sitesi</a>
            <a href="{{ route('public.mobile.pwa') }}" target="_blank" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-indigo-700 hover:bg-indigo-600">📲 Mobil Uygulama</a>
            <a href="{{ route('public.qr.poster') }}" target="_blank" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400">🏁 QR Afişi</a>

            <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white shadow">
                    🔒 Çıkış
                </button>
            </form>
        </div>
    </header>

    <div class="flex-1 flex flex-col lg:flex-row">
        <!-- Sol Menü: 15 Modül (Yetkiye Göre Filtrelenir) -->
        <aside class="w-full lg:w-64 bg-white border-b lg:border-b-0 lg:border-r border-slate-200 p-3 shrink-0">
            <div class="flex items-center justify-between px-3 py-1.5">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Menü Modülleri ({{ count($allowedTabs) }})</span>
            </div>
            @php
                $navItems = [
                    ['id' => 'pano', 'icon' => '📊', 'label' => '1. Pano (Özet)'],
                    ['id' => 'adaylar', 'icon' => '🎯', 'label' => '2. Adaylar (Ön Kayıt)'],
                    ['id' => 'ogrenciler', 'icon' => '🤸', 'label' => '3. Öğrenciler & Kayıt'],
                    ['id' => 'gruplar', 'icon' => '🏅', 'label' => '4-5. Branş, Grup & Program'],
                    ['id' => 'yoklama', 'icon' => '✅', 'label' => '6. Tek Dokunuş Yoklama'],
                    ['id' => 'paketler', 'icon' => '🏷️', 'label' => '7. Paket & Toplu Fiyat'],
                    ['id' => 'aidat', 'icon' => '💳', 'label' => '8. Aidat & Tahsilat'],
                    ['id' => 'kasa', 'icon' => '🏦', 'label' => '9. Gelir-Gider & Kasa'],
                    ['id' => 'personel', 'icon' => '👥', 'label' => '10. Personel, Yetki & Hakediş'],
                    ['id' => 'mesajlar', 'icon' => '💬', 'label' => '11. WhatsApp Hazır Cevap'],
                    ['id' => 'etkinlikler', 'icon' => '🎪', 'label' => '12. Etkinlik & Yaz Okulu'],
                    ['id' => 'gelisim', 'icon' => '📈', 'label' => '13. Gelişim Karnesi'],
                    ['id' => 'raporlar', 'icon' => '📑', 'label' => '14. Raporlar & Excel'],
                    ['id' => 'site_icerigi', 'icon' => '🌐', 'label' => '15. Site İçeriği (CMS)'],
                    ['id' => 'ayarlar', 'icon' => '⚙️', 'label' => '16. Ayarlar, Yedek & KVKK'],
                ];
            @endphp
            <nav class="flex lg:flex-col gap-1 overflow-x-auto pb-2 lg:pb-0">
                @foreach($navItems as $item)
                    @if(in_array($item['id'], $allowedTabs, true))
                        <a href="{{ route('admin.index', ['tab' => $item['id'], 'role_view' => $currentRole]) }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition {{ $activeTab === $item['id'] ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">
                            <span>{{ $item['icon'] }}</span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>

        <!-- Ana İçerik Alanı -->
        <main class="flex-1 p-4 sm:p-6 space-y-6 max-w-7xl">

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-bold flex justify-between items-center">
                    <span>✅ {{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-sm font-bold">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-sm font-bold space-y-1">
                    <div>⚠️ Girdiğiniz bilgilerde hata var, kayıt güncellenmedi:</div>
                    <ul class="list-disc list-inside text-xs font-semibold">
                        @foreach($errors->all() as $hata)
                            <li>{{ $hata }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 1. PANO (DASHBOARD)                        -->
            <!-- ========================================== -->
            @if($activeTab === 'pano')
                @include("admin.tabs._pano")
            @endif

            <!-- ========================================== -->
            <!-- 2. ADAYLAR (ÖN KAYIT & DENEME DERSİ)       -->
            <!-- ========================================== -->
            @if($activeTab === 'adaylar')
                @include("admin.tabs._adaylar")
            @endif

            <!-- ========================================== -->
            <!-- 3. ÖĞRENCİLER & YENİ KAYIT                 -->
            <!-- ========================================== -->
            @if($activeTab === 'ogrenciler')
                @include("admin.tabs._ogrenciler")
            @endif

            <!-- ========================================== -->
            <!-- 4 & 5. BRANŞ, GRUP VE EĞİTİM PROGRAMI      -->
            <!-- ========================================== -->
            @if($activeTab === 'gruplar')
                @include("admin.tabs._gruplar")
            @endif

            <!-- ========================================== -->
            <!-- 6. TEK DOKUNUŞLA YOKLAMA                   -->
            <!-- ========================================== -->
            @if($activeTab === 'yoklama')
                @include("admin.tabs._yoklama")
            @endif

            <!-- ========================================== -->
            <!-- 7. PAKETLER, EĞİTİM PROGRAMI PAKETİ & İNDİRİMLER -->
            <!-- ========================================== -->
            @if($activeTab === 'paketler' && $currentRole === 'super_admin')
                @include("admin.tabs._paketler")
            @endif

            <!-- ========================================== -->
            <!-- 8. AİDAT, BORÇLANDIRMA VE TAHSİLAT         -->
            <!-- ========================================== -->
            @if($activeTab === 'aidat' && $currentRole === 'super_admin')
                @include("admin.tabs._aidat")
            @endif

            <!-- ========================================== -->
            <!-- 9. GELİR-GİDER, ÜRÜN SATIŞI VE KASA        -->
            <!-- ========================================== -->
            @if($activeTab === 'kasa' && $currentRole === 'super_admin')
                @include("admin.tabs._kasa")
            @endif

            <!-- ========================================== -->
            <!-- 10. PERSONEL, SEKRETER VE ANTRENÖR HAKEDİŞİ -->
            <!-- ========================================== -->
            @if($activeTab === 'personel' && $currentRole === 'super_admin')
                @include("admin.tabs._personel")
            @endif

            <!-- ========================================== -->
            <!-- 11. WHATSAPP BUSINESS HAZIR CEVAPLARI      -->
            <!-- ========================================== -->
            @if($activeTab === 'mesajlar')
                @include("admin.tabs._mesajlar")
            @endif

            <!-- ========================================== -->
            <!-- 12 & 13. ETKİNLİKLER VE GELİŞİM KARNESİ    -->
            <!-- ========================================== -->
            @if($activeTab === 'etkinlikler' || $activeTab === 'gelisim')
                @include("admin.tabs._etkinlikler_gelisim")
            @endif

            <!-- ========================================== -->
            <!-- 14. RAPORLAR VE EXCEL ÇIKTISI              -->
            <!-- ========================================== -->
            @if($activeTab === 'raporlar' && $currentRole === 'super_admin')
                @include("admin.tabs._raporlar")
            @endif

            <!-- ========================================== -->
            <!-- 15. TAM KAPSAMLI SİTE İÇERİĞİ (CMS STÜDYOSU) -->
            <!-- ========================================== -->
            @if($activeTab === 'site_icerigi')
                @include("admin.tabs._site_icerigi")
            @endif

            <!-- ========================================== -->
            <!-- 16. AYARLAR, YEDEK İNDİRME VE İŞLEM GÜNLÜĞÜ -->
            <!-- ========================================== -->
            @if($activeTab === 'ayarlar' && $currentRole === 'super_admin')
                @include("admin.tabs._ayarlar")
            @endif

        </main>
    </div>

</body>
</html>
