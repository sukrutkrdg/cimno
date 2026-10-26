<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Group;
use App\Models\NavMenuItem;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ManagesCms
{
    // MODÜL 15: Tam Kapsamlı Site İçeriği (CMS) Yönetimi (Kabul Kriteri #6)
    public function updateSiteSettings(Request $request)
    {
        foreach ($request->except(['_token']) as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }
        AuditLog::record('site_icerigi_guncellendi', 'Web sitesi tüm içerik, görünüm, istatistik, iletişim ve SEO ayarları güncellendi.');
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Web sitesi metinleri, manşet, bölüm başlıkları, görünürlük ve iletişim ayarları anında güncellendi!');
    }

    // CMS: Branş Ekle / Güncelle / Sil
    public function storeBranch(Request $request)
    {
        $name = $request->input('name', 'Yeni Branş');
        Branch::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(4)),
            'icon' => $request->input('icon', '🤸'),
            'age_range' => $request->input('age_range', '4-12 Yaş'),
            'level' => $request->input('level', 'Başlangıç & Orta Seviye'),
            'description' => $request->input('description', ''),
            'benefits' => $request->input('benefits', ''),
            'sort_order' => (int) $request->input('sort_order', 1),
            'is_active' => true,
        ]);
        AuditLog::record('brans_eklendi', "Branşlara '{$name}' eklendi.");
        $tab = $request->input('return_tab', 'site_icerigi');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', "'{$name}' branşı başarıyla eklendi!");
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $branch->update([
            'name' => $request->input('name', $branch->name),
            'icon' => $request->input('icon', $branch->icon),
            'age_range' => $request->input('age_range', $branch->age_range),
            'level' => $request->input('level', $branch->level),
            'description' => $request->input('description', $branch->description),
            'benefits' => $request->input('benefits', $branch->benefits),
            'sort_order' => (int) $request->input('sort_order', $branch->sort_order),
        ]);
        AuditLog::record('brans_guncellendi', "'{$branch->name}' branş bilgileri güncellendi.");
        $tab = $request->input('return_tab', 'site_icerigi');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', "'{$branch->name}' branşı güncellendi!");
    }

    public function destroyBranch(Request $request, Branch $branch)
    {
        $name = $branch->name;
        Group::where('branch_id', $branch->id)->update(['branch_id' => null]);
        Student::where('branch_id', $branch->id)->update(['branch_id' => null]);
        $branch->delete();
        AuditLog::record('brans_silindi', "'{$name}' branşı kaldırıldı.");
        $tab = $request->input('return_tab', 'site_icerigi');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', "'{$name}' branşı silindi.");
    }

    // CMS: Duyuru Ekle / Güncelle / Sil
    public function storeAnnouncement(Request $request)
    {
        Announcement::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'target_audience' => $request->input('target_audience', 'all'),
            'is_pinned' => $request->boolean('is_pinned', true),
        ]);
        AuditLog::record('duyuru_eklendi', "Yeni duyuru yayınlandı: " . $request->input('title'));
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Yeni duyuru sitede ve mobil uygulamada yayınlandı!');
    }

    public function updateAnnouncement(Request $request, Announcement $announcement)
    {
        $announcement->update([
            'title' => $request->input('title', $announcement->title),
            'content' => $request->input('content', $announcement->content),
        ]);
        AuditLog::record('duyuru_guncellendi', "Duyuru güncellendi: {$announcement->title}");
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Duyuru metni güncellendi!');
    }

    public function destroyAnnouncement(Announcement $announcement)
    {
        $announcement->delete();
        AuditLog::record('duyuru_silindi', 'Duyuru yayından kaldırıldı.');
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Duyuru silindi.');
    }

    // CMS: Sıkça Sorulan Sorular (SSS / FAQ) Ekle / Güncelle / Sil
    public function storeFaq(Request $request)
    {
        Faq::create([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'sort_order' => (int) $request->input('sort_order', 1),
            'is_active' => true,
        ]);
        AuditLog::record('sss_eklendi', 'Web sitesine yeni SSS eklendi.');
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Yeni Sıkça Sorulan Soru sitede yayınlandı!');
    }

    public function updateFaq(Request $request, Faq $faq)
    {
        $faq->update([
            'question' => $request->input('question', $faq->question),
            'answer' => $request->input('answer', $faq->answer),
            'sort_order' => (int) $request->input('sort_order', $faq->sort_order),
        ]);
        AuditLog::record('sss_guncellendi', 'SSS maddesi güncellendi.');
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'SSS sorusu ve cevabı güncellendi!');
    }

    public function destroyFaq(Faq $faq)
    {
        $faq->delete();
        AuditLog::record('sss_silindi', 'SSS maddesi silindi.');
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'SSS maddesi silindi.');
    }

    // CMS: Üst Menü (Navigasyon) Ekle / Güncelle / Sil
    // Ana sayfanın üst menüsü eskiden sabit kodlanmış 6 bağlantıydı; artık burada
    // tamamen yönetici tarafından eklenebilir/kaldırılabilir/sıralanabilir.
    public function storeNavMenuItem(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:60',
            'link_type' => 'required|in:anchor,url',
            'target' => 'required|string|max:255',
            'placement' => 'required|in:header,footer',
            'sort_order' => 'nullable|integer',
        ]);

        $menuItem = NavMenuItem::create([
            'label' => $validated['label'],
            'link_type' => $validated['link_type'],
            'target' => $validated['link_type'] === 'anchor' ? ltrim($validated['target'], '#') : $validated['target'],
            'placement' => $validated['placement'],
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
            'sort_order' => $validated['sort_order'] ?? 1,
            'is_active' => true,
        ]);

        AuditLog::record('menu_eklendi', "Üst menüye '{$menuItem->label}' eklendi.", 'NavMenuItem', $menuItem->id);
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', "'{$menuItem->label}' menüsü eklendi!");
    }

    public function updateNavMenuItem(Request $request, NavMenuItem $navMenuItem)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:60',
            'link_type' => 'required|in:anchor,url',
            'target' => 'required|string|max:255',
            'placement' => 'required|in:header,footer',
            'sort_order' => 'nullable|integer',
        ]);

        $navMenuItem->update([
            'label' => $validated['label'],
            'link_type' => $validated['link_type'],
            'target' => $validated['link_type'] === 'anchor' ? ltrim($validated['target'], '#') : $validated['target'],
            'placement' => $validated['placement'],
            'open_in_new_tab' => $request->boolean('open_in_new_tab'),
            'sort_order' => $validated['sort_order'] ?? $navMenuItem->sort_order,
            // boolean() ikinci argümansız: checkbox işaretsizse alan formda hiç
            // gönderilmez, bu da "gizle" seçeneğinin çalışmasını sağlar (varsayılan
            // true verilseydi işaretsiz kutu da hep "aktif" kalırdı).
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::record('menu_guncellendi', "Üst menü güncellendi: '{$navMenuItem->label}'.", 'NavMenuItem', $navMenuItem->id);
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', "'{$navMenuItem->label}' menüsü güncellendi!");
    }

    public function destroyNavMenuItem(NavMenuItem $navMenuItem)
    {
        $label = $navMenuItem->label;
        $navMenuItem->delete();
        AuditLog::record('menu_silindi', "Üst menüden '{$label}' kaldırıldı.");
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', "'{$label}' menüsü kaldırıldı.");
    }

    // CMS: Galeri Fotoğrafı Ekle / Güncelle / Sil
    // Hem dosya yükleme (image_file) hem de harici görsel linki (image_url) desteklenir.
    // Dosyalar sembolik link (storage:link) gerektirmeyen public/uploads/gallery klasörüne
    // doğrudan taşınır; bu, SSH erişimi kısıtlı paylaşımlı hosting (Natro) ile de çalışır.
    protected function storeUploadedGalleryImage(Request $request): ?string
    {
        if (!$request->hasFile('image_file')) {
            return null;
        }

        $request->validate([
            'image_file' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $file = $request->file('image_file');

        // Dosya adını istemcinin gönderdiği uzantıya (getClientOriginalExtension)
        // GÜVENME: biri "resim.jpg" içeriğini "zararlı.html" adıyla yükleyip
        // tarayıcıda HTML/script olarak servis edilmesini sağlayabilir. Uzantıyı
        // dosya İÇERİĞİNDEN (MIME sniffing) tahmin edip sabit bir izin listesiyle
        // eşleştiriyoruz.
        $safeExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $extension = $safeExtensions[$file->getMimeType()] ?? 'jpg';
        $filename = Str::uuid() . '.' . $extension;
        $destination = public_path('uploads/gallery');
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $file->move($destination, $filename);

        return '/uploads/gallery/' . $filename;
    }

    public function storeGalleryItem(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:120',
            'image_url' => 'nullable|url',
        ]);

        $studentId = $request->input('student_id');
        if ($studentId) {
            $st = Student::find($studentId);
            if ($st && !$st->photo_consent) {
                return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('error', "KVKK UYARISI: {$st->full_name} adlı öğrencinin velisi fotoğraf/video paylaşım onayı VERMEMİŞTİR! Galeriye eklenemedi.");
            }
        }

        $uploadedUrl = $this->storeUploadedGalleryImage($request);
        $imageUrl = $uploadedUrl ?? $request->input('image_url');
        if (!$imageUrl) {
            return back()->with('error', 'Lütfen bir fotoğraf dosyası yükleyin veya bir görsel linki girin.');
        }

        $galleryItem = GalleryItem::create([
            'title' => $request->input('title'),
            'category' => $request->input('category', 'Antrenman'),
            'image_url' => $imageUrl,
            'student_id' => $studentId ?: null,
            'photo_consent_verified' => true,
        ]);

        AuditLog::record('galeri_eklendi', "Galeriye yeni fotoğraf eklendi: '{$galleryItem->title}'.", 'GalleryItem', $galleryItem->id);
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Fotoğraf KVKK izin kontrolünden geçerek galeriye eklendi!');
    }

    public function updateGalleryItem(Request $request, GalleryItem $galleryItem)
    {
        $request->validate([
            'image_url' => 'nullable|url',
        ]);

        $uploadedUrl = $this->storeUploadedGalleryImage($request);

        $galleryItem->update([
            'title' => $request->input('title', $galleryItem->title),
            'category' => $request->input('category', $galleryItem->category),
            'image_url' => $uploadedUrl ?? $request->input('image_url', $galleryItem->image_url),
        ]);
        AuditLog::record('galeri_guncellendi', "Galeri fotoğrafı güncellendi: '{$galleryItem->title}'.", 'GalleryItem', $galleryItem->id);
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Galeri fotoğrafı güncellendi!');
    }

    public function destroyGalleryItem(GalleryItem $galleryItem)
    {
        $title = $galleryItem->title;
        $galleryItem->delete();
        AuditLog::record('galeri_silindi', "Galeri fotoğrafı kaldırıldı: '{$title}'.");
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Fotoğraf galeriden kaldırıldı.');
    }

    // CMS: Antrenör Vitrin Bilgisi (Sitede Görünen Bio & Uzmanlık) Güncelleme
    public function updateCoachProfile(Request $request, User $user)
    {
        $user->update([
            'name' => $request->input('name', $user->name),
            'specialty' => $request->input('specialty', $user->specialty),
            'bio' => $request->input('bio', $user->bio),
            'show_on_website' => $request->boolean('show_on_website'),
        ]);
        AuditLog::record('antrenor_vitrin_guncellendi', "{$user->name} antrenör vitrin bilgileri güncellendi.");
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', "{$user->name} antrenör tanıtım kartı güncellendi!");
    }

    // Antrenör Vitrin Kartını Tek Tıkla Sitede Göster/Gizle (Personel kaydını etkilemez)
    public function toggleCoachVisibility(User $user)
    {
        $user->update(['show_on_website' => !$user->show_on_website]);
        $durum = $user->show_on_website ? 'sitede gösterilecek' : 'siteden kaldırıldı';
        AuditLog::record('antrenor_vitrin_gorunurluk', "{$user->name} antrenör kartı {$durum}.");
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', "{$user->name} antrenör kartı {$durum}.");
    }

    // Dinamik Seçenek Havuzu (Salon Alanları, Yaş Grupları, Seviyeler, Gelir/Gider Kategorileri)
    public function updateDynamicOptions(Request $request)
    {
        // NOT: alan adları ("option_key", "operation") tüm formlarda (bkz. _gruplar.blade.php,
        // _kasa.blade.php) kullanılan isimlerle birebir eşleşmeli — daha önce burada
        // "setting_key"/"action" bekleniyordu, formlar hiçbiri bunu göndermediğinden
        // TÜM dinamik liste (salon alanı, yaş grubu, seviye, gelir/gider kategorisi,
        // aday kaynağı) ekle/sil butonları sessizce "Geçersiz dinamik seçenek anahtarı"
        // hatasıyla başarısız oluyordu.
        $settingKey = $request->input('option_key');
        $allowedKeys = [
            'custom_hall_areas', 'custom_age_groups', 'custom_group_levels',
            'custom_income_categories', 'custom_expense_categories', 'custom_lead_sources',
        ];
        if (!in_array($settingKey, $allowedKeys, true)) {
            return back()->with('error', 'Geçersiz dinamik seçenek anahtarı.');
        }

        $defaults = [
            'custom_hall_areas' => 'Ana Minder|Trampolin Alanı|Ana Minder & Bar Alanı|Bale & Ritmik Salonu',
            'custom_age_groups' => '3-4 Yaş (Oyun)|4-6 Yaş (Minikler)|5-8 Yaş|7-10 Yaş (Yıldızlar)|9-14 Yaş (Gençler)|16+ Yaş (Yetişkin)',
            'custom_group_levels' => 'Başlangıç|Orta Seviye|İleri Seviye|Performans / Yarışmacı|Hobi Grubu',
            'custom_income_categories' => 'Aidat / Ders Ücreti|Kayıt Ücreti|Ürün Satışı (Mayo / Forma)|Deneme Dersi Ücreti|Etkinlik / Gösteri Geliri|Sponsorluk|Bağış|Diğer Gelir',
            'custom_expense_categories' => 'Kira|Elektrik / Su / Doğalgaz|Maaş ve SGK|Antrenör Hakedişi|Ekipman ve Bakım|Temizlik|Reklam (Instagram/Google)|Lisans ve Yarışma Harcı|Sigorta|Muhasebe / Mali Müşavir|Vergi / Resmi Harç|Diğer Gider',
            'custom_lead_sources' => 'website|instagram|whatsapp|telefon|tavsiye',
        ];

        $currentRaw = SiteSetting::where('key', $settingKey)->value('value') ?? $defaults[$settingKey];
        $items = array_values(array_filter(array_map('trim', explode('|', $currentRaw))));

        $action = $request->input('operation', 'save_all');
        if ($action === 'add' && $request->filled('new_option')) {
            $newOpt = trim($request->input('new_option'));
            if (!in_array($newOpt, $items, true)) {
                $items[] = $newOpt;
            }
        } elseif ($action === 'remove' && $request->filled('remove_option')) {
            $remOpt = trim($request->input('remove_option'));
            $items = array_values(array_filter($items, fn($i) => $i !== $remOpt));
        } elseif ($action === 'save_all' && $request->has('options_text')) {
            $rawLines = preg_split('/[\r\n|]+/', (string) $request->input('options_text'));
            $items = array_values(array_filter(array_map('trim', $rawLines)));
        }

        SiteSetting::updateOrCreate(['key' => $settingKey], ['value' => implode('|', $items)]);
        AuditLog::record('dinamik_secenek_guncellendi', "Dinamik liste ({$settingKey}) güncellendi.");

        $tab = $request->input('return_tab', 'gruplar');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', 'Seçenek listesi başarıyla güncellendi!');
    }
}
