<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Group;
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

        GalleryItem::create([
            'title' => $request->input('title'),
            'category' => $request->input('category', 'Antrenman'),
            'image_url' => $imageUrl,
            'student_id' => $studentId ?: null,
            'photo_consent_verified' => true,
        ]);

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
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Galeri fotoğrafı güncellendi!');
    }

    public function destroyGalleryItem(GalleryItem $galleryItem)
    {
        $galleryItem->delete();
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', 'Fotoğraf galeriden kaldırıldı.');
    }

    // CMS: Antrenör Vitrin Bilgisi (Sitede Görünen Bio & Uzmanlık) Güncelleme
    public function updateCoachProfile(Request $request, User $user)
    {
        $user->update([
            'name' => $request->input('name', $user->name),
            'specialty' => $request->input('specialty', $user->specialty),
            'bio' => $request->input('bio', $user->bio),
        ]);
        AuditLog::record('antrenor_vitrin_guncellendi', "{$user->name} antrenör vitrin bilgileri güncellendi.");
        return redirect()->route('admin.index', ['tab' => 'site_icerigi'])->with('success', "{$user->name} antrenör tanıtım kartı güncellendi!");
    }

    // Dinamik Seçenek Havuzu (Salon Alanları, Yaş Grupları, Seviyeler, Gelir/Gider Kategorileri)
    public function updateDynamicOptions(Request $request)
    {
        $settingKey = $request->input('setting_key');
        $allowedKeys = ['custom_hall_areas', 'custom_age_groups', 'custom_group_levels', 'custom_finance_categories', 'custom_lead_sources'];
        if (!in_array($settingKey, $allowedKeys, true)) {
            return back()->with('error', 'Geçersiz dinamik seçenek anahtarı.');
        }

        $defaults = [
            'custom_hall_areas' => 'Ana Minder|Trampolin Alanı|Ana Minder & Bar Alanı|Bale & Ritmik Salonu',
            'custom_age_groups' => '3-4 Yaş (Oyun)|4-6 Yaş (Minikler)|5-8 Yaş|7-10 Yaş (Yıldızlar)|9-14 Yaş (Gençler)|16+ Yaş (Yetişkin)',
            'custom_group_levels' => 'Başlangıç|Orta Seviye|İleri Seviye|Performans / Yarışmacı|Hobi Grubu',
            'custom_finance_categories' => 'Kira|Elektrik / Su / Doğalgaz|Maaş ve SGK|Antrenör Hakedişi|Ekipman ve Bakım|Temizlik|Reklam (Instagram/Google)|Ürün Satışı (Mayo / Forma)|Lisans ve Yarışma Harcı',
            'custom_lead_sources' => 'website|instagram|whatsapp|telefon|tavsiye',
        ];

        $currentRaw = SiteSetting::where('key', $settingKey)->value('value') ?? $defaults[$settingKey];
        $items = array_values(array_filter(array_map('trim', explode('|', $currentRaw))));

        $action = $request->input('action', 'save_all');
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
