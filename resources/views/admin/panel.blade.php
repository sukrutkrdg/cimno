<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli (16 Modül) | Enerjik Cimnastik</title>
    <link rel="icon" type="image/jpeg" href="/icons/logo.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
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
                <span class="text-[11px] text-slate-400">Tek Veritabanı · 16 Modül · WhatsApp Business Hazır Cevap Motoru</span>
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
        <!-- Sol Menü: 16 Modül (Yetkiye Göre Filtrelenir) -->
        <aside class="w-full lg:w-64 bg-white border-b lg:border-b-0 lg:border-r border-slate-200 p-3 shrink-0">
            <div class="flex items-center justify-between px-3 py-1.5">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Yetkili Modüller ({{ count($allowedTabs) }})</span>
                @if($authUser->role === 'super_admin')
                    <a href="{{ route('admin.index', ['tab' => 'personel']) }}" class="text-[10px] font-bold text-indigo-600 hover:underline">⚙️ Yetkiler</a>
                @endif
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

            <!-- ========================================== -->
            <!-- 1. PANO (DASHBOARD)                        -->
            <!-- ========================================== -->
            @if($activeTab === 'pano')
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold">📊 Salon Yönetim Panosu</h2>
                        <p class="text-xs text-slate-500">Bugünkü dersler, tahsilatlar, bekleyen adaylar ve WhatsApp Business hazır mesaj kuyruğu</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.reports.csv') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold">📥 Gelir-Gider Excel İndir</a>
                        <a href="{{ route('admin.backup.download') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold">💾 Veritabanı Yedeği İndir</a>
                    </div>
                </div>

                <!-- Özet Kartlar -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="text-xs font-bold text-slate-500">Aktif Kayıtlı Sporcu</span>
                        <div class="text-3xl font-extrabold text-indigo-700 mt-1">{{ $activeStudentsCount }}</div>
                        <span class="text-[11px] text-emerald-600 font-semibold">{{ $groups->count() }} Aktif Grup</span>
                    </div>
                    @if($currentRole !== 'coach')
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span class="text-xs font-bold text-slate-500">Bu Ayki Tahsilat</span>
                            <div class="text-3xl font-extrabold text-emerald-600 mt-1">{{ number_format($monthCollection, 0, ',', '.') }} ₺</div>
                            <span class="text-[11px] text-slate-500">Bugün: {{ number_format($todayCollection, 0, ',', '.') }} ₺</span>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span class="text-xs font-bold text-slate-500">Geciken / Bekleyen Aidat</span>
                            <div class="text-3xl font-extrabold text-rose-600 mt-1">{{ number_format($pendingTotal, 0, ',', '.') }} ₺</div>
                            <span class="text-[11px] text-rose-600 font-semibold">Vadesi Geçen: {{ number_format($overdueTotal, 0, ',', '.') }} ₺</span>
                        </div>
                    @endif
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="text-xs font-bold text-slate-500">Bekleyen Deneme Adayı</span>
                        <div class="text-3xl font-extrabold text-amber-600 mt-1">{{ $newLeadsCount }}</div>
                        <a href="{{ route('admin.index', ['tab' => 'adaylar']) }}" class="text-[11px] text-indigo-600 font-bold underline">Adayları Yönet →</a>
                    </div>
                </div>

                <!-- Periyodik Uyarılar: 2+ Ders Kaçıranlar & Sağlık Raporu Bitenler & WhatsApp Kuyruğu -->
                <div class="grid lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-extrabold text-sm text-rose-700 mb-3">⚠️ Üst Üste 2+ Ders Kaçıranlar ({{ $absentWarnStudents->count() }})</h3>
                        @forelse($absentWarnStudents as $st)
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 flex justify-between items-center mb-2">
                                <div>
                                    <div class="font-bold text-xs">{{ $st->full_name }} ({{ $st->consecutive_absences }} Ders)</div>
                                    <div class="text-[11px] text-slate-600">Veli: {{ $st->parent_name }} · {{ $st->parent_phone }}</div>
                                </div>
                                <a href="https://wa.me/90{{ substr(preg_replace('/\D+/', '', $st->parent_phone), -10) }}?text={{ urlencode('Merhaba ' . $st->parent_name . ', sporcumuz ' . $st->full_name . ' son 2 antrenmanımıza katılamamıştır. Her şeyin yolunda olduğunu umuyoruz, telafi planlaması için bize ulaşabilirsiniz.') }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">💬 WhatsApp</a>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">Üst üste devamsızlık yapan öğrenci yok.</p>
                        @endforelse
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-extrabold text-sm text-amber-700 mb-3">🩺 Sağlık Raporu Yenilenecekler ({{ $expiringMedicalStudents->count() }})</h3>
                        @forelse($expiringMedicalStudents as $st)
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 flex justify-between items-center mb-2">
                                <div>
                                    <div class="font-bold text-xs">{{ $st->full_name }}</div>
                                    <div class="text-[11px] text-slate-600">Bitiş: {{ $st->medical_report_expires_at?->format('d.m.Y') }}</div>
                                </div>
                                <a href="https://wa.me/90{{ substr(preg_replace('/\D+/', '', $st->parent_phone), -10) }}?text={{ urlencode('Sayın ' . $st->parent_name . ', ' . $st->full_name . ' ait sağlık raporu süresi dolmaktadır. Yenilenen raporu iletmenizi rica ederiz.') }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">💬 Hatırlat</a>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">15 gün içinde raporu dolacak öğrenci yok.</p>
                        @endforelse
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-extrabold text-sm text-emerald-700">💬 Bekleyen WhatsApp Kuyruğu</h3>
                            <a href="{{ route('admin.index', ['tab' => 'mesajlar']) }}" class="text-xs text-indigo-600 font-bold underline">Tümünü Gör</a>
                        </div>
                        @foreach($whatsappQueue->where('status', 'queued')->take(3) as $msg)
                            <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 mb-2 flex justify-between items-center gap-2">
                                <div class="text-xs truncate">
                                    <strong class="block">{{ $msg->recipient_name }}</strong>
                                    <span class="text-[11px] text-slate-600">{{ Str::limit($msg->message_text, 55) }}</span>
                                </div>
                                <a href="{{ route('admin.whatsapp.send', $msg->id) }}" target="_blank" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">Gönder →</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 2. ADAYLAR (ÖN KAYIT & DENEME DERSİ)       -->
            <!-- ========================================== -->
            @if($activeTab === 'adaylar')
                <div>
                    <h2 class="text-2xl font-extrabold">🎯 Adaylar (Ön Kayıt & Deneme Dersi Talepleri)</h2>
                    <p class="text-xs text-slate-500">Web sitesinden veya Instagram'dan gelen adayları tek tıkla kesin kayda dönüştürün</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase">
                            <tr>
                                <th class="p-3.5">Aday Sporcu / Yaş</th>
                                <th class="p-3.5">Veli & Telefon</th>
                                <th class="p-3.5">Branş / Kaynak</th>
                                <th class="p-3.5">Durum</th>
                                <th class="p-3.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($leads as $lead)
                                <tr>
                                    <td class="p-3.5 font-bold">{{ $lead->student_name }} ({{ $lead->student_age }} Yaş)</td>
                                    <td class="p-3.5">{{ $lead->parent_name }} · <span class="font-mono">{{ $lead->parent_phone }}</span></td>
                                    <td class="p-3.5">{{ $lead->branch?->name ?? 'Genel' }} · <span class="uppercase text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100">{{ $lead->source }}</span></td>
                                    <td class="p-3.5">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $lead->status === 'converted' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $lead->status }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 text-right flex justify-end gap-2">
                                        <a href="https://wa.me/90{{ substr(preg_replace('/\D+/', '', $lead->parent_phone), -10) }}?text={{ urlencode('Merhaba ' . $lead->parent_name . ', Enerjik Cimnastik deneme dersi randevunuz için yazıyoruz.') }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold">💬 WhatsApp</a>
                                        @if($lead->status !== 'converted')
                                            <form action="{{ route('admin.leads.convert', $lead->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">✨ Tek Tıkla Kesin Kayda Dönüştür</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 3. ÖĞRENCİLER & YENİ KAYIT                 -->
            <!-- ========================================== -->
            @if($activeTab === 'ogrenciler')
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Yeni Öğrenci Kaydı + İlk Tahsilat Formu (Kabul Kriteri #1) -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #1</span>
                        <h3 class="text-lg font-extrabold mb-3">➕ Yeni Öğrenci Kaydı Aç & İlk Tahsilatı Gir</h3>
                        <form action="{{ route('admin.students.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="font-bold block mb-1">Öğrenci Adı Soyadı *</label>
                                    <input type="text" name="full_name" required placeholder="Ör: Bora Demir" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Doğum Tarihi</label>
                                    <input type="date" name="birth_date" value="2019-06-15" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="font-bold block mb-1">Cinsiyet</label>
                                    <select name="gender" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        <option value="Kız">Kız</option>
                                        <option value="Erkek">Erkek</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Branş *</label>
                                    <select name="branch_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="font-bold block mb-1">Grup Seçimi *</label>
                                    <select name="group_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($groups as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->active_students_count }}/{{ $g->capacity }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Paket *</label>
                                    <select name="package_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($packages as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} ({{ number_format($p->price, 0, ',', '.') }} ₺)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="font-bold block mb-1">İndirim Kuralı (Kardeş/Burs)</label>
                                    <select name="discount_rule_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        <option value="">İndirim Yok (Tam Ücret)</option>
                                        @foreach($discountRules as $d)
                                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Beden Ölçüsü / Lisans No</label>
                                    <input type="text" name="uniform_size" placeholder="Ör: 7-8 Yaş / Lisanslı" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="font-bold block mb-1">Veli Adı Soyadı *</label>
                                    <input type="text" name="parent_name" required placeholder="Ör: Kemal Demir" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Veli Telefon (WhatsApp) *</label>
                                    <input type="text" name="parent_phone" required placeholder="0532XXXXXXX" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Sağlık Bilgisi & Rapor Notu (Özel Nitelikli Veri)</label>
                                <input type="text" name="health_notes" placeholder="Spor yapabilir raporu alındı, bilinen alerji yok." class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="font-bold text-emerald-900 block mb-1">İlk Tahsilat Tutarı (₺)</label>
                                    <input type="number" name="initial_payment_amount" value="3200" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                                </div>
                                <div>
                                    <label class="font-bold text-emerald-900 block mb-1">Tahsilat Hesabı / POS</label>
                                    <select name="cash_account_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($cashAccounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3 pt-1">
                                <label class="flex items-center gap-1.5"><input type="checkbox" name="kvkk_consent" value="1" checked> KVKK Onayı</label>
                                <label class="flex items-center gap-1.5"><input type="checkbox" name="health_data_consent" value="1" checked> Sağlık Verisi Rızası</label>
                                <label class="flex items-center gap-1.5"><input type="checkbox" name="photo_consent" value="1" checked> Fotoğraf Paylaşım Onayı</label>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow">
                                ✅ Kaydı Aç, Aidat Planını Oluştur & İlk Tahsilatı Kaydet
                            </button>
                        </form>
                    </div>

                    <!-- Kayıtlı Öğrenciler Listesi -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm overflow-x-auto">
                        <h3 class="text-lg font-extrabold mb-3">📋 Kayıtlı Sporcular ({{ $students->count() }})</h3>
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                <tr>
                                    <th class="p-2.5">Sporcu</th>
                                    <th class="p-2.5">Branş / Grup</th>
                                    <th class="p-2.5">Veli / İletişim</th>
                                    <th class="p-2.5">KVKK & Rapor</th>
                                    <th class="p-2.5">Durum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($students as $st)
                                    <tr>
                                        <td class="p-2.5">
                                            <div class="font-extrabold text-slate-900">{{ $st->full_name }}</div>
                                            <div class="text-[11px] text-slate-500">Telafi: {{ $st->makeup_credits }} · Lisans: {{ $st->license_no ?? '-' }}</div>
                                        </td>
                                        <td class="p-2.5">
                                            <div class="font-bold text-indigo-700">{{ $st->group?->name }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $st->package?->name }}</div>
                                        </td>
                                        <td class="p-2.5">
                                            <div>{{ $st->parent_name }}</div>
                                            <div class="font-mono text-[11px]">{{ $st->parent_phone }}</div>
                                        </td>
                                        <td class="p-2.5">
                                            <span class="block text-[11px]">{{ $st->photo_consent ? '📸 Foto İzinli' : '🚫 Foto İzinsiz' }}</span>
                                            <span class="block text-[11px] text-slate-500">Rapor: {{ $st->medical_report_expires_at?->format('d.m.Y') ?? '-' }}</span>
                                        </td>
                                        <td class="p-2.5">
                                            <div class="flex items-center gap-1.5">
                                                <form action="{{ route('admin.students.status', $st->id) }}" method="POST">
                                                    @csrf
                                                    <select name="status" onchange="this.form.submit()" class="text-[11px] font-bold rounded-lg border px-2 py-1 {{ $st->status === 'active' ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }}">
                                                        <option value="active" {{ $st->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                                        <option value="frozen" {{ $st->status === 'frozen' ? 'selected' : '' }}>Donduruldu</option>
                                                        <option value="left" {{ $st->status === 'left' ? 'selected' : '' }}>Ayrıldı (Arşiv)</option>
                                                    </select>
                                                </form>
                                                @if($authUser->hasPermission('delete_student', $authUser->role === 'super_admin'))
                                                    <form action="{{ route('admin.students.destroy', $st->id) }}" method="POST" onsubmit="return confirm('{{ $st->full_name }} sporcu ve veli giriş kaydını silmek istediğinize emin misiniz?')">
                                                        @csrf
                                                        <button type="submit" title="Kaydı Sil" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold">
                                                            🗑️ Sil
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 4 & 5. BRANŞ, GRUP VE DERS PROGRAMI        -->
            <!-- ========================================== -->
            @if($activeTab === 'gruplar')
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Kabul Kriteri #2: Yeni Grup Açıp Ders Programına Ekleme -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #2</span>
                        <h3 class="text-lg font-extrabold mb-3">➕ Yeni Grup Aç & Ders Programına Ekle</h3>
                        <form action="{{ route('admin.groups.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="font-bold block mb-1">Grup Adı *</label>
                                <input type="text" name="name" required placeholder="Ör: Hafta Sonu Yıldızlar C Grubu" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="font-bold block mb-1">Branş *</label>
                                    <select name="branch_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Antrenör *</label>
                                    <select name="coach_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($staff->where('role', 'coach') as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">Yaş Grubu</label>
                                    <input type="text" name="age_group" value="5-8 Yaş" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Seviye</label>
                                    <input type="text" name="level" value="Başlangıç" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Kontenjan</label>
                                    <input type="number" name="capacity" value="12" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Salon Alanı (Çakışma Kontrolü Yapılır)</label>
                                <select name="area" class="w-full border rounded-xl px-3 py-2 bg-white">
                                    <option value="Ana Minder">Ana Minder</option>
                                    <option value="Trampolin Alanı">Trampolin Alanı</option>
                                    <option value="Ana Minder & Bar Alanı">Ana Minder & Bar Alanı</option>
                                    <option value="Bale & Ritmik Salonu">Bale & Ritmik Salonu</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">Ders Günü</label>
                                    <select name="day_of_week" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach(['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'] as $d)
                                            <option value="{{ $d }}">{{ $d }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Başlangıç</label>
                                    <input type="time" name="start_time" value="11:30" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Bitiş</label>
                                    <input type="time" name="end_time" value="12:30" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">
                                📅 Grubu Aç ve Haftalık Programa Ekle
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-lg font-extrabold mb-3">🗓️ Mevcut Gruplar, Doluluk ve Haftalık Ders Programı</h3>
                        <div class="space-y-3">
                            @foreach($groups as $g)
                                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-wrap justify-between items-center gap-3">
                                    <div>
                                        <span class="text-[11px] font-bold text-indigo-600">{{ $g->branch?->name }} · {{ $g->area }}</span>
                                        <h4 class="font-extrabold text-sm">{{ $g->name }} ({{ $g->active_students_count }}/{{ $g->capacity }} Öğrenci)</h4>
                                        <p class="text-xs text-slate-500">Antrenör: {{ $g->coach?->name ?? '-' }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($g->schedules as $sch)
                                            <span class="px-2.5 py-1 rounded-lg bg-white border text-xs font-bold">{{ $sch->day_of_week }} {{ substr($sch->start_time, 0, 5) }}-{{ substr($sch->end_time, 0, 5) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 6. TEK DOKUNUŞLA YOKLAMA                   -->
            <!-- ========================================== -->
            @if($activeTab === 'yoklama')
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="text-xl font-extrabold mb-1">✅ Grup Bazlı Tek Dokunuşla Yoklama & Telafi Takibi</h2>
                    <p class="text-xs text-slate-500 mb-6">Üst üste 2 devamsızlıkta otomatik WhatsApp uyarısı kuyruğa düşer; izinli işaretlenen öğrenciye +1 telafi dersi hakkı eklenir.</p>

                    @foreach($groups as $grp)
                        <form action="{{ route('admin.attendance.store') }}" method="POST" class="mb-6 p-4 rounded-2xl border border-slate-200 bg-slate-50">
                            @csrf
                            <input type="hidden" name="group_id" value="{{ $grp->id }}">
                            <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                                <div>
                                    <h3 class="font-extrabold text-sm text-indigo-900">{{ $grp->name }} ({{ $grp->branch?->name }})</h3>
                                    <span class="text-xs text-slate-500">Antrenör: {{ $grp->coach?->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="date" name="attendance_date" value="{{ today()->toDateString() }}" class="border rounded-xl px-3 py-1.5 text-xs bg-white font-bold">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">💾 Yoklamayı Kaydet</button>
                                </div>
                            </div>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($students->where('group_id', $grp->id) as $st)
                                    <div class="bg-white p-3 rounded-xl border border-slate-200 flex flex-col justify-between gap-2">
                                        <div class="flex justify-between items-center">
                                            <span class="font-bold text-xs">{{ $st->full_name }}</span>
                                            <span class="text-[10px] px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold">Telafi: {{ $st->makeup_credits }}</span>
                                        </div>
                                        <select name="statuses[{{ $st->id }}]" class="w-full border rounded-lg px-2 py-1.5 text-xs font-bold bg-emerald-50 text-emerald-900">
                                            <option value="present">✅ Geldi</option>
                                            <option value="absent">❌ Gelmedi</option>
                                            <option value="excused">🩺 Raporlu / İzinli (+1 Telafi)</option>
                                            <option value="makeup">🔄 Telafi Dersi Kullandı</option>
                                        </select>
                                        <input type="text" name="notes[{{ $st->id }}]" placeholder="Antrenör ders notu..." class="w-full border rounded-lg px-2 py-1 text-[11px]">
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    @endforeach
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 7. PAKETLER VE TOPLU FİYAT GÜNCELLEME      -->
            <!-- ========================================== -->
            @if($activeTab === 'paketler')
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Kabul Kriteri #3: Paket Fiyatlarını Toplu Güncelleme -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #3</span>
                        <h3 class="text-lg font-extrabold mb-2">🏷️ Paket Fiyatlarını Toplu Güncelle</h3>
                        <p class="text-xs text-slate-500 mb-4">Dönem başında tüm paket fiyatlarını yüzdesel (%) veya sabit TL olarak tek tıkla güncelleyin.</p>
                        <form action="{{ route('admin.packages.bulk_price') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="font-bold block mb-1">Yüzdesel Artış / İndirim Oranı (%)</label>
                                <input type="number" step="0.1" name="percentage" value="15" class="w-full border rounded-xl px-3 py-2 font-bold">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Veya Sabit Ek Tutar (₺)</label>
                                <input type="number" name="fixed_amount" value="0" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold">
                                🚀 Tüm Paket Fiyatlarını Toplu Güncelle
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-lg font-extrabold mb-3">📦 Aktif Eğitim Paketleri ve İndirim Kuralları</h3>
                        <div class="space-y-3 mb-6">
                            @foreach($packages as $p)
                                <div class="p-3.5 rounded-xl border flex justify-between items-center">
                                    <div>
                                        <div class="font-extrabold text-sm">{{ $p->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $p->lessons_count }} Ders · Kayıt Ücreti: {{ number_format($p->registration_fee, 0, ',', '.') }} ₺</div>
                                    </div>
                                    <div class="text-xl font-extrabold text-indigo-700">{{ number_format($p->price, 0, ',', '.') }} ₺</div>
                                </div>
                            @endforeach
                        </div>
                        <h4 class="font-extrabold text-xs uppercase text-slate-500 mb-2">Otomatik İndirim Kuralları</h4>
                        <div class="grid sm:grid-cols-3 gap-2.5">
                            @foreach($discountRules as $d)
                                <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-100 text-xs font-bold text-indigo-900">
                                    {{ $d->name }} (%{{ $d->percentage }})
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 8. AİDAT, BORÇLANDIRMA VE TAHSİLAT         -->
            <!-- ========================================== -->
            @if($activeTab === 'aidat')
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold">💳 Aidat, Otomatik Borçlandırma ve Tahsilat</h2>
                        <p class="text-xs text-slate-500">"Önce borç açılır, tahsilat ona bağlanır" mimarisi · Makbuz PDF ve WhatsApp gönderimi</p>
                    </div>
                    <form action="{{ route('admin.invoices.generate') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="period_label" value="Kasım 2026 Aidatı" class="border rounded-xl px-3 py-2 text-xs bg-white font-bold">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">⚡ Tüm Aktif Öğrencilere Dönem Borcu Aç</button>
                    </form>
                </div>

                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Hızlı Tahsilat Girişi -->
                    <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-base font-extrabold mb-3">➕ Tahsilat Gir & Makbuz Üret</h3>
                        <form action="{{ route('admin.payments.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="font-bold block mb-1">Açık Borç / Öğrenci Seçin *</label>
                                <select name="invoice_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                    @foreach($invoices->where('remaining_amount', '>', 0) as $inv)
                                        <option value="{{ $inv->id }}">{{ $inv->student?->full_name }} - {{ $inv->period_label }} (Kalan: {{ number_format($inv->remaining_amount, 0, ',', '.') }} ₺)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">Tahsilat Tutarı (₺) *</label>
                                    <input type="number" name="amount" value="1220" required class="w-full border rounded-xl px-3 py-2 font-bold">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Ödeme Yöntemi *</label>
                                    <select name="payment_method" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        <option value="cash">Nakit</option>
                                        <option value="pos">Fiziki POS (Kart)</option>
                                        <option value="bank_transfer">Havale / EFT</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Kasa / Banka Hesabı *</label>
                                <select name="cash_account_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                    @foreach($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (Bakiye: {{ number_format($acc->balance, 0, ',', '.') }} ₺)</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
                                🧾 Tahsilatı Kaydet & WhatsApp Makbuzu Hazırla
                            </button>
                        </form>
                    </div>

                    <!-- Borçlar ve Makbuzlar Listesi -->
                    <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm overflow-x-auto">
                        <h3 class="text-base font-extrabold mb-3">📋 Aidat Borçları ve Tahsilat Makbuzları</h3>
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                <tr>
                                    <th class="p-2.5">Öğrenci / Dönem</th>
                                    <th class="p-2.5">Net Tutar</th>
                                    <th class="p-2.5">Ödenen / Kalan</th>
                                    <th class="p-2.5">Vade</th>
                                    <th class="p-2.5 text-right">Makbuz / WhatsApp</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($invoices as $inv)
                                    <tr>
                                        <td class="p-2.5">
                                            <div class="font-bold">{{ $inv->student?->full_name }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $inv->period_label }}</div>
                                        </td>
                                        <td class="p-2.5 font-bold">{{ number_format($inv->net_amount, 0, ',', '.') }} ₺</td>
                                        <td class="p-2.5">
                                            <span class="text-emerald-700 font-bold">{{ number_format($inv->paid_amount, 0, ',', '.') }} ₺</span> /
                                            <span class="text-rose-600 font-extrabold">{{ number_format($inv->remaining_amount, 0, ',', '.') }} ₺</span>
                                        </td>
                                        <td class="p-2.5">{{ $inv->due_date->format('d.m.Y') }}</td>
                                        <td class="p-2.5 text-right space-x-1">
                                            @foreach($inv->payments as $p)
                                                <a href="{{ route('public.receipt', $p->id) }}" target="_blank" class="inline-block px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 font-bold">🧾 {{ $p->receipt_no }}</a>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 9. GELİR-GİDER, ÜRÜN SATIŞI VE KASA        -->
            <!-- ========================================== -->
            @if($activeTab === 'kasa' && $currentRole === 'super_admin')
                <div class="grid sm:grid-cols-3 gap-4">
                    @foreach($cashAccounts as $acc)
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span class="text-xs font-bold uppercase text-indigo-600">{{ $acc->type }}</span>
                            <h3 class="font-extrabold text-base mt-0.5">{{ $acc->name }}</h3>
                            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ number_format($acc->balance, 2, ',', '.') }} ₺</div>
                            @if($acc->iban)
                                <div class="text-[11px] font-mono text-slate-500 mt-1">{{ $acc->iban }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="grid lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-base font-extrabold mb-3">➕ Yeni Gider veya Ürün Satışı (Mayo/Forma) Ekle</h3>
                        <form action="{{ route('admin.transactions.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">İşlem Türü</label>
                                    <select name="direction" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                                        <option value="expense">🔴 Gider Çıkışı</option>
                                        <option value="income">🟢 Ürün Satışı / Ek Gelir</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Kategori</label>
                                    <select name="category" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        <option value="Kira">Kira</option>
                                        <option value="Elektrik / Su / Doğalgaz">Elektrik / Su / Doğalgaz</option>
                                        <option value="Maaş ve SGK">Maaş ve SGK</option>
                                        <option value="Antrenör Hakedişi">Antrenör Hakedişi</option>
                                        <option value="Ekipman ve Bakım">Ekipman ve Bakım</option>
                                        <option value="Temizlik">Temizlik</option>
                                        <option value="Reklam (Instagram/Google)">Reklam (Instagram/Google)</option>
                                        <option value="Ürün Satışı (Mayo / Forma)">Ürün Satışı (Mayo / Forma)</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Açıklama *</label>
                                <input type="text" name="title" required placeholder="Ör: Ekim Ayı Temizlik Malzemesi Alımı" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">Tutar (₺) *</label>
                                    <input type="number" name="amount" required value="1500" class="w-full border rounded-xl px-3 py-2 font-bold">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Tarih</label>
                                    <input type="date" name="transaction_date" value="{{ today()->toDateString() }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Hesap</label>
                                    <select name="cash_account_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($cashAccounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 pt-1">
                                <input type="checkbox" name="is_recurring" value="1"> Her ay sabit tekrar eden kayıt (ör. Kira)
                            </label>
                            <button type="submit" class="w-full py-3 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white font-bold">💾 Kasa Hareketini Kaydet</button>
                        </form>
                    </div>

                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-base font-extrabold mb-3">📊 Gelir - Gider Hareketleri</h3>
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                <tr>
                                    <th class="p-2.5">Tarih</th>
                                    <th class="p-2.5">Kategori / Başlık</th>
                                    <th class="p-2.5">Hesap</th>
                                    <th class="p-2.5 text-right">Tutar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($transactions as $tx)
                                    <tr>
                                        <td class="p-2.5">{{ $tx->transaction_date->format('d.m.Y') }}</td>
                                        <td class="p-2.5">
                                            <span class="font-bold block">{{ $tx->title }}</span>
                                            <span class="text-[11px] text-slate-500">{{ $tx->category }} {{ $tx->is_recurring ? '· 🔄 Sabit Tekrarlı' : '' }}</span>
                                        </td>
                                        <td class="p-2.5">{{ $tx->cashAccount?->name }}</td>
                                        <td class="p-2.5 text-right font-extrabold {{ $tx->direction === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $tx->direction === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2, ',', '.') }} ₺
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 10. PERSONEL, SEKRETER VE ANTRENÖR HAKEDİŞİ -->
            <!-- ========================================== -->
            @if($activeTab === 'personel' && $currentRole === 'super_admin')
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Kabul Kriteri #5: Sekreter / Antrenör Hesabı Açma ve Kapatma -->
                    <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm h-fit">
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #5</span>
                        <h3 class="text-lg font-extrabold mb-3">👤 Yeni Yönetici / Sekreter / Antrenör Ekle</h3>
                        <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="font-bold block mb-1">Ad Soyad *</label>
                                <input type="text" name="name" required placeholder="Ör: Elif Yıldız (Yeni Sekreter)" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">E-posta *</label>
                                    <input type="email" name="email" required placeholder="elif@enerjikcimnastik.com" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Telefon *</label>
                                    <input type="text" name="phone" required placeholder="0532XXXXXXX" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">Rolü *</label>
                                    <select name="role" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                                        <option value="secretary">Sekreter (Ön Büro)</option>
                                        <option value="coach">Antrenör (Eğitmen)</option>
                                        <option value="super_admin">Süper Yönetici</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Ücret / Hakediş Modeli</label>
                                    <select name="compensation_type" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        <option value="fixed">Sabit Maaş</option>
                                        <option value="per_lesson">Ders Başı Ücret</option>
                                        <option value="both">Sabit Maaş + Ders Başı</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">Sabit Maaş (₺)</label>
                                    <input type="number" name="base_salary" value="25000" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Ders Başı Ücret (₺)</label>
                                    <input type="number" name="per_lesson_rate" value="0" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Giriş Şifresi *</label>
                                <input type="text" name="password" required value="enerjik2026" placeholder="Giriş şifresi belirleyin" class="w-full border rounded-xl px-3 py-2 font-mono font-bold">
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">
                                ➕ Yeni Kullanıcı Hesabını Aç
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-8 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                                <div>
                                    <h3 class="text-base font-extrabold">🛡️ Yönetici, Sekreter & Antrenör Yetki Matrisi (Düzenle & Sil)</h3>
                                    <p class="text-[11px] text-slate-500">Her personelin hangi ekranları görebileceğini, işlem yetkilerini, şifresini özelleştirin veya hesabını silin.</p>
                                </div>
                                <a href="{{ route('admin.login') }}" class="text-xs font-bold text-indigo-600 underline">🔐 Giriş Ekranı: /panel/giris</a>
                            </div>

                            <div class="space-y-4">
                                @foreach($staff as $u)
                                    <div class="p-4 rounded-2xl border {{ $u->is_active ? 'border-slate-200 bg-slate-50/60' : 'border-rose-200 bg-rose-50/40' }} space-y-3 text-xs">
                                        <form action="{{ route('admin.staff.permissions', $u->id) }}" method="POST" class="space-y-3">
                                            @csrf
                                            <div class="flex flex-wrap justify-between items-center gap-3 pb-3 border-b border-slate-200/80">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-extrabold text-sm text-slate-900">{{ $u->name }}</span>
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                            {{ $u->is_active ? '🟢 Aktif Hesap' : '🔴 Girişe Kapalı' }}
                                                        </span>
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                                        📧 <span class="font-mono">{{ $u->email }}</span> · 📞 <span class="font-mono">{{ $u->phone }}</span>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-2">
                                                    <select name="role" class="border border-slate-300 rounded-xl px-2.5 py-1.5 bg-white font-bold text-xs">
                                                        <option value="super_admin" {{ $u->role === 'super_admin' ? 'selected' : '' }}>👑 Süper Yönetici (Salon Sahibi)</option>
                                                        <option value="secretary" {{ $u->role === 'secretary' ? 'selected' : '' }}>🗂️ Sekreter (Ön Büro)</option>
                                                        <option value="coach" {{ $u->role === 'coach' ? 'selected' : '' }}>🎽 Antrenör (Eğitmen)</option>
                                                    </select>

                                                    <label class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white border border-slate-300 font-bold cursor-pointer">
                                                        <input type="checkbox" name="is_active" value="1" {{ $u->is_active ? 'checked' : '' }}>
                                                        <span>Giriş Aktif</span>
                                                    </label>
                                                </div>
                                            </div>

                                            @if($u->role === 'super_admin')
                                                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] font-bold">
                                                    👑 Süper Yönetici (Salon Sahibi) tüm 16 modüle, kasa/gider hesaplarına, personel yetkilerine ve veritabanı yedeğine tam erişime sahiptir.
                                                </div>
                                            @else
                                                <div class="grid sm:grid-cols-3 gap-2 pt-1">
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_collect_payment" value="1" {{ $u->hasPermission('collect_payment') ? 'checked' : '' }}>
                                                        <span>💳 Aidat & Tahsilat Girebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_view_expenses" value="1" {{ $u->hasPermission('view_expenses') ? 'checked' : '' }}>
                                                        <span>🏦 Kasa & Giderleri Görebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_view_reports" value="1" {{ $u->hasPermission('view_reports') ? 'checked' : '' }}>
                                                        <span>📑 Finansal Raporları Görebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_send_whatsapp" value="1" {{ $u->hasPermission('send_whatsapp') ? 'checked' : '' }}>
                                                        <span>💬 WhatsApp Kuyruğu Gönderebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_edit_content" value="1" {{ $u->hasPermission('edit_content') ? 'checked' : '' }}>
                                                        <span>🌐 Site İçeriği Düzenleyebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_only_own_groups" value="1" {{ $u->hasPermission('only_own_groups') ? 'checked' : '' }}>
                                                        <span>🎽 Yalnızca Kendi Gruplarını Görsün</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_take_attendance" value="1" {{ $u->hasPermission('take_attendance') ? 'checked' : '' }}>
                                                        <span>✅ Yoklama Alabilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_add_development" value="1" {{ $u->hasPermission('add_development') ? 'checked' : '' }}>
                                                        <span>📈 Gelişim Karnesi Girebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_delete_student" value="1" {{ $u->hasPermission('delete_student') ? 'checked' : '' }}>
                                                        <span>🗑️ Kayıt Silebilsin / Arşivlesin</span>
                                                    </label>
                                                </div>
                                            @endif

                                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2">
                                                <div class="flex items-center gap-2 flex-1 min-w-[220px]">
                                                    <label class="text-[11px] font-bold text-slate-600 shrink-0">🔑 Yeni Şifre:</label>
                                                    <input type="password" name="new_password" placeholder="Değiştirmek istemiyorsanız boş bırakın" class="w-full border border-slate-300 rounded-xl px-3 py-1.5 bg-white text-xs">
                                                </div>
                                                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white font-bold text-xs shadow-sm">
                                                    💾 Yetki & Şifreyi Kaydet
                                                </button>
                                            </div>
                                        </form>

                                        @if($authUser->id !== $u->id)
                                            <div class="pt-2 border-t border-slate-200/70 flex justify-end">
                                                <form action="{{ route('admin.staff.destroy', $u->id) }}" method="POST" onsubmit="return confirm('{{ $u->name }} ({{ $u->roleLabel() }}) kullanıcısını sistemden tamamen silmek istediğinize emin misiniz?')">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-700 font-bold text-[11px] transition">
                                                        🗑️ Kullanıcıyı Sil
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Otomatik Antrenör Hakediş Hesabı -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-3">🏅 Bu Ayki Otomatik Antrenör Hakediş Tablosu (Yoklamadan Hesaplanan)</h3>
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                    <tr>
                                        <th class="p-2.5">Antrenör</th>
                                        <th class="p-2.5">Verilen Ders</th>
                                        <th class="p-2.5">Sabit Maaş</th>
                                        <th class="p-2.5">Ders Başı Hakediş</th>
                                        <th class="p-2.5 text-right">Toplam Ödenecek</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($coachPayrolls as $cp)
                                        <tr>
                                            <td class="p-2.5 font-bold">{{ $cp['coach']->name }}</td>
                                            <td class="p-2.5">{{ $cp['lesson_count'] }} Ders</td>
                                            <td class="p-2.5">{{ number_format($cp['base_salary'], 0, ',', '.') }} ₺</td>
                                            <td class="p-2.5">{{ number_format($cp['lesson_earnings'], 0, ',', '.') }} ₺</td>
                                            <td class="p-2.5 text-right font-extrabold text-indigo-700">{{ number_format($cp['total_payable'], 0, ',', '.') }} ₺</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 11. WHATSAPP BUSINESS HAZIR CEVAPLARI      -->
            <!-- ========================================== -->
            @if($activeTab === 'mesajlar')
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-emerald-700">Meta Doğrulaması Gerektirmeyen Pratik Çözüm · Kabul Kriteri #4</span>
                        <h2 class="text-2xl font-extrabold">💬 WhatsApp Business Hazır Cevapları & Tıkla-Gönder Motoru</h2>
                        <p class="text-xs text-slate-500">Şablon metinlerini ve gönderim saatlerini düzenleyin; günlük hatırlatma kuyruğunu tek tıkla WhatsApp Business'ta açıp gönderin.</p>
                    </div>
                    <form action="{{ route('admin.whatsapp.generate') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow">
                            🔄 Bugünün Hatırlatma Kuyruğunu Otomatik Oluştur
                        </button>
                    </form>
                </div>

                <!-- Tıkla-Gönder Kuyruğu -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-base font-extrabold mb-3">📲 Gönderime Hazır Mesaj Kuyruğu (`wa.me` Tıkla-Gönder)</h3>
                    <div class="space-y-2.5">
                        @foreach($whatsappQueue as $msg)
                            <div class="p-3.5 rounded-xl border {{ $msg->status === 'sent' ? 'bg-slate-50 border-slate-200' : 'bg-emerald-50/50 border-emerald-200' }} flex flex-wrap justify-between items-center gap-3">
                                <div class="max-w-3xl">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-extrabold text-slate-900">{{ $msg->recipient_name }} ({{ $msg->recipient_phone }})</span>
                                        <span class="text-[11px] font-mono bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded font-bold">{{ $msg->template?->shortcut ?? '/ozel' }}</span>
                                        <span class="text-[11px] font-bold {{ $msg->status === 'sent' ? 'text-slate-500' : 'text-emerald-700' }}">
                                            {{ $msg->status === 'sent' ? '✅ Gönderildi' : '⏳ Gönderim Bekliyor' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-700 mt-1">{{ $msg->message_text }}</p>
                                </div>
                                <a href="{{ route('admin.whatsapp.send', $msg->id) }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shrink-0">
                                    💬 WhatsApp Business'ta Aç & Gönder →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Kabul Kriteri #4: Şablon Metnini ve Saatini Değiştirme -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-base font-extrabold mb-3">⚙️ WhatsApp Business Hazır Cevap Kısayolları & Otomatik Kurallar</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($whatsappTemplates as $tpl)
                            <form action="{{ route('admin.whatsapp.template.update', $tpl->id) }}" method="POST" class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2.5 text-xs">
                                @csrf
                                <div class="flex justify-between items-center">
                                    <span class="font-extrabold text-sm text-indigo-900">{{ $tpl->title }}</span>
                                    <input type="text" name="shortcut" value="{{ $tpl->shortcut }}" class="w-32 font-mono font-bold text-indigo-700 bg-white border rounded-lg px-2 py-1 text-right">
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="font-bold text-slate-600 block">Hatırlatma Saati</label>
                                        <input type="time" name="send_time" value="{{ substr($tpl->send_time, 0, 5) }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                    </div>
                                    <div>
                                        <label class="font-bold text-slate-600 block">Gün Farkı (-3 = 3 gün önce)</label>
                                        <input type="number" name="days_offset" value="{{ $tpl->days_offset }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="font-bold text-slate-600 block mb-1">Mesaj Şablonu ({veli_adi}, {ogrenci_adi}, {tutar}, {vade})</label>
                                    <textarea name="message_template" rows="3" class="w-full border rounded-xl p-2.5 bg-white text-xs">{{ $tpl->message_template }}</textarea>
                                </div>
                                <div class="flex justify-between items-center pt-1">
                                    <label class="flex items-center gap-1.5 font-bold">
                                        <input type="checkbox" name="is_active" value="1" {{ $tpl->is_active ? 'checked' : '' }}> Kural Aktif
                                    </label>
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">💾 Şablonu & Saati Kaydet</button>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 12 & 13. ETKİNLİKLER VE GELİŞİM KARNESİ    -->
            <!-- ========================================== -->
            @if($activeTab === 'etkinlikler' || $activeTab === 'gelisim')
                <div class="grid lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-lg font-extrabold mb-3">📈 Yeni Dönem Gelişim Karnesi Oluştur</h3>
                        <form action="{{ route('admin.development.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="font-bold block mb-1">Sporcu Seçin</label>
                                <select name="student_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                    @foreach($students as $st)
                                        <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->group?->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">Dönem Başlığı</label>
                                    <input type="text" name="term_label" value="2026 Kış Dönemi Karnesi" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Mevcut Seviye</label>
                                    <input type="text" name="current_level" value="Artistik Seviye 2" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div class="grid grid-cols-5 gap-1.5">
                                <div><label class="font-bold block">Esneklik</label><input type="number" name="flexibility_score" value="90" class="w-full border rounded-lg p-1.5"></div>
                                <div><label class="font-bold block">Kuvvet</label><input type="number" name="strength_score" value="85" class="w-full border rounded-lg p-1.5"></div>
                                <div><label class="font-bold block">Denge</label><input type="number" name="balance_score" value="88" class="w-full border rounded-lg p-1.5"></div>
                                <div><label class="font-bold block">Koord.</label><input type="number" name="coordination_score" value="92" class="w-full border rounded-lg p-1.5"></div>
                                <div><label class="font-bold block">Disiplin</label><input type="number" name="discipline_score" value="95" class="w-full border rounded-lg p-1.5"></div>
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Antrenör Değerlendirme Notu</label>
                                <textarea name="coach_comment" rows="3" class="w-full border rounded-xl p-2.5">Sporcumuz dönem boyunca tüm temel cimnastik becerilerinde üstün gayret göstermiştir.</textarea>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-bold">🏅 Gelişim Karnesini Yayınla</button>
                        </form>
                    </div>

                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-3">🎪 Etkinlikler, Gösteriler ve Yaz Okulu</h3>
                            @foreach($events as $ev)
                                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex justify-between items-center">
                                    <div>
                                        <span class="text-xs font-bold text-indigo-600">{{ $ev->event_date->format('d.m.Y') }} · {{ $ev->location }}</span>
                                        <h4 class="font-extrabold text-sm">{{ $ev->title }}</h4>
                                        <p class="text-xs text-slate-600">{{ $ev->description }} (Katılımcı: {{ $ev->participants->count() }}/{{ $ev->quota }})</p>
                                    </div>
                                    <span class="font-extrabold text-indigo-700 text-sm">{{ number_format($ev->fee, 0, ',', '.') }} ₺</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-3">🏅 Yayınlanan Gelişim Karneleri</h3>
                            @foreach($developmentRecords as $dev)
                                <div class="p-3.5 rounded-xl border border-slate-200 mb-2.5 text-xs">
                                    <div class="flex justify-between font-bold">
                                        <span>{{ $dev->student?->full_name }} — {{ $dev->term_label }}</span>
                                        <span class="text-indigo-600">{{ $dev->current_level }}</span>
                                    </div>
                                    <p class="text-slate-600 mt-1">Esneklik: {{ $dev->flexibility_score }} · Kuvvet: {{ $dev->strength_score }} · Denge: {{ $dev->balance_score }} · "{{ $dev->coach_comment }}"</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 14. RAPORLAR VE EXCEL ÇIKTISI              -->
            <!-- ========================================== -->
            @if($activeTab === 'raporlar' && $currentRole === 'super_admin')
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #7</span>
                        <h2 class="text-2xl font-extrabold">📑 Finansal Raporlar, Yaşlandırma ve Excel İndirme</h2>
                    </div>
                    <a href="{{ route('admin.reports.csv') }}" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold shadow">
                        📥 Aylık Gelir-Gider Raporunu Excel (CSV) Olarak İndir
                    </a>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500">0 - 30 Gün Geciken Alacaklar</span>
                        <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($agingBuckets['0_30'], 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500">31 - 60 Gün Geciken Alacaklar</span>
                        <div class="text-2xl font-extrabold text-orange-600 mt-1">{{ number_format($agingBuckets['31_60'], 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500">61+ Gün Kritik Gecikenler</span>
                        <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($agingBuckets['61_plus'], 2, ',', '.') }} ₺</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-extrabold text-base mb-3">📊 Branş Bazlı Öğrenci ve Doluluk Dağılımı</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        @foreach($branches as $b)
                            <div class="p-4 rounded-xl bg-slate-50 border text-xs">
                                <span class="text-xl">{{ $b->icon }}</span>
                                <div class="font-extrabold mt-1">{{ $b->name }}</div>
                                <div class="text-indigo-600 font-bold mt-1">{{ $b->students_count }} Kayıtlı Sporcu</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 15. TAM KAPSAMLI SİTE İÇERİĞİ (CMS STÜDYOSU) -->
            <!-- ========================================== -->
            @if($activeTab === 'site_icerigi')
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #6 · Tam Kapsamlı Görsel & Metinsel CMS Stüdyosu</span>
                        <h2 class="text-2xl font-extrabold">🌐 Web Sitesi (Frontend) Tam İçerik Yönetim Merkezi</h2>
                        <p class="text-xs text-slate-500">Ana sayfadaki tüm başlıkları, butonları, istatistikleri, branşları, paket fiyatlarını, antrenör vitrinini, SSS, duyuru ve fotoğraf galerisini buradan yönetin.</p>
                    </div>
                    <a href="{{ route('public.home') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold shadow">
                        👁️ Canlı Web Sitesini Görüntüle ↗
                    </a>
                </div>

                <!-- 1. GENEL SİTE METİNLERİ, MANŞET (HERO), İSTATİSTİKLER, MENÜ & GÖRÜNÜRLÜK AYARLARI -->
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs">
                    @csrf
                    <div class="grid lg:grid-cols-12 gap-6">
                        <!-- Sol: Kurumsal Kimlik, İletişim, Menü & Bölüm Görünürlükleri -->
                        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                            <h3 class="text-base font-extrabold text-indigo-950 border-b pb-2">🏢 1. Kurumsal Kimlik, Logo, Slogan & İletişim</h3>
                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Kulüp Adı (Üst Bar & Footer)</label>
                                    <input type="text" name="club_name" value="{{ $settings['club_name'] ?? 'Tekirdağ Enerjik Cimnastik' }}" class="w-full border rounded-xl px-3 py-2 font-bold">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Logo Altı Slogan</label>
                                    <input type="text" name="club_slogan" value="{{ $settings['club_slogan'] ?? '🟣 Enerjik ⚡️ Mutlu 🤩 Güçlü 💪 Çocuklar' }}" class="w-full border rounded-xl px-3 py-2 font-bold text-purple-700">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Logo Görsel Adresi (URL)</label>
                                    <input type="text" name="logo_url" value="{{ $settings['logo_url'] ?? '/icons/logo.jpg' }}" class="w-full border rounded-xl px-3 py-2 font-mono">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Tarayıcı Sekme Başlığı (SEO Title)</label>
                                    <input type="text" name="seo_title" value="{{ $settings['seo_title'] ?? 'Tekirdağ Enerjik Cimnastik Spor Kulübü' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Telefon Numarası</label>
                                    <input type="text" name="phone" value="{{ $settings['phone'] ?? '0538 899 31 59' }}" class="w-full border rounded-xl px-3 py-2 font-bold">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">WhatsApp Numarası (905...)</label>
                                    <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '905388993159' }}" class="w-full border rounded-xl px-3 py-2 font-mono">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Instagram Kullanıcı Adı</label>
                                    <input type="text" name="instagram_handle" value="{{ $settings['instagram_handle'] ?? '@enerjik.cimnastik' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Instagram Profil Linki</label>
                                    <input type="text" name="instagram_url" value="{{ $settings['instagram_url'] ?? 'https://www.instagram.com/enerjik.cimnastik/' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Şehir / Bölge (Yerel SEO)</label>
                                    <input type="text" name="city_district" value="{{ $settings['city_district'] ?? 'Tekirdağ / Süleymanpaşa' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Açık Adres</label>
                                    <input type="text" name="address" value="{{ $settings['address'] ?? 'Hürriyet Mah. Süleymanpaşa / Tekirdağ' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div>
                                <label class="font-bold block mb-1">Kulüp IBAN Numarası (Veli Portalı & Aidat Ekranı İçin)</label>
                                <input type="text" name="iban_number" value="{{ $settings['iban_number'] ?? '' }}" class="w-full border rounded-xl px-3 py-2 font-mono">
                            </div>

                            <!-- Bölüm Görünürlük Kontrolleri (Aç / Kapat) -->
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                                <span class="font-extrabold block text-slate-800">👁️ Ana Sayfa Bölümlerini Göster / Gizle</span>
                                <div class="grid sm:grid-cols-2 gap-2">
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>📅 Ders Programı Bölümü</span>
                                        <select name="show_schedule_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_schedule_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_schedule_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>🏷️ Paket Fiyatları Bölümü</span>
                                        <select name="show_prices_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_prices_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_prices_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>🏅 Antrenörler Bölümü</span>
                                        <select name="show_coaches_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_coaches_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_coaches_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>📸 Galeri & ❓ SSS</span>
                                        <select name="show_gallery_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_gallery_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_gallery_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Sağ: Manşet (Hero), İstatistik Kutuları, Bölüm Başlıkları & KVKK -->
                        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                            <h3 class="text-base font-extrabold text-indigo-950 border-b pb-2">✨ 2. Ana Sayfa Manşet (Hero), İstatistikler & Başlıklar</h3>
                            <div>
                                <label class="font-bold block mb-1">Manşet Üst Rozet Yazısı</label>
                                <input type="text" name="hero_badge" value="{{ $settings['hero_badge'] ?? '🏆 3-16 Yaş Çocuk & Yetişkin Cimnastik Okulu' }}" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Ana Manşet Başlığı (H1)</label>
                                <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? '' }}" class="w-full border rounded-xl px-3 py-2 font-bold">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Ana Manşet Alt Açıklama</label>
                                <textarea name="hero_subtitle" rows="2" class="w-full border rounded-xl p-2.5">{{ $settings['hero_subtitle'] ?? '' }}</textarea>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Manşet 1. Buton Yazısı</label>
                                    <input type="text" name="hero_btn_primary" value="{{ $settings['hero_btn_primary'] ?? '🤸 Ücretsiz Deneme Dersi Randevusu Al' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Manşet 2. Buton Yazısı</label>
                                    <input type="text" name="hero_btn_secondary" value="{{ $settings['hero_btn_secondary'] ?? '📅 Haftalık Ders Programını Gör' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <!-- 3 İstatistik Kutusu -->
                            <div class="p-3 rounded-2xl bg-indigo-50/60 border border-indigo-100 space-y-2">
                                <span class="font-extrabold block text-indigo-950">📊 Manşet Altı 3 İstatistik Kutusu</span>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <input type="text" name="stat_1_val" value="{{ $settings['stat_1_val'] ?? 'Maks. 12' }}" class="w-full border rounded-lg px-2 py-1 font-extrabold text-indigo-700 mb-1">
                                        <input type="text" name="stat_1_label" value="{{ $settings['stat_1_label'] ?? 'Kişilik Butik Gruplar' }}" class="w-full border rounded-lg px-2 py-1 text-[11px]">
                                    </div>
                                    <div>
                                        <input type="text" name="stat_2_val" value="{{ $settings['stat_2_val'] ?? '%100' }}" class="w-full border rounded-lg px-2 py-1 font-extrabold text-emerald-700 mb-1">
                                        <input type="text" name="stat_2_label" value="{{ $settings['stat_2_label'] ?? 'Dijital Yoklama & Gelişim Karnesi' }}" class="w-full border rounded-lg px-2 py-1 text-[11px]">
                                    </div>
                                    <div>
                                        <input type="text" name="stat_3_val" value="{{ $settings['stat_3_val'] ?? '%15' }}" class="w-full border rounded-lg px-2 py-1 font-extrabold text-rose-700 mb-1">
                                        <input type="text" name="stat_3_label" value="{{ $settings['stat_3_label'] ?? 'Kardeş & Dönem İndirimi' }}" class="w-full border rounded-lg px-2 py-1 text-[11px]">
                                    </div>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Branşlar Bölümü Başlığı</label>
                                    <input type="text" name="section_branches_title" value="{{ $settings['section_branches_title'] ?? 'Cimnastik Branşlarımız' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Paketler Bölümü Başlığı</label>
                                    <input type="text" name="section_packages_title" value="{{ $settings['section_packages_title'] ?? 'Eğitim Paketleri ve İndirim Kuralları' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div>
                                <label class="font-bold block mb-1">Alt Bilgi (Footer) KVKK Aydınlatma Metni</label>
                                <textarea name="kvkk_text" rows="2" class="w-full border rounded-xl p-2.5">{{ $settings['kvkk_text'] ?? '' }}</textarea>
                            </div>

                            <button type="submit" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold shadow-lg shadow-indigo-600/20">
                                💾 Tüm Site Metinlerini, Görünürlükleri ve Manşeti Kaydet
                            </button>
                        </div>
                    </div>
                </form>

                <!-- 3. SİTEDEKİ CİMNASTİK BRANŞ KARTLARINI YÖNET (EKLE / DÜZENLE / SİL) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                    <div class="flex flex-wrap justify-between items-center gap-2 border-b pb-3">
                        <div>
                            <h3 class="text-base font-extrabold">🤸 3. Ana Sayfa Cimnastik Branş Kartları Yönetimi ({{ $branches->count() }} Branş)</h3>
                            <p class="text-[11px] text-slate-500">Sitede görünen branşların ikonunu, yaş aralığını, açıklamasını ve kazanımlarını düzenleyin veya yeni branş ekleyin.</p>
                        </div>
                    </div>

                    <!-- Yeni Branş Ekleme Formu -->
                    <form action="{{ route('admin.branches.store') }}" method="POST" class="p-3.5 rounded-2xl bg-indigo-50/60 border border-indigo-200 grid sm:grid-cols-6 gap-2.5 text-xs items-end">
                        @csrf
                        <div>
                            <label class="font-bold block mb-1">İkon</label>
                            <input type="text" name="icon" value="🤸" class="w-full border rounded-xl px-3 py-2 bg-white text-center text-base">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="font-bold block mb-1">Yeni Branş Adı *</label>
                            <input type="text" name="name" required placeholder="Ör: Parkur & Akrobasi" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Yaş Aralığı</label>
                            <input type="text" name="age_range" value="6-14 Yaş" class="w-full border rounded-xl px-3 py-2 bg-white">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Seviye</label>
                            <input type="text" name="level" value="Tüm Seviyeler" class="w-full border rounded-xl px-3 py-2 bg-white">
                        </div>
                        <div>
                            <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">➕ Branş Ekle</button>
                        </div>
                        <div class="sm:col-span-3">
                            <input type="text" name="description" placeholder="Branş açıklaması..." class="w-full border rounded-xl px-3 py-1.5 bg-white">
                        </div>
                        <div class="sm:col-span-3">
                            <input type="text" name="benefits" placeholder="Kazanımlar (Ör: Çeviklik, Denge, Özgüven)..." class="w-full border rounded-xl px-3 py-1.5 bg-white">
                        </div>
                    </form>

                    <!-- Mevcut Branşları Düzenle / Sil -->
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($branches as $b)
                            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-2.5 text-xs">
                                <form action="{{ route('admin.branches.update', $b->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <div class="grid grid-cols-6 gap-2">
                                        <input type="text" name="icon" value="{{ $b->icon }}" class="border rounded-xl px-2 py-1.5 bg-white text-center text-base font-bold">
                                        <input type="text" name="name" value="{{ $b->name }}" class="col-span-3 border rounded-xl px-3 py-1.5 bg-white font-extrabold">
                                        <input type="text" name="age_range" value="{{ $b->age_range }}" class="col-span-2 border rounded-xl px-2.5 py-1.5 bg-white font-bold text-indigo-700">
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input type="text" name="level" value="{{ $b->level }}" placeholder="Seviye" class="col-span-2 border rounded-xl px-3 py-1.5 bg-white">
                                        <input type="number" name="sort_order" value="{{ $b->sort_order }}" placeholder="Sıra" class="border rounded-xl px-3 py-1.5 bg-white">
                                    </div>
                                    <textarea name="description" rows="2" class="w-full border rounded-xl p-2 bg-white">{{ $b->description }}</textarea>
                                    <input type="text" name="benefits" value="{{ $b->benefits }}" placeholder="Kazanımlar" class="w-full border rounded-xl px-3 py-1.5 bg-white">
                                    <div class="flex justify-between items-center pt-1">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white font-bold">💾 Branşı Güncelle</button>
                                    </div>
                                </form>
                                <form action="{{ route('admin.branches.destroy', $b->id) }}" method="POST" onsubmit="return confirm('{{ $b->name }} branşını silmek istediğinize emin misiniz?')" class="flex justify-end -mt-8">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-700 font-bold">🗑️ Sil</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 4. SİTEDEKİ EĞİTİM PAKETLERİ VE ÜCRET KARTLARINI YÖNET -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                    <h3 class="text-base font-extrabold border-b pb-2">🏷️ 4. Sitedeki Eğitim Paketleri ve Ücret Kartları ({{ $packages->count() }} Paket)</h3>

                    <!-- Yeni Paket Ekle -->
                    <form action="{{ route('admin.packages.store') }}" method="POST" class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-200 grid sm:grid-cols-5 gap-2.5 text-xs items-end">
                        @csrf
                        <div class="sm:col-span-2">
                            <label class="font-bold block mb-1">Paket Adı *</label>
                            <input type="text" name="name" required placeholder="Ör: Haftada 2 Ders Aylık Paket" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Ücret (₺) *</label>
                            <input type="number" name="price" required value="3200" class="w-full border rounded-xl px-3 py-2 bg-white font-extrabold text-emerald-700">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Ders Sayısı</label>
                            <input type="number" name="lessons_count" value="8" class="w-full border rounded-xl px-3 py-2 bg-white">
                        </div>
                        <div>
                            <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">➕ Paket Ekle</button>
                        </div>
                        <div class="sm:col-span-5">
                            <input type="text" name="description" placeholder="Paket alt açıklaması..." class="w-full border rounded-xl px-3 py-1.5 bg-white">
                        </div>
                    </form>

                    <div class="grid md:grid-cols-3 gap-4">
                        @foreach($packages as $pkg)
                            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2 text-xs">
                                <form action="{{ route('admin.packages.update', $pkg->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <input type="text" name="name" value="{{ $pkg->name }}" class="w-full border rounded-xl px-3 py-1.5 bg-white font-extrabold">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500">Fiyat (₺)</label>
                                            <input type="number" name="price" value="{{ (int) $pkg->price }}" class="w-full border rounded-xl px-3 py-1.5 bg-white font-extrabold text-indigo-700">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-slate-500">Ders Sayısı</label>
                                            <input type="number" name="lessons_count" value="{{ $pkg->lessons_count }}" class="w-full border rounded-xl px-3 py-1.5 bg-white">
                                        </div>
                                    </div>
                                    <textarea name="description" rows="2" class="w-full border rounded-xl p-2 bg-white">{{ $pkg->description }}</textarea>
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white font-bold">💾 Paketi Güncelle</button>
                                </form>
                                <form action="{{ route('admin.packages.destroy', $pkg->id) }}" method="POST" onsubmit="return confirm('Bu paketi silmek istediğinize emin misiniz?')" class="flex justify-end -mt-7">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-700 font-bold">🗑️ Sil</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 5. DUYURULAR & 6. SIKÇA SORULAN SORULAR (SSS) YÖNETİMİ -->
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Duyurular (Ekle / Düzenle / Sil) -->
                    <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <h3 class="text-base font-extrabold border-b pb-2">📢 5. Sitedeki ve Mobil Uygulamadaki Duyurular</h3>
                        <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-2.5 text-xs p-3.5 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                            @csrf
                            <input type="text" name="title" required placeholder="Yeni Duyuru Başlığı..." class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                            <textarea name="content" rows="2" required placeholder="Duyuru içeriği..." class="w-full border rounded-xl p-2.5 bg-white"></textarea>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold">📢 Yeni Duyuru Yayınla</button>
                        </form>

                        <div class="space-y-3">
                            @foreach($announcements as $ann)
                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                                    <form action="{{ route('admin.announcements.update', $ann->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <input type="text" name="title" value="{{ $ann->title }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                        <textarea name="content" rows="2" class="w-full border rounded-lg p-2 bg-white">{{ $ann->content }}</textarea>
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-slate-900 text-white font-bold">💾 Güncelle</button>
                                    </form>
                                    <form action="{{ route('admin.announcements.destroy', $ann->id) }}" method="POST" class="flex justify-end -mt-7">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold">🗑️ Sil</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- SSS / FAQ (Ekle / Düzenle / Sil) -->
                    <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <h3 class="text-base font-extrabold border-b pb-2">❓ 6. Sıkça Sorulan Sorular (SSS) Yönetimi</h3>
                        <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-2.5 text-xs p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200">
                            @csrf
                            <input type="text" name="question" required placeholder="Yeni Soru (Ör: Deneme dersi ücretli mi?)" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                            <textarea name="answer" rows="2" required placeholder="Cevap metni..." class="w-full border rounded-xl p-2.5 bg-white"></textarea>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-extrabold">➕ Yeni SSS Sorusu Ekle</button>
                        </form>

                        <div class="space-y-3">
                            @foreach($faqs as $faq)
                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                                    <form action="{{ route('admin.faqs.update', $faq->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <input type="text" name="question" value="{{ $faq->question }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                        <textarea name="answer" rows="2" class="w-full border rounded-lg p-2 bg-white">{{ $faq->answer }}</textarea>
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-slate-900 text-white font-bold">💾 Güncelle</button>
                                    </form>
                                    <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="flex justify-end -mt-7">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold">🗑️ Sil</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 7. SALON FOTOĞRAF GALERİSİ & 8. ANTRENÖR VİTRİN KARTLARI -->
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Galeri Yönetimi -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <h3 class="text-base font-extrabold border-b pb-2">📸 7. Salon Fotoğraf Galerisi (Ekle / Düzenle / Sil - KVKK Kontrollü)</h3>
                        <form action="{{ route('admin.gallery.store') }}" method="POST" class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 grid sm:grid-cols-2 gap-2.5 text-xs">
                            @csrf
                            <input type="text" name="title" required placeholder="Fotoğraf Başlığı" class="border rounded-xl px-3 py-2 bg-white font-bold">
                            <input type="text" name="category" value="Antrenman" placeholder="Kategori (Antrenman, Salon, Gösteri)" class="border rounded-xl px-3 py-2 bg-white">
                            <input type="url" name="image_url" required placeholder="Fotoğraf URL (https://...)" value="https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80" class="sm:col-span-2 border rounded-xl px-3 py-2 bg-white">
                            <select name="student_id" class="border rounded-xl px-3 py-2 bg-white">
                                <option value="">Genel Salon Fotoğrafı (Öğrenci Yok)</option>
                                @foreach($students as $st)
                                    <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->photo_consent ? '✅ Foto İzinli' : '🚫 KVKK Foto İzni YOK' }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="py-2 rounded-xl bg-indigo-600 text-white font-bold">📸 Galeriye Fotoğraf Ekle</button>
                        </form>

                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach($gallery as $item)
                                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-28 object-cover rounded-lg border">
                                    <form action="{{ route('admin.gallery.update', $item->id) }}" method="POST" class="space-y-1.5">
                                        @csrf
                                        <input type="text" name="title" value="{{ $item->title }}" class="w-full border rounded-lg px-2 py-1 bg-white font-bold">
                                        <input type="text" name="category" value="{{ $item->category }}" class="w-full border rounded-lg px-2 py-1 bg-white text-[11px]">
                                        <input type="url" name="image_url" value="{{ $item->image_url }}" class="w-full border rounded-lg px-2 py-1 bg-white text-[10px] font-mono">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-bold">💾 Kaydet</button>
                                    </form>
                                    <form action="{{ route('admin.gallery.destroy', $item->id) }}" method="POST" class="flex justify-end -mt-6">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold">🗑️ Sil</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Antrenör Vitrin Bilgileri (Sitede Görünen Uzmanlık & Bio) -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4 h-fit">
                        <h3 class="text-base font-extrabold border-b pb-2">🏅 8. Sitede Görünen Antrenör Tanıtım Kartları</h3>
                        <p class="text-[11px] text-slate-500">Ana sayfadaki "Federasyon Belgeli Antrenörlerimiz" bölümünde yer alan unvan ve özgeçmiş yazılarını düzenleyin.</p>
                        <div class="space-y-3">
                            @foreach($staff->where('role', 'coach') as $coach)
                                <form action="{{ route('admin.coaches.profile', $coach->id) }}" method="POST" class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-2 text-xs">
                                    @csrf
                                    <div>
                                        <label class="font-bold block mb-0.5">Antrenör Adı Soyadı</label>
                                        <input type="text" name="name" value="{{ $coach->name }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-extrabold">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-0.5">Uzmanlık / Kademe Unvanı</label>
                                        <input type="text" name="specialty" value="{{ $coach->specialty }}" placeholder="Ör: 2. Kademe Artistik Cimnastik Antrenörü" class="w-full border rounded-lg px-2.5 py-1.5 bg-white text-indigo-700 font-bold">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-0.5">Sitede Görünen Özgeçmiş / Tanıtım Metni</label>
                                        <textarea name="bio" rows="2" class="w-full border rounded-lg p-2 bg-white">{{ $coach->bio }}</textarea>
                                    </div>
                                    <button type="submit" class="w-full py-2 rounded-lg bg-slate-900 hover:bg-indigo-600 text-white font-bold">💾 Antrenör Vitrin Kartını Güncelle</button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- ========================================== -->
            <!-- 16. AYARLAR, YEDEK İNDİRME VE İŞLEM GÜNLÜĞÜ -->
            <!-- ========================================== -->
            @if($activeTab === 'ayarlar' && $currentRole === 'super_admin')
                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #8</span>
                        <h2 class="text-2xl font-extrabold">⚙️ Sistem Ayarları, Veritabanı Yedeği ve İşlem Günlüğü</h2>
                    </div>
                    <a href="{{ route('admin.backup.download') }}" class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold shadow">
                        💾 Tam Veritabanı Yedeğini İndir (.JSON)
                    </a>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-base font-extrabold mb-3">🛡️ İşlem Günlüğü (Audit Logs - Silme Yerine Arşivleme & Kritik İşlem Kayıtları)</h3>
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                            <tr>
                                <th class="p-2.5">Tarih / Saat</th>
                                <th class="p-2.5">İşlem Kodu</th>
                                <th class="p-2.5">Açıklama</th>
                                <th class="p-2.5">IP Adresi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($auditLogs as $log)
                                <tr>
                                    <td class="p-2.5 font-mono text-[11px]">{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                                    <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-slate-100 font-mono font-bold">{{ $log->action }}</span></td>
                                    <td class="p-2.5 font-semibold">{{ $log->description }}</td>
                                    <td class="p-2.5 font-mono text-[11px] text-slate-500">{{ $log->ip_address }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </main>
    </div>

</body>
</html>
