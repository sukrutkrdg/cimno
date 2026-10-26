                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Kabul Kriteri #5: Sekreter / Antrenör Hesabı Açma ve Kapatma -->
                    <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm h-fit">
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kullanıcı & Personel Yönetimi</span>
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
                                    <input type="text" name="phone" required placeholder="532XXXXXXX (başında 0 olmadan 10 hane)" maxlength="15" class="w-full border rounded-xl px-3 py-2">
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
                                <label class="font-bold block mb-1">Giriş Şifresi</label>
                                <input type="password" name="password" minlength="6" placeholder="Boş bırakırsanız otomatik güçlü şifre üretilir" class="w-full border rounded-xl px-3 py-2 font-mono font-bold">
                                <p class="text-[11px] text-slate-500 mt-1">En az 6 karakter. Boş bırakırsanız sistem rastgele güçlü bir şifre üretir ve ekranda gösterir.</p>
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
                                    <p class="text-[11px] text-slate-500">Sekreter ve Antrenör hesaplarında mali/finansal bilgiler, Excel ve yedek indirme tamamen kapalıdır.</p>
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
                                                    👑 Süper Yönetici (Salon Sahibi) tüm 16 modüle, kasa/gider hesaplarına, personel yetkilerine, Excel çıktısına ve veritabanı yedeğine tam erişime sahiptir.
                                                </div>
                                            @else
                                                <div class="p-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-semibold">
                                                    🔒 Mali bilgiler, aidat/tahsilat, kasa, gelir-gider Excel çıktısı ve veritabanı yedekleme bu rol için tamamen gizlidir.
                                                </div>
                                                <div class="grid sm:grid-cols-3 gap-2 pt-1">
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_send_whatsapp" value="1" {{ $u->hasPermission('send_whatsapp', $u->role === 'secretary') ? 'checked' : '' }}>
                                                        <span>💬 WhatsApp Kuyruğu Gönderebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_edit_content" value="1" {{ $u->hasPermission('edit_content', $u->role === 'secretary') ? 'checked' : '' }}>
                                                        <span>🌐 Site İçeriği Düzenleyebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_only_own_groups" value="1" {{ $u->hasPermission('only_own_groups', $u->role === 'coach') ? 'checked' : '' }}>
                                                        <span>🎽 Yalnızca Kendi Gruplarını Görsün</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_take_attendance" value="1" {{ $u->hasPermission('take_attendance', true) ? 'checked' : '' }}>
                                                        <span>✅ Yoklama Alabilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_add_development" value="1" {{ $u->hasPermission('add_development', true) ? 'checked' : '' }}>
                                                        <span>📈 Gelişim Karnesi Girebilsin</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer">
                                                        <input type="checkbox" name="perm_delete_student" value="1" {{ $u->hasPermission('delete_student', true) ? 'checked' : '' }}>
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
                                            <td class="p-2.5">
                                                {{ $cp['lesson_count'] }} Ders
                                                @if($cp['is_estimated'])
                                                    <span class="text-[10px] text-amber-600 font-bold" title="Bu ay henüz yoklama kaydı yok, program bazlı tahmini sayı gösteriliyor.">(tahmini)</span>
                                                @endif
                                            </td>
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
