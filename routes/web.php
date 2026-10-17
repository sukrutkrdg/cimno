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

// 2. Yönetim Paneli (16 Modül - Salon Sahibi, Sekreter, Antrenör)
Route::prefix('panel')->group(function () {
    Route::get('/', [AdminPanelController::class, 'index'])->name('admin.index');
    Route::post('/adaylar/{lead}/kayda-donustur', [AdminPanelController::class, 'convertLeadToStudent'])->name('admin.leads.convert');
    Route::post('/ogrenciler', [AdminPanelController::class, 'storeStudent'])->name('admin.students.store');
    Route::post('/ogrenciler/{student}/durum', [AdminPanelController::class, 'updateStudentStatus'])->name('admin.students.status');
    Route::post('/gruplar', [AdminPanelController::class, 'storeGroupAndSchedule'])->name('admin.groups.store');
    Route::post('/yoklama', [AdminPanelController::class, 'storeAttendance'])->name('admin.attendance.store');
    Route::post('/paketler/toplu-fiyat', [AdminPanelController::class, 'bulkUpdatePackagePrices'])->name('admin.packages.bulk_price');
    Route::post('/tahsilat', [AdminPanelController::class, 'storePayment'])->name('admin.payments.store');
    Route::post('/aidat/otomatik-borclandir', [AdminPanelController::class, 'generateMonthlyInvoices'])->name('admin.invoices.generate');
    Route::post('/kasa-hareketi', [AdminPanelController::class, 'storeFinancialTransaction'])->name('admin.transactions.store');
    Route::post('/personel', [AdminPanelController::class, 'storeStaff'])->name('admin.staff.store');
    Route::post('/personel/{user}/durum', [AdminPanelController::class, 'toggleStaffStatus'])->name('admin.staff.toggle');
    Route::post('/whatsapp-sablon/{template}', [AdminPanelController::class, 'updateWhatsappTemplate'])->name('admin.whatsapp.template.update');
    Route::post('/whatsapp-kuyruk-uret', [AdminPanelController::class, 'generateDailyWhatsappQueue'])->name('admin.whatsapp.generate');
    Route::get('/whatsapp-gonder/{message}', [AdminPanelController::class, 'markWhatsappSent'])->name('admin.whatsapp.send');
    Route::post('/gelisim', [AdminPanelController::class, 'storeDevelopmentRecord'])->name('admin.development.store');
    Route::post('/site-ayarlari', [AdminPanelController::class, 'updateSiteSettings'])->name('admin.settings.update');
    Route::post('/duyuru', [AdminPanelController::class, 'storeAnnouncement'])->name('admin.announcements.store');
    Route::post('/galeri', [AdminPanelController::class, 'storeGalleryItem'])->name('admin.gallery.store');
    Route::get('/raporlar/excel-indir', [AdminPanelController::class, 'exportFinancialReportCsv'])->name('admin.reports.csv');
    Route::get('/ayarlar/yedek-indir', [AdminPanelController::class, 'downloadDatabaseBackup'])->name('admin.backup.download');
});
