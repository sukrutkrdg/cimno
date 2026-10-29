                @if($currentRole === 'super_admin')
                    <!-- Dinamik Seçenekler (Salon Alanları, Yaş Grupları, Seviyeler & Branşlar) Yönetimi -->
                    <details class="bg-indigo-950 text-white rounded-2xl border border-indigo-800 p-4 shadow-sm">
                        <summary class="cursor-pointer font-extrabold text-xs sm:text-sm flex flex-wrap items-center justify-between gap-2">
                            <span>🎛️ Branşlar, Salon Alanları, Yaş Grupları ve Seviye Seçeneklerini Yönet (Ekle · Düzenle · Sil)</span>
                            <span class="px-2.5 py-1 rounded-lg bg-amber-400 text-slate-950 text-[11px] font-extrabold">⚡ Seçenekleri Özelleştir</span>
                        </summary>
                        <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-4 mt-4 pt-4 border-t border-indigo-800/80 text-xs text-slate-900">
                            <!-- Salon Alanları -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1">📍 Salon Alanları</div>
                                <form action="{{ route('admin.options.update') }}" method="POST" class="flex gap-1.5">
                                    @csrf
                                    <input type="hidden" name="option_key" value="custom_hall_areas">
                                    <input type="hidden" name="operation" value="add">
                                    <input type="hidden" name="return_tab" value="gruplar">
                                    <input type="text" name="new_option" required placeholder="Ör: Cimnastik Salon 2" class="w-full border rounded-lg px-2.5 py-1.5">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold shrink-0">➕ Ekle</button>
                                </form>
                                <div class="space-y-1 max-h-32 overflow-y-auto">
                                    @foreach($hallAreas as $areaItem)
                                        <div class="flex justify-between items-center bg-slate-50 px-2.5 py-1 rounded border">
                                            <span class="font-semibold">{{ $areaItem }}</span>
                                            <form action="{{ route('admin.options.update') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="option_key" value="custom_hall_areas">
                                                <input type="hidden" name="operation" value="remove">
                                                <input type="hidden" name="remove_option" value="{{ $areaItem }}">
                                                <input type="hidden" name="return_tab" value="gruplar">
                                                <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Yaş Grupları -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1">👶 Yaş Grubu Seçenekleri</div>
                                <form action="{{ route('admin.options.update') }}" method="POST" class="flex gap-1.5">
                                    @csrf
                                    <input type="hidden" name="option_key" value="custom_age_groups">
                                    <input type="hidden" name="operation" value="add">
                                    <input type="hidden" name="return_tab" value="gruplar">
                                    <input type="text" name="new_option" required placeholder="Ör: 12-16 Yaş Gençler" class="w-full border rounded-lg px-2.5 py-1.5">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold shrink-0">➕ Ekle</button>
                                </form>
                                <div class="space-y-1 max-h-32 overflow-y-auto">
                                    @foreach($ageGroups as $ageItem)
                                        <div class="flex justify-between items-center bg-slate-50 px-2.5 py-1 rounded border">
                                            <span class="font-semibold">{{ $ageItem }}</span>
                                            <form action="{{ route('admin.options.update') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="option_key" value="custom_age_groups">
                                                <input type="hidden" name="operation" value="remove">
                                                <input type="hidden" name="remove_option" value="{{ $ageItem }}">
                                                <input type="hidden" name="return_tab" value="gruplar">
                                                <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Seviye Seçenekleri -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1">🏅 Seviye Seçenekleri</div>
                                <form action="{{ route('admin.options.update') }}" method="POST" class="flex gap-1.5">
                                    @csrf
                                    <input type="hidden" name="option_key" value="custom_group_levels">
                                    <input type="hidden" name="operation" value="add">
                                    <input type="hidden" name="return_tab" value="gruplar">
                                    <input type="text" name="new_option" required placeholder="Ör: Milli Takım Aday" class="w-full border rounded-lg px-2.5 py-1.5">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold shrink-0">➕ Ekle</button>
                                </form>
                                <div class="space-y-1 max-h-32 overflow-y-auto">
                                    @foreach($groupLevels as $lvlItem)
                                        <div class="flex justify-between items-center bg-slate-50 px-2.5 py-1 rounded border">
                                            <span class="font-semibold">{{ $lvlItem }}</span>
                                            <form action="{{ route('admin.options.update') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="option_key" value="custom_group_levels">
                                                <input type="hidden" name="operation" value="remove">
                                                <input type="hidden" name="remove_option" value="{{ $lvlItem }}">
                                                <input type="hidden" name="return_tab" value="gruplar">
                                                <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Branş Ekle & Sil -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1">🤸 Branşlar ({{ $branches->count() }})</div>
                                <form action="{{ route('admin.branches.store') }}" method="POST" class="space-y-1.5">
                                    @csrf
                                    <input type="hidden" name="return_tab" value="gruplar">
                                    <div class="grid grid-cols-4 gap-1">
                                        <input type="text" name="icon" value="🤸" class="border rounded-lg px-2 py-1 text-center">
                                        <input type="text" name="name" required placeholder="Yeni Branş Adı" class="col-span-3 border rounded-lg px-2 py-1">
                                    </div>
                                    <div class="grid grid-cols-2 gap-1">
                                        <input type="text" name="age_range" value="4-12 Yaş" class="border rounded-lg px-2 py-1">
                                        <input type="text" name="level" value="Temel & İleri" class="border rounded-lg px-2 py-1">
                                    </div>
                                    <input type="hidden" name="description" value="Federasyon müfredatına uygun cimnastik branşı.">
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-indigo-600 text-white font-bold">➕ Branş Ekle</button>
                                </form>
                                <div class="space-y-1 max-h-24 overflow-y-auto">
                                    @foreach($branches as $b)
                                        <div class="flex justify-between items-center bg-slate-50 px-2 py-1 rounded border text-[11px]">
                                            <span class="font-semibold truncate">{{ $b->icon }} {{ $b->name }}</span>
                                            <form action="{{ route('admin.branches.destroy', $b->id) }}" method="POST" onsubmit="return confirm('{{ $b->name }} branşını silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Hareket Kataloğu (Komple Takvim modülünde kullanılacak) -->
                            <div class="bg-white rounded-xl p-3.5 space-y-2">
                                <div class="font-extrabold text-indigo-900 border-b pb-1">🤸‍♀️ Hareket Kataloğu</div>
                                <form action="{{ route('admin.movements.store') }}" method="POST" class="space-y-1.5">
                                    @csrf
                                    <input type="hidden" name="return_tab" value="gruplar">
                                    <input type="text" name="name" required placeholder="Ör: Öne Takla" class="w-full border rounded-lg px-2.5 py-1.5">
                                    <select name="branch_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                        <option value="">Tüm Branşlar</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-indigo-600 text-white font-bold">➕ Hareket Ekle</button>
                                </form>
                                <div class="space-y-1 max-h-32 overflow-y-auto">
                                    @foreach($movements as $mv)
                                        <div class="flex justify-between items-center bg-slate-50 px-2.5 py-1 rounded border">
                                            <span class="font-semibold truncate">{{ $mv->name }}@if($mv->branch) <span class="text-slate-400 font-normal">({{ $mv->branch->name }})</span>@endif</span>
                                            <form action="{{ route('admin.movements.destroy', $mv->id) }}" method="POST" onsubmit="return confirm('{{ $mv->name }} hareketini silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <input type="hidden" name="return_tab" value="gruplar">
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
                    <!-- Sol Kolon: Yeni Grup Aç & Mevcut Gruba Yeni Ders Saati Ekle -->
                    <div class="lg:col-span-5 space-y-6">
                        @if($currentRole !== 'coach')
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <span class="text-[11px] font-bold uppercase text-indigo-600">Eğitim Planlama</span>
                            <h3 class="text-lg font-extrabold mb-3">➕ Yeni Grup Aç & Eğitim Programına Ekle</h3>
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
                                        <select name="age_group" class="w-full border rounded-xl px-2 py-2 bg-white">
                                            @foreach($ageGroups as $ag)
                                                <option value="{{ $ag }}">{{ $ag }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Seviye</label>
                                        <select name="level" class="w-full border rounded-xl px-2 py-2 bg-white">
                                            @foreach($groupLevels as $lvl)
                                                <option value="{{ $lvl }}">{{ $lvl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Kontenjan</label>
                                        <input type="number" name="capacity" value="12" class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Salon Alanı (Çakışma Kontrolü Yapılır)</label>
                                    <select name="area" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($hallAreas as $ha)
                                            <option value="{{ $ha }}">{{ $ha }}</option>
                                        @endforeach
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
                                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow">
                                    📅 Grubu Aç ve Haftalık Eğitim Programına Ekle
                                </button>
                            </form>
                        </div>
                        @endif

                        <!-- Mevcut Gruba Yeni Eğitim Programı (Ders Günü & Saati) Ekleme -->
                        <div class="bg-white rounded-2xl border border-emerald-200 p-5 shadow-sm">
                            <span class="text-[11px] font-bold uppercase text-emerald-700">Haftalık Eğitim Programı Ekleme</span>
                            <h3 class="text-base font-extrabold mb-2">🗓️ Mevcut Gruba Yeni Ders Günü / Saati Ekle</h3>
                            <p class="text-[11px] text-slate-500 mb-3">Bir grubun haftada birden fazla dersi varsa veya programa yeni bir gün/saat eklemek istiyorsanız buradan ekleyin.</p>
                            <form action="" method="POST" id="quick-add-schedule-form" onsubmit="this.action='/panel/gruplar/' + this.group_id.value + '/program-ekle'" class="space-y-2.5 text-xs">
                                @csrf
                                <div>
                                    <label class="font-bold block mb-1">Grup Seçin *</label>
                                    <select name="group_id" required class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                                        @foreach($groups as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->branch?->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="font-bold block mb-1">Ders Günü *</label>
                                        <select name="day_of_week" class="w-full border rounded-xl px-3 py-2 bg-white">
                                            @foreach(['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'] as $d)
                                                <option value="{{ $d }}">{{ $d }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Salon Alanı</label>
                                        <select name="area" class="w-full border rounded-xl px-3 py-2 bg-white">
                                            @foreach($hallAreas as $ha)
                                                <option value="{{ $ha }}">{{ $ha }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="font-bold block mb-1">Başlangıç Saati *</label>
                                        <input type="time" name="start_time" value="14:00" required class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Bitiş Saati *</label>
                                        <input type="time" name="end_time" value="15:00" required class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow">
                                    ➕ Eğitim Programına Yeni Ders Saati Ekle
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sağ Kolon: Mevcut Gruplar & Haftalık Eğitim Programı (Düzenle / Ekle / Sil) -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                            <div>
                                <h3 class="text-lg font-extrabold">🗓️ Mevcut Gruplar ve Haftalık Eğitim Programı (Düzenle & Sil)</h3>
                                <p class="text-xs text-slate-500">Her grubun adını, kontenjanını, antrenörünü ve ders gün/saatlerini doğrudan güncelleyebilir veya silebilirsiniz.</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            @foreach($groups as $g)
                                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-3">
                                    <div class="flex flex-wrap justify-between items-start gap-3">
                                        <div>
                                            <span class="text-[11px] font-bold text-indigo-600">{{ $g->branch?->name }} · {{ $g->area }}</span>
                                            <h4 class="font-extrabold text-sm text-slate-900">{{ $g->name }} ({{ $g->active_students_count }}/{{ $g->capacity }} Öğrenci)</h4>
                                            <p class="text-xs text-slate-500">Antrenör: {{ $g->coach?->name ?? '-' }} · {{ $g->age_group }} · {{ $g->level }}</p>
                                        @if($g->assistantCoaches->isNotEmpty())
                                            <p class="text-[11px] text-slate-500 mt-1 flex flex-wrap items-center gap-1">
                                                👥 Yardımcı:
                                                @foreach($g->assistantCoaches as $ac)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-800 font-bold">
                                                        {{ $ac->name }}
                                                        <form action="{{ route('admin.groups.assistant.remove', [$g->id, $ac->id]) }}" method="POST" onsubmit="return confirm('{{ $ac->name }} yardımcı antrenörlükten çıkarılsın mı?')" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-indigo-400 hover:text-rose-600" title="Çıkar">✕</button>
                                                        </form>
                                                    </span>
                                                @endforeach
                                            </p>
                                        @endif
                                        <form action="{{ route('admin.groups.assistant.add', $g->id) }}" method="POST" class="flex items-center gap-1.5 mt-1.5">
                                            @csrf
                                            <select name="coach_id" class="text-[11px] border rounded-lg px-2 py-1 bg-white" required>
                                                <option value="">+ Yardımcı Antrenör Seç</option>
                                                @foreach($staff->where('role', 'coach') as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-[11px] px-2 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold">➕ Ekle</button>
                                        </form>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" onclick="document.getElementById('edit-group-{{ $g->id }}').classList.toggle('hidden')" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold">
                                                ✏️ Grubu Düzenle
                                            </button>
                                            <form action="{{ route('admin.groups.destroy', $g->id) }}" method="POST" onsubmit="return confirm('{{ $g->name }} grubunu ve bağlı haftalık ders programını silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 text-xs font-bold transition">
                                                    🗑️ Grubu Sil
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Grup Bilgilerini Güncelleme Formu (Gizli/Açılır) -->
                                    <form id="edit-group-{{ $g->id }}" action="{{ route('admin.groups.update', $g->id) }}" method="POST" class="hidden p-3.5 rounded-xl bg-white border border-indigo-200 space-y-2.5 text-xs">
                                        @csrf
                                        <div class="font-extrabold text-indigo-900">✏️ {{ $g->name }} Bilgilerini Güncelle</div>
                                        <div class="grid sm:grid-cols-3 gap-2">
                                            <div>
                                                <label class="font-bold block mb-0.5">Grup Adı *</label>
                                                <input type="text" name="name" value="{{ $g->name }}" required class="w-full border rounded-lg px-2.5 py-1.5">
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-0.5">Branş *</label>
                                                <select name="branch_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                    @foreach($branches as $b)
                                                        <option value="{{ $b->id }}" {{ $g->branch_id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-0.5">Antrenör *</label>
                                                <select name="coach_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                    @foreach($staff->where('role', 'coach') as $c)
                                                        <option value="{{ $c->id }}" {{ $g->coach_id === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-0.5">Yaş Grubu</label>
                                                <input type="text" name="age_group" value="{{ $g->age_group }}" class="w-full border rounded-lg px-2.5 py-1.5">
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-0.5">Seviye</label>
                                                <input type="text" name="level" value="{{ $g->level }}" class="w-full border rounded-lg px-2.5 py-1.5">
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-0.5">Kontenjan & Alan</label>
                                                <div class="flex gap-1">
                                                    <input type="number" name="capacity" value="{{ $g->capacity }}" class="w-16 border rounded-lg px-2 py-1.5">
                                                    <input type="text" name="area" value="{{ $g->area }}" class="w-full border rounded-lg px-2 py-1.5">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <label class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-50 border border-slate-200 font-bold cursor-pointer">
                                                <input type="checkbox" name="show_in_trial_signup" value="1" {{ $g->show_in_trial_signup ? 'checked' : '' }}>
                                                <span>👀 Deneme Dersi Kaydı Listesinde Görünsün</span>
                                            </label>
                                            <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white font-bold">💾 Grup Bilgilerini Kaydet</button>
                                        </div>
                                    </form>

                                    <!-- Grubun Haftalık Eğitim Programı (Ders Günleri ve Saatleri - Güncelle & Sil) -->
                                    <div class="space-y-2 pt-2 border-t border-slate-200/80">
                                        <div class="text-[11px] font-bold text-slate-600">📅 Grubun Eğitim Programı Saatleri (Güncelle veya Sil):</div>
                                        @forelse($g->schedules as $sch)
                                            <div class="flex flex-wrap items-center justify-between gap-2 bg-white p-2 rounded-xl border border-slate-200 text-xs">
                                                <form action="{{ route('admin.schedules.update', $sch->id) }}" method="POST" class="flex flex-wrap items-center gap-1.5 flex-1">
                                                    @csrf
                                                    <select name="day_of_week" class="border rounded-lg px-2 py-1 bg-slate-50 font-bold">
                                                        @foreach(['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'] as $d)
                                                            <option value="{{ $d }}" {{ $sch->day_of_week === $d ? 'selected' : '' }}>{{ $d }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="time" name="start_time" value="{{ substr($sch->start_time, 0, 5) }}" class="border rounded-lg px-2 py-1 font-mono">
                                                    <span>-</span>
                                                    <input type="time" name="end_time" value="{{ substr($sch->end_time, 0, 5) }}" class="border rounded-lg px-2 py-1 font-mono">
                                                    <input type="text" name="area" value="{{ $sch->area ?? $g->area }}" placeholder="Salon Alanı" class="border rounded-lg px-2 py-1 w-32">
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px]">💾 Saat Güncelle</button>
                                                </form>
                                                <form action="{{ route('admin.schedules.destroy', $sch->id) }}" method="POST" onsubmit="return confirm('{{ $sch->day_of_week }} ders saati kaydını programdan silmek istediğinize emin misiniz?')">
                                                    @csrf
                                                    <button type="submit" title="Ders Saatini Sil" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 font-bold text-[11px]">🗑️ Sil</button>
                                                </form>
                                            </div>
                                        @empty
                                            <p class="text-[11px] text-slate-400 italic">Bu gruba henüz haftalık ders saati eklenmemiş.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
