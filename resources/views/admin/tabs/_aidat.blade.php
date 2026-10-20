                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold">💳 Aidat, Otomatik Borçlandırma ve Tahsilat</h2>
                        <p class="text-xs text-slate-500">"Önce borç açılır, tahsilat ona bağlanır" mimarisi · Makbuz PDF, WhatsApp gönderimi, borç ekleme/güncelleme ve silme</p>
                    </div>
                    <form action="{{ route('admin.invoices.generate') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="period_label" value="Kasım 2026 Aidatı" class="border rounded-xl px-3 py-2 text-xs bg-white font-bold">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold">⚡ Tüm Aktif Öğrencilere Dönem Borcu Aç</button>
                    </form>
                </div>

                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Sol Kolon: Tahsilat Girişi & Tekil Özel Borç Açma -->
                    <div class="lg:col-span-4 space-y-6">
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
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

                        <!-- Tek Öğrenciye Özel Borç / Aidat Ekleme -->
                        <div class="bg-white rounded-2xl border border-indigo-200 p-5 shadow-sm">
                            <span class="text-[11px] font-bold uppercase text-indigo-600">Özel Borçlandırma</span>
                            <h3 class="text-base font-extrabold mb-3">➕ Tek Öğrenciye Özel Aidat / Borç Ekle</h3>
                            <form action="{{ route('admin.invoices.store_single') }}" method="POST" class="space-y-2.5 text-xs">
                                @csrf
                                <div>
                                    <label class="font-bold block mb-1">Öğrenci Seçin *</label>
                                    <select name="student_id" required class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($students as $st)
                                            <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->group?->name }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Dönem / Borç Açıklaması *</label>
                                    <input type="text" name="period_label" required placeholder="Ör: Ekim 2026 Aidatı veya Mayo Bedeli" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="font-bold block mb-1">Net Borç Tutarı (₺) *</label>
                                        <input type="number" name="net_amount" required value="3200" class="w-full border rounded-xl px-3 py-2 font-bold">
                                    </div>
                                    <div>
                                        <label class="font-bold block mb-1">Son Ödeme Vadesi *</label>
                                        <input type="date" name="due_date" required value="{{ today()->addDays(5)->toDateString() }}" class="w-full border rounded-xl px-3 py-2">
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">
                                    ➕ Öğrenciye Borç Kaydı Aç
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Borçlar ve Makbuzlar Listesi -->
                    <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm overflow-x-auto h-fit">
                        <h3 class="text-base font-extrabold mb-3">📋 Aidat Borçları ve Tahsilat Makbuzları (Düzenle & Sil)</h3>
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                <tr>
                                    <th class="p-2.5">Öğrenci / Dönem</th>
                                    <th class="p-2.5">Net Tutar</th>
                                    <th class="p-2.5">Ödenen / Kalan</th>
                                    <th class="p-2.5">Vade</th>
                                    <th class="p-2.5 text-right">Makbuz & İşlem</th>
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
                                        <td class="p-2.5 text-right">
                                            <div class="flex flex-wrap justify-end items-center gap-1.5">
                                                @foreach($inv->payments as $p)
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-indigo-50 text-indigo-700 font-bold">
                                                        <a href="{{ $p->receiptUrl() }}" target="_blank">🧾 {{ $p->receipt_no }}</a>
                                                        <form action="{{ route('admin.payments.destroy', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ $p->receipt_no }} tahsilat makbuzunu silmek istediğinize emin misiniz?')">
                                                            @csrf
                                                            <button type="submit" title="Tahsilatı Sil" class="text-rose-600 hover:text-rose-800 font-extrabold px-0.5">✕</button>
                                                        </form>
                                                    </span>
                                                @endforeach
                                                <button type="button" onclick="document.getElementById('edit-inv-{{ $inv->id }}').classList.toggle('hidden')" class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold">✏️ Düzenle</button>
                                                <form action="{{ route('admin.invoices.destroy', $inv->id) }}" method="POST" class="inline" onsubmit="return confirm('Bu aidat borç kaydını ve bağlı tahsilatları silmek istediğinize emin misiniz?')">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 font-bold transition">🗑️ Sil</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="edit-inv-{{ $inv->id }}" class="hidden bg-indigo-50/50">
                                        <td colspan="5" class="p-3">
                                            <form action="{{ route('admin.invoices.update', $inv->id) }}" method="POST" class="grid sm:grid-cols-4 gap-2 items-end text-xs">
                                                @csrf
                                                <div>
                                                    <label class="font-bold block mb-0.5">Dönem / Açıklama</label>
                                                    <input type="text" name="period_label" value="{{ $inv->period_label }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                </div>
                                                <div>
                                                    <label class="font-bold block mb-0.5">Net Tutar (₺)</label>
                                                    <input type="number" name="net_amount" value="{{ $inv->net_amount }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                                </div>
                                                <div>
                                                    <label class="font-bold block mb-0.5">Son Ödeme Vadesi</label>
                                                    <input type="date" name="due_date" value="{{ $inv->due_date->format('Y-m-d') }}" required class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                                </div>
                                                <div>
                                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-indigo-600 text-white font-bold">💾 Borcu Güncelle</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
