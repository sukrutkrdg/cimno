<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * "Ödeme Alındı" ve "Devamsızlık Uyarısı" şablonları artık gerçekten gönderilen
     * mesaja bağlandı (daha önce yalnızca referans olarak tutulup gerçek metin kodda
     * sabitti). Canlıdaki mevcut satırların metnini, kodun artık doldurduğu yeni
     * placeholder'larla (ör. {makbuz_linki}, {sayi}) eşleşecek şekilde günceller —
     * DatabaseSeeder yalnızca ilk kurulumda çalıştığı için bu veri migration'ı gerekir.
     * Admin panelden metni zaten elle özelleştirmiş olabileceğinden, yalnızca seed'deki
     * ORİJİNAL (eski) metinle birebir eşleşen satırlar güncellenir — başka bir metne
     * değiştirilmiş satırlara dokunulmaz.
     */
    public function up(): void
    {
        DB::table('whatsapp_templates')
            ->where('trigger_event', 'payment_received')
            ->where('message_template', 'Sayın {veli_adi}, sporcumuz {ogrenci_adi} için {tutar} TL tutarındaki ödemeniz alınmıştır (Makbuz No: {makbuz_no}). Dijital makbuzunuzu Veli Panelinden görüntüleyebilirsiniz. Enerjik Cimnastik ailesi olarak teşekkür ederiz! 🌟')
            ->update([
                'message_template' => 'Sayın {veli_adi}, sporcumuz {ogrenci_adi} için {tutar} TL tutarındaki ödemeniz alınmıştır (Makbuz No: {makbuz_no}, Kalan Borç: {kalan_borc} TL). Dijital Makbuz: {makbuz_linki}. Enerjik Cimnastik ailesi olarak teşekkür ederiz! 🌟',
            ]);

        DB::table('whatsapp_templates')
            ->where('trigger_event', 'absence_warning')
            ->where('message_template', 'Merhaba {veli_adi}, sporcumuz {ogrenci_adi} son 2 antrenmanımıza katılamamıştır. Her şeyin yolunda olduğunu umuyoruz 🙏 Raporlu veya izinli durumlarda telafi dersi planlaması için bizimle iletişime geçebilirsiniz.')
            ->update([
                'message_template' => 'Merhaba {veli_adi}, sporcumuz {ogrenci_adi} üst üste {sayi} antrenmana katılamamıştır. Her şeyin yolunda olduğunu umuyoruz 🙏 Raporlu veya izinli durumlarda telafi dersi planlaması için bizimle iletişime geçebilirsiniz.',
            ]);
    }

    public function down(): void
    {
        // Metin geri alınmaz — kullanıcı panelden ayrıca düzenlemiş olabilir.
    }
};
