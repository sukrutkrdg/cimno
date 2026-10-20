                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-emerald-700">Meta Doğrulaması Gerektirmeyen Pratik Çözüm</span>
                        <h2 class="text-2xl font-extrabold">💬 WhatsApp Business Hazır Cevapları & Tıkla-Gönder Motoru</h2>
                        <p class="text-xs text-slate-500">Şablon metinlerini ekleyin, düzenleyin ve gönderim saatlerini ayarlayın; günlük hatırlatma kuyruğunu tek tıkla gönderin.</p>
                    </div>
                    <form action="{{ route('admin.whatsapp.generate') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow">
                            🔄 Bugünün Hatırlatma Kuyruğunu Otomatik Oluştur
                        </button>
                    </form>
                </div>

                <!-- Manuel WhatsApp Mesajı & Yeni Şablon Ekleme -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-sm font-extrabold text-emerald-800 mb-3">➕ Kuyruğa Manuel Özel WhatsApp Mesajı Ekle</h3>
                        <form action="{{ route('admin.whatsapp.message.store') }}" method="POST" class="space-y-2.5 text-xs">
                            @csrf
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="recipient_name" required placeholder="Alıcı Veli Adı *" class="border rounded-xl px-3 py-2">
                                <input type="text" name="recipient_phone" required placeholder="Telefon (0532...) *" class="border rounded-xl px-3 py-2 font-mono">
                            </div>
                            <textarea name="message_text" rows="2" required placeholder="Gönderilecek WhatsApp mesaj metni..." class="w-full border rounded-xl p-2.5"></textarea>
                            <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">➕ Mesajı Kuyruğa Ekle</button>
                        </form>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                        <h3 class="text-sm font-extrabold text-indigo-900 mb-3">➕ Yeni WhatsApp Hazır Cevap Şablonu Ekle</h3>
                        <form action="{{ route('admin.whatsapp.template.store') }}" method="POST" class="space-y-2.5 text-xs">
                            @csrf
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" name="title" required placeholder="Şablon Başlığı *" class="border rounded-xl px-3 py-2">
                                <input type="text" name="shortcut" required placeholder="/kisayol *" class="border rounded-xl px-3 py-2 font-mono">
                                <input type="time" name="send_time" value="10:30" required class="border rounded-xl px-3 py-2">
                            </div>
                            <input type="hidden" name="trigger_event" value="custom">
                            <input type="hidden" name="days_offset" value="0">
                            <textarea name="message_template" rows="2" required placeholder="Merhaba {veli_adi}, sporcumuz {ogrenci_adi}..." class="w-full border rounded-xl p-2.5"></textarea>
                            <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">➕ Yeni Şablon Ekle</button>
                        </form>
                    </div>
                </div>

                <!-- Tıkla-Gönder Kuyruğu -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-base font-extrabold mb-3">📲 Gönderime Hazır Mesaj Kuyruğu (`wa.me` Tıkla-Gönder)</h3>
                    <div class="space-y-2.5">
                        @foreach($whatsappQueue as $msg)
                            <div class="p-3.5 rounded-xl border {{ $msg->status === 'sent' ? 'bg-slate-50 border-slate-200' : 'bg-emerald-50/50 border-emerald-200' }} flex flex-wrap justify-between items-center gap-3">
                                <div class="max-w-3xl">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-extrabold text-slate-900">{{ $msg->recipient_name }} ({{ $msg->recipient_phone }})</span>
                                        <span class="text-[11px] font-mono bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded font-bold">{{ $msg->template?->shortcut ?? '/ozel' }}</span>
                                        <span class="text-[11px] font-bold {{ $msg->status === 'sent' ? 'text-slate-500' : 'text-emerald-700' }}">
                                            {{ $msg->status === 'sent' ? '✅ Gönderildi' : '⏳ Gönderim Bekliyor' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-700 mt-1">{{ $msg->message_text }}</p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <form action="{{ route('admin.whatsapp.send', $msg->id) }}" method="POST" target="_blank" class="inline">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold">
                                            💬 WhatsApp'ta Aç & Gönder →
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.whatsapp.destroy', $msg->id) }}" method="POST" onsubmit="return confirm('Bu WhatsApp kuyruk mesajını silmek istediğinize emin misiniz?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 text-xs font-bold transition">🗑️ Sil</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Kabul Kriteri #4: Şablon Metnini ve Saatini Değiştirme -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <h3 class="text-base font-extrabold mb-3">⚙️ WhatsApp Business Hazır Cevap Kısayolları & Otomatik Kurallar (Düzenle & Sil)</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($whatsappTemplates as $tpl)
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2.5 text-xs">
                                <form action="{{ route('admin.whatsapp.template.update', $tpl->id) }}" method="POST" class="space-y-2.5">
                                    @csrf
                                    <div class="flex justify-between items-center">
                                        <span class="font-extrabold text-sm text-indigo-900">{{ $tpl->title }}</span>
                                        <input type="text" name="shortcut" value="{{ $tpl->shortcut }}" class="w-32 font-mono font-bold text-indigo-700 bg-white border rounded-lg px-2 py-1 text-right">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="font-bold text-slate-600 block">Hatırlatma Saati</label>
                                            <input type="time" name="send_time" value="{{ substr($tpl->send_time, 0, 5) }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                        </div>
                                        <div>
                                            <label class="font-bold text-slate-600 block">Gün Farkı (-3 = 3 gün önce)</label>
                                            <input type="number" name="days_offset" value="{{ $tpl->days_offset }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="font-bold text-slate-600 block mb-1">Mesaj Şablonu ({veli_adi}, {ogrenci_adi}, {tutar}, {vade})</label>
                                        <textarea name="message_template" rows="3" class="w-full border rounded-xl p-2.5 bg-white text-xs">{{ $tpl->message_template }}</textarea>
                                    </div>
                                    <div class="flex justify-between items-center pt-1">
                                        <label class="flex items-center gap-1.5 font-bold">
                                            <input type="checkbox" name="is_active" value="1" {{ $tpl->is_active ? 'checked' : '' }}> Kural Aktif
                                        </label>
                                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold">💾 Şablonu & Saati Kaydet</button>
                                    </div>
                                </form>
                                <div class="flex justify-end pt-1 border-t border-slate-200/70">
                                    <form action="{{ route('admin.whatsapp.template.destroy', $tpl->id) }}" method="POST" onsubmit="return confirm('{{ $tpl->title }} şablonunu silmek istediğinize emin misiniz?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-200 text-[11px] font-bold">🗑️ Şablonu Sil</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
