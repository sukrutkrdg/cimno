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
