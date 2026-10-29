                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <span class="text-[11px] font-bold uppercase text-indigo-600">Komple Takvim</span>
                    <h2 class="text-xl font-extrabold mb-1">📅 Haftalık Hareket Takvimi</h2>
                    <p class="text-xs text-slate-500 mb-5">
                        @if($currentRole === 'coach')
                            Gruplarınızın bu hafta hangi derste hangi hareketi çalışacağı aşağıda listelenir.
                        @else
                            Hangi gün, hangi grupta, hangi hareket(ler)in çalışılacağını buradan planlayın. Bir derse tıklayarak hareket atamasını düzenleyebilirsiniz; antrenörler bu takvimi kendi panellerinde salt okunur olarak görür.
                        @endif
                    </p>

                    @if($schedules->isEmpty())
                        <p class="text-sm text-slate-400 italic">Henüz haftalık ders programı eklenmemiş. Ders programı eklemek için <a href="{{ route('admin.index', ['tab' => 'gruplar']) }}" class="text-indigo-600 underline font-bold">Branş, Grup & Program</a> sekmesine gidin.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
                            @foreach(['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'] as $gun)
                                @php $gunSchedules = $schedules->where('day_of_week', $gun)->sortBy('start_time'); @endphp
                                <div class="bg-slate-50 rounded-2xl border border-slate-200 p-2.5 min-h-[8rem]">
                                    <div class="text-xs font-extrabold text-indigo-900 bg-indigo-100 rounded-lg px-2.5 py-1.5 mb-2.5 text-center">{{ $gun }}</div>
                                    <div class="space-y-2">
                                        @forelse($gunSchedules as $sch)
                                            @php
                                                $uygunHareketler = $movements->filter(fn($mv) => $mv->branch_id === null || $mv->branch_id === $sch->group?->branch_id);
                                            @endphp
                                            <div class="bg-white rounded-xl border border-slate-200 p-2.5 text-[11px] shadow-sm">
                                                <div class="font-extrabold text-slate-900">{{ substr($sch->start_time, 0, 5) }}-{{ substr($sch->end_time, 0, 5) }}</div>
                                                <div class="font-bold text-indigo-700 truncate">{{ $sch->group?->name }}</div>
                                                <div class="text-slate-400 text-[10px] mb-1.5 truncate">{{ $sch->group?->branch?->name }} · {{ $sch->group?->coach?->name ?? '-' }}</div>

                                                @if($sch->movements->isNotEmpty())
                                                    <div class="flex flex-wrap gap-1 mb-1.5">
                                                        @foreach($sch->movements as $mv)
                                                            <span class="px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-bold text-[10px]">{{ $mv->name }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-slate-400 italic mb-1.5">Hareket atanmadı.</div>
                                                @endif

                                                @if($currentRole !== 'coach')
                                                    <button type="button" onclick="document.getElementById('mv-edit-{{ $sch->id }}').classList.toggle('hidden')" class="text-indigo-600 font-bold text-[10px] underline">
                                                        ✏️ Hareketleri Düzenle
                                                    </button>
                                                    <form id="mv-edit-{{ $sch->id }}" action="{{ route('admin.schedules.movements.update', $sch->id) }}" method="POST" class="hidden mt-1.5 pt-1.5 border-t border-slate-100">
                                                        @csrf
                                                        <div class="flex flex-wrap gap-1 mb-1.5">
                                                            @forelse($uygunHareketler as $mv)
                                                                <label class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-lg bg-slate-50 border border-slate-200 cursor-pointer">
                                                                    <input type="checkbox" name="movement_ids[]" value="{{ $mv->id }}" {{ $sch->movements->contains('id', $mv->id) ? 'checked' : '' }}>
                                                                    <span>{{ $mv->name }}</span>
                                                                </label>
                                                            @empty
                                                                <span class="text-slate-400 italic">Hareket kataloğu boş.</span>
                                                            @endforelse
                                                        </div>
                                                        @if($uygunHareketler->isNotEmpty())
                                                            <button type="submit" class="w-full py-1 rounded-lg bg-indigo-600 text-white font-bold text-[10px]">💾 Kaydet</button>
                                                        @endif
                                                    </form>
                                                @endif
                                            </div>
                                        @empty
                                            <p class="text-[11px] text-slate-400 italic text-center py-4">Ders yok</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($currentRole !== 'coach' && $movements->isEmpty())
                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3 mt-4">
                                ⚠️ Hareket kataloğunuz boş. Hareket eklemek için <a href="{{ route('admin.index', ['tab' => 'gruplar']) }}" class="underline font-bold">Branş, Grup & Program</a> sekmesindeki "Seçenekleri Özelleştir" panosunu açın.
                            </p>
                        @endif
                    @endif
                </div>
