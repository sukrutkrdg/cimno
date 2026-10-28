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

                    <!-- Son Yoklama Kayıtları & Silme -->
                    <div class="mt-6 pt-6 border-t border-slate-200">
                        <h3 class="text-base font-extrabold mb-3">📋 Son Alınan Yoklama Kayıtları (Durumu Düzelt / Kaydı Sil)</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                    <tr>
                                        <th class="p-2.5">Tarih</th>
                                        <th class="p-2.5">Sporcu</th>
                                        <th class="p-2.5">Grup</th>
                                        <th class="p-2.5">Durum / Not</th>
                                        <th class="p-2.5 text-right">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($attendances as $att)
                                        <tr>
                                            <td class="p-2.5 font-mono">{{ $att->attendance_date?->format('d.m.Y') }}</td>
                                            <td class="p-2.5 font-bold">{{ $att->student?->full_name ?? '-' }}</td>
                                            <td class="p-2.5">{{ $att->group?->name ?? '-' }}</td>
                                            <td class="p-2.5">
                                                <form action="{{ route('admin.attendance.update', $att->id) }}" method="POST" class="flex items-center gap-1.5">
                                                    @csrf
                                                    <select name="status" class="border rounded-lg px-1.5 py-1 bg-white text-[11px] font-bold">
                                                        <option value="present" {{ $att->status === 'present' ? 'selected' : '' }}>✅ Geldi</option>
                                                        <option value="absent" {{ $att->status === 'absent' ? 'selected' : '' }}>❌ Gelmedi</option>
                                                        <option value="excused" {{ $att->status === 'excused' ? 'selected' : '' }}>🩺 Raporlu / İzinli (+1 Telafi)</option>
                                                        <option value="makeup" {{ $att->status === 'makeup' ? 'selected' : '' }}>🔄 Telafi Kullandı</option>
                                                    </select>
                                                    <button type="submit" class="px-2 py-1 rounded-lg bg-slate-900 text-white text-[11px] font-bold shrink-0">💾</button>
                                                </form>
                                                @if($att->note)
                                                    <span class="text-slate-500 block mt-0.5">· {{ $att->note }}</span>
                                                @endif
                                            </td>
                                            <td class="p-2.5 text-right">
                                                <form action="{{ route('admin.attendance.destroy', $att->id) }}" method="POST" class="inline" onsubmit="return confirm('Bu yoklama kaydını silmek istediğinize emin misiniz?')">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 font-bold transition">🗑️ Sil</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-3 text-slate-500">Henüz yoklama kaydı bulunmuyor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
