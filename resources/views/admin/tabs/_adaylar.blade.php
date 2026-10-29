                <div class="flex flex-wrap justify-between items-center gap-3">
                    <div>
                        <h2 class="text-2xl font-extrabold">🎯 Adaylar (Ön Kayıt & Deneme Dersi Talepleri)</h2>
                        <p class="text-xs text-slate-500">Web sitesinden, telefonla veya Instagram'dan gelen adayları ekleyin, düzenleyin, kesin kayda dönüştürün veya silin</p>
                    </div>
                </div>

                <!-- Yeni Aday / Deneme Dersi Kaydı Ekleme Formu -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-sm font-extrabold text-indigo-900 mb-3">➕ Manuel Yeni Aday / Deneme Dersi Talebi Ekle</h3>
                    <form action="{{ route('admin.leads.store') }}" method="POST" class="grid sm:grid-cols-2 lg:grid-cols-6 gap-2.5 text-xs items-end">
                        @csrf
                        <div>
                            <label class="font-bold block mb-1">Aday Sporcu Adı *</label>
                            <input type="text" name="student_name" required placeholder="Ör: Zeynep Kaya" class="w-full border rounded-xl px-3 py-2">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Yaşı *</label>
                            <input type="number" name="student_age" required value="6" min="2" max="50" class="w-full border rounded-xl px-3 py-2">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Veli Adı Soyadı *</label>
                            <input type="text" name="parent_name" required placeholder="Ör: Selin Kaya" class="w-full border rounded-xl px-3 py-2">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Veli Telefon *</label>
                            <input type="text" name="parent_phone" required placeholder="532XXXXXXX (başında 0 olmadan 10 hane)" maxlength="15" class="w-full border rounded-xl px-3 py-2 font-mono">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Branş & Kaynak</label>
                            <div class="flex gap-1.5">
                                <select name="branch_id" class="w-1/2 border rounded-xl px-2 py-2 bg-white">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                <select name="source" class="w-1/2 border rounded-xl px-2 py-2 bg-white">
                                    @foreach($leadSources as $src)
                                        <option value="{{ $src }}">{{ $src }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow">➕ Aday Ekle</button>
                        </div>
                    </form>
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
                                    <td class="p-3.5 text-right">
                                        <div class="flex flex-wrap justify-end gap-1.5">
                                            <a href="https://wa.me/90{{ substr(preg_replace('/\D+/', '', $lead->parent_phone), -10) }}?text={{ urlencode('Merhaba ' . $lead->parent_name . ', Enerjik Cimnastik deneme dersi randevunuz için yazıyoruz.') }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white font-bold">💬 WhatsApp</a>
                                            <button type="button" onclick="document.getElementById('edit-lead-{{ $lead->id }}').classList.toggle('hidden')" class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold">✏️ Düzenle</button>
                                            @if($lead->status !== 'converted')
                                                <form action="{{ route('admin.leads.convert', $lead->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">✨ Kayda Dönüştür</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('{{ $lead->student_name }} deneme / aday kaydını silmek istediğinize emin misiniz?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 font-bold transition">🗑️ Sil</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="edit-lead-{{ $lead->id }}" class="hidden bg-indigo-50/50">
                                    <td colspan="5" class="p-4">
                                        <form action="{{ route('admin.leads.update', $lead->id) }}" method="POST" class="grid sm:grid-cols-2 lg:grid-cols-7 gap-2 items-end text-xs">
                                            @csrf
                                            <div>
                                                <label class="font-bold block mb-1">Sporcu Adı</label>
                                                <input type="text" name="student_name" value="{{ $lead->student_name }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-1">Yaş</label>
                                                <input type="number" name="student_age" value="{{ $lead->student_age }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-1">Veli Adı</label>
                                                <input type="text" name="parent_name" value="{{ $lead->parent_name }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-1">Veli Telefon</label>
                                                <input type="text" name="parent_phone" value="{{ $lead->parent_phone }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-mono">
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-1">Branş</label>
                                                <select name="branch_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                    @foreach($branches as $b)
                                                        <option value="{{ $b->id }}" {{ $lead->branch_id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-1">Durum</label>
                                                <select name="status" class="w-full border rounded-lg px-2 py-1.5 bg-white font-bold">
                                                    <option value="trial_scheduled" {{ $lead->status === 'trial_scheduled' ? 'selected' : '' }}>Deneme Planlandı</option>
                                                    <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>Yeni Başvuru</option>
                                                    <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Kesin Kayıt Oldu</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-1">Deneme Grubu</label>
                                                <select name="trial_group_id" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                    <option value="">Seçilmedi</option>
                                                    @foreach($trialEligibleGroups as $g)
                                                        <option value="{{ $g->id }}" {{ (int) $lead->trial_group_id === $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="font-bold block mb-1">Deneme Tarihi</label>
                                                <input type="date" name="trial_date" value="{{ $lead->trial_date?->toDateString() }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                <p class="text-[10px] text-slate-400 mt-0.5">Tarih + grup girilirse 1 gün önce otomatik WhatsApp hatırlatması kuyruğa eklenir.</p>
                                            </div>
                                            <div>
                                                <button type="submit" class="w-full py-1.5 rounded-lg bg-indigo-600 text-white font-bold">💾 Güncelle</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
