<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinLoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_pin_guesses_for_the_same_phone_get_throttled(): void
    {
        $phone = '05331112233';

        // Limiter izin veriyor: 10 dakikada telefon başına 8 deneme.
        for ($i = 0; $i < 8; $i++) {
            $resp = $this->get('/mobil?phone=' . $phone . '&pin=000' . $i);
            $resp->assertStatus(200);
        }

        // 9. deneme (aynı telefon, farklı PIN tahmini) kaba kuvvet denemesi olarak sınırlanmalı.
        $this->get('/mobil?phone=' . $phone . '&pin=9999')->assertStatus(429);
    }

    public function test_a_different_phone_is_not_affected_by_another_phones_throttle(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $this->get('/mobil?phone=05331112233&pin=000' . $i);
        }
        $this->get('/mobil?phone=05331112233&pin=9999')->assertStatus(429);

        // Farklı bir telefon numarası kendi ayrı kotasına sahip olmalı, önceki telefonun
        // limitinden etkilenmemeli.
        $this->get('/mobil?phone=05449998877&pin=1234')->assertStatus(200);
    }
}
