                @if($currentRole === 'super_admin')
                    <!-- Süper Yönetici İçin: Kayıt Formundaki Seçenekleri (Grup, Eğitim Programı/Paket, Branş, İndirim) Anında Ekleme / Güncelleme / Silme -->
                    <details class="bg-indigo-950 text-white rounded-2xl border border-indigo-800 p-4 shadow-sm">
                        <summary class="cursor-pointer font-extrabold text-xs sm:text-sm flex flex-wrap items-center justify-between gap-2">
                            <span>🎛️ Kayıt Formu Dinamik Seçenek Yönetimi (Yeni Grup, Eğitim Programı/Paket, Branş veya İndirim Ekle · Güncelle · Sil)</span>
                            <span class="px-2.5 py-1 rounded-lg bg-amber-400 text-slate-950 text-[11px] font-extrabold">⚡ Tıkla Aç / Kapat</span>
                        </summary>
                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4 pt-4 border-t border-indigo-800/80 text-xs text-slate-900">
                            <!-- 1. Hızlı Grup & Ders Programı Ekle/Sil -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1.5">👥 Grup Seçenekleri ({{ $groups->count() }})</div>
                                <form action="{{ route('admin.groups.store') }}" method="POST" class="space-y-1.5">
                                    @csrf
                                    <input type="hidden" name="return_tab" value="ogrenciler">
                                    <input type="text" name="name" required placeholder="Yeni Grup Adı (Ör: D Grubu)" class="w-full border rounded-lg px-2.5 py-1.5">
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <select name="branch_id" class="border rounded-lg px-2 py-1.5 bg-white">
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                        <select name="coach_id" class="border rounded-lg px-2 py-1.5 bg-white">
                                            @foreach($staff->where('role', 'coach') as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1">
                                        <input type="text" name="age_group" value="5-8 Yaş" placeholder="Yaş" class="border rounded-lg px-2 py-1">
                                        <input type="text" name="level" value="Başlangıç" placeholder="Seviye" class="border rounded-lg px-2 py-1">
                                        <input type="number" name="capacity" value="12" placeholder="Kişi" class="border rounded-lg px-2 py-1">
                                    </div>
                                    <div class="grid grid-cols-3 gap-1">
                                        <select name="day_of_week" class="border rounded-lg px-1.5 py-1 bg-white">
                                            @foreach(['Cumartesi','Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma'] as $d)
                                                <option value="{{ $d }}">{{ $d }}</option>
                                            @endforeach
                                        </select>
                                        <input type="time" name="start_time" value="10:00" class="border rounded-lg px-1.5 py-1">
                                        <input type="time" name="end_time" value="11:00" class="border rounded-lg px-1.5 py-1">
                                    </div>
                                    <input type="hidden" name="area" value="{{ $hallAreas[0] ?? 'Ana Minder' }}">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">➕ Yeni Grup Ekle</button>
                                </form>
                                <div class="max-h-28 overflow-y-auto space-y-1 pt-1 border-t">
                                    @foreach($groups as $g)
                                        <div class="flex justify-between items-center text-[11px] bg-slate-50 px-2 py-1 rounded">
                                            <span class="truncate font-semibold">{{ $g->name }}</span>
                                            <form action="{{ route('admin.groups.destroy', $g->id) }}" method="POST" onsubmit="return confirm('{{ $g->name }} grubunu silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 2. Hızlı Eğitim Programı / Paket Ekle & Güncelle -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1.5">📦 Eğitim Programı / Paketler ({{ $packages->count() }})</div>
                                <form action="{{ route('admin.packages.store') }}" method="POST" class="space-y-1.5">
                                    @csrf
                                    <input type="hidden" name="return_tab" value="ogrenciler">
                                    <input type="text" name="name" required placeholder="Yeni Paket Adı (Ör: Haftada 3 Gün)" class="w-full border rounded-lg px-2.5 py-1.5">
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <input type="number" name="price" required placeholder="Fiyat (₺)" value="4200" class="border rounded-lg px-2 py-1.5 font-bold">
                                        <input type="number" name="lessons_count" required placeholder="Ders Sayısı" value="12" class="border rounded-lg px-2 py-1.5">
                                    </div>
                                    <input type="hidden" name="duration_months" value="1">
                                    <input type="hidden" name="registration_fee" value="0">
                                    <input type="text" name="description" placeholder="Kısa açıklama..." class="w-full border rounded-lg px-2.5 py-1">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold">➕ Yeni Paket Ekle</button>
                                </form>
                                <div class="max-h-28 overflow-y-auto space-y-1 pt-1 border-t">
                                    @foreach($packages as $p)
                                        <div class="flex justify-between items-center text-[11px] bg-slate-50 px-2 py-1 rounded">
                                            <span class="truncate font-semibold">{{ $p->name }} ({{ number_format($p->price, 0, ',', '.') }} ₺)</span>
                                            <form action="{{ route('admin.packages.destroy', $p->id) }}" method="POST" onsubmit="return confirm('{{ $p->name }} paketini silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 3. Hızlı Branş Ekle & Sil -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1.5">🤸 Branş Seçenekleri ({{ $branches->count() }})</div>
                                <form action="{{ route('admin.branches.store') }}" method="POST" class="space-y-1.5">
                                    @csrf
                                    <input type="hidden" name="return_tab" value="ogrenciler">
                                    <div class="grid grid-cols-4 gap-1">
                                        <input type="text" name="icon" value="🤸" class="border rounded-lg px-2 py-1.5 text-center">
                                        <input type="text" name="name" required placeholder="Yeni Branş Adı" class="col-span-3 border rounded-lg px-2.5 py-1.5">
                                    </div>
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <input type="text" name="age_range" value="4-12 Yaş" placeholder="Yaş Aralığı" class="border rounded-lg px-2 py-1">
                                        <input type="text" name="level" value="Tüm Seviyeler" placeholder="Seviye" class="border rounded-lg px-2 py-1">
                                    </div>
                                    <input type="hidden" name="description" value="Türkiye Cimnastik Federasyonu müfredatına uygun branş eğitimi.">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">➕ Yeni Branş Ekle</button>
                                </form>
                                <div class="max-h-28 overflow-y-auto space-y-1 pt-1 border-t">
                                    @foreach($branches as $b)
                                        <div class="flex justify-between items-center text-[11px] bg-slate-50 px-2 py-1 rounded">
                                            <span class="truncate font-semibold">{{ $b->icon }} {{ $b->name }}</span>
                                            <form action="{{ route('admin.branches.destroy', $b->id) }}" method="POST" onsubmit="return confirm('{{ $b->name }} branşını silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 4. Hızlı İndirim Kuralı Ekle & Sil -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1.5">🏷️ İndirim Seçenekleri ({{ $discountRules->count() }})</div>
                                <form action="{{ route('admin.discounts.store') }}" method="POST" class="space-y-1.5">
                                    @csrf
                                    <input type="hidden" name="return_tab" value="ogrenciler">
                                    <input type="text" name="name" required placeholder="İndirim Adı (Ör: Memur İndirimi)" class="w-full border rounded-lg px-2.5 py-1.5">
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <input type="number" step="0.1" name="percentage" required value="10" placeholder="Oran (%)" class="border rounded-lg px-2 py-1.5 font-bold">
                                        <select name="type" class="border rounded-lg px-2 py-1.5 bg-white">
                                            <option value="sibling">Kardeş</option>
                                            <option value="term">Dönem</option>
                                            <option value="custom">Özel</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold">➕ Yeni İndirim Ekle</button>
                                </form>
                                <div class="max-h-28 overflow-y-auto space-y-1 pt-1 border-t">
                                    @foreach($discountRules as $d)
                                        <div class="flex justify-between items-center text-[11px] bg-slate-50 px-2 py-1 rounded">
                                            <span class="truncate font-semibold">{{ $d->name }} (%{{ $d->percentage }})</span>
                                            <form action="{{ route('admin.discounts.destroy', $d->id) }}" method="POST" onsubmit="return confirm('{{ $d->name }} indirimini silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </details>
                @endif

                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Yeni Öğrenci Kaydı Formu -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm h-fit">
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Öğrenci Kayıt Merkezi</span>
                        <h3 class="text-lg font-extrabold mb-3">
                            {{ $currentRole === 'super_admin' ? '➕ Yeni Öğrenci Kaydı Aç & İlk Tahsilatı Gir' : '➕ Yeni Öğrenci Kaydı Aç' }}
                        </h3>
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
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="font-bold">Branş *</label>
                                        @if($currentRole === 'super_admin')
                                            <a href="{{ route('admin.index', ['tab' => 'gruplar', 'role_view' => $currentRole]) }}" class="text-[10px] text-indigo-600 font-bold underline">+ Branş Yönet</a>
                                        @endif
                                    </div>
                                    <select name="branch_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="font-bold">Grup Seçimi *</label>
                                        @if($currentRole === 'super_admin')
                                            <a href="{{ route('admin.index', ['tab' => 'gruplar', 'role_view' => $currentRole]) }}" class="text-[10px] text-indigo-600 font-bold underline">+ Grup Ekle/Düzenle</a>
                                        @endif
                                    </div>
                                    <select name="group_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($groups as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->active_students_count }}/{{ $g->capacity }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="font-bold">Eğitim Programı / Paket *</label>
                                        @if($currentRole === 'super_admin')
                                            <a href="{{ route('admin.index', ['tab' => 'paketler', 'role_view' => $currentRole]) }}" class="text-[10px] text-indigo-600 font-bold underline">+ Paket Ekle/Düzenle</a>
                                        @endif
                                    </div>
                                    <select name="package_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($packages as $p)
                                            <option value="{{ $p->id }}">
                                                {{ $p->name }}{{ $currentRole === 'super_admin' ? ' (' . number_format($p->price, 0, ',', '.') . ' ₺)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="font-bold">İndirim / Kardeş Durumu</label>
                                        @if($currentRole === 'super_admin')
                                            <a href="{{ route('admin.index', ['tab' => 'paketler', 'role_view' => $currentRole]) }}" class="text-[10px] text-indigo-600 font-bold underline">+ İndirim Yönet</a>
                                        @endif
                                    </div>
                                    <select name="discount_rule_id" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        <option value="">Standart Kayıt</option>
                                        @foreach($discountRules as $d)
                                            <option value="{{ $d->id }}">{{ $d->name }} (%{{ $d->percentage }})</option>
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
                                    <input type="text" name="parent_phone" required placeholder="532XXXXXXX (başında 0 olmadan 10 hane)" maxlength="15" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="font-bold block mb-1">İkinci Veli Adı Soyadı (Opsiyonel)</label>
                                    <input type="text" name="secondary_parent_name" placeholder="Ör: Ayşe Demir" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">İkinci Veli Telefon (Opsiyonel)</label>
                                    <input type="text" name="secondary_parent_phone" placeholder="532XXXXXXX (başında 0 olmadan 10 hane)" maxlength="15" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 -mt-1.5">İkinci veli girilirse, aynı PIN ile kendi telefon numarasıyla da veli portalına (📱 Mobil Uygulama) giriş yapıp öğrenciyi görüntüleyebilir.</p>
                            <div>
                                <label class="font-bold block mb-1">Sağlık Bilgisi & Rapor Notu (Özel Nitelikli Veri)</label>
                                <input type="text" name="health_notes" placeholder="Spor yapabilir raporu alındı, bilinen alerji yok." class="w-full border rounded-xl px-3 py-2">
                            </div>
                            @if($currentRole === 'super_admin')
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
                            @endif
                            <div class="flex flex-wrap gap-3 pt-1">
                                <label class="flex items-center gap-1.5"><input type="checkbox" name="kvkk_consent" value="1" checked> KVKK Onayı</label>
                                <label class="flex items-center gap-1.5"><input type="checkbox" name="health_data_consent" value="1" checked> Sağlık Verisi Rızası</label>
                                <label class="flex items-center gap-1.5"><input type="checkbox" name="photo_consent" value="1" checked> Fotoğraf Paylaşım Onayı</label>
                            </div>
                            <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow">
                                {{ $currentRole === 'super_admin' ? '✅ Kaydı Aç, Aidat Planını Oluştur & İlk Tahsilatı Kaydet' : '✅ Yeni Öğrenci Kaydını Aç' }}
                            </button>
                        </form>
                    </div>

                    <!-- Kayıtlı Öğrenciler Listesi -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm overflow-x-auto">
                        <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                            <h3 class="text-lg font-extrabold">📋 Kayıtlı Sporcular ({{ $students->count() }})</h3>
                            <div class="flex flex-wrap gap-1.5 text-[11px] font-bold" id="student-status-filters">
                                <button type="button" onclick="enerjikFilterStudents('all')" data-filter-btn="all" class="px-2.5 py-1 rounded-lg border">Tümü ({{ $students->count() }})</button>
                                <button type="button" onclick="enerjikFilterStudents('active')" data-filter-btn="active" class="px-2.5 py-1 rounded-lg border bg-emerald-50 text-emerald-800 border-emerald-200">✅ Aktif ({{ $students->where('status', 'active')->count() }})</button>
                                <button type="button" onclick="enerjikFilterStudents('waiting_list')" data-filter-btn="waiting_list" class="px-2.5 py-1 rounded-lg border bg-purple-50 text-purple-800 border-purple-200">⏳ Bekleme Listesi ({{ $students->where('status', 'waiting_list')->count() }})</button>
                                <button type="button" onclick="enerjikFilterStudents('frozen')" data-filter-btn="frozen" class="px-2.5 py-1 rounded-lg border bg-amber-50 text-amber-800 border-amber-200">⏸️ Donduruldu ({{ $students->where('status', 'frozen')->count() }})</button>
                                <button type="button" onclick="enerjikFilterStudents('left')" data-filter-btn="left" class="px-2.5 py-1 rounded-lg border bg-slate-100 text-slate-600 border-slate-200">📦 Ayrıldı ({{ $students->where('status', 'left')->count() }})</button>
                            </div>
                        </div>
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                <tr>
                                    <th class="p-2.5">Sporcu</th>
                                    <th class="p-2.5">Branş / Grup / Paket</th>
                                    <th class="p-2.5">Veli / İletişim</th>
                                    <th class="p-2.5">KVKK & Rapor</th>
                                    <th class="p-2.5">Durum & İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($students as $st)
                                    <tr data-student-status="{{ $st->status }}">
                                        <td class="p-2.5">
                                            <div class="font-extrabold text-slate-900">{{ $st->full_name }}</div>
                                            <div class="text-[11px] text-slate-500">Telafi: {{ $st->makeup_credits }} · Lisans: {{ $st->license_no ?? '-' }}</div>
                                        </td>
                                        <td class="p-2.5">
                                            <div class="font-bold text-indigo-700">{{ $st->group?->name }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $st->package?->name }}</div>
                                            @if($st->status === 'waiting_list')
                                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 text-[10px] font-extrabold">⏳ Bekleme Listesinde</span>
                                            @endif
                                        </td>
                                        <td class="p-2.5">
                                            <div>{{ $st->parent_name }}</div>
                                            <div class="font-mono text-[11px]">{{ $st->parent_phone }}</div>
                                            @if($st->secondary_parent_phone)
                                                <div class="text-[11px] text-indigo-600 mt-0.5">👤 {{ $st->secondary_parent_name }} <span class="font-mono">{{ $st->secondary_parent_phone }}</span></div>
                                            @endif
                                            <div class="mt-1">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-mono text-[11px] font-extrabold" title="Veli Portalı (/mobil) giriş PIN'i — telefon numarasıyla birlikte kullanılır">
                                                    🔑 PIN: {{ $st->portal_pin ?? '—' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="p-2.5">
                                            <span class="block text-[11px]">{{ $st->photo_consent ? '📸 Foto İzinli' : '🚫 Foto İzinsiz' }}</span>
                                            <span class="block text-[11px] text-slate-500">Rapor: {{ $st->medical_report_expires_at?->format('d.m.Y') ?? '-' }}</span>
                                        </td>
                                        <td class="p-2.5">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <button type="button" onclick="document.getElementById('edit-student-{{ $st->id }}').classList.toggle('hidden')" class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-[11px] font-bold">
                                                    ✏️ Düzenle
                                                </button>
                                                <form action="{{ route('admin.students.status', $st->id) }}" method="POST">
                                                    @csrf
                                                    <select name="status" onchange="this.form.submit()" class="text-[11px] font-bold rounded-lg border px-2 py-1 {{ $st->status === 'active' ? 'bg-emerald-50 text-emerald-800' : ($st->status === 'waiting_list' ? 'bg-purple-50 text-purple-800' : 'bg-amber-50 text-amber-800') }}">
                                                        <option value="active" {{ $st->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                                        <option value="waiting_list" {{ $st->status === 'waiting_list' ? 'selected' : '' }}>⏳ Bekleme Listesi</option>
                                                        <option value="frozen" {{ $st->status === 'frozen' ? 'selected' : '' }}>Donduruldu</option>
                                                        <option value="left" {{ $st->status === 'left' ? 'selected' : '' }}>Ayrıldı (Arşiv)</option>
                                                    </select>
                                                </form>
                                                @if($authUser->hasPermission('delete_student', true))
                                                    <form action="{{ route('admin.students.destroy', $st->id) }}" method="POST" onsubmit="return confirm('{{ $st->full_name }} sporcu ve veli giriş kaydını silmek istediğinize emin misiniz?')">
                                                        @csrf
                                                        <button type="submit" title="Kaydı Sil" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 text-[11px] font-bold transition">
                                                            🗑️ Sil
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="edit-student-{{ $st->id }}" class="hidden bg-indigo-50/60">
                                        <td colspan="5" class="p-4">
                                            <form action="{{ route('admin.students.update', $st->id) }}" method="POST" class="space-y-2.5 text-xs">
                                                @csrf
                                                <div class="font-extrabold text-indigo-900">✏️ {{ $st->full_name }} — Öğrenci, Grup ve Paket Bilgilerini Güncelle</div>
                                                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Ad Soyad *</label>
                                                        <input type="text" name="full_name" value="{{ $st->full_name }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Doğum Tarihi</label>
                                                        <input type="date" name="birth_date" value="{{ $st->birth_date?->format('Y-m-d') }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Branş *</label>
                                                        <select name="branch_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                            @foreach($branches as $b)
                                                                <option value="{{ $b->id }}" {{ $st->branch_id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Grup Seçimi *</label>
                                                        <select name="group_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                            @foreach($groups as $g)
                                                                <option value="{{ $g->id }}" {{ $st->group_id === $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Eğitim Programı / Paket *</label>
                                                        <select name="package_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                            @foreach($packages as $p)
                                                                <option value="{{ $p->id }}" {{ $st->package_id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">İndirim Kuralı</label>
                                                        <select name="discount_rule_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                            <option value="">Standart Kayıt</option>
                                                            @foreach($discountRules as $d)
                                                                <option value="{{ $d->id }}" {{ $st->discount_rule_id === $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Veli Adı *</label>
                                                        <input type="text" name="parent_name" value="{{ $st->parent_name }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Veli Telefon *</label>
                                                        <input type="text" name="parent_phone" value="{{ $st->parent_phone }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-mono">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">İkinci Veli Adı (Opsiyonel)</label>
                                                        <input type="text" name="secondary_parent_name" value="{{ $st->secondary_parent_name }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">İkinci Veli Telefon (Opsiyonel)</label>
                                                        <input type="text" name="secondary_parent_phone" value="{{ $st->secondary_parent_phone }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-mono">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Beden / Lisans</label>
                                                        <input type="text" name="uniform_size" value="{{ $st->uniform_size }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Telafi Hakkı</label>
                                                        <input type="number" name="makeup_credits" value="{{ $st->makeup_credits }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Rapor Bitiş Tarihi</label>
                                                        <input type="date" name="medical_report_expires_at" value="{{ $st->medical_report_expires_at?->format('Y-m-d') }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="font-bold block mb-0.5">Sağlık Notu</label>
                                                        <input type="text" name="health_notes" value="{{ $st->health_notes }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap justify-between items-center gap-2 pt-1 border-t mt-1">
                                                    <div class="flex items-center gap-2 text-[11px]">
                                                        <span class="px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-mono font-extrabold">🔑 Veli Portalı PIN: {{ $st->portal_pin ?? '—' }}</span>
                                                        <label class="flex items-center gap-1 text-slate-600"><input type="checkbox" name="regenerate_pin" value="1"> Yeni PIN oluştur (eskisi geçersiz olur)</label>
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap justify-between items-center pt-1">
                                                    <div class="flex gap-3">
                                                        <label class="flex items-center gap-1"><input type="checkbox" name="kvkk_consent" value="1" {{ $st->kvkk_consent ? 'checked' : '' }}> KVKK</label>
                                                        <label class="flex items-center gap-1"><input type="checkbox" name="health_data_consent" value="1" {{ $st->health_data_consent ? 'checked' : '' }}> Sağlık Rızası</label>
                                                        <label class="flex items-center gap-1"><input type="checkbox" name="photo_consent" value="1" {{ $st->photo_consent ? 'checked' : '' }}> Foto İzin</label>
                                                    </div>
                                                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">💾 Öğrenciyi Güncelle</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <script>
                    function enerjikFilterStudents(status) {
                        document.querySelectorAll('tr[data-student-status]').forEach(function (row) {
                            row.style.display = (status === 'all' || row.dataset.studentStatus === status) ? '' : 'none';
                        });
                        document.querySelectorAll('[data-filter-btn]').forEach(function (btn) {
                            btn.classList.toggle('ring-2', btn.dataset.filterBtn === status);
                            btn.classList.toggle('ring-offset-1', btn.dataset.filterBtn === status);
                        });
                    }
                </script>
