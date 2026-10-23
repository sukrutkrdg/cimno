                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #7</span>
                        <h2 class="text-2xl font-extrabold">📑 Finansal Raporlar, Yaşlandırma ve Excel İndirme</h2>
                    </div>
                    <a href="{{ route('admin.reports.csv') }}" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold shadow">
                        📥 Aylık Gelir-Gider Raporunu Excel (CSV) Olarak İndir
                    </a>
                </div>

                <div class="grid md:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500">0 - 30 Gün Geciken Alacaklar</span>
                        <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($agingBuckets['0_30'], 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500">31 - 60 Gün Geciken Alacaklar</span>
                        <div class="text-2xl font-extrabold text-orange-600 mt-1">{{ number_format($agingBuckets['31_60'], 2, ',', '.') }} ₺</div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-500">61+ Gün Kritik Gecikenler</span>
                        <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($agingBuckets['61_plus'], 2, ',', '.') }} ₺</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-extrabold text-base mb-3">📊 Branş Bazlı Öğrenci ve Doluluk Dağılımı</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        @foreach($branches as $b)
                            <div class="p-4 rounded-xl bg-slate-50 border text-xs">
                                <span class="text-xl">{{ $b->icon }}</span>
                                <div class="font-extrabold mt-1">{{ $b->name }}</div>
                                <div class="text-indigo-600 font-bold mt-1">{{ $b->students_count }} Kayıtlı Sporcu</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>

                <!-- ==================================================== -->
                <!-- AYLIK GELİR-GİDER TRENDİ (Son 12 Ay)                 -->
                <!-- ==================================================== -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Finansal Trend</span>
                        <h3 class="font-extrabold text-base">💹 Aylık Gelir-Gider Trendi (Son 12 Ay)</h3>
                        <p class="text-xs text-slate-500">Gelir: tahsilatlar + diğer gelir hareketleri (ör. ürün satışı). Gider: kasa modülündeki gider kayıtları.</p>
                    </div>
                    <canvas id="chartFinanceTrend" height="90"></canvas>
                </div>
                <script>
                (function () {
                    const trend = @json($monthlyFinanceTrend);
                    new Chart(document.getElementById('chartFinanceTrend'), {
                        type: 'bar',
                        data: {
                            labels: trend.map(r => r.label),
                            datasets: [
                                { label: 'Gelir', data: trend.map(r => r.gelir), backgroundColor: '#059669', borderRadius: 4 },
                                { label: 'Gider', data: trend.map(r => r.gider), backgroundColor: '#e11d48', borderRadius: 4 },
                            ],
                        },
                        options: {
                            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
                            scales: { x: { ticks: { font: { size: 10 } } }, y: { beginAtZero: true, ticks: { font: { size: 10 } } } },
                        },
                    });
                })();
                </script>

                <!-- ==================================================== -->
                <!-- YOKLAMA ANALİZ & RAPORLARI (Çoklu Kriter + Grafik)   -->
                <!-- ==================================================== -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-5">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Yoklama Analizi</span>
                        <h3 class="font-extrabold text-base">✅ Çoklu Kriterli Yoklama Raporu & Grafikler</h3>
                        <p class="text-xs text-slate-500">Tarih aralığı, branş, grup veya antrenöre göre filtreleyip katılım oranını, günlük trendi ve devamsızlıkları inceleyin.</p>
                    </div>

                    <!-- Filtre Formu -->
                    <form method="GET" action="{{ route('admin.index') }}" class="grid sm:grid-cols-2 lg:grid-cols-6 gap-2.5 items-end bg-slate-50 border border-slate-200 rounded-2xl p-4">
                        <input type="hidden" name="tab" value="raporlar">
                        <input type="hidden" name="role_view" value="{{ $currentRole }}">
                        <div>
                            <label class="font-bold text-[11px] block mb-1">Başlangıç</label>
                            <input type="date" name="att_from" value="{{ $attFrom }}" class="w-full border rounded-xl px-2.5 py-2 text-xs bg-white">
                        </div>
                        <div>
                            <label class="font-bold text-[11px] block mb-1">Bitiş</label>
                            <input type="date" name="att_to" value="{{ $attTo }}" class="w-full border rounded-xl px-2.5 py-2 text-xs bg-white">
                        </div>
                        <div>
                            <label class="font-bold text-[11px] block mb-1">Branş</label>
                            <select name="att_branch_id" class="w-full border rounded-xl px-2.5 py-2 text-xs bg-white">
                                <option value="">Tümü</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (string) $attBranchId === (string) $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-[11px] block mb-1">Grup</label>
                            <select name="att_group_id" class="w-full border rounded-xl px-2.5 py-2 text-xs bg-white">
                                <option value="">Tümü</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}" {{ (string) $attGroupId === (string) $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-[11px] block mb-1">Antrenör</label>
                            <select name="att_coach_id" class="w-full border rounded-xl px-2.5 py-2 text-xs bg-white">
                                <option value="">Tümü</option>
                                @foreach($staff->where('role', 'coach') as $c)
                                    <option value="{{ $c->id }}" {{ (string) $attCoachId === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold">🔍 Filtrele</button>
                    </form>

                    <!-- Özet Kartlar -->
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200">
                            <span class="text-[11px] font-bold text-indigo-700">Toplam Yoklama Kaydı</span>
                            <div class="text-2xl font-extrabold text-indigo-900 mt-1">{{ $attendanceTotal }}</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
                            <span class="text-[11px] font-bold text-emerald-700">Katılım Oranı</span>
                            <div class="text-2xl font-extrabold text-emerald-700 mt-1">%{{ number_format($attendanceRate, 1, ',', '.') }}</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200">
                            <span class="text-[11px] font-bold text-rose-700">Gelmedi (Devamsız)</span>
                            <div class="text-2xl font-extrabold text-rose-700 mt-1">{{ $attendanceStatusCounts['absent'] ?? 0 }}</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200">
                            <span class="text-[11px] font-bold text-amber-700">Raporlu / Telafi</span>
                            <div class="text-2xl font-extrabold text-amber-700 mt-1">{{ ($attendanceStatusCounts['excused'] ?? 0) + ($attendanceStatusCounts['makeup'] ?? 0) }}</div>
                        </div>
                    </div>

                    @if($attendanceTotal > 0)
                        <!-- Grafikler -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div class="p-4 rounded-2xl border border-slate-200">
                                <h4 class="text-xs font-extrabold text-slate-600 mb-2">Durum Dağılımı</h4>
                                <canvas id="chartAttStatus" height="200"></canvas>
                            </div>
                            <div class="p-4 rounded-2xl border border-slate-200">
                                <h4 class="text-xs font-extrabold text-slate-600 mb-2">Günlük Katılım Oranı Trendi (%)</h4>
                                <canvas id="chartAttTrend" height="200"></canvas>
                            </div>
                            <div class="p-4 rounded-2xl border border-slate-200">
                                <h4 class="text-xs font-extrabold text-slate-600 mb-2">Branş Bazlı Katılım Oranı (%)</h4>
                                <canvas id="chartAttBranch" height="220"></canvas>
                            </div>
                            <div class="p-4 rounded-2xl border border-slate-200">
                                <h4 class="text-xs font-extrabold text-slate-600 mb-2">Grup Bazlı Katılım Oranı (%)</h4>
                                <canvas id="chartAttGroup" height="220"></canvas>
                            </div>
                        </div>

                        <!-- En Çok Devamsızlık Yapanlar -->
                        <div class="p-4 rounded-2xl border border-slate-200">
                            <h4 class="text-xs font-extrabold text-rose-700 mb-2">⚠️ Seçili Aralıkta En Çok Devamsızlık Yapanlar (İlk 10)</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                                        <tr>
                                            <th class="p-2">Sporcu</th>
                                            <th class="p-2 text-right">Devamsızlık Sayısı</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @forelse($attendanceTopAbsentees as $row)
                                            <tr>
                                                <td class="p-2 font-bold">{{ $row->full_name }}</td>
                                                <td class="p-2 text-right font-extrabold text-rose-700">{{ $row->devamsizlik }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="p-2 text-slate-500">Seçili kriterlerde devamsızlık kaydı yok.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <script>
                        (function () {
                            const statusLabels = { present: 'Geldi', absent: 'Gelmedi', excused: 'Raporlu/İzinli', makeup: 'Telafi' };
                            const statusColors = { present: '#059669', absent: '#e11d48', excused: '#d97706', makeup: '#4f46e5' };
                            const rawStatus = @json($attendanceStatusCounts);
                            const statusKeys = Object.keys(rawStatus);

                            new Chart(document.getElementById('chartAttStatus'), {
                                type: 'doughnut',
                                data: {
                                    labels: statusKeys.map(k => statusLabels[k] || k),
                                    datasets: [{
                                        data: statusKeys.map(k => rawStatus[k]),
                                        backgroundColor: statusKeys.map(k => statusColors[k] || '#94a3b8'),
                                    }],
                                },
                                options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } },
                            });

                            const trend = @json($attendanceDailyTrend);
                            new Chart(document.getElementById('chartAttTrend'), {
                                type: 'line',
                                data: {
                                    labels: trend.map(r => r.attendance_date),
                                    datasets: [{
                                        label: 'Katılım Oranı (%)',
                                        data: trend.map(r => r.toplam > 0 ? Math.round((r.gelen / r.toplam) * 1000) / 10 : 0),
                                        borderColor: '#4f46e5',
                                        backgroundColor: 'rgba(79,70,229,0.15)',
                                        fill: true,
                                        tension: 0.3,
                                    }],
                                },
                                options: {
                                    plugins: { legend: { display: false } },
                                    scales: { y: { beginAtZero: true, max: 100, ticks: { font: { size: 10 } } }, x: { ticks: { font: { size: 9 } } } },
                                },
                            });

                            const byBranch = @json($attendanceByBranch);
                            new Chart(document.getElementById('chartAttBranch'), {
                                type: 'bar',
                                data: {
                                    labels: byBranch.map(r => r.branch_name),
                                    datasets: [{
                                        label: 'Katılım Oranı (%)',
                                        data: byBranch.map(r => r.toplam > 0 ? Math.round((r.gelen / r.toplam) * 1000) / 10 : 0),
                                        backgroundColor: '#0d9488',
                                    }],
                                },
                                options: {
                                    indexAxis: 'y',
                                    plugins: { legend: { display: false } },
                                    scales: { x: { beginAtZero: true, max: 100, ticks: { font: { size: 10 } } }, y: { ticks: { font: { size: 10 } } } },
                                },
                            });

                            const byGroup = @json($attendanceByGroup);
                            new Chart(document.getElementById('chartAttGroup'), {
                                type: 'bar',
                                data: {
                                    labels: byGroup.map(r => r.group_name),
                                    datasets: [{
                                        label: 'Katılım Oranı (%)',
                                        data: byGroup.map(r => r.toplam > 0 ? Math.round((r.gelen / r.toplam) * 1000) / 10 : 0),
                                        backgroundColor: '#7d2945',
                                    }],
                                },
                                options: {
                                    indexAxis: 'y',
                                    plugins: { legend: { display: false } },
                                    scales: { x: { beginAtZero: true, max: 100, ticks: { font: { size: 10 } } }, y: { ticks: { font: { size: 10 } } } },
                                },
                            });
                        })();
                        </script>
                    @else
                        <p class="text-xs text-slate-500">Seçili tarih aralığı ve kriterlerde yoklama kaydı bulunamadı.</p>
                    @endif
                </div>
