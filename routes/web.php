<?php

use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

// 1. Ön Yüz (Veli & Aday Veli Web Sitesi + Mobil Web Kısayol Uygulaması + Veli QR Yoklama)
Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
Route::post('/on-kayit', [PublicSiteController::class, 'submitTrialLead'])->middleware('throttle:10,1')->name('public.trial.submit');
// PIN ile telefon+PIN eşleşmesi burada kontrol edildiği için (bkz.
// PublicSiteController@mobilePwa), bu rotalar aynı zamanda PIN deneme/kaba kuvvet
// yüzeyidir (4 haneli PIN, 10.000 olasılık). Salt IP bazlı bir limit rotasyonlu
// IP'lerle aşılabileceğinden, 'pin-login' limiter'ı (bkz. AppServiceProvider)
// asıl olarak GÖNDERİLEN TELEFON NUMARASINA göre sınırlar.
Route::get('/mobil', [PublicSiteController::class, 'mobilePwa'])->middleware('throttle:pin-login')->name('public.mobile.pwa');
Route::get('/qr-yoklama', [PublicSiteController::class, 'qrCheckinPage'])->middleware('throttle:pin-login')->name('public.qr.checkin');
Route::post('/qr-yoklama', [PublicSiteController::class, 'submitQrCheckin'])->middleware('throttle:20,1')->name('public.qr.checkin.submit');
Route::get('/veli-portali', [PublicSiteController::class, 'parentPortal'])->name('public.parent.portal');
Route::get('/qr-afis', [PublicSiteController::class, 'qrPoster'])->name('public.qr.poster');
Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap'])->name('public.sitemap');
Route::get('/makbuz/{payment}', [PublicSiteController::class, 'receiptView'])->name('public.receipt')->middleware('signed');
Route::get('/takvim/{student}', [PublicSiteController::class, 'calendarIcs'])->name('public.calendar.ics')->middleware('signed');

