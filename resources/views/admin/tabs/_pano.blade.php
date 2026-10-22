                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-extrabold">📊 Salon Yönetim Panosu</h2>
                        <p class="text-xs text-slate-500">Bugünkü dersler, sporcu yoklama durumu, bekleyen adaylar ve WhatsApp Business hazır mesaj kuyruğu</p>
                    </div>
                    @if($currentRole === 'super_admin')
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.reports.csv') }}" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold">📥 Gelir-Gider Excel İndir</a>
                            <a href="{{ route('admin.backup.download') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold">💾 Veritabanı Yedeği İndir</a>
                        </div>
                    @endif
                </div>

                <!-- Özet Kartlar -->
                <div class="grid sm:grid-cols-2 {{ $currentRole === 'super_admin' ? 'lg:grid-cols-4' : 'lg:grid-cols-2' }} gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="text-xs font-bold text-slate-500">Aktif Kayıtlı Sporcu</span>
                        <div class="text-3xl font-extrabold text-indigo-700 mt-1">{{ $activeStudentsCount }}</div>
                        <span class="text-[11px] text-emerald-600 font-semibold">{{ $groups->count() }} Aktif Grup</span>
                    </div>
                    @if($currentRole === 'super_admin')
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span class="text-xs font-bold text-slate-500">Bu Ayki Tahsilat</span>
                            <div class="text-3xl font-extrabold text-emerald-600 mt-1">{{ number_format($monthCollection, 0, ',', '.') }} ₺</div>
                            <span class="text-[11px] text-slate-500">Bugün: {{ number_format($todayCollection, 0, ',', '.') }} ₺</span>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                            <span class="text-xs font-bold text-slate-500">Geciken / Bekleyen Aidat</span>
                            <div class="text-3xl font-extrabold text-rose-600 mt-1">{{ number_format($pendingTotal, 0, ',', '.') }} ₺</div>
                            <span class="text-[11px] text-rose-600 font-semibold">Vadesi Geçen: {{ number_format($overdueTotal, 0, ',', '.') }} ₺</span>
                        </div>
                    @endif
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="text-xs font-bold text-slate-500">Bekleyen Deneme Adayı</span>
                        <div class="text-3xl font-extrabold text-amber-600 mt-1">{{ $newLeadsCount }}</div>
                        @if(in_array('adaylar', $allowedTabs, true))
                            <a href="{{ route('admin.index', ['tab' => 'adaylar', 'role_view' => $currentRole]) }}" class="text-[11px] text-indigo-600 font-bold underline">Adayları Yönet →</a>
                        @else
                            <span class="text-[11px] text-slate-400">Ön Kayıt & Deneme Takibi</span>
                        @endif
                    </div>
                </div>

                <!-- Bugünkü Yoklama Özeti -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                        <h3 class="font-extrabold text-sm text-indigo-900">✅ Bugünkü Yoklama Durumu</h3>
                        @if($currentRole === 'super_admin' && in_array('raporlar', $allowedTabs, true))
                            <a href="{{ route('admin.index', ['tab' => 'raporlar', 'role_view' => $currentRole]) }}" class="text-[11px] text-indigo-600 font-bold underline">Detaylı Analiz & Grafikler →</a>
                        @endif
                    </div>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <div class="p-3.5 rounded-xl bg-slate-50 border text-center">
                            <div class="text-[11px] font-bold text-slate-500">Bugün Alınan Kayıt</div>
                            <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $todayAttendanceTotal }}</div>
                        </div>
                        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-center">
                            <div class="text-[11px] font-bold text-emerald-700">✅ Geldi</div>
                            <div class="text-xl font-extrabold text-emerald-700 mt-0.5">{{ $todayAttendanceCounts['present'] ?? 0 }}</div>
                        </div>
                        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-center">
                            <div class="text-[11px] font-bold text-rose-700">❌ Gelmedi</div>
                            <div class="text-xl font-extrabold text-rose-700 mt-0.5">{{ $todayAttendanceCounts['absent'] ?? 0 }}</div>
                        </div>
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-center">
                            <div class="text-[11px] font-bold text-amber-700">🩺 Raporlu/🔄 Telafi</div>
                            <div class="text-xl font-extrabold text-amber-700 mt-0.5">{{ ($todayAttendanceCounts['excused'] ?? 0) + ($todayAttendanceCounts['makeup'] ?? 0) }}</div>
                        </div>
                        <div class="p-3.5 rounded-xl bg-indigo-50 border border-indigo-200 text-center">
                            <div class="text-[11px] font-bold text-indigo-700">Son 7 Gün Katılım</div>
                            <div class="text-xl font-extrabold text-indigo-700 mt-0.5">{{ $weekAttendanceRate !== null ? '%' . number_format($weekAttendanceRate, 1, ',', '.') : '—' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Periyodik Uyarılar: 2+ Ders Kaçıranlar & Sağlık Raporu Bitenler & WhatsApp Kuyruğu -->
                <div class="grid lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-extrabold text-sm text-rose-700 mb-3">⚠️ Üst Üste 2+ Ders Kaçıranlar ({{ $absentWarnStudents->count() }})</h3>
                        @forelse($absentWarnStudents as $st)
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 flex justify-between items-center mb-2">
                                <div>
                                    <div class="font-bold text-xs">{{ $st->full_name }} ({{ $st->consecutive_absences }} Ders)</div>
                                    <div class="text-[11px] text-slate-600">Veli: {{ $st->parent_name }} · {{ $st->parent_phone }}</div>
                                </div>
                                <a href="https://wa.me/90{{ substr(preg_replace('/\D+/', '', $st->parent_phone), -10) }}?text={{ urlencode('Merhaba ' . $st->parent_name . ', sporcumuz ' . $st->full_name . ' son 2 antrenmanımıza katılamamıştır. Her şeyin yolunda olduğunu umuyoruz, telafi planlaması için bize ulaşabilirsiniz.') }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">💬 WhatsApp</a>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">Üst üste devamsızlık yapan öğrenci yok.</p>
                        @endforelse
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-extrabold text-sm text-amber-700 mb-3">🩺 Sağlık Raporu Yenilenecekler ({{ $expiringMedicalStudents->count() }})</h3>
                        @forelse($expiringMedicalStudents as $st)
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 flex justify-between items-center mb-2">
                                <div>
                                    <div class="font-bold text-xs">{{ $st->full_name }}</div>
                                    <div class="text-[11px] text-slate-600">Bitiş: {{ $st->medical_report_expires_at?->format('d.m.Y') }}</div>
                                </div>
                                <a href="https://wa.me/90{{ substr(preg_replace('/\D+/', '', $st->parent_phone), -10) }}?text={{ urlencode('Sayın ' . $st->parent_name . ', ' . $st->full_name . ' ait sağlık raporu süresi dolmaktadır. Yenilenen raporu iletmenizi rica ederiz.') }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">💬 Hatırlat</a>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500">15 gün içinde raporu dolacak öğrenci yok.</p>
                        @endforelse
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-extrabold text-sm text-emerald-700">💬 Bekleyen WhatsApp Kuyruğu</h3>
                            @if(in_array('mesajlar', $allowedTabs, true))
                                <a href="{{ route('admin.index', ['tab' => 'mesajlar', 'role_view' => $currentRole]) }}" class="text-xs text-indigo-600 font-bold underline">Tümünü Gör</a>
                            @endif
                        </div>
                        @foreach($whatsappQueue->where('status', 'queued')->take(3) as $msg)
                            <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 mb-2 flex justify-between items-center gap-2">
                                <div class="text-xs truncate">
                                    <strong class="block">{{ $msg->recipient_name }}</strong>
                                    <span class="text-[11px] text-slate-600">{{ Str::limit($msg->message_text, 55) }}</span>
                                </div>
                                <form action="{{ route('admin.whatsapp.send', $msg->id) }}" method="POST" target="_blank" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">Gönder →</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
