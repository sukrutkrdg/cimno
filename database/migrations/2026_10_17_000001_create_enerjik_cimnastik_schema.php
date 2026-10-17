<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Branşlar (Branches)
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Artistik Cimnastik, Ritmik Cimnastik, Trampolin, Okul Öncesi, Yetişkin
            $table->string('slug')->unique();
            $table->string('age_range'); // ör. 3-5 Yaş, 6-12 Yaş
            $table->string('level')->default('Başlangıç - Orta - İleri');
            $table->text('description')->nullable();
            $table->text('benefits')->nullable();
            $table->string('icon')->default('🤸');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Paket ve Fiyatlar (Packages)
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Aylık Haftada 2 Ders, 3 Aylık Dönem, 8 Derslik Paket
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'lesson_pack', 'event'])->default('monthly');
            $table->integer('lessons_count')->default(8);
            $table->decimal('price', 10, 2);
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('show_on_site')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. İndirim Kuralları (Discount Rules)
        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Kardeş İndirimi (%15), Peşin Ödeme (%10), Sporcu Bursu (%50)
            $table->enum('type', ['sibling', 'upfront', 'scholarship', 'custom'])->default('sibling');
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('fixed_amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Gruplar (Groups)
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name'); // Minik Yıldızlar A, Artistik Performans B
            $table->string('level')->default('Başlangıç');
            $table->string('age_group')->nullable(); // 4-6 Yaş
            $table->string('area')->default('Ana Minder'); // Ana Minder, Trampolin Alanı, Bar/Denge Alanı
            $table->integer('capacity')->default(12);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Ders Programı (Schedules)
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->enum('day_of_week', ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('area')->default('Ana Minder');
            $table->boolean('is_cancelled')->default(false);
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
        });

        // 6. Adaylar / Ön Kayıt ve Deneme Dersi (Leads)
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('parent_name');
            $table->string('parent_phone', 30);
            $table->string('student_name');
            $table->integer('student_age');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('trial_group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->date('trial_date')->nullable();
            $table->string('preferred_days')->nullable();
            $table->enum('source', ['website', 'instagram', 'referral', 'walk_in', 'phone'])->default('website');
            $table->enum('status', ['new', 'trial_scheduled', 'thinking', 'converted', 'not_converted'])->default('new');
            $table->text('notes')->nullable();
            $table->boolean('kvkk_approved')->default(true);
            $table->timestamp('kvkk_approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Öğrenciler (Students)
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['Kız', 'Erkek', 'Belirtilmedi'])->default('Belirtilmedi');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->foreignId('discount_rule_id')->nullable()->constrained('discount_rules')->nullOnDelete();
            $table->string('parent_name');
            $table->string('parent_phone', 30);
            $table->string('secondary_parent_name')->nullable();
            $table->string('secondary_parent_phone', 30)->nullable();
            $table->foreignId('parent_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->text('health_notes')->nullable(); // Özel nitelikli sağlık verisi (yalnız yetkili görür)
            $table->date('medical_report_expires_at')->nullable(); // Spor yapabilir raporu bitiş tarihi
            $table->string('license_no')->nullable(); // Federasyon lisans no
            $table->date('license_visa_date')->nullable();
            $table->string('uniform_size')->nullable(); // Mayo/Forma beden ölçüsü
            $table->integer('makeup_credits')->default(0); // Telafi dersi hakkı
            $table->integer('consecutive_absences')->default(0); // Üst üste devamsızlık sayacı
            $table->integer('billing_day')->default(1); // Her ayın kaçında borç açılır
            $table->enum('status', ['active', 'frozen', 'left', 'waiting_list'])->default('active');
            $table->string('leave_reason')->nullable();
            $table->boolean('kvkk_consent')->default(true);
            $table->boolean('health_data_consent')->default(true);
            $table->boolean('photo_consent')->default(true); // Fotoğraf/video paylaşım izni
            $table->boolean('marketing_consent')->default(false); // İYS / Kampanya izni
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Kardeş Bağlantısı (Siblings)
        Schema::create('student_siblings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('sibling_student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamps();
        });

        // 9. Yoklama (Attendances)
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'excused', 'makeup'])->default('present');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'student_id', 'attendance_date']);
        });

        // 10. Kasa, Banka ve POS Hesapları (Cash Accounts)
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nakit Kasa, Ziraat Bankası IBAN, Salon Fiziki POS
            $table->enum('type', ['cash', 'bank', 'pos', 'online_pos'])->default('cash');
            $table->string('iban')->nullable();
            $table->decimal('balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 11. Aidat ve Borçlar (Invoices)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('period_label'); // ör. Ekim 2026 Aidatı, Yaz Okulu Kayıt
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'partial', 'paid', 'cancelled'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 12. Tahsilatlar (Payments)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique(); // MKB-202610-0001
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('cash_account_id')->constrained('cash_accounts')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'pos', 'bank_transfer', 'online'])->default('cash');
            $table->date('payment_date');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 13. Gelir-Gider ve Ürün Satışı (Transactions / Expenses)
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('direction', ['income', 'expense'])->default('expense');
            $table->string('category'); // Kira, Elektrik/Su/Doğalgaz, Maaş/SGK, Antrenör Hakedişi, Ekipman, Temizlik, Reklam, Ürün Satışı (Mayo/Forma)
            $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts')->nullOnDelete();
            $table->foreignId('related_user_id')->nullable()->constrained('users')->nullOnDelete(); // Antrenör veya personel
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->date('transaction_date');
            $table->boolean('is_recurring')->default(false); // Her ay sabit tekrar eden gider (ör. Kira)
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 14. Gelişim Karnesi (Development Records)
        Schema::create('development_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('term_label'); // 2026 Güz Dönemi
            $table->string('current_level')->default('Temel Cimnastik 1');
            $table->integer('flexibility_score')->default(80); // 0-100
            $table->integer('strength_score')->default(80);
            $table->integer('balance_score')->default(80);
            $table->integer('coordination_score')->default(80);
            $table->integer('discipline_score')->default(90);
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->json('skills')->nullable(); // Ör: {"Takla": "Başarılı", "Çember": "Gelişiyor", "Amut": "Başarılı"}
            $table->text('coach_comment')->nullable();
            $table->timestamps();
        });

        // 15. Etkinlikler (Events: Gösteri, Yarışma, Yaz Okulu, Kamp)
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['show', 'competition', 'summer_school', 'camp'])->default('show');
            $table->date('event_date');
            $table->string('location')->default('Enerjik Cimnastik Ana Salon');
            $table->decimal('fee', 10, 2)->default(0);
            $table->integer('quota')->default(50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->boolean('fee_paid')->default(false);
            $table->boolean('parent_approved')->default(true);
            $table->timestamps();
        });

        // 16. WhatsApp Business Hazır Cevapları ve Otomatik Kurallar
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('shortcut')->unique(); // /deneme, /aidat_hatirlatma, /aidat_gecikme, /makbuz, /devamsizlik, /rapor_bitis, /dogum_gunu
            $table->string('title');
            $table->enum('trigger_event', [
                'trial_reminder',
                'class_cancelled',
                'due_approaching',
                'due_overdue',
                'payment_received',
                'absence_warning',
                'medical_report_expiring',
                'birthday',
                'campaign'
            ]);
            $table->enum('category', ['utility', 'marketing'])->default('utility');
            $table->integer('days_offset')->default(0); // ör. Vadeden -3 gün önce, +3 gün sonra
            $table->time('send_time')->default('10:00:00');
            $table->text('message_template'); // Merhaba {veli_adi}, {ogrenci_adi} için {tutar} TL tutarındaki aidatın son ödeme tarihi {vade}...
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 17. WhatsApp Gönderim Kuyruğu & Geçmişi (Tıkla-Gönder & Hazır Cevap Kuyruğu)
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('whatsapp_templates')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_phone', 30);
            $table->text('message_text');
            $table->string('channel')->default('whatsapp_business'); // whatsapp_business, sms
            $table->enum('status', ['queued', 'opened_in_whatsapp', 'sent', 'failed'])->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // 18. Duyurular, SSS, Galeri ve Site Ayarları (CMS)
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('target_audience', ['all', 'parents', 'coaches', 'public_site'])->default('all');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('Antrenman'); // Antrenman, Yarışma, Salonumuz, Gösteri
            $table->string('image_url');
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->boolean('photo_consent_verified')->default(true);
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general'); // general, seo, contact, kvkk, finance
            $table->timestamps();
        });

        // 19. KVKK İlgili Kişi Başvuruları ve İşlem Günlüğü (Audit Logs)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // tahsilat_eklendi, fiyat_guncellendi, ogrenci_arsivlendi, kvkk_onayi
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('event_participants');
        Schema::dropIfExists('events');
        Schema::dropIfExists('development_records');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('cash_accounts');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('student_siblings');
        Schema::dropIfExists('students');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('discount_rules');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('branches');
    }
};