// 2. Yönetim Paneli (Yetkili Giriş + 16 Modül: Salon Sahibi, Sekreter, Antrenör)
Route::prefix('panel')->group(function () {
    // Giriş / Çıkış: oturum olmadan erişilebilmesi gerektiği için admin.auth dışında kalır.
    Route::get('/giris', [AdminPanelController::class, 'loginForm'])->name('admin.login');
    Route::post('/giris', [AdminPanelController::class, 'loginSubmit'])->middleware('throttle:6,1')->name('admin.login.submit');
    Route::match(['get', 'post'], '/cikis', [AdminPanelController::class, 'logout'])->name('admin.logout');

    // Bu noktadan sonraki TÜM rotalar için merkezi oturum zorunluluğu.
    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminPanelController::class, 'index'])->name('admin.index');

        // --- Herhangi bir yetkili personelin (süper yönetici / sekreter / antrenör) erişebildiği temel modüller ---
        Route::post('/adaylar', [AdminPanelController::class, 'storeLead'])->name('admin.leads.store');
        Route::post('/adaylar/{lead}/guncelle', [AdminPanelController::class, 'updateLead'])->name('admin.leads.update');
        Route::post('/adaylar/{lead}/kayda-donustur', [AdminPanelController::class, 'convertLeadToStudent'])->name('admin.leads.convert');
        Route::post('/adaylar/{lead}/sil', [AdminPanelController::class, 'destroyLead'])->name('admin.leads.destroy');

        Route::post('/ogrenciler', [AdminPanelController::class, 'storeStudent'])->name('admin.students.store');
        Route::post('/ogrenciler/{student}/guncelle', [AdminPanelController::class, 'updateStudent'])->name('admin.students.update');
        Route::post('/ogrenciler/{student}/durum', [AdminPanelController::class, 'updateStudentStatus'])->name('admin.students.status');

        Route::post('/gruplar', [AdminPanelController::class, 'storeGroupAndSchedule'])->name('admin.groups.store');
        Route::post('/gruplar/{group}/guncelle', [AdminPanelController::class, 'updateGroup'])->name('admin.groups.update');
        Route::post('/gruplar/{group}/sil', [AdminPanelController::class, 'destroyGroup'])->name('admin.groups.destroy');
        Route::post('/gruplar/{group}/program-ekle', [AdminPanelController::class, 'storeSchedule'])->name('admin.schedules.store');
        Route::post('/program/{schedule}/guncelle', [AdminPanelController::class, 'updateSchedule'])->name('admin.schedules.update');
        Route::post('/program/{schedule}/sil', [AdminPanelController::class, 'destroySchedule'])->name('admin.schedules.destroy');

        Route::post('/etkinlik', [AdminPanelController::class, 'storeEvent'])->name('admin.events.store');
        Route::post('/etkinlik/{event}/guncelle', [AdminPanelController::class, 'updateEvent'])->name('admin.events.update');
        Route::post('/etkinlik/{event}/sil', [AdminPanelController::class, 'destroyEvent'])->name('admin.events.destroy');

        // --- Öğrenci/veli kaydı silme: hassas, ayrıca verilen bir izin gerektirir ---
        Route::middleware('admin.permission:delete_student')->group(function () {
            Route::post('/ogrenciler/{student}/sil', [AdminPanelController::class, 'destroyStudent'])->name('admin.students.destroy');
        });

        // --- Yoklama: "take_attendance" iznine sahip personel (sekreter / yetkili antrenör) ---
        Route::middleware('admin.permission:take_attendance')->group(function () {
            Route::post('/yoklama', [AdminPanelController::class, 'storeAttendance'])->name('admin.attendance.store');
            Route::post('/yoklama/{attendance}/sil', [AdminPanelController::class, 'destroyAttendance'])->name('admin.attendance.destroy');
        });

        // --- Gelişim karnesi: "add_development" iznine sahip personel ---
        Route::middleware('admin.permission:add_development')->group(function () {
            Route::post('/gelisim', [AdminPanelController::class, 'storeDevelopmentRecord'])->name('admin.development.store');
            Route::post('/gelisim/{record}/guncelle', [AdminPanelController::class, 'updateDevelopmentRecord'])->name('admin.development.update');
            Route::post('/gelisim/{record}/sil', [AdminPanelController::class, 'destroyDevelopmentRecord'])->name('admin.development.destroy');
        });

        // --- Tahsilat (ödeme alma): "collect_payment" iznine sahip personel ---
        Route::middleware('admin.permission:collect_payment')->group(function () {
            Route::post('/tahsilat', [AdminPanelController::class, 'storePayment'])->name('admin.payments.store');
            Route::post('/tahsilat/{payment}/sil', [AdminPanelController::class, 'destroyPayment'])->name('admin.payments.destroy');
        });

        // --- WhatsApp: "send_whatsapp" iznine sahip personel ---
        Route::middleware('admin.permission:send_whatsapp')->group(function () {
            Route::post('/whatsapp-sablon', [AdminPanelController::class, 'storeWhatsappTemplate'])->name('admin.whatsapp.template.store');
            Route::post('/whatsapp-sablon/{template}', [AdminPanelController::class, 'updateWhatsappTemplate'])->name('admin.whatsapp.template.update');
            Route::post('/whatsapp-sablon/{template}/sil', [AdminPanelController::class, 'destroyWhatsappTemplate'])->name('admin.whatsapp.template.destroy');
            Route::post('/whatsapp-mesaj', [AdminPanelController::class, 'storeWhatsappMessage'])->name('admin.whatsapp.message.store');
            Route::post('/whatsapp-kuyruk-uret', [AdminPanelController::class, 'generateDailyWhatsappQueue'])->name('admin.whatsapp.generate');
            // Durum değiştiren bir işlem olduğu için GET değil POST (bağlantı
            // önizleyiciler/taraycılar GET'i "ziyaret ederek" yanlışlıkla mesajı
            // gönderilmiş işaretleyebiliyordu).
            Route::post('/whatsapp-gonder/{message}', [AdminPanelController::class, 'markWhatsappSent'])->name('admin.whatsapp.send');
            Route::post('/whatsapp-mesaj/{message}/sil', [AdminPanelController::class, 'destroyWhatsappMessage'])->name('admin.whatsapp.destroy');
        });

        // --- Site/CMS içeriği (branş, paket, duyuru, SSS, galeri, antrenör vitrini, dinamik seçenekler): "edit_content" izni ---
        Route::middleware('admin.permission:edit_content')->group(function () {
            Route::post('/dinamik-secenekler', [AdminPanelController::class, 'updateDynamicOptions'])->name('admin.options.update');
            Route::post('/cms/brans', [AdminPanelController::class, 'storeBranch'])->name('admin.branches.store');
            Route::post('/cms/brans/{branch}/guncelle', [AdminPanelController::class, 'updateBranch'])->name('admin.branches.update');
            Route::post('/cms/brans/{branch}/sil', [AdminPanelController::class, 'destroyBranch'])->name('admin.branches.destroy');
            Route::post('/cms/paket', [AdminPanelController::class, 'storePackage'])->name('admin.packages.store');
            Route::post('/cms/paket/{package}/guncelle', [AdminPanelController::class, 'updatePackage'])->name('admin.packages.update');
            Route::post('/cms/paket/{package}/sil', [AdminPanelController::class, 'destroyPackage'])->name('admin.packages.destroy');
            Route::post('/duyuru', [AdminPanelController::class, 'storeAnnouncement'])->name('admin.announcements.store');
            Route::post('/duyuru/{announcement}/guncelle', [AdminPanelController::class, 'updateAnnouncement'])->name('admin.announcements.update');
            Route::post('/duyuru/{announcement}/sil', [AdminPanelController::class, 'destroyAnnouncement'])->name('admin.announcements.destroy');
            Route::post('/cms/sss', [AdminPanelController::class, 'storeFaq'])->name('admin.faqs.store');
            Route::post('/cms/sss/{faq}/guncelle', [AdminPanelController::class, 'updateFaq'])->name('admin.faqs.update');
            Route::post('/cms/sss/{faq}/sil', [AdminPanelController::class, 'destroyFaq'])->name('admin.faqs.destroy');
            Route::post('/galeri', [AdminPanelController::class, 'storeGalleryItem'])->name('admin.gallery.store');
            Route::post('/galeri/{galleryItem}/guncelle', [AdminPanelController::class, 'updateGalleryItem'])->name('admin.gallery.update');
            Route::post('/galeri/{galleryItem}/sil', [AdminPanelController::class, 'destroyGalleryItem'])->name('admin.gallery.destroy');
            Route::post('/cms/antrenor/{user}/vitrin', [AdminPanelController::class, 'updateCoachProfile'])->name('admin.coaches.profile');
            Route::post('/cms/antrenor/{user}/gorunurluk', [AdminPanelController::class, 'toggleCoachVisibility'])->name('admin.coaches.toggle_visibility');
        });

        // --- Yalnızca Süper Yönetici (Salon Sahibi): finans yapılandırması, personel, raporlar, yedekleme, sistem ayarları ---
        Route::middleware('admin.permission:role:super_admin')->group(function () {
            Route::post('/paketler/toplu-fiyat', [AdminPanelController::class, 'bulkUpdatePackagePrices'])->name('admin.packages.bulk_price');
            Route::post('/indirim', [AdminPanelController::class, 'storeDiscountRule'])->name('admin.discounts.store');
            Route::post('/indirim/{discountRule}/guncelle', [AdminPanelController::class, 'updateDiscountRule'])->name('admin.discounts.update');
            Route::post('/indirim/{discountRule}/sil', [AdminPanelController::class, 'destroyDiscountRule'])->name('admin.discounts.destroy');
            Route::post('/aidat/otomatik-borclandir', [AdminPanelController::class, 'generateMonthlyInvoices'])->name('admin.invoices.generate');
            Route::post('/aidat/tekil-borc', [AdminPanelController::class, 'storeSingleInvoice'])->name('admin.invoices.store_single');
            Route::post('/aidat/{invoice}/guncelle', [AdminPanelController::class, 'updateInvoice'])->name('admin.invoices.update');
            Route::post('/aidat/{invoice}/sil', [AdminPanelController::class, 'destroyInvoice'])->name('admin.invoices.destroy');
            Route::post('/kasa-hesabi', [AdminPanelController::class, 'storeCashAccount'])->name('admin.cash_accounts.store');
            Route::post('/kasa-hesabi/{cashAccount}/guncelle', [AdminPanelController::class, 'updateCashAccount'])->name('admin.cash_accounts.update');
            Route::post('/kasa-hesabi/{cashAccount}/sil', [AdminPanelController::class, 'destroyCashAccount'])->name('admin.cash_accounts.destroy');
            Route::post('/kasa-hareketi', [AdminPanelController::class, 'storeFinancialTransaction'])->name('admin.transactions.store');
            Route::post('/kasa-hareketi/{transaction}/guncelle', [AdminPanelController::class, 'updateTransaction'])->name('admin.transactions.update');
            Route::post('/kasa-hareketi/{transaction}/sil', [AdminPanelController::class, 'destroyTransaction'])->name('admin.transactions.destroy');
            Route::post('/personel', [AdminPanelController::class, 'storeStaff'])->name('admin.staff.store');
            Route::post('/personel/{user}/durum', [AdminPanelController::class, 'toggleStaffStatus'])->name('admin.staff.toggle');
            Route::post('/personel/{user}/yetki', [AdminPanelController::class, 'updateStaffPermissions'])->name('admin.staff.permissions');
            Route::post('/personel/{user}/sil', [AdminPanelController::class, 'destroyStaff'])->name('admin.staff.destroy');
            Route::post('/site-ayarlari', [AdminPanelController::class, 'updateSiteSettings'])->name('admin.settings.update');
            Route::get('/raporlar/excel-indir', [AdminPanelController::class, 'exportFinancialReportCsv'])->name('admin.reports.csv');
            Route::get('/ayarlar/yedek-indir', [AdminPanelController::class, 'downloadDatabaseBackup'])->name('admin.backup.download');
        });
    });
});
