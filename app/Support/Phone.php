<?php

namespace App\Support;

// Kanonik telefon depolama biçimi: başında 0 olmadan tam 10 hane (Ör: "5332223344").
// Hem cep (5XX...) hem sabit hat (2XX... vb.) numaraları için geçerli — Türkiye'de
// alan kodu dahil tüm numaralar başındaki 0 çıkarıldığında 10 haneye iner.
class Phone
{
    /**
     * Serbest biçimli bir girdiyi (0532 222 33 44, +90 532 222 33 44, 905322223344,
     * boşluklu/tireli her türlü yazım) kanonik 10 haneli forma indirger. Sonuç tam
     * 10 hane değilse (eksik/bozuk girdi) null döner.
     */
    public static function normalize(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if (strlen($digits) === 12 && str_starts_with($digits, '90')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return strlen($digits) === 10 ? $digits : null;
    }
}
