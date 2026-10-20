                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Sistem Yönetimi & Veri Temizliği</span>
                        <h2 class="text-2xl font-extrabold">⚙️ Sistem Ayarları, Veritabanı Yedeği ve Test Kayıtlarını Temizleme</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form action="{{ route('admin.demo.clear') }}" method="POST" onsubmit="return confirm('Sistemdeki tüm deneme/test kayıtlarını (adaylar, öğrenciler, gruplar, ders programı, yoklama, aidat ve kasa hareketleri) tek seferde temizlemek istediğinize emin misiniz?')">
                            @csrf
                            <button type="submit" class="px-4 py-3 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold shadow">
                                🧹 Tüm Test / Deneme Kayıtlarını Temizle
                            </button>
                        </form>
                        <a href="{{ route('admin.backup.download') }}" class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold shadow">
                            💾 Tam Veritabanı Yedeğini İndir (.JSON)
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-base font-extrabold mb-3">🛡️ İşlem Günlüğü (Audit Logs - Kritik İşlem Kayıtları)</h3>
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b text-slate-600 uppercase">
                            <tr>
                                <th class="p-2.5">Tarih / Saat</th>
                                <th class="p-2.5">İşlem Kodu</th>
                                <th class="p-2.5">Açıklama</th>
                                <th class="p-2.5">IP Adresi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($auditLogs as $log)
                                <tr>
                                    <td class="p-2.5 font-mono text-[11px]">{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                                    <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-slate-100 font-mono font-bold">{{ $log->action }}</span></td>
                                    <td class="p-2.5 font-semibold">{{ $log->description }}</td>
                                    <td class="p-2.5 font-mono text-[11px] text-slate-500">{{ $log->ip_address }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
