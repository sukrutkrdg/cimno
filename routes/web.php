<?php

use App\Http\Controllers\AdminPanelController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

// 1. Ön Yüz (Veli & Aday Veli Web Sitesi + Mobil Web Kısayol Uygulaması + Veli QR Yoklama)
Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
Route::post('/on-kayit', [PublicSiteController::class, 'submitTrialLead'])->name('public.trial.submit');
Route::get('/mobil', [PublicSiteController::class, 'mobilePwa'])->name('public.mobile.pwa');
Route::post('/mobil/yoklama-senkron', [PublicSiteController::class, 'syncMobileAttendance'])->name('public.mobile.attendance.sync');
Route::get('/qr-yoklama', [PublicSiteController::class, 'qrCheckinPage'])->name('public.qr.checkin');
Route::post('/qr-yoklama', [PublicSiteController::class, 'submitQrCheckin'])->name('public.qr.checkin.submit');
Route::get('/veli-portali', [PublicSiteController::class, 'parentPortal'])->name('public.parent.portal');
Route::get('/qr-afis', [PublicSiteController::class, 'qrPoster'])->name('public.qr.poster');
Route::get('/makbuz/{payment}', [PublicSiteController::class, 'receiptView'])->name('public.receipt');

// 2. Yönetim Paneli (Yetkili Giriş + 16 Modül: Salon Sahibi, Sekreter, Antrenör)
Route::prefix('panel')->group(function () {
    Route::get('/giris', [AdminPanelController::class, 'loginForm'])->name('admin.login');
    Route::post('/giris', [AdminPanelController::class, 'loginSubmit'])->name('admin.login.submit');
    Route::match(['get', 'post'], '/cikis', [AdminPanelController::class, 'logout'])->name('admin.logout');

    Route::get('/', [AdminPanelController::class, 'index'])->name('admin.index');
    Route::post('/adaylar/{lead}/kayda-donustur', [AdminPanelController::class, 'convertLeadToStudent'])->name('admin.leads.convert');
    Route::post('/ogrenciler', [AdminPanelController::class, 'storeStudent'])->name('admin.students.store');
    Route::post('/ogrenciler/{student}/durum', [AdminPanelController::class, 'updateStudentStatus'])->name('admin.students.status');
    Route::post('/ogrenciler/{student}/sil', [AdminPanelController::class, 'destroyStudent'])->name('admin.students.destroy');
    Route::post('/gruplar', [AdminPanelController::class, 'storeGroupAndSchedule'])->name('admin.groups.store');
    Route::post('/yoklama', [AdminPanelController::class, 'storeAttendance'])->name('admin.attendance.store');
    Route::post('/paketler/toplu-fiyat', [AdminPanelController::class, 'bulkUpdatePackagePrices'])->name('admin.packages.bulk_price');
    Route::post('/tahsilat', [AdminPanelController::class, 'storePayment'])->name('admin.payments.store');
    Route::post('/aidat/otomatik-borclandir', [AdminPanelController::class, 'generateMonthlyInvoices'])->name('admin.invoices.generate');
    Route::post('/kasa-hareketi', [AdminPanelController::class, 'storeFinancialTransaction'])->name('admin.transactions.store');
    Route::post('/personel', [AdminPanelController::class, 'storeStaff'])->name('admin.staff.store');
    Route::post('/personel/{user}/durum', [AdminPanelController::class, 'toggleStaffStatus'])->name('admin.staff.toggle');
    Route::post('/personel/{user}/yetki', [AdminPanelController::class, 'updateStaffPermissions'])->name('admin.staff.permissions');
    Route::post('/personel/{user}/sil', [AdminPanelController::class, 'destroyStaff'])->name('admin.staff.destroy');
    Route::post('/whatsapp-sablon/{template}', [AdminPanelController::class, 'updateWhatsappTemplate'])->name('admin.whatsapp.template.update');
    Route::post('/whatsapp-kuyruk-uret', [AdminPanelController::class, 'generateDailyWhatsappQueue'])->name('admin.whatsapp.generate');
    Route::get('/whatsapp-gonder/{message}', [AdminPanelController::class, 'markWhatsappSent'])->name('admin.whatsapp.send');
    Route::post('/gelisim', [AdminPanelController::class, 'storeDevelopmentRecord'])->name('admin.development.store');
    Route::post('/site-ayarlari', [AdminPanelController::class, 'updateSiteSettings'])->name('admin.settings.update');
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
    Route::get('/raporlar/excel-indir', [AdminPanelController::class, 'exportFinancialReportCsv'])->name('admin.reports.csv');
    Route::get('/ayarlar/yedek-indir', [AdminPanelController::class, 'downloadDatabaseBackup'])->name('admin.backup.download');
});
