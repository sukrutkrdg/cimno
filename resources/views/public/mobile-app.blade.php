<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 pb-24">

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
                <button onclick="triggerPwaInstall()" class="px-2.5 py-1.5 rounded-xl bg-amber-400 text-slate-950 font-extrabold text-[11px] shadow">
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
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-lg space-y-5 mt-2">
                <div class="text-center space-y-2">
                    <img src="/icons/logo.jpg" alt="Logo" class="w-20 h-20 rounded-full object-cover mx-auto border-4 border-purple-600 shadow-md">
                    <span class="inline-block text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-purple-100 text-purple-800">
                        Öğrenciye Bağlı Veli Sistemi
                    </span>
                    <h2 class="text-xl font-extrabold text-slate-900">👨‍👩‍👧 Veli Girişi</h2>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Çocuğunuzun kulübümüze kesin kaydı yapıldığında <strong>cep telefonu numaranız</strong> otomatik olarak öğrencinize tanımlanır. Kayıtlı telefon numaranızı girerek öğrencinizin ders programını, yoklamasını, aidat makbuzlarını ve gelişim karnesini görüntüleyebilirsiniz.
                    </p>
                </div>

                @if($loginError)
                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
                        ⚠️ {{ $loginError }}
                    </div>
                @endif

                @if(session('loginSuccess'))
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold animate-pulse">
                        {{ session('loginSuccess') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('public.mobile.pwa') }}" class="space-y-3">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-700 mb-1">Salonda Kayıtlı Cep Telefonu Numaranız</label>
                        <input type="tel" name="phone" required placeholder="05XX XXX XX XX" class="w-full rounded-2xl border-2 border-slate-300 focus:border-indigo-600 focus:outline-none px-4 py-3 text-base font-extrabold tracking-wide">
                    </div>
                    <button type="submit" class="w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm shadow-lg shadow-indigo-600/25 transition">
                        🔓 Giriş Yap ve Öğrencimi Göster
                    </button>
                </form>

                <!-- Telefon Ana Ekranına Kısayol Ekleme Kutusu -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100 flex items-center justify-between gap-3">
                    <div class="text-xs">
                        <strong class="block text-indigo-950 font-extrabold">📲 Telefon Ekranına Kısayol Atın</strong>
                        <span class="text-slate-600 text-[11px]">Uygulama gibi tek dokunuşla açmak için ana ekranınıza ekleyin.</span>
                    </div>
                    <button onclick="triggerPwaInstall()" class="shrink-0 px-3 py-2 rounded-xl bg-indigo-600 text-white font-extrabold text-xs">
                        Kısayol Ekle
                    </button>
                </div>

                <!-- Demo / Test İçin Örnek Veli Numaraları -->
                <div class="pt-3 border-t border-slate-200 space-y-2">
                    <span class="block text-[11px] font-extrabold uppercase text-slate-400">Hızlı Test İçin Örnek Kayıtlı Veliler:</span>
                    <div class="grid grid-cols-1 gap-2">
                        <a href="{{ route('public.mobile.pwa', ['phone' => '05332223344']) }}" class="p-3 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 flex justify-between items-center text-xs">
                            <div>
                                <strong class="block text-slate-900">👨‍👩‍👧‍👦 Veli: Murat Arslan (0533 222 33 44)</strong>
                                <span class="text-[11px] text-slate-500">2 Kardeş Sporcu: Elif Arslan & Kerem Arslan</span>
                            </div>
                            <span class="text-indigo-600 font-extrabold">Giriş →</span>
                        </a>
                        <a href="{{ route('public.mobile.pwa', ['phone' => '05354445566']) }}" class="p-3 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 flex justify-between items-center text-xs">
                            <div>
                                <strong class="block text-slate-900">👩 Veli: Zeynep Çelik (0535 444 55 66)</strong>
                                <span class="text-[11px] text-slate-500">Sporcu: Defne Çelik (Ritmik Cimnastik)</span>
                            </div>
                            <span class="text-indigo-600 font-extrabold">Giriş →</span>
                        </a>
                    </div>
                </div>
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
                <a href="{{ route('public.qr.checkin', ['phone' => $activePhone]) }}" class="shrink-0 px-3.5 py-2.5 rounded-xl bg-white text-emerald-900 font-extrabold text-xs shadow">
                    ✅ Yoklama Yap
                </a>
            </div>

            <!-- Veliye Bağlı Öğrenciler (Kardeşse Alt Alta veya Sekmeli Görünür) -->
            @foreach($students as $st)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
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
                            <!-- Ders Saatleri -->
                            <div>
                                <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2">📅 Haftalık Antrenman Saatleri</h3>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($st->group?->schedules ?? [] as $sch)
                                        <span class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-900 font-bold text-xs">
                                            🗓️ {{ $sch->day_of_week }} {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }} ({{ $sch->area }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Son Yoklamalar -->
                            <div>
                                <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2">✅ Son Yoklama Kayıtları</h3>
                                <div class="grid grid-cols-2 gap-1.5">
                                    @forelse($st->attendances->take(4) as $att)
                                        <div class="p-2 rounded-xl bg-slate-50 border text-[11px] flex justify-between items-center">
                                            <span>{{ $att->attendance_date->format('d.m.Y') }}</span>
                                            <span class="font-bold {{ $att->status === 'present' ? 'text-emerald-600' : 'text-rose-600' }}">
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
                                                    <a href="{{ route('public.receipt', $pay->id) }}" class="px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-bold">Makbuz PDF</a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($sekme === 'cocuklarim' || $sekme === 'karne')
                            <!-- Gelişim Karnesi (Gamification) -->
                            @forelse($st->developmentRecords as $dev)
                                <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-yellow-50 border border-amber-200 shadow-sm">
                                    <div class="flex justify-between items-center mb-3">
                                        <div class="font-extrabold text-amber-900 text-sm">🏅 {{ $dev->term_label }}</div>
                                        <span class="px-2 py-0.5 bg-amber-200 text-amber-800 text-[10px] font-extrabold rounded-full uppercase tracking-wider">{{ $dev->current_level }}</span>
                                    </div>

                                    <!-- İlerleme Çubukları -->
                                    <div class="space-y-2 mb-4">
                                        @php
                                            $scores = [
                                                ['label' => 'Esneklik', 'val' => $dev->flexibility_score, 'color' => 'bg-pink-500'],
                                                ['label' => 'Kuvvet', 'val' => $dev->strength_score, 'color' => 'bg-orange-500'],
                                                ['label' => 'Denge', 'val' => $dev->balance_score, 'color' => 'bg-blue-500'],
                                                ['label' => 'Disiplin', 'val' => $dev->discipline_score, 'color' => 'bg-emerald-500'],
                                            ];
                                        @endphp
                                        @foreach($scores as $score)
                                            <div>
                                                <div class="flex justify-between text-[10px] font-bold text-slate-600 mb-0.5">
                                                    <span>{{ $score['label'] }}</span>
                                                    <span>{{ $score['val'] }}%</span>
                                                </div>
                                                <div class="w-full bg-slate-200 rounded-full h-1.5">
                                                    <div class="{{ $score['color'] }} h-1.5 rounded-full" style="width: {{ $score['val'] }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Beceriler (Rozetler) -->
                                    @if(is_array($dev->skills) && count($dev->skills) > 0)
                                        <div class="mb-3">
                                            <div class="text-[10px] font-extrabold text-slate-500 uppercase mb-1.5">Kazanılan Beceriler</div>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($dev->skills as $skillName => $skillStatus)
                                                    @php
                                                        $badgeClass = 'bg-slate-100 text-slate-600';
                                                        $icon = '⚪';
                                                        if(stripos($skillStatus, 'başarılı') !== false || stripos($skillStatus, 'iyi') !== false) {
                                                            $badgeClass = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                                                            $icon = '⭐';
                                                        } elseif(stripos($skillStatus, 'gelişiyor') !== false || stripos($skillStatus, 'orta') !== false) {
                                                            $badgeClass = 'bg-blue-100 text-blue-800 border border-blue-200';
                                                            $icon = '🚀';
                                                        }
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-[10px] font-bold {{ $badgeClass }}">
                                                        <span>{{ $icon }}</span> {{ $skillName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($dev->coach_comment)
                                        <div class="mt-2 p-2.5 bg-white/60 rounded-lg text-xs italic text-slate-700 border border-amber-100 relative">
                                            <span class="absolute -top-2 -left-1 text-xl opacity-20">"</span>
                                            {{ $dev->coach_comment }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center p-3 rounded-xl border border-dashed border-slate-300 text-xs text-slate-500">
                                    Henüz girilmiş gelişim karnesi bulunmuyor.
                                </div>
                            @endforelse
                        @endif

                        @if($sekme === 'cocuklarim' || $sekme === 'telafi')
                            <!-- Telafi Hakkı ve Self-Service Rezervasyon -->
                            @if($st->makeup_credits > 0)
                                <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-sm font-extrabold text-indigo-900">🔄 Telafi Ders Hakkınız Var!</div>
                                        <span class="bg-indigo-600 text-white px-2.5 py-1 rounded-full text-xs font-bold">{{ $st->makeup_credits }} Ders</span>
                                    </div>
                                    <p class="text-xs text-indigo-700 mb-3">Çocuğunuzun devamsızlık yaptığı dersler için telafi hakkınız bulunmaktadır. Lütfen aşağıdan telafi yapmak istediğiniz uygun sınıfı seçerek randevunuzu onaylayın.</p>

                                    <form action="{{ route('public.mobile.makeup.reserve') }}" method="POST" class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $st->id }}">

                                        <div>
                                            <label class="block text-[11px] font-extrabold text-indigo-900 mb-1">Uygun Sınıflar (Kapasite Müsait)</label>
                                            <select name="group_id" required class="w-full rounded-xl border border-indigo-300 px-3 py-2 text-xs font-bold text-slate-700 bg-white">
                                                <option value="" disabled selected>Lütfen Sınıf Seçin</option>
                                                @foreach($availableMakeupGroups as $mGroup)
                                                    @php
                                                        // Grupların ders programını basit bir metne çeviriyoruz
                                                        $days = $mGroup->schedules->pluck('day_of_week')->join(', ');
                                                        $time = $mGroup->schedules->first() ? substr($mGroup->schedules->first()->start_time, 0, 5) : '';
                                                    @endphp
                                                    <option value="{{ $mGroup->id }}">{{ $mGroup->name }} ({{ $days }} - {{ $time }}) - Antrenör: {{ $mGroup->coach?->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-extrabold text-indigo-900 mb-1">Tarih</label>
                                            <input type="date" name="attendance_date" required min="{{ today()->toDateString() }}" value="{{ today()->addDays(1)->toDateString() }}" class="w-full rounded-xl border border-indigo-300 px-3 py-2 text-xs font-bold text-slate-700 bg-white">
                                        </div>

                                        <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-extrabold shadow transition">
                                            📅 Telafi Randevusunu Onayla
                                        </button>
                                    </form>
                                </div>
                            @elseif($sekme === 'telafi')
                                <div class="text-center p-4 rounded-xl border border-dashed border-slate-300 text-xs text-slate-500">
                                    Şu anda kullanılabilir telafi ders hakkınız (devamsızlığınız) bulunmuyor.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Kulüp IBAN ve Hızlı WhatsApp -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-2">
                <div class="text-xs font-extrabold text-indigo-600">🏦 Havale / EFT İçin Kulüp IBAN Bilgisi</div>
                <div class="text-xs font-bold">{{ $settings['iban_holder'] ?? 'Enerjik Cimnastik Spor Kulübü' }}</div>
                <div class="text-xs font-mono bg-slate-100 p-2 rounded-xl select-all">{{ $settings['iban_number'] ?? '' }}</div>
                <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '905388993159' }}?text={{ urlencode('Merhaba, veli ekranından yazıyorum.') }}" target="_blank" class="block text-center w-full py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold">
                    💬 Salon WhatsApp Hattına Yaz
                </a>
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
            <p class="text-xs text-slate-600">Mağaza indirmesi gerekmeden uygulamamızı 5 saniyede telefon ekranınıza ekleyebilirsiniz:</p>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5 text-xs">
                <div class="font-extrabold text-indigo-900">🍎 iPhone / iPad (Safari) İçin:</div>
                <p>1. Ekranın altındaki <strong>Paylaş (Yukarı Oklu Kare ⬆️)</strong> simgesine dokunun.</p>
                <p>2. Menüden <strong>"Ana Ekrana Ekle"</strong> seçeneğine basıp sağ üstten <strong>Ekle</strong> deyin.</p>
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

    <!-- Alt Mobil Uygulama Navigasyon Barı (Yalnız Veli Menüsü) -->
    @if($activePhone && $students->isNotEmpty())
        <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-40 shadow-lg">
            <div class="max-w-lg mx-auto grid grid-cols-4 text-center py-2">
                <a href="{{ route('public.mobile.pwa', ['phone' => $activePhone, 'sekme' => 'cocuklarim']) }}" class="flex flex-col items-center gap-0.5 {{ $sekme === 'cocuklarim' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
                    <span class="text-lg">🤸</span>
                    <span class="text-[11px]">Öğrencim</span>
                </a>
                <a href="{{ route('public.qr.checkin', ['phone' => $activePhone]) }}" class="flex flex-col items-center gap-0.5 text-emerald-600 font-extrabold">
                    <span class="text-lg">📷</span>
                    <span class="text-[11px]">QR Yoklama</span>
                </a>
                <a href="{{ route('public.mobile.pwa', ['phone' => $activePhone, 'sekme' => 'aidat']) }}" class="flex flex-col items-center gap-0.5 {{ $sekme === 'aidat' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
                    <span class="text-lg">💳</span>
                    <span class="text-[11px]">Aidat & Makbuz</span>
                </a>
                <a href="{{ route('public.mobile.pwa', ['phone' => $activePhone, 'sekme' => 'karne']) }}" class="flex flex-col items-center gap-0.5 {{ $sekme === 'karne' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
                    <span class="text-lg">🏅</span>
                    <span class="text-[11px]">Gelişim Karnesi</span>
                </a>
                <a href="{{ route('public.mobile.pwa', ['phone' => $activePhone, 'sekme' => 'telafi']) }}" class="flex flex-col items-center gap-0.5 {{ $sekme === 'telafi' ? 'text-indigo-600 font-extrabold' : 'text-slate-500 font-semibold' }}">
                    <span class="text-lg">🔄</span>
                    <span class="text-[11px]">Telafi Randevu</span>
                </a>
            </div>
        </nav>
    @endif

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

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

        // Basit Web Push İzin Alma Simülasyonu
        if ('Notification' in window && 'serviceWorker' in navigator) {
            document.addEventListener('DOMContentLoaded', function() {
                if (Notification.permission === 'default') {
                    setTimeout(() => {
                        Notification.requestPermission().then(function(permission) {
                            if (permission === 'granted') {
                                console.log('Bildirim izni verildi. PushSubscription işlemi başlatılabilir.');
                            }
                        });
                    }, 4000);
                }
            });
        }
    </script>
</body>
</html>
