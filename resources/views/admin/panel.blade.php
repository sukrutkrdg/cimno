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
            <!-- Rol Görünüm Seçici (Rol Bazlı Yetki Testi) -->
            <form method="GET" action="{{ route('admin.index') }}" class="flex items-center gap-1.5">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <span class="text-xs text-slate-400">Aktif Rol:</span>
                <select name="role_view" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 text-amber-300 text-xs rounded-lg px-2.5 py-1.5 font-bold">
                    <option value="super_admin" {{ $currentRole === 'super_admin' ? 'selected' : '' }}>👑 Süper Yönetici (Salon Sahibi - Tüm Yetkiler)</option>
                    <option value="secretary" {{ $currentRole === 'secretary' ? 'selected' : '' }}>🗂️ Sekreter (Ön Büro - Gider/Kâr Gizli)</option>
                    <option value="coach" {{ $currentRole === 'coach' ? 'selected' : '' }}>🎽 Antrenör (Yalnız Gruplar, Yoklama & Gelişim)</option>
                </select>
            </form>
            <a href="{{ route('public.home') }}" target="_blank" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700">🌐 Veli Sitesi</a>
            <a href="{{ route('public.mobile.pwa') }}" target="_blank" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-indigo-700 hover:bg-indigo-600">📲 Mobil Uygulama (PWA)</a>
            <a href="{{ route('public.qr.poster') }}" target="_blank" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 hover:bg-amber-400">🏁 QR Kısayol Afişi</a>
        </div>
    </header>

    <div class="flex-1 flex flex-col lg:flex-row">
        <!-- Sol Menü: 16 Modül -->
        <aside class="w-full lg:w-64 bg-white border-b lg:border-b-0 lg:border-r border-slate-200 p-3 shrink-0">
            <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 px-3 py-1.5">16 Yönetim Modülü</div>
            @php
                $navItems = [
                    ['id' => 'pano', 'icon' => '📊', 'label' => '1. Pano (Özet)', 'roles' => ['super_admin', 'secretary', 'coach']],
                    ['id' => 'adaylar', 'icon' => '🎯', 'label' => '2. Adaylar (Ön Kayıt)', 'roles' => ['super_admin', 'secretary']],
                    ['id' => 'ogrenciler', 'icon' => '🤸', 'label' => '3. Öğrenciler & Kayıt', 'roles' => ['super_admin', 'secretary', 'coach']],
                    ['id' => 'gruplar', 'icon' => '🏅', 'label' => '4-5. Branş, Grup & Program', 'roles' => ['super_admin', 'secretary', 'coach']],
                    ['id' => 'yoklama', 'icon' => '✅', 'label' => '6. Tek Dokunuş Yoklama', 'roles' => ['super_admin', 'secretary', 'coach']],
                    ['id' => 'paketler', 'icon' => '🏷️', 'label' => '7. Paket & Toplu Fiyat', 'roles' => ['super_admin', 'secretary']],
                    ['id' => 'aidat', 'icon' => '💳', 'label' => '8. Aidat & Tahsilat', 'roles' => ['super_admin', 'secretary']],
                    ['id' => 'kasa', 'icon' => '🏦', 'label' => '9. Gelir-Gider & Kasa', 'roles' => ['super_admin']],
                    ['id' => 'personel', 'icon' => '👥', 'label' => '10. Personel & Hakediş', 'roles' => ['super_admin']],
                    ['id' => 'mesajlar', 'icon' => '💬', 'label' => '11. WhatsApp Hazır Cevap', 'roles' => ['super_admin', 'secretary']],
                    ['id' => 'etkinlikler', 'icon' => '🎪', 'label' => '12. Etkinlik & Yaz Okulu', 'roles' => ['super_admin', 'secretary', 'coach']],
                    ['id' => 'gelisim', 'icon' => '📈', 'label' => '13. Gelişim Karnesi', 'roles' => ['super_admin', 'secretary', 'coach']],
                    ['id' => 'raporlar', 'icon' => '📑', 'label' => '14. Raporlar & Excel', 'roles' => ['super_admin']],
                    ['id' => 'site_icerigi', 'icon' => '🌐', 'label' => '15. Site İçeriği (CMS)', 'roles' => ['super_admin', 'secretary']],
                    ['id' => 'ayarlar', 'icon' => '⚙️', 'label' => '16. Ayarlar, Yedek & KVKK', 'roles' => ['super_admin']],
                ];
            @endphp
            <nav class="flex lg:flex-col gap-1 overflow-x-auto pb-2 lg:pb-0">
                @foreach($navItems as $item)
                    @if(in_array($currentRole, $item['roles']))
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
                                            <form action="{{ route('admin.students.status', $st->id) }}" method="POST">
                                                @csrf
                                                <select name="status" onchange="this.form.submit()" class="text-[11px] font-bold rounded-lg border px-2 py-1 {{ $st->status === 'active' ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-800' }}">
                                                    <option value="active" {{ $st->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                                    <option value="frozen" {{ $st->status === 'frozen' ? 'selected' : '' }}>Donduruldu</option>
                                                    <option value="left" {{ $st->status === 'left' ? 'selected' : '' }}>Ayrıldı (Arşiv)</option>
                                                </select>
                                            </form>
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
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #5</span>
                        <h3 class="text-lg font-extrabold mb-3">👤 Yeni Sekreter / Antrenör Hesabı Aç</h3>
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
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">
                                ➕ Personel Hesabını Aç
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-3">👥 Kullanıcılar ve Yetki Durumu (Hesap Aç/Kapat)</h3>
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                    <tr>
                                        <th class="p-2.5">Ad Soyad</th>
                                        <th class="p-2.5">Rol</th>
                                        <th class="p-2.5">Durum</th>
                                        <th class="p-2.5 text-right">Hesap İşlemi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($staff as $u)
                                        <tr>
                                            <td class="p-2.5 font-bold">{{ $u->name }} <span class="block text-[11px] text-slate-500">{{ $u->phone }}</span></td>
                                            <td class="p-2.5">{{ $u->roleLabel() }}</td>
                                            <td class="p-2.5">
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $u->is_active ? 'Aktif' : 'Kapatıldı' }}
                                                </span>
                                            </td>
                                            <td class="p-2.5 text-right">
                                                <form action="{{ route('admin.staff.toggle', $u->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 rounded-lg font-bold {{ $u->is_active ? 'bg-rose-100 text-rose-700 hover:bg-rose-200' : 'bg-emerald-100 text-emerald-700' }}">
                                                        {{ $u->is_active ? '🔒 Hesabı Kapat' : '🔓 Hesabı Aç' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
            <!-- 15. SİTE İÇERİĞİ (CMS)                     -->
            <!-- ========================================== -->
            @if($activeTab === 'site_icerigi')
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Kabul Kriteri #6: Sitedeki metin, duyuru ve fotoğrafı değiştirme -->
                    <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #6 · Yazılımcıya İhtiyaç Duymadan Yönetim</span>
                        <h3 class="text-lg font-extrabold mb-3">🌐 Web Sitesi Metinleri, İletişim ve SEO Ayarları</h3>
                        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div>
                                <label class="font-bold block mb-1">Kulüp Adı</label>
                                <input type="text" name="club_name" value="{{ $settings['club_name'] ?? '' }}" class="w-full border rounded-xl px-3 py-2 font-bold">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Ana Sayfa Manşet Başlığı</label>
                                <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? '' }}" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Ana Sayfa Alt Açıklama</label>
                                <textarea name="hero_subtitle" rows="2" class="w-full border rounded-xl p-2.5">{{ $settings['hero_subtitle'] ?? '' }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">Telefon</label>
                                    <input type="text" name="phone" value="{{ $settings['phone'] ?? '' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">WhatsApp Numarası (905...)</label>
                                    <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Yerel SEO Bölge / Şehir</label>
                                <input type="text" name="city_district" value="{{ $settings['city_district'] ?? '' }}" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Kulüp IBAN Numarası</label>
                                <input type="text" name="iban_number" value="{{ $settings['iban_number'] ?? '' }}" class="w-full border rounded-xl px-3 py-2 font-mono">
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-bold">💾 Site İçeriğini Güncelle</button>
                        </form>
                    </div>

                    <div class="lg:col-span-6 space-y-6">
                        <!-- Yeni Duyuru Ekle -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-3">📢 Sitede ve Mobil Uygulamada Yeni Duyuru Yayınla</h3>
                            <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-3 text-xs">
                                @csrf
                                <input type="text" name="title" required placeholder="Duyuru Başlığı..." class="w-full border rounded-xl px-3 py-2 font-bold">
                                <textarea name="content" rows="2" required placeholder="Duyuru metni..." class="w-full border rounded-xl p-2.5"></textarea>
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 text-white font-bold">📢 Duyuruyu Yayınla</button>
                            </form>
                        </div>

                        <!-- KVKK Fotoğraf Onay Kontrollü Galeri Ekleme -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-1">📸 Galeriye Fotoğraf Ekle (KVKK İzin Kontrollü)</h3>
                            <p class="text-[11px] text-slate-500 mb-3">Fotoğraf paylaşım onayı vermeyen öğrenci seçilirse sistem otomatik olarak uyarır ve engeller.</p>
                            <form action="{{ route('admin.gallery.store') }}" method="POST" class="space-y-3 text-xs">
                                @csrf
                                <input type="text" name="title" required placeholder="Fotoğraf Başlığı" class="w-full border rounded-xl px-3 py-2">
                                <input type="url" name="image_url" required value="https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=800&q=80" class="w-full border rounded-xl px-3 py-2">
                                <div>
                                    <label class="font-bold block mb-1">Fotoğraftaki Öğrenci (KVKK Kontrolü İçin)</label>
                                    <select name="student_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        <option value="">Genel Salon Fotoğrafı</option>
                                        @foreach($students as $st)
                                            <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->photo_consent ? '✅ Foto İzinli' : '🚫 KVKK Foto İzni YOK' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-bold">📸 Galeriye Ekle</button>
                            </form>
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
