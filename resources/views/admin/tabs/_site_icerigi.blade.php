                <div class="flex flex-wrap justify-between items-center gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-indigo-600">Kabul Kriteri #6 · Tam Kapsamlı Görsel & Metinsel CMS Stüdyosu</span>
                        <h2 class="text-2xl font-extrabold">🌐 Web Sitesi (Frontend) Tam İçerik Yönetim Merkezi</h2>
                        <p class="text-xs text-slate-500">Ana sayfadaki tüm başlıkları, butonları, istatistikleri, branşları, paket fiyatlarını, antrenör vitrinini, SSS, duyuru ve fotoğraf galerisini buradan yönetin.</p>
                    </div>
                    <a href="{{ route('public.home') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold shadow">
                        👁️ Canlı Web Sitesini Görüntüle ↗
                    </a>
                </div>

                <!-- 1. GENEL SİTE METİNLERİ, MANŞET (HERO), İSTATİSTİKLER, MENÜ & GÖRÜNÜRLÜK AYARLARI -->
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6 text-xs">
                    @csrf
                    <div class="grid lg:grid-cols-12 gap-6">
                        <!-- Sol: Kurumsal Kimlik, İletişim, Menü & Bölüm Görünürlükleri -->
                        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                            <h3 class="text-base font-extrabold text-indigo-950 border-b pb-2">🏢 1. Kurumsal Kimlik, Logo, Slogan & İletişim</h3>
                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Kulüp Adı (Üst Bar & Footer)</label>
                                    <input type="text" name="club_name" value="{{ $settings['club_name'] ?? 'Tekirdağ Enerjik Cimnastik' }}" class="w-full border rounded-xl px-3 py-2 font-bold">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Logo Altı Slogan</label>
                                    <input type="text" name="club_slogan" value="{{ $settings['club_slogan'] ?? '🟣 Enerjik ⚡️ Mutlu 🤩 Güçlü 💪 Çocuklar' }}" class="w-full border rounded-xl px-3 py-2 font-bold text-purple-700">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Logo Görsel Adresi (URL)</label>
                                    <input type="text" name="logo_url" value="{{ $settings['logo_url'] ?? '/icons/logo.jpg' }}" class="w-full border rounded-xl px-3 py-2 font-mono">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Tarayıcı Sekme Başlığı (SEO Title)</label>
                                    <input type="text" name="seo_title" value="{{ $settings['seo_title'] ?? 'Tekirdağ Enerjik Cimnastik Spor Kulübü' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div>
                                <label class="font-bold block mb-1">🎨 Marka Rengi (Sitedeki Butonlar, Rozetler, Vurgular)</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="brand_color" value="{{ $settings['brand_color'] ?? '#9d174d' }}" class="h-10 w-14 border rounded-lg cursor-pointer shrink-0">
                                    <span class="text-[11px] text-slate-500">Tek bir renk seçin — sitedeki tüm açık/koyu tonlar bu renkten otomatik üretilir. Kaydettikten sonra siteyi yenileyip kontrol edin.</span>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Telefon Numarası</label>
                                    <input type="text" name="phone" value="{{ $settings['phone'] ?? '0538 899 31 59' }}" class="w-full border rounded-xl px-3 py-2 font-bold">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">WhatsApp Numarası (905...)</label>
                                    <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '905388993159' }}" class="w-full border rounded-xl px-3 py-2 font-mono">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Instagram Kullanıcı Adı</label>
                                    <input type="text" name="instagram_handle" value="{{ $settings['instagram_handle'] ?? '@enerjik.cimnastik' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Instagram Profil Linki</label>
                                    <input type="text" name="instagram_url" value="{{ $settings['instagram_url'] ?? 'https://www.instagram.com/enerjik.cimnastik/' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Şehir / Bölge (Yerel SEO)</label>
                                    <input type="text" name="city_district" value="{{ $settings['city_district'] ?? 'Tekirdağ / Süleymanpaşa' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Açık Adres</label>
                                    <input type="text" name="address" value="{{ $settings['address'] ?? 'Hürriyet Mah. Süleymanpaşa / Tekirdağ' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <!-- WhatsApp Karşılama Mesajı -->
                            <div>
                                <label class="font-bold block mb-1">Üst Bar "WhatsApp İletişim" Tıklanınca Açılan Hazır Mesaj</label>
                                <input type="text" name="whatsapp_welcome_msg" value="{{ $settings['whatsapp_welcome_msg'] ?? 'Merhaba, ücretsiz deneme dersi hakkında bilgi almak istiyorum.' }}" class="w-full border rounded-xl px-3 py-2">
                            </div>

                            <!-- Bölüm Görünürlük Kontrolleri (Aç / Kapat) -->
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                                <span class="font-extrabold block text-slate-800">👁️ Ana Sayfa Bölümlerini Göster / Gizle</span>
                                <div class="grid sm:grid-cols-2 gap-2">
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>🤸 Branşlar Bölümü</span>
                                        <select name="show_branches_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_branches_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_branches_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>📅 Ders Programı Bölümü</span>
                                        <select name="show_schedule_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_schedule_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_schedule_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>🏷️ Paket Fiyatları Bölümü</span>
                                        <select name="show_prices_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_prices_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_prices_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>🏅 Antrenörler Bölümü</span>
                                        <select name="show_coaches_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_coaches_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_coaches_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>📸 Galeri Bölümü</span>
                                        <select name="show_gallery_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_gallery_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_gallery_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center justify-between p-2 rounded-xl bg-white border">
                                        <span>❓ SSS Bölümü</span>
                                        <select name="show_faq_section" class="font-bold text-indigo-700 bg-transparent">
                                            <option value="1" {{ ($settings['show_faq_section'] ?? '1') === '1' ? 'selected' : '' }}>Açık</option>
                                            <option value="0" {{ ($settings['show_faq_section'] ?? '1') === '0' ? 'selected' : '' }}>Gizli</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Sağ: Manşet (Hero), İstatistik Kutuları, Bölüm Başlıkları & KVKK -->
                        <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                            <h3 class="text-base font-extrabold text-indigo-950 border-b pb-2">✨ 2. Ana Sayfa Manşet (Hero), İstatistikler & Başlıklar</h3>
                            <div>
                                <label class="font-bold block mb-1">Manşet Üst Rozet Yazısı</label>
                                <input type="text" name="hero_badge" value="{{ $settings['hero_badge'] ?? '🏆 3-16 Yaş Çocuk & Yetişkin Cimnastik Okulu' }}" class="w-full border rounded-xl px-3 py-2">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Ana Manşet Başlığı (H1)</label>
                                <input type="text" name="hero_title" value="{{ $settings['hero_title'] ?? '' }}" class="w-full border rounded-xl px-3 py-2 font-bold">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Ana Manşet Alt Açıklama</label>
                                <textarea name="hero_subtitle" rows="2" class="w-full border rounded-xl p-2.5">{{ $settings['hero_subtitle'] ?? '' }}</textarea>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Manşet 1. Buton Yazısı</label>
                                    <input type="text" name="hero_btn_primary" value="{{ $settings['hero_btn_primary'] ?? '🤸 Ücretsiz Deneme Dersi Randevusu Al' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Manşet 2. Buton Yazısı</label>
                                    <input type="text" name="hero_btn_secondary" value="{{ $settings['hero_btn_secondary'] ?? '📅 Haftalık Ders Programını Gör' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <!-- 3 İstatistik Kutusu -->
                            <div class="p-3 rounded-2xl bg-indigo-50/60 border border-indigo-100 space-y-2">
                                <span class="font-extrabold block text-indigo-950">📊 Manşet Altı 3 İstatistik Kutusu</span>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <input type="text" name="stat_1_val" value="{{ $settings['stat_1_val'] ?? 'Maks. 12' }}" class="w-full border rounded-lg px-2 py-1 font-extrabold text-indigo-700 mb-1">
                                        <input type="text" name="stat_1_label" value="{{ $settings['stat_1_label'] ?? 'Kişilik Butik Gruplar' }}" class="w-full border rounded-lg px-2 py-1 text-[11px]">
                                    </div>
                                    <div>
                                        <input type="text" name="stat_2_val" value="{{ $settings['stat_2_val'] ?? '%100' }}" class="w-full border rounded-lg px-2 py-1 font-extrabold text-emerald-700 mb-1">
                                        <input type="text" name="stat_2_label" value="{{ $settings['stat_2_label'] ?? 'Dijital Yoklama & Gelişim Karnesi' }}" class="w-full border rounded-lg px-2 py-1 text-[11px]">
                                    </div>
                                    <div>
                                        <input type="text" name="stat_3_val" value="{{ $settings['stat_3_val'] ?? '%15' }}" class="w-full border rounded-lg px-2 py-1 font-extrabold text-rose-700 mb-1">
                                        <input type="text" name="stat_3_label" value="{{ $settings['stat_3_label'] ?? 'Kardeş & Dönem İndirimi' }}" class="w-full border rounded-lg px-2 py-1 text-[11px]">
                                    </div>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold block mb-1">Branşlar Bölümü Başlığı</label>
                                    <input type="text" name="section_branches_title" value="{{ $settings['section_branches_title'] ?? 'Cimnastik Branşlarımız' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                                <div>
                                    <label class="font-bold block mb-1">Paketler Bölümü Başlığı</label>
                                    <input type="text" name="section_packages_title" value="{{ $settings['section_packages_title'] ?? 'Eğitim Paketleri ve İndirim Kuralları' }}" class="w-full border rounded-xl px-3 py-2">
                                </div>
                            </div>

                            <div>
                                <label class="font-bold block mb-1">Alt Bilgi (Footer) KVKK Aydınlatma Metni</label>
                                <textarea name="kvkk_text" rows="2" class="w-full border rounded-xl p-2.5">{{ $settings['kvkk_text'] ?? '' }}</textarea>
                            </div>

                            <button type="submit" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold shadow-lg shadow-indigo-600/20">
                                💾 Tüm Site Metinlerini, Görünürlükleri ve Manşeti Kaydet
                            </button>
                        </div>
                    </div>
                </form>

                <!-- 2b. NAVİGASYON, BUTON, BÖLÜM ROZETİ VE FOOTER METİNLERİ (Sitede Görünen Her Küçük Yazı) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-5">
                    <div>
                        <h3 class="text-base font-extrabold">🧩 2b. Navigasyon, Buton, Bölüm Rozeti & Footer Metinleri</h3>
                        <p class="text-[11px] text-slate-500">Menüdeki, bölüm başlıklarının üstündeki küçük rozetlerde, ön kayıt formunda ve alt bilgide görünen tüm kısa metinler — sitede görünen hemen her yazı buradan değiştirilir.</p>
                    </div>
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5 text-xs">
                        @csrf
                        <div>
                            <span class="font-extrabold block text-slate-700 mb-2">☰ Üst Menü & Buton Etiketleri</span>
                            <p class="text-[11px] text-slate-500 mb-2">Üst menüdeki bağlantılar artık aşağıdaki "7. Üst Menü (Navigasyon) Yönetimi" bölümünden eklenir/düzenlenir/silinir. Burada sadece sabit giriş/deneme butonlarının etiketleri kalır.</p>
                            <div class="grid sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                                <div><label class="font-bold block mb-1">"Veli Girişi" Butonu</label><input type="text" name="header_parent_btn" value="{{ $settings['header_parent_btn'] ?? 'Veli Girişi' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">"Deneme Dersi" Butonu</label><input type="text" name="header_trial_btn" value="{{ $settings['header_trial_btn'] ?? 'Ücretsiz Deneme Dersi' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                            </div>
                        </div>

                        <div>
                            <span class="font-extrabold block text-slate-700 mb-2">🏷️ Bölüm Üstü Rozetler & Alt Açıklamalar</span>
                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                <div><label class="font-bold block mb-1">Branşlar Rozeti</label><input type="text" name="section_branches_badge" value="{{ $settings['section_branches_badge'] ?? 'Yaş ve Seviyeye Özel Eğitim' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Branşlar Alt Açıklama</label><input type="text" name="section_branches_sub" value="{{ $settings['section_branches_sub'] ?? '' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Ders Programı Rozeti</label><input type="text" name="section_schedule_badge" value="{{ $settings['section_schedule_badge'] ?? 'Canlı Salon Takvimi' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Ders Programı Başlığı</label><input type="text" name="section_schedule_title" value="{{ $settings['section_schedule_title'] ?? 'Haftalık Ders Programı & Kontenjanlar' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Paketler Rozeti</label><input type="text" name="section_packages_badge" value="{{ $settings['section_packages_badge'] ?? 'Şeffaf Fiyatlandırma' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Paketler Alt Açıklama</label><input type="text" name="section_packages_sub" value="{{ $settings['section_packages_sub'] ?? '' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Antrenörler Rozeti</label><input type="text" name="section_coaches_badge" value="{{ $settings['section_coaches_badge'] ?? 'Uzman Kadromuz' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Antrenörler Başlığı</label><input type="text" name="section_coaches_title" value="{{ $settings['section_coaches_title'] ?? 'Federasyon Belgeli Antrenörlerimiz' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Galeri Başlığı</label><input type="text" name="section_gallery_title" value="{{ $settings['section_gallery_title'] ?? 'Salonumuzdan Kareler' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">SSS Başlığı</label><input type="text" name="section_faq_title" value="{{ $settings['section_faq_title'] ?? 'Güncel Duyurular & SSS' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                            </div>
                        </div>

                        <div>
                            <span class="font-extrabold block text-slate-700 mb-2">🎯 Ön Kayıt Formu Metinleri</span>
                            <div class="grid sm:grid-cols-3 gap-2.5">
                                <div><label class="font-bold block mb-1">Form Rozeti</label><input type="text" name="trial_form_badge" value="{{ $settings['trial_form_badge'] ?? 'Online Ön Kayıt' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Form Başlığı</label><input type="text" name="trial_form_title" value="{{ $settings['trial_form_title'] ?? 'Ücretsiz Deneme Dersi Formu' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                                <div><label class="font-bold block mb-1">Gönder Butonu</label><input type="text" name="trial_form_submit" value="{{ $settings['trial_form_submit'] ?? '🚀 Ücretsiz Deneme Dersi Talebi Gönder' }}" class="w-full border rounded-lg px-2.5 py-1.5"></div>
                            </div>
                        </div>

                        <div>
                            <span class="font-extrabold block text-slate-700 mb-2">⬇️ Alt Bilgi (Footer)</span>
                            <input type="text" name="footer_copyright" value="{{ $settings['footer_copyright'] ?? '© ' . date('Y') . ' Tekirdağ Enerjik Cimnastik Spor Kulübü. Tüm hakları saklıdır.' }}" class="w-full border rounded-lg px-2.5 py-1.5">
                        </div>

                        <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold shadow-lg shadow-indigo-600/20">
                            💾 Bu Metinleri Kaydet
                        </button>
                    </form>
                </div>

                <!-- 3. SİTEDEKİ CİMNASTİK BRANŞ KARTLARINI YÖNET (EKLE / DÜZENLE / SİL) -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                    <div class="flex flex-wrap justify-between items-center gap-2 border-b pb-3">
                        <div>
                            <h3 class="text-base font-extrabold">🤸 3. Ana Sayfa Cimnastik Branş Kartları Yönetimi ({{ $branches->count() }} Branş)</h3>
                            <p class="text-[11px] text-slate-500">Sitede görünen branşların ikonunu, yaş aralığını, açıklamasını ve kazanımlarını düzenleyin veya yeni branş ekleyin.</p>
                        </div>
                    </div>

                    <!-- Yeni Branş Ekleme Formu -->
                    <form action="{{ route('admin.branches.store') }}" method="POST" class="p-3.5 rounded-2xl bg-indigo-50/60 border border-indigo-200 grid sm:grid-cols-6 gap-2.5 text-xs items-end">
                        @csrf
                        <div>
                            <label class="font-bold block mb-1">İkon</label>
                            <input type="text" name="icon" value="🤸" class="w-full border rounded-xl px-3 py-2 bg-white text-center text-base">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="font-bold block mb-1">Yeni Branş Adı *</label>
                            <input type="text" name="name" required placeholder="Ör: Parkur & Akrobasi" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Yaş Aralığı</label>
                            <input type="text" name="age_range" value="6-14 Yaş" class="w-full border rounded-xl px-3 py-2 bg-white">
                        </div>
                        <div>
                            <label class="font-bold block mb-1">Seviye</label>
                            <input type="text" name="level" value="Tüm Seviyeler" class="w-full border rounded-xl px-3 py-2 bg-white">
                        </div>
                        <div>
                            <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold">➕ Branş Ekle</button>
                        </div>
                        <div class="sm:col-span-3">
                            <input type="text" name="description" placeholder="Branş açıklaması..." class="w-full border rounded-xl px-3 py-1.5 bg-white">
                        </div>
                        <div class="sm:col-span-3">
                            <input type="text" name="benefits" placeholder="Kazanımlar (Ör: Çeviklik, Denge, Özgüven)..." class="w-full border rounded-xl px-3 py-1.5 bg-white">
                        </div>
                    </form>

                    <!-- Mevcut Branşları Düzenle / Sil -->
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($branches as $b)
                            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-2.5 text-xs">
                                <form action="{{ route('admin.branches.update', $b->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <div class="grid grid-cols-6 gap-2">
                                        <input type="text" name="icon" value="{{ $b->icon }}" class="border rounded-xl px-2 py-1.5 bg-white text-center text-base font-bold">
                                        <input type="text" name="name" value="{{ $b->name }}" class="col-span-3 border rounded-xl px-3 py-1.5 bg-white font-extrabold">
                                        <input type="text" name="age_range" value="{{ $b->age_range }}" class="col-span-2 border rounded-xl px-2.5 py-1.5 bg-white font-bold text-indigo-700">
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input type="text" name="level" value="{{ $b->level }}" placeholder="Seviye" class="col-span-2 border rounded-xl px-3 py-1.5 bg-white">
                                        <input type="number" name="sort_order" value="{{ $b->sort_order }}" placeholder="Sıra" class="border rounded-xl px-3 py-1.5 bg-white">
                                    </div>
                                    <textarea name="description" rows="2" class="w-full border rounded-xl p-2 bg-white">{{ $b->description }}</textarea>
                                    <input type="text" name="benefits" value="{{ $b->benefits }}" placeholder="Kazanımlar" class="w-full border rounded-xl px-3 py-1.5 bg-white">
                                    <div class="flex justify-between items-center pt-1">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white font-bold">💾 Branşı Güncelle</button>
                                    </div>
                                </form>
                                <form action="{{ route('admin.branches.destroy', $b->id) }}" method="POST" onsubmit="return confirm('{{ $b->name }} branşını silmek istediğinize emin misiniz?')" class="flex justify-end -mt-8">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-700 font-bold">🗑️ Sil</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($currentRole === 'super_admin')
                    <!-- 4. SİTEDEKİ EĞİTİM PAKETLERİ VE ÜCRET KARTLARINI YÖNET (Sadece Süper Yönetici) -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <h3 class="text-base font-extrabold border-b pb-2">🏷️ 4. Sitedeki Eğitim Paketleri ve Ücret Kartları ({{ $packages->count() }} Paket)</h3>

                        <!-- Yeni Paket Ekle -->
                        <form action="{{ route('admin.packages.store') }}" method="POST" class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-200 grid sm:grid-cols-5 gap-2.5 text-xs items-end">
                            @csrf
                            <div class="sm:col-span-2">
                                <label class="font-bold block mb-1">Paket Adı *</label>
                                <input type="text" name="name" required placeholder="Ör: Haftada 2 Ders Aylık Paket" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Ücret (₺) *</label>
                                <input type="number" name="price" required value="3200" class="w-full border rounded-xl px-3 py-2 bg-white font-extrabold text-emerald-700">
                            </div>
                            <div>
                                <label class="font-bold block mb-1">Ders Sayısı</label>
                                <input type="number" name="lessons_count" value="8" class="w-full border rounded-xl px-3 py-2 bg-white">
                            </div>
                            <div>
                                <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">➕ Paket Ekle</button>
                            </div>
                            <div class="sm:col-span-5">
                                <input type="text" name="description" placeholder="Paket alt açıklaması..." class="w-full border rounded-xl px-3 py-1.5 bg-white">
                            </div>
                        </form>

                        <div class="grid md:grid-cols-3 gap-4">
                            @foreach($packages as $pkg)
                                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-2 text-xs">
                                    <form action="{{ route('admin.packages.update', $pkg->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <input type="text" name="name" value="{{ $pkg->name }}" class="w-full border rounded-xl px-3 py-1.5 bg-white font-extrabold">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500">Fiyat (₺)</label>
                                                <input type="number" name="price" value="{{ (int) $pkg->price }}" class="w-full border rounded-xl px-3 py-1.5 bg-white font-extrabold text-indigo-700">
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-bold text-slate-500">Ders Sayısı</label>
                                                <input type="number" name="lessons_count" value="{{ $pkg->lessons_count }}" class="w-full border rounded-xl px-3 py-1.5 bg-white">
                                            </div>
                                        </div>
                                        <textarea name="description" rows="2" class="w-full border rounded-xl p-2 bg-white">{{ $pkg->description }}</textarea>
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white font-bold">💾 Paketi Güncelle</button>
                                    </form>
                                    <form action="{{ route('admin.packages.destroy', $pkg->id) }}" method="POST" onsubmit="return confirm('Bu paketi silmek istediğinize emin misiniz?')" class="flex justify-end -mt-7">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-700 font-bold">🗑️ Sil</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 5. DUYURULAR & 6. SIKÇA SORULAN SORULAR (SSS) YÖNETİMİ -->
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Duyurular (Ekle / Düzenle / Sil) -->
                    <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <h3 class="text-base font-extrabold border-b pb-2">📢 5. Sitedeki ve Mobil Uygulamadaki Duyurular</h3>
                        <form action="{{ route('admin.announcements.store') }}" method="POST" class="space-y-2.5 text-xs p-3.5 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                            @csrf
                            <input type="text" name="title" required placeholder="Yeni Duyuru Başlığı..." class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                            <textarea name="content" rows="2" required placeholder="Duyuru içeriği..." class="w-full border rounded-xl p-2.5 bg-white"></textarea>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold">📢 Yeni Duyuru Yayınla</button>
                        </form>

                        <div class="space-y-3">
                            @foreach($announcements as $ann)
                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                                    <form action="{{ route('admin.announcements.update', $ann->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <input type="text" name="title" value="{{ $ann->title }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                        <textarea name="content" rows="2" class="w-full border rounded-lg p-2 bg-white">{{ $ann->content }}</textarea>
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-slate-900 text-white font-bold">💾 Güncelle</button>
                                    </form>
                                    <form action="{{ route('admin.announcements.destroy', $ann->id) }}" method="POST" class="flex justify-end -mt-7">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold">🗑️ Sil</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- SSS / FAQ (Ekle / Düzenle / Sil) -->
                    <div class="lg:col-span-6 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <h3 class="text-base font-extrabold border-b pb-2">❓ 6. Sıkça Sorulan Sorular (SSS) Yönetimi</h3>
                        <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-2.5 text-xs p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200">
                            @csrf
                            <input type="text" name="question" required placeholder="Yeni Soru (Ör: Deneme dersi ücretli mi?)" class="w-full border rounded-xl px-3 py-2 bg-white font-bold">
                            <textarea name="answer" rows="2" required placeholder="Cevap metni..." class="w-full border rounded-xl p-2.5 bg-white"></textarea>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-extrabold">➕ Yeni SSS Sorusu Ekle</button>
                        </form>

                        <div class="space-y-3">
                            @foreach($faqs as $faq)
                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                                    <form action="{{ route('admin.faqs.update', $faq->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <input type="text" name="question" value="{{ $faq->question }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                        <textarea name="answer" rows="2" class="w-full border rounded-lg p-2 bg-white">{{ $faq->answer }}</textarea>
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-slate-900 text-white font-bold">💾 Güncelle</button>
                                    </form>
                                    <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="flex justify-end -mt-7">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold">🗑️ Sil</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 7. ÜST MENÜ (NAVİGASYON) YÖNETİMİ -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                    <h3 class="text-base font-extrabold border-b pb-2">🧭 7. Üst Menü (Navigasyon) Yönetimi</h3>
                    <p class="text-xs text-slate-500">Ana sayfanın üst menüsündeki bağlantıları buradan ekleyebilir, silebilir, etiketini/hedefini değiştirebilir ve sıralayabilirsiniz (küçük sayı önce gösterilir). "Sayfa İçi Bölüm" tipi, sayfadaki bir bölüme kaydırır (örn. <code>branslar</code>); "Bağlantı" tipi herhangi bir iç adrese (örn. <code>/veli-portali</code>) veya harici bir adrese (örn. <code>https://...</code>) gider.</p>
                    <form action="{{ route('admin.nav_menu.store') }}" method="POST" class="grid sm:grid-cols-5 gap-2.5 text-xs p-3.5 rounded-2xl bg-sky-50/60 border border-sky-200 items-end">
                        @csrf
                        <input type="text" name="label" required placeholder="Menü Etiketi (Ör: İletişim)" class="border rounded-xl px-3 py-2 bg-white font-bold sm:col-span-2">
                        <select name="link_type" class="border rounded-xl px-2.5 py-2 bg-white font-bold">
                            <option value="anchor">Sayfa İçi Bölüm</option>
                            <option value="url">Bağlantı (URL)</option>
                        </select>
                        <input type="text" name="target" required placeholder="branslar  veya  /veli-portali" class="border rounded-xl px-3 py-2 bg-white sm:col-span-1">
                        <input type="number" name="sort_order" value="{{ ($navMenuItems->max('sort_order') ?? 0) + 1 }}" placeholder="Sıra" class="border rounded-xl px-3 py-2 bg-white">
                        <label class="flex items-center gap-1.5 font-bold sm:col-span-4"><input type="checkbox" name="open_in_new_tab" value="1"> Yeni sekmede açılsın (harici bağlantılar için)</label>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 text-white font-extrabold">➕ Menüye Ekle</button>
                    </form>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($navMenuItems as $item)
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                                <form action="{{ route('admin.nav_menu.update', $item->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <input type="text" name="label" value="{{ $item->label }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-bold">
                                    <div class="flex gap-1.5">
                                        <select name="link_type" class="flex-1 border rounded-lg px-1.5 py-1.5 bg-white">
                                            <option value="anchor" {{ $item->link_type === 'anchor' ? 'selected' : '' }}>Sayfa İçi Bölüm</option>
                                            <option value="url" {{ $item->link_type === 'url' ? 'selected' : '' }}>Bağlantı (URL)</option>
                                        </select>
                                        <input type="number" name="sort_order" value="{{ $item->sort_order }}" class="w-16 border rounded-lg px-2 py-1.5 bg-white" title="Sıra">
                                    </div>
                                    <input type="text" name="target" value="{{ $item->target }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white">
                                    <div class="flex items-center justify-between">
                                        <label class="flex items-center gap-1 font-semibold"><input type="checkbox" name="open_in_new_tab" value="1" {{ $item->open_in_new_tab ? 'checked' : '' }}> Yeni sekme</label>
                                        <label class="flex items-center gap-1 font-semibold"><input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}> Görünür</label>
                                    </div>
                                    <button type="submit" class="w-full px-3 py-1 rounded-lg bg-slate-900 text-white font-bold">💾 Güncelle</button>
                                </form>
                                <form action="{{ route('admin.nav_menu.destroy', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold">🗑️ Menüden Kaldır</button>
                                </form>
                            </div>
                        @endforeach
                        @if($navMenuItems->isEmpty())
                            <p class="text-xs text-slate-400 italic">Henüz hiç menü öğesi yok — yukarıdan ekleyin.</p>
                        @endif
                    </div>
                </div>

                <!-- 8. SALON FOTOĞRAF GALERİSİ & 9. ANTRENÖR VİTRİN KARTLARI -->
                <div class="grid lg:grid-cols-12 gap-6">
                    <!-- Galeri Yönetimi -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                        <h3 class="text-base font-extrabold border-b pb-2">📸 8. Salon Fotoğraf Galerisi (Ekle / Düzenle / Sil - KVKK Kontrollü)</h3>
                        <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 grid sm:grid-cols-2 gap-2.5 text-xs">
                            @csrf
                            <input type="text" name="title" required placeholder="Fotoğraf Başlığı" class="border rounded-xl px-3 py-2 bg-white font-bold">
                            <input type="text" name="category" value="Antrenman" placeholder="Kategori (Antrenman, Salon, Gösteri)" class="border rounded-xl px-3 py-2 bg-white">
                            <div class="sm:col-span-2 space-y-1">
                                <label class="block font-bold text-[11px] text-slate-600">📁 Bilgisayardan Fotoğraf Yükle (JPG/PNG/WEBP, maks. 4MB)</label>
                                <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="w-full border rounded-xl px-3 py-2 bg-white">
                            </div>
                            <input type="url" name="image_url" placeholder="— veya — Fotoğraf URL (https://...)" class="sm:col-span-2 border rounded-xl px-3 py-2 bg-white">
                            <select name="student_id" class="border rounded-xl px-3 py-2 bg-white">
                                <option value="">Genel Salon Fotoğrafı (Öğrenci Yok)</option>
                                @foreach($students as $st)
                                    <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->photo_consent ? '✅ Foto İzinli' : '🚫 KVKK Foto İzni YOK' }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="py-2 rounded-xl bg-indigo-600 text-white font-bold">📸 Galeriye Fotoğraf Ekle</button>
                        </form>

                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach($gallery as $item)
                                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-28 object-cover rounded-lg border">
                                    <form action="{{ route('admin.gallery.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-1.5">
                                        @csrf
                                        <input type="text" name="title" value="{{ $item->title }}" class="w-full border rounded-lg px-2 py-1 bg-white font-bold">
                                        <input type="text" name="category" value="{{ $item->category }}" class="w-full border rounded-lg px-2 py-1 bg-white text-[11px]">
                                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="w-full border rounded-lg px-2 py-1 bg-white text-[10px]">
                                        <input type="url" name="image_url" value="{{ $item->image_url }}" class="w-full border rounded-lg px-2 py-1 bg-white text-[10px] font-mono">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-bold">💾 Kaydet</button>
                                    </form>
                                    <form action="{{ route('admin.gallery.destroy', $item->id) }}" method="POST" class="flex justify-end -mt-6">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-bold">🗑️ Sil</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Antrenör Vitrin Bilgileri (Sitede Görünen Uzmanlık & Bio) -->
                    <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4 h-fit">
                        <h3 class="text-base font-extrabold border-b pb-2">🏅 9. Sitede Görünen Antrenör Tanıtım Kartları</h3>
                        <p class="text-[11px] text-slate-500">Ana sayfadaki "Federasyon Belgeli Antrenörlerimiz" bölümünde yer alan unvan ve özgeçmiş yazılarını düzenleyin, kartı sitede göster/gizle. Yeni bir antrenör eklemek için <strong>10. Personel</strong> sekmesinden personel kaydı açın — buraya otomatik düşer.</p>
                        <div class="space-y-3">
                            @forelse($staff->where('role', 'coach') as $coach)
                                <div class="p-3.5 rounded-xl border {{ $coach->show_on_website ? 'border-slate-200 bg-slate-50' : 'border-rose-200 bg-rose-50/50' }} space-y-2 text-xs">
                                    <div class="flex justify-between items-center">
                                        @if($coach->show_on_website)
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">✅ Sitede Görünüyor</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">🚫 Siteden Gizli</span>
                                        @endif
                                        @if(!$coach->is_active)
                                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 font-bold text-[10px]">⏸️ Pasif Personel</span>
                                        @endif
                                        <form action="{{ route('admin.coaches.toggle_visibility', $coach->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg {{ $coach->show_on_website ? 'bg-rose-100 hover:bg-rose-600 hover:text-white text-rose-700' : 'bg-emerald-100 hover:bg-emerald-600 hover:text-white text-emerald-700' }} font-bold">
                                                {{ $coach->show_on_website ? '🚫 Siteden Kaldır' : '✅ Sitede Göster' }}
                                            </button>
                                        </form>
                                    </div>
                                    <form action="{{ route('admin.coaches.profile', $coach->id) }}" method="POST" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="font-bold block mb-0.5">Antrenör Adı Soyadı</label>
                                            <input type="text" name="name" value="{{ $coach->name }}" class="w-full border rounded-lg px-2.5 py-1.5 bg-white font-extrabold">
                                        </div>
                                        <div>
                                            <label class="font-bold block mb-0.5">Uzmanlık / Kademe Unvanı</label>
                                            <input type="text" name="specialty" value="{{ $coach->specialty }}" placeholder="Ör: 2. Kademe Artistik Cimnastik Antrenörü" class="w-full border rounded-lg px-2.5 py-1.5 bg-white text-indigo-700 font-bold">
                                        </div>
                                        <div>
                                            <label class="font-bold block mb-0.5">Sitede Görünen Özgeçmiş / Tanıtım Metni</label>
                                            <textarea name="bio" rows="2" class="w-full border rounded-lg p-2 bg-white">{{ $coach->bio }}</textarea>
                                        </div>
                                        <label class="flex items-center gap-2 font-bold">
                                            <input type="checkbox" name="show_on_website" value="1" {{ $coach->show_on_website ? 'checked' : '' }} class="rounded text-indigo-600">
                                            Ana sayfada göster
                                        </label>
                                        <button type="submit" class="w-full py-2 rounded-lg bg-slate-900 hover:bg-indigo-600 text-white font-bold">💾 Antrenör Vitrin Kartını Güncelle</button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500">Henüz antrenör kaydı yok — Personel sekmesinden ekleyin.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
