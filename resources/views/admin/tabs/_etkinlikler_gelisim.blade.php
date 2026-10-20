                <div class="grid lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
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

                        <!-- Yeni Etkinlik / Kamp / Gösteri Ekleme Formu -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-3">🎪 Yeni Etkinlik / Gösteri / Kamp Ekle</h3>
                            <form action="{{ route('admin.events.store') }}" method="POST" class="space-y-2.5 text-xs">
                                @csrf
                                <div>
                                    <label class="font-bold block mb-1">Etkinlik Başlığı *</label>
                                    <input type="text" name="title" required placeholder="Ör: 2026 Yıl Sonu Cimnastik Gösterisi" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="font-bold block mb-1">Tarih *</label>
                                        <input type="date" name="event_date" required value="{{ today()->addDays(30)->toDateString() }}" class="w-full border rounded-xl px-2.5 py-2">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Kontenjan *</label>
                                        <input type="number" name="quota" required value="50" class="w-full border rounded-xl px-2.5 py-2">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Ücret (₺)</label>
                                        <input type="number" name="fee" value="0" class="w-full border rounded-xl px-2.5 py-2">
                                    </div>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Konum / Salon *</label>
                                    <input type="text" name="location" required value="Tekirdağ Enerjik Cimnastik Ana Salonu" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Açıklama</label>
                                    <textarea name="description" rows="2" class="w-full border rounded-xl p-2.5" placeholder="Etkinlik detayları..."></textarea>
                                </div>
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">➕ Etkinliği Ekle</button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-3">🎪 Etkinlikler, Gösteriler ve Yaz Okulu (Düzenle & Sil)</h3>
                            <div class="space-y-2.5">
                                @foreach($events as $ev)
                                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                                        <div class="flex flex-wrap justify-between items-center gap-3">
                                            <div>
                                                <span class="text-xs font-bold text-indigo-600">{{ $ev->event_date->format('d.m.Y') }} · {{ $ev->location }}</span>
                                                <h4 class="font-extrabold text-sm">{{ $ev->title }}</h4>
                                                <p class="text-xs text-slate-600">{{ $ev->description }} (Katılımcı: {{ $ev->participants->count() }}/{{ $ev->quota }})</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if($currentRole === 'super_admin')
                                                    <span class="font-extrabold text-indigo-700 text-sm">{{ number_format($ev->fee, 0, ',', '.') }} ₺</span>
                                                @endif
                                                <button type="button" onclick="document.getElementById('edit-ev-{{ $ev->id }}').classList.toggle('hidden')" class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold">✏️ Düzenle</button>
                                                <form action="{{ route('admin.events.destroy', $ev->id) }}" method="POST" onsubmit="return confirm('{{ $ev->title }} etkinliğini silmek istediğinize emin misiniz?')">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 text-xs font-bold transition">🗑️ Sil</button>
                                                </form>
                                            </div>
                                        </div>
                                        <form id="edit-ev-{{ $ev->id }}" action="{{ route('admin.events.update', $ev->id) }}" method="POST" class="hidden pt-2 border-t grid sm:grid-cols-4 gap-2 text-xs">
                                            @csrf
                                            <input type="text" name="title" value="{{ $ev->title }}" required class="sm:col-span-2 border rounded-lg px-2 py-1.5 bg-white">
                                            <input type="date" name="event_date" value="{{ $ev->event_date->format('Y-m-d') }}" required class="border rounded-lg px-2 py-1.5 bg-white">
                                            <input type="number" name="fee" value="{{ $ev->fee }}" class="border rounded-lg px-2 py-1.5 bg-white">
                                            <input type="text" name="location" value="{{ $ev->location }}" required class="sm:col-span-2 border rounded-lg px-2 py-1.5 bg-white">
                                            <input type="number" name="quota" value="{{ $ev->quota }}" required class="border rounded-lg px-2 py-1.5 bg-white">
                                            <button type="submit" class="py-1.5 rounded-lg bg-indigo-600 text-white font-bold">💾 Kaydet</button>
                                            <input type="hidden" name="description" value="{{ $ev->description }}">
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <h3 class="text-base font-extrabold mb-3">🏅 Yayınlanan Gelişim Karneleri (Düzenle & Sil)</h3>
                            @foreach($developmentRecords as $dev)
                                <div class="p-3.5 rounded-xl border border-slate-200 mb-2.5 text-xs space-y-2">
                                    <div class="flex justify-between items-start gap-3">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 font-bold">
                                                <span>{{ $dev->student?->full_name }} — {{ $dev->term_label }}</span>
                                                <span class="text-indigo-600">({{ $dev->current_level }})</span>
                                            </div>
                                            <p class="text-slate-600 mt-1">Esneklik: {{ $dev->flexibility_score }} · Kuvvet: {{ $dev->strength_score }} · Denge: {{ $dev->balance_score }} · "{{ $dev->coach_comment }}"</p>
                                        </div>
                                        <div class="flex gap-1.5 shrink-0">
                                            <button type="button" onclick="document.getElementById('edit-dev-{{ $dev->id }}').classList.toggle('hidden')" class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-bold">✏️ Düzenle</button>
                                            <form action="{{ route('admin.development.destroy', $dev->id) }}" method="POST" onsubmit="return confirm('Bu gelişim karnesi kaydını silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 font-bold transition">🗑️ Sil</button>
                                            </form>
                                        </div>
                                    </div>
                                    <form id="edit-dev-{{ $dev->id }}" action="{{ route('admin.development.update', $dev->id) }}" method="POST" class="hidden pt-2 border-t space-y-2">
                                        @csrf
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" name="term_label" value="{{ $dev->term_label }}" required class="border rounded-lg px-2 py-1 bg-white">
                                            <input type="text" name="current_level" value="{{ $dev->current_level }}" required class="border rounded-lg px-2 py-1 bg-white">
                                        </div>
                                        <div class="grid grid-cols-5 gap-1">
                                            <input type="number" name="flexibility_score" value="{{ $dev->flexibility_score }}" class="border rounded-lg p-1">
                                            <input type="number" name="strength_score" value="{{ $dev->strength_score }}" class="border rounded-lg p-1">
                                            <input type="number" name="balance_score" value="{{ $dev->balance_score }}" class="border rounded-lg p-1">
                                            <input type="number" name="coordination_score" value="{{ $dev->coordination_score }}" class="border rounded-lg p-1">
                                            <input type="number" name="discipline_score" value="{{ $dev->discipline_score }}" class="border rounded-lg p-1">
                                        </div>
                                        <div class="flex gap-2">
                                            <input type="text" name="coach_comment" value="{{ $dev->coach_comment }}" class="w-full border rounded-lg px-2 py-1 bg-white">
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-indigo-600 text-white font-bold shrink-0">💾 Güncelle</button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
