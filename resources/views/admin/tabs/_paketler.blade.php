                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Sol Kolon: Yeni Eğitim Paketi Ekle, Yeni İndirim Ekle & Toplu Fiyat Güncelle -->
                    <div class="lg:col-span-5 space-y-6">
                        <!-- 1. Yeni Eğitim Programı / Paket Ekleme Formu -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <span class="text-[11px] font-bold uppercase text-indigo-600">Eğitim Programı & Paket Yönetimi</span>
                            <h3 class="text-lg font-extrabold mb-3">➕ Yeni Eğitim Paketi Ekle</h3>
                            <form action="{{ route('admin.packages.store') }}" method="POST" class="space-y-3 text-xs">
                                @csrf
                                <input type="hidden" name="return_tab" value="paketler">
                                <div>
                                    <label class="font-bold block mb-1">Paket / Eğitim Programı Adı *</label>
                                    <input type="text" name="name" required placeholder="Ör: Haftada 3 Gün Performans Paketi (12 Ders)" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="font-bold block mb-1">Paket Ücreti (₺) *</label>
                                        <input type="number" name="price" required value="4200" class="w-full border rounded-xl px-3 py-2 font-bold">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Ders Sayısı *</label>
                                        <input type="number" name="lessons_count" required value="12" class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Süre (Ay) *</label>
                                        <input type="number" name="duration_months" required value="1" class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="font-bold block mb-1">Kayıt / Dosya Ücreti (₺)</label>
                                        <input type="number" name="registration_fee" value="500" class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Kısa Açıklama</label>
                                        <input type="text" name="description" placeholder="Ör: Lisanslı sporcular ve yoğun antrenman" class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow">
                                    ➕ Yeni Eğitim Paketini Kaydet
                                </button>
                            </form>
                        </div>

                        <!-- 2. Yeni Otomatik İndirim Kuralı Ekleme Formu -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <span class="text-[11px] font-bold uppercase text-emerald-700">İndirim Yönetimi</span>
                            <h3 class="text-base font-extrabold mb-3">➕ Yeni İndirim Kuralı Ekle</h3>
                            <form action="{{ route('admin.discounts.store') }}" method="POST" class="space-y-3 text-xs">
                                @csrf
                                <input type="hidden" name="return_tab" value="paketler">
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-2">
                                        <label class="font-bold block mb-1">İndirim Adı *</label>
                                        <input type="text" name="name" required placeholder="Ör: Erken Kayıt İndirimi" class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Oran (%) *</label>
                                        <input type="number" step="0.1" name="percentage" required value="10" class="w-full border rounded-xl px-3 py-2 font-bold">
                                    </div>
                                </div>
                                <input type="hidden" name="type" value="custom">
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                                    ➕ İndirim Kuralını Ekle
                                </button>
                            </form>
                        </div>

                        <!-- 3. Paket Fiyatlarını Toplu Güncelleme -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                            <span class="text-[11px] font-bold uppercase text-rose-600">Toplu Zam / Güncelleme</span>
                            <h3 class="text-base font-extrabold mb-2">🏷️ Tüm Paket Fiyatlarını Toplu Güncelle</h3>
                            <form action="{{ route('admin.packages.bulk_price') }}" method="POST" class="space-y-3 text-xs">
                                @csrf
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="font-bold block mb-1">Yüzdesel Oran (%)</label>
                                        <input type="number" step="0.1" name="percentage" value="15" class="w-full border rounded-xl px-3 py-2 font-bold">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Veya Sabit Ek Tutar (₺)</label>
                                        <input type="number" name="fixed_amount" value="0" class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold">
                                    🚀 Tüm Paket Fiyatlarını Toplu Güncelle
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sağ Kolon: Mevcut Paketleri ve İndirimleri Tek Tek Düzenle & Sil -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-lg font-extrabold mb-1">📦 Mevcut Eğitim Paketleri (Güncelle & Sil)</h3>
                        <p class="text-xs text-slate-500 mb-4">Her paketin adını, fiyatını, ders sayısını, kayıt ücretini ve açıklamasını doğrudan düzenleyip kaydedebilirsiniz.</p>
                        <div class="space-y-3 mb-6">
                            @foreach($packages as $p)
                                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2.5 text-xs">
                                    <form action="{{ route('admin.packages.update', $p->id) }}" method="POST" class="space-y-2.5">
                                        @csrf
                                        <input type="hidden" name="return_tab" value="paketler">
                                        <div class="grid sm:grid-cols-12 gap-2 items-end">
                                            <div class="sm:col-span-5">
                                                <label class="font-bold text-slate-600 block mb-0.5">Paket / Program Adı</label>
                                                <input type="text" name="name" value="{{ $p->name }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="font-bold text-slate-600 block mb-0.5">Ücret (₺)</label>
                                                <input type="number" name="price" value="{{ $p->price }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-extrabold text-indigo-700">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="font-bold text-slate-600 block mb-0.5">Ders Sayısı</label>
                                                <input type="number" name="lessons_count" value="{{ $p->lessons_count }}" required class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="font-bold text-slate-600 block mb-0.5">Süre (Ay)</label>
                                                <input type="number" name="duration_months" value="{{ $p->duration_months }}" required class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                            </div>
                                        </div>
                                        <div class="grid sm:grid-cols-12 gap-2 items-center">
                                            <div class="sm:col-span-3">
                                                <label class="font-bold text-slate-600 block mb-0.5">Kayıt Ücreti (₺)</label>
                                                <input type="number" name="registration_fee" value="{{ $p->registration_fee }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                            </div>
                                            <div class="sm:col-span-6">
                                                <label class="font-bold text-slate-600 block mb-0.5">Açıklama</label>
                                                <input type="text" name="description" value="{{ $p->description }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                            </div>
                                            <div class="sm:col-span-3 flex items-center justify-end gap-2 pt-3">
                                                <label class="flex items-center gap-1 font-bold">
                                                    <input type="checkbox" name="is_active" value="1" {{ $p->is_active ? 'checked' : '' }}> Aktif
                                                </label>
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">💾 Güncelle</button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="flex justify-end pt-1 border-t border-slate-200/70">
                                        <form action="{{ route('admin.packages.destroy', $p->id) }}" method="POST" onsubmit="return confirm('{{ $p->name }} paketini silmek istediğinize emin misiniz?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 text-[11px] font-bold transition">🗑️ Paketi Sil</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <h4 class="font-extrabold text-sm text-slate-800 mb-2">🏷️ Otomatik İndirim Kuralları (Güncelle & Sil)</h4>
                        <div class="space-y-2">
                            @foreach($discountRules as $d)
                                <div class="p-3 rounded-xl bg-indigo-50/70 border border-indigo-100 text-xs flex flex-wrap justify-between items-center gap-2">
                                    <form action="{{ route('admin.discounts.update', $d->id) }}" method="POST" class="flex flex-wrap items-center gap-2 flex-1">
                                        @csrf
                                        <input type="hidden" name="return_tab" value="paketler">
                                        <input type="text" name="name" value="{{ $d->name }}" required class="border rounded-lg px-2.5 py-1.5 bg-white font-bold flex-1 min-w-[160px]">
                                        <div class="flex items-center gap-1">
                                            <span class="font-bold">%</span>
                                            <input type="number" step="0.1" name="percentage" value="{{ $d->percentage }}" required class="w-20 border rounded-lg px-2 py-1.5 bg-white font-extrabold text-indigo-800">
                                        </div>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold">💾 Güncelle</button>
                                    </form>
                                    <form action="{{ route('admin.discounts.destroy', $d->id) }}" method="POST" onsubmit="return confirm('{{ $d->name }} indirim kuralını silmek istediğinize emin misiniz?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 font-bold">🗑️ Sil</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
