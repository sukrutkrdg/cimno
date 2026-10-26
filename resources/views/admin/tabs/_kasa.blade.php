                <!-- Kasa & Banka Hesapları + Gelir/Gider Kategorileri Yönetimi -->
                <details class="bg-indigo-950 text-white rounded-2xl border border-indigo-800 p-4 shadow-sm">
                    <summary class="cursor-pointer font-extrabold text-xs sm:text-sm flex flex-wrap items-center justify-between gap-2">
                        <span>🎛️ Kasa / Banka / POS Hesapları ve Gelir-Gider Kategorilerini Yönet (Yeni Hesap Ekle · Güncelle · Sil)</span>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-400 text-slate-950 text-[11px] font-extrabold">⚡ Kasa & Kategori Ayarları</span>
                    </summary>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4 pt-4 border-t border-indigo-800/80 text-xs text-slate-900">
                        <!-- Yeni Kasa / Banka / POS Hesabı Ekle -->
                        <div class="bg-white rounded-xl p-4 space-y-2.5">
                            <div class="font-extrabold text-indigo-900 border-b pb-1.5">🏦 Yeni Kasa / Banka / POS Hesabı Ekle</div>
                            <form action="{{ route('admin.cash_accounts.store') }}" method="POST" class="grid sm:grid-cols-2 gap-2">
                                @csrf
                                <div>
                                    <label class="font-bold block mb-0.5">Hesap Adı *</label>
                                    <input type="text" name="name" required placeholder="Ör: Garanti POS Hesabı" class="w-full border rounded-lg px-2.5 py-1.5">
                                </div>
                                <div>
                                    <label class="font-bold block mb-0.5">Tür *</label>
                                    <select name="type" class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                        <option value="cash">Nakit Kasa</option>
                                        <option value="bank">Banka / IBAN</option>
                                        <option value="pos">Fiziki POS</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-0.5">Başlangıç Bakiyesi (₺)</label>
                                    <input type="number" step="0.01" name="balance" value="0" class="w-full border rounded-lg px-2.5 py-1.5 font-bold">
                                </div>
                                <div>
                                    <label class="font-bold block mb-0.5">IBAN / Bilgi</label>
                                    <input type="text" name="iban" placeholder="TR..." class="w-full border rounded-lg px-2.5 py-1.5 font-mono">
                                </div>
                                <div class="sm:col-span-2">
                                    <button type="submit" class="w-full py-2 rounded-lg bg-indigo-600 text-white font-bold">➕ Yeni Hesap Ekle</button>
                                </div>
                            </form>
                        </div>

                        <!-- Gelir Kategorileri Ekle/Sil -->
                        <div class="bg-white rounded-xl p-4 space-y-2.5">
                            <div class="font-extrabold text-emerald-700 border-b pb-1.5">🟢 Gelir Kategorileri</div>
                            <form action="{{ route('admin.options.update') }}" method="POST" class="flex gap-1.5">
                                @csrf
                                <input type="hidden" name="option_key" value="custom_income_categories">
                                <input type="hidden" name="operation" value="add">
                                <input type="hidden" name="return_tab" value="kasa">
                                <input type="text" name="new_option" required placeholder="Yeni Gelir Kategorisi (Ör: Sponsorluk)" class="w-full border rounded-lg px-2.5 py-1.5">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold shrink-0">➕ Ekle</button>
                            </form>
                            <div class="grid sm:grid-cols-2 gap-1.5 max-h-36 overflow-y-auto">
                                @foreach($incomeCategories as $catItem)
                                    <div class="flex justify-between items-center bg-slate-50 px-2.5 py-1 rounded border">
                                        <span class="font-semibold truncate">{{ $catItem }}</span>
                                        <form action="{{ route('admin.options.update') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="option_key" value="custom_income_categories">
                                            <input type="hidden" name="operation" value="remove">
                                            <input type="hidden" name="remove_option" value="{{ $catItem }}">
                                            <input type="hidden" name="return_tab" value="kasa">
                                            <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Gider Kategorileri Ekle/Sil -->
                        <div class="bg-white rounded-xl p-4 space-y-2.5">
                            <div class="font-extrabold text-rose-700 border-b pb-1.5">🔴 Gider Kategorileri</div>
                            <form action="{{ route('admin.options.update') }}" method="POST" class="flex gap-1.5">
                                @csrf
                                <input type="hidden" name="option_key" value="custom_expense_categories">
                                <input type="hidden" name="operation" value="add">
                                <input type="hidden" name="return_tab" value="kasa">
                                <input type="text" name="new_option" required placeholder="Yeni Gider Kategorisi (Ör: Muhasebe / Vergi)" class="w-full border rounded-lg px-2.5 py-1.5">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-bold shrink-0">➕ Ekle</button>
                            </form>
                            <div class="grid sm:grid-cols-2 gap-1.5 max-h-36 overflow-y-auto">
                                @foreach($expenseCategories as $catItem)
                                    <div class="flex justify-between items-center bg-slate-50 px-2.5 py-1 rounded border">
                                        <span class="font-semibold truncate">{{ $catItem }}</span>
                                        <form action="{{ route('admin.options.update') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="option_key" value="custom_expense_categories">
                                            <input type="hidden" name="operation" value="remove">
                                            <input type="hidden" name="remove_option" value="{{ $catItem }}">
                                            <input type="hidden" name="return_tab" value="kasa">
                                            <button type="submit" class="text-rose-600 font-extrabold">✕</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </details>

                <div class="grid sm:grid-cols-3 gap-4">
                    @foreach($cashAccounts as $acc)
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-bold uppercase text-indigo-600">{{ $acc->type }}</span>
                                    <h3 class="font-extrabold text-base mt-0.5">{{ $acc->name }}</h3>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" onclick="document.getElementById('edit-acc-{{ $acc->id }}').classList.toggle('hidden')" class="px-2 py-1 rounded bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-bold">✏️</button>
                                    <form action="{{ route('admin.cash_accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('{{ $acc->name }} hesabını silmek istediğinize emin misiniz?')">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold">🗑️</button>
                                    </form>
                                </div>
                            </div>
                            <div class="text-2xl font-extrabold text-slate-900">{{ number_format($acc->balance, 2, ',', '.') }} ₺</div>
                            @if($acc->iban)
                                <div class="text-[11px] font-mono text-slate-500">{{ $acc->iban }}</div>
                            @endif
                            <form id="edit-acc-{{ $acc->id }}" action="{{ route('admin.cash_accounts.update', $acc->id) }}" method="POST" class="hidden pt-2 border-t space-y-1.5 text-xs">
                                @csrf
                                <input type="text" name="name" value="{{ $acc->name }}" required class="w-full border rounded-lg px-2 py-1">
                                <div class="grid grid-cols-2 gap-1">
                                    <select name="type" class="border rounded-lg px-2 py-1 bg-white">
                                        <option value="cash" {{ $acc->type === 'cash' ? 'selected' : '' }}>Nakit</option>
                                        <option value="bank" {{ $acc->type === 'bank' ? 'selected' : '' }}>Banka</option>
                                        <option value="pos" {{ $acc->type === 'pos' ? 'selected' : '' }}>POS</option>
                                    </select>
                                    <input type="number" step="0.01" name="balance" value="{{ $acc->balance }}" required class="border rounded-lg px-2 py-1 font-bold">
                                </div>
                                <input type="text" name="iban" value="{{ $acc->iban }}" placeholder="IBAN" class="w-full border rounded-lg px-2 py-1 font-mono">
                                <button type="submit" class="w-full py-1 rounded-lg bg-indigo-600 text-white font-bold">💾 Hesabı Güncelle</button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="grid lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm h-fit">
                        <h3 class="text-base font-extrabold mb-3">➕ Yeni Gelir veya Gider Hareketi Ekle</h3>
                        <form action="{{ route('admin.transactions.store') }}" method="POST" class="space-y-3 text-xs">
                            @csrf
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="font-bold block mb-1">İşlem Türü</label>
                                    <select name="direction" id="tx-direction" onchange="enerjikToggleTxCategory(this.value)" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                                        <option value="expense">🔴 Gider Çıkışı</option>
                                        <option value="income">🟢 Gelir Girişi</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Kategori</label>
                                    <select name="category" id="tx-category-expense" class="w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($expenseCategories as $fc)
                                            <option value="{{ $fc }}">{{ $fc }}</option>
                                        @endforeach
                                    </select>
                                    <select name="category" id="tx-category-income" disabled class="hidden w-full border rounded-xl px-3 py-2 bg-white">
                                        @foreach($incomeCategories as $fc)
                                            <option value="{{ $fc }}">{{ $fc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <script>
                                function enerjikToggleTxCategory(direction) {
                                    var expenseSel = document.getElementById('tx-category-expense');
                                    var incomeSel = document.getElementById('tx-category-income');
                                    var showIncome = direction === 'income';
                                    expenseSel.classList.toggle('hidden', showIncome);
                                    expenseSel.disabled = showIncome;
                                    incomeSel.classList.toggle('hidden', !showIncome);
                                    incomeSel.disabled = !showIncome;
                                }
                            </script>
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
                        <h3 class="text-base font-extrabold mb-3">📊 Gelir - Gider Hareketleri (Düzenle & Sil)</h3>
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                <tr>
                                    <th class="p-2.5">Tarih</th>
                                    <th class="p-2.5">Kategori / Başlık</th>
                                    <th class="p-2.5">Hesap</th>
                                    <th class="p-2.5 text-right">Tutar & İşlem</th>
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
                                        <td class="p-2.5 text-right">
                                            <div class="flex justify-end items-center gap-1.5">
                                                <span class="font-extrabold {{ $tx->direction === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    {{ $tx->direction === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2, ',', '.') }} ₺
                                                </span>
                                                <button type="button" onclick="document.getElementById('edit-tx-{{ $tx->id }}').classList.toggle('hidden')" class="px-2 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 font-bold">✏️</button>
                                                <form action="{{ route('admin.transactions.destroy', $tx->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ $tx->title }} kasa hareketini silmek istediğinize emin misiniz?')">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 font-bold transition">🗑️ Sil</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="edit-tx-{{ $tx->id }}" class="hidden bg-indigo-50/50">
                                        <td colspan="4" class="p-3">
                                            <form action="{{ route('admin.transactions.update', $tx->id) }}" method="POST" class="grid sm:grid-cols-5 gap-2 items-end text-xs">
                                                @csrf
                                                <div class="sm:col-span-2">
                                                    <label class="font-bold block mb-0.5">Başlık / Açıklama</label>
                                                    <input type="text" name="title" value="{{ $tx->title }}" required class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                </div>
                                                <div>
                                                    <label class="font-bold block mb-0.5">Kategori</label>
                                                    <input type="text" name="category" value="{{ $tx->category }}" required class="w-full border rounded-lg px-2 py-1.5 bg-white">
                                                </div>
                                                <div>
                                                    <label class="font-bold block mb-0.5">Tutar (₺)</label>
                                                    <input type="number" step="0.01" name="amount" value="{{ $tx->amount }}" required class="w-full border rounded-lg px-2 py-1.5 bg-white font-bold">
                                                </div>
                                                <div>
                                                    <input type="hidden" name="transaction_date" value="{{ $tx->transaction_date->format('Y-m-d') }}">
                                                    <button type="submit" class="w-full py-1.5 rounded-lg bg-indigo-600 text-white font-bold">💾 Güncelle</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
