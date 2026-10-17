<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['seo_title'] ?? 'Enerjik Cimnastik Spor Kulübü' }}</title>
    <meta name="description" content="{{ $settings['hero_subtitle'] ?? '' }} - {{ $settings['city_district'] ?? '' }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E1B4B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Enerjik Cimnastik">
    <link rel="apple-touch-icon" href="/icons/logo.jpg">
    <link rel="icon" type="image/jpeg" href="/icons/logo.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <!-- Üst İletişim Barı (Sadece Ziyaretçi Bilgileri) -->
    <div class="bg-indigo-950 text-indigo-100 text-xs py-2 px-4 border-b border-indigo-900">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4 truncate">
                <span>📍 {{ $settings['city_district'] ?? 'Tekirdağ' }} · {{ $settings['address'] ?? 'Süleymanpaşa' }}</span>
                <span class="hidden sm:inline">☎️ {{ $settings['phone'] ?? '0538 899 31 59' }}</span>
            </div>
            <div class="flex items-center gap-4 font-semibold shrink-0">
                <a href="https://www.instagram.com/enerjik.cimnastik/" target="_blank" class="text-pink-400 hover:underline">📸 @enerjik.cimnastik</a>
                <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '905388993159' }}?text={{ urlencode('Merhaba, ücretsiz deneme dersi hakkında bilgi almak istiyorum.') }}" target="_blank" class="text-emerald-400 hover:underline">💬 WhatsApp İletişim</a>
            </div>
        </div>
    </div>

    <!-- Kurumsal Navigasyon (Masaüstünde Dengeli, Mobilde Açılır Menülü) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 h-20 flex justify-between items-center gap-4">
            <!-- Sol: Logo ve Kulüp Adı -->
            <a href="/" class="flex items-center gap-3 shrink-0">
                <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-12 h-12 rounded-full object-cover border-2 border-purple-600 shadow">
                <div>
                    <span class="font-extrabold text-base sm:text-lg tracking-tight text-slate-900 block leading-tight">Tekirdağ Enerjik Cimnastik</span>
                    <span class="text-[11px] text-purple-700 font-bold block">🟣 Enerjik ⚡️ Mutlu 🤩 Güçlü 💪 Çocuklar</span>
                </div>
            </a>

            <!-- Orta: Masaüstü Menü Linkleri -->
            <nav class="hidden xl:flex items-center gap-7 text-sm font-bold text-slate-700">
                <a href="#branslar" class="hover:text-indigo-600 transition">Branşlarımız</a>
                <a href="#program" class="hover:text-indigo-600 transition">Ders Programı</a>
                <a href="#ucretler" class="hover:text-indigo-600 transition">Paket & Ücretler</a>
                <a href="#antrenorler" class="hover:text-indigo-600 transition">Antrenörler</a>
                <a href="#galeri" class="hover:text-indigo-600 transition">Galeri</a>
                <a href="#sss" class="hover:text-indigo-600 transition">SSS</a>
            </nav>

            <!-- Sağ: Veli Girişi & Deneme Dersi Butonları -->
            <div class="hidden sm:flex items-center gap-2.5 shrink-0">
                <a href="{{ route('public.mobile.pwa') }}" class="inline-flex items-center gap-1.5 text-xs font-extrabold px-4 py-2.5 rounded-xl border-2 border-indigo-600 text-indigo-700 hover:bg-indigo-50 transition">
                    👨‍👩‍👧 Veli Girişi
                </a>
                <a href="#on-kayit" class="inline-flex items-center gap-1.5 text-xs font-extrabold px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20 transition">
                    ✨ Ücretsiz Deneme Dersi
                </a>
            </div>

            <!-- Mobil / Tablet Hamburger Menü Butonu -->
            <button onclick="document.getElementById('mobile-nav-drawer').classList.toggle('hidden')" class="xl:hidden p-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100" aria-label="Menüyü Aç">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Mobil / Tablet Açılır Menü -->
        <div id="mobile-nav-drawer" class="hidden xl:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-3 shadow-lg">
            <div class="grid grid-cols-2 gap-2 text-xs font-bold text-slate-700">
                <a href="#branslar" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50">🤸 Branşlarımız</a>
                <a href="#program" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50">📅 Ders Programı</a>
                <a href="#ucretler" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50">🏷️ Paket & Ücretler</a>
                <a href="#antrenorler" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50">🏅 Antrenörler</a>
                <a href="#galeri" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50">📸 Salon Galerisi</a>
                <a href="#sss" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50">❓ Sık Sorulanlar</a>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                <a href="{{ route('public.mobile.pwa') }}" class="text-center py-2.5 rounded-xl border-2 border-indigo-600 text-indigo-700 font-extrabold text-xs">
                    👨‍👩‍👧 Veli Girişi
                </a>
                <a href="#on-kayit" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="text-center py-2.5 rounded-xl bg-rose-600 text-white font-extrabold text-xs">
                    ✨ Deneme Dersi
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Bölümü -->
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 text-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 grid lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-800/80 border border-indigo-600/50 text-xs font-semibold text-amber-300">
                    <span>🏆 3-16 Yaş Çocuk & Yetişkin Cimnastik Okulu</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight tracking-tight">
                    {{ $settings['hero_title'] ?? 'Çocuğunuzun Enerjisini Esneklik, Özgüven ve Disipline Dönüştürüyoruz!' }}
                </h1>
                <p class="text-indigo-100/90 text-base sm:text-lg leading-relaxed max-w-2xl">
                    {{ $settings['hero_subtitle'] ?? '' }}
                </p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="#on-kayit" class="px-6 py-3.5 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-sm shadow-lg shadow-rose-500/30">
                        🤸 Ücretsiz Deneme Dersi Randevusu Al
                    </a>
                    <a href="#program" class="px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm border border-white/20">
                        📅 Haftalık Ders Programını Gör
                    </a>
                </div>
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-indigo-800/80 text-center sm:text-left">
                    <div>
                        <div class="text-2xl font-extrabold text-amber-400">Maks. 12</div>
                        <div class="text-xs text-indigo-200">Kişilik Butik Gruplar</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-emerald-400">%100</div>
                        <div class="text-xs text-indigo-200">Dijital Yoklama & Gelişim Karnesi</div>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-rose-400">%15</div>
                        <div class="text-xs text-indigo-200">Kardeş & Dönem İndirimi</div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-5">
                <div class="bg-white text-slate-900 rounded-2xl p-6 shadow-2xl border border-indigo-100" id="on-kayit">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Online Ön Kayıt</span>
                            <h2 class="text-xl font-extrabold">Ücretsiz Deneme Dersi Formu</h2>
                        </div>
                        <span class="text-2xl">🎯</span>
                    </div>

                    @if(session('trial_success'))
                        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-semibold">
                            ✅ {{ session('trial_success') }}
                        </div>
                    @endif

                    <form action="{{ route('public.trial.submit') }}" method="POST" class="space-y-3.5">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Veli Adı Soyadı *</label>
                                <input type="text" name="parent_name" required placeholder="Ör: Ayşe Yılmaz" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Cep Telefonu (WhatsApp) *</label>
                                <input type="tel" name="parent_phone" required placeholder="05XX XXX XX XX" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:outline-none">
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Sporcu (Çocuk) Adı *</label>
                                <input type="text" name="student_name" required placeholder="Ör: Can Yılmaz" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Yaşı *</label>
                                <input type="number" name="student_age" required min="3" max="50" value="6" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-600 focus:outline-none">
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Branş Tercihi</label>
                                <select name="branch_id" class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm bg-white">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->age_range }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Uygun Gün / Saat</label>
                                <input type="text" name="preferred_days" placeholder="Ör: Hafta sonu sabah" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                            </div>
                        </div>
                        <label class="flex items-start gap-2 text-xs text-slate-600 pt-1 cursor-pointer">
                            <input type="checkbox" name="kvkk_approved" value="1" checked required class="mt-0.5 rounded text-indigo-600">
                            <span><a href="#kvkk" class="text-indigo-600 underline font-semibold">KVKK Aydınlatma Metni</a>'ni okudum, deneme dersi planlaması için tarafımla iletişime geçilmesini onaylıyorum.</span>
                        </label>
                        <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-600/25 transition">
                            🚀 Ücretsiz Deneme Dersi Talebi Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Branşlarımız -->
    <section id="branslar" class="py-16 max-w-7xl mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Yaş ve Seviyeye Özel Eğitim</span>
            <h2 class="text-3xl font-extrabold mt-1">Cimnastik Branşlarımız</h2>
            <p class="text-slate-600 text-sm mt-2">Her yaş grubunun fiziksel ve pedagojik gelişimine uygun, Türkiye Cimnastik Federasyonu müfredatıyla uyumlu branşlar.</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($branches as $b)
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-3xl bg-indigo-50 w-12 h-12 rounded-xl flex items-center justify-center">{{ $b->icon }}</span>
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-indigo-100 text-indigo-800">{{ $b->age_range }}</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900">{{ $b->name }}</h3>
                        <p class="text-xs font-semibold text-rose-600 mt-0.5">{{ $b->level }}</p>
                        <p class="text-sm text-slate-600 mt-3">{{ $b->description }}</p>
                        @if($b->benefits)
                            <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700">
                                <strong class="text-slate-900">Kazanımlar:</strong> {{ $b->benefits }}
                            </div>
                        @endif
                    </div>
                    <a href="#on-kayit" class="mt-5 inline-flex items-center justify-center w-full py-2.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-xs font-bold transition">
                        Deneme Dersine Başvur →
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Haftalık Ders Programı & Canlı Kontenjan -->
    <section id="program" class="py-16 bg-white border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-wrap justify-between items-end gap-4 mb-10">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Canlı Salon Takvimi</span>
                    <h2 class="text-3xl font-extrabold mt-1">Haftalık Ders Programı & Kontenjanlar</h2>
                </div>
                <span class="text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-lg font-semibold">
                    🟢 Kontenjan bilgisi yönetim panelinden anlık güncellenir
                </span>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                @foreach($groups as $grp)
                    @php
                        $remainingQuota = max(0, $grp->capacity - $grp->active_students_count);
                    @endphp
                    <div class="rounded-2xl border border-slate-200 p-5 bg-slate-50/70 flex flex-col justify-between">
                        <div>
                            <div class="flex flex-wrap justify-between items-start gap-2">
                                <div>
                                    <span class="text-xs font-bold text-indigo-600">{{ $grp->branch?->name }}</span>
                                    <h3 class="text-lg font-extrabold text-slate-900">{{ $grp->name }}</h3>
                                </div>
                                @if($remainingQuota === 0)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Kontenjan Dolu (Yedek Liste)</span>
                                @elseif($remainingQuota <= 4)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">🔥 Son {{ $remainingQuota }} Yer!</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">✅ Kayıt Açık ({{ $grp->active_students_count }}/{{ $grp->capacity }})</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 mt-1">👨‍🏫 Antrenör: <strong>{{ $grp->coach?->name ?? 'Kulüp Antrenörü' }}</strong> · 📍 Alan: <strong>{{ $grp->area }}</strong></p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach($grp->schedules as $sch)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-800 shadow-sm">
                                        🗓️ {{ $sch->day_of_week }}: {{ substr($sch->start_time, 0, 5) }} - {{ substr($sch->end_time, 0, 5) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Paket ve Ücretler -->
    <section id="ucretler" class="py-16 max-w-7xl mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Şeffaf Fiyatlandırma</span>
            <h2 class="text-3xl font-extrabold mt-1">Eğitim Paketleri ve İndirim Kuralları</h2>
            <p class="text-slate-600 text-sm mt-2">Kardeş kaydında %15, 3 aylık peşin dönem kaydında %10 indirim otomatik uygulanır.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($packages as $pkg)
                <div class="bg-white rounded-2xl p-6 border-2 {{ $loop->iteration === 2 ? 'border-indigo-600 shadow-xl' : 'border-slate-200 shadow-sm' }} flex flex-col justify-between">
                    <div>
                        @if($loop->iteration === 2)
                            <span class="inline-block text-[11px] font-extrabold uppercase tracking-wider bg-indigo-600 text-white px-3 py-1 rounded-full mb-3">En Çok Tercih Edilen</span>
                        @endif
                        <h3 class="text-lg font-extrabold text-slate-900">{{ $pkg->name }}</h3>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-3xl font-extrabold text-indigo-700">{{ number_format($pkg->price, 0, ',', '.') }} ₺</span>
                            <span class="text-xs text-slate-500">/ {{ $pkg->lessons_count }} Ders</span>
                        </div>
                        <p class="text-sm text-slate-600 mt-3">{{ $pkg->description }}</p>
                    </div>
                    <a href="#on-kayit" class="mt-6 block text-center py-3 rounded-xl font-bold text-xs {{ $loop->iteration === 2 ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-slate-100 text-slate-900 hover:bg-slate-200' }}">
                        Bu Paket İçin Ön Kayıt Oluştur
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Antrenörler -->
    <section id="antrenorler" class="py-16 bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Uzman Kadromuz</span>
                <h2 class="text-3xl font-extrabold mt-1">Federasyon Belgeli Antrenörlerimiz</h2>
            </div>
            <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                @foreach($coaches as $coach)
                    <div class="bg-slate-800/90 border border-slate-700 rounded-2xl p-6 flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-2xl font-bold shrink-0">
                            Medal
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold">{{ $coach->name }}</h3>
                            <p class="text-xs font-semibold text-amber-400 mt-0.5">{{ $coach->specialty }}</p>
                            <p class="text-sm text-slate-300 mt-2">{{ $coach->bio }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Galeri & Duyurular -->
    <section id="galeri" class="py-16 max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-12 gap-10">
            <div class="lg:col-span-7">
                <h2 class="text-2xl font-extrabold mb-6">📸 Salonumuzdan Kareler</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach($gallery as $item)
                        <div class="group relative rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-sm">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-48 object-cover">
                            <div class="p-3">
                                <span class="text-[11px] font-bold text-indigo-600">{{ $item->category }}</span>
                                <h4 class="text-sm font-bold text-slate-900">{{ $item->title }}</h4>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="lg:col-span-5" id="sss">
                <h2 class="text-2xl font-extrabold mb-6">📢 Güncel Duyurular & SSS</h2>
                <div class="space-y-3 mb-8">
                    @foreach($announcements as $ann)
                        <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100">
                            <span class="text-[11px] font-bold text-indigo-600">{{ $ann->created_at->format('d.m.Y') }}</span>
                            <h3 class="font-bold text-sm text-slate-900 mt-0.5">{{ $ann->title }}</h3>
                            <p class="text-xs text-slate-600 mt-1">{{ $ann->content }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="space-y-3">
                    @foreach($faqs as $faq)
                        <details class="group bg-white rounded-xl border border-slate-200 p-4">
                            <summary class="font-bold text-sm cursor-pointer list-none flex justify-between items-center">
                                <span>❓ {{ $faq->question }}</span>
                                <span class="text-indigo-600 font-extrabold">+</span>
                            </summary>
                            <p class="text-xs text-slate-600 mt-2 leading-relaxed">{{ $faq->answer }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- KVKK ve Alt Bilgi -->
    <footer id="kvkk" class="bg-slate-950 text-slate-400 py-12 text-xs border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-3 gap-8">
            <div class="flex items-start gap-3.5">
                <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-14 h-14 rounded-full object-cover border-2 border-purple-500 shrink-0">
                <div>
                    <h4 class="text-white font-extrabold text-base mb-1">{{ $settings['club_name'] ?? 'Tekirdağ Enerjik Cimnastik' }}</h4>
                    <p class="leading-relaxed">{{ $settings['address'] ?? '' }} ({{ $settings['city_district'] ?? 'Tekirdağ' }})</p>
                    <p class="mt-1.5 text-slate-300 font-semibold">Tel: {{ $settings['phone'] ?? '0538 899 31 59' }}</p>
                    <a href="https://www.instagram.com/enerjik.cimnastik/" target="_blank" class="inline-block mt-1 text-pink-400 font-bold hover:underline">📸 instagram.com/enerjik.cimnastik</a>
                </div>
            </div>
            <div class="md:col-span-2 space-y-3">
                <div>
                    <h4 class="text-white font-bold mb-1.5">🔒 KVKK Aydınlatma Metni ve Çocuk Verisi Güvenliği</h4>
                    <p class="leading-relaxed text-slate-400">{{ $settings['kvkk_text'] ?? '' }}</p>
                </div>
                <div class="pt-3 border-t border-slate-800/80 flex flex-wrap justify-between items-center gap-3 text-[11px] text-slate-500">
                    <span>© {{ date('Y') }} Tekirdağ Enerjik Cimnastik Spor Kulübü. Tüm hakları saklıdır.</span>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('public.mobile.pwa') }}" class="hover:text-slate-300">👨‍👩‍👧 Veli Girişi</a>
                        <a href="{{ route('public.qr.checkin') }}" class="hover:text-slate-300">📷 Salon QR Yoklama</a>
                        <a href="{{ route('public.qr.poster') }}" class="hover:text-slate-300">🏁 QR Afiş</a>
                        <a href="{{ route('admin.index') }}" class="hover:text-slate-300">⚙️ Yönetici & Antrenör Girişi</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
