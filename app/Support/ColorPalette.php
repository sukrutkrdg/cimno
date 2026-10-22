<?php

namespace App\Support;

/**
 * Tek bir marka rengi hex kodundan, Tailwind'in 50-950 ton ölçeğine benzer bir
 * palet üretir. Kullanıcının seçtiği renk aynen 600 tonu olur (en çok kullanılan
 * buton/aksan rengi budur); daha açık tonlar (50-500) sabit, okunabilir açık
 * değerlerde, daha koyu tonlar (700-950) ise seçilen rengin tonundan (hue/doygunluk)
 * kademeli olarak karartılarak üretilir.
 */
class ColorPalette
{
    private const LIGHT_LIGHTNESS = [
        '50' => 0.97, '100' => 0.93, '200' => 0.84, '300' => 0.72, '400' => 0.60, '500' => 0.54,
    ];

    private const DARK_OFFSETS = [
        '700' => 0.08, '800' => 0.16, '900' => 0.24, '950' => 0.32,
    ];

    /** @return array<string, string> ton => hex */
    public static function fromHex(?string $hex, float $saturationFactor = 1.0): array
    {
        $hex = self::sanitizeHex($hex) ?? '#9d174d';
        [$h, $s, $l] = self::hexToHsl($hex);
        $s = max(0.0, min(1.0, $s * $saturationFactor));

        $palette = ['600' => strtoupper($hex)];

        foreach (self::LIGHT_LIGHTNESS as $shade => $lightness) {
            $palette[$shade] = self::hslToHex($h, $s, $lightness);
        }

        foreach (self::DARK_OFFSETS as $shade => $offset) {
            $palette[$shade] = self::hslToHex($h, $s, max(0.03, $l - $offset));
        }

        ksort($palette, SORT_STRING);

        return $palette;
    }

    public static function sanitizeHex(?string $hex): ?string
    {
        if (!$hex) {
            return null;
        }
        $hex = trim($hex);
        if (!preg_match('/^#?[0-9a-fA-F]{6}$/', $hex)) {
            return null;
        }
        return '#' . ltrim($hex, '#');
    }

    /** @return array{0: float, 1: float, 2: float} [hue 0-360, saturation 0-1, lightness 0-1] */
    private static function hexToHsl(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        $h = match ($max) {
            $r => fmod((($g - $b) / $d) + ($g < $b ? 6 : 0), 6),
            $g => (($b - $r) / $d) + 2,
            default => (($r - $g) / $d) + 4,
        };
        $h *= 60;

        return [$h, $s, $l];
    }

    private static function hslToHex(float $h, float $s, float $l): string
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
