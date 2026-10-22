<?php

namespace Tests\Unit;

use App\Support\ColorPalette;
use PHPUnit\Framework\TestCase;

class ColorPaletteTest extends TestCase
{
    private function luminance(string $hex): int
    {
        $hex = ltrim($hex, '#');
        return hexdec(substr($hex, 0, 2)) + hexdec(substr($hex, 2, 2)) + hexdec(substr($hex, 4, 2));
    }

    public function test_600_shade_matches_the_chosen_color_exactly(): void
    {
        $palette = ColorPalette::fromHex('#9d174d');
        $this->assertSame('#9D174D', $palette['600']);
    }

    public function test_invalid_or_missing_hex_falls_back_to_default(): void
    {
        $this->assertSame('#9D174D', ColorPalette::fromHex(null)['600']);
        $this->assertSame('#9D174D', ColorPalette::fromHex('not-a-color')['600']);
        $this->assertSame('#9D174D', ColorPalette::fromHex('#zzz')['600']);
    }

    public function test_palette_has_all_eleven_tailwind_shades(): void
    {
        $palette = ColorPalette::fromHex('#2563eb');
        foreach (['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'] as $shade) {
            $this->assertArrayHasKey($shade, $palette);
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $palette[$shade]);
        }
    }

    public function test_lighter_shades_are_brighter_and_darker_shades_are_dimmer_than_600(): void
    {
        $palette = ColorPalette::fromHex('#2563eb');
        $base = $this->luminance($palette['600']);

        $this->assertGreaterThan($base, $this->luminance($palette['50']));
        $this->assertGreaterThan($base, $this->luminance($palette['300']));
        $this->assertLessThan($base, $this->luminance($palette['800']));
        $this->assertLessThan($base, $this->luminance($palette['950']));
    }

    public function test_accepts_hex_without_leading_hash(): void
    {
        $this->assertSame('#9D174D', ColorPalette::fromHex('9d174d')['600']);
    }
}
