<?php
namespace App\Support;
class GeoText {
    public static function normalize(string $value): string {
        $value = trim(mb_strtolower($value));
        $value = strtr($value, ['أ'=>'ا','إ'=>'ا','آ'=>'ا','ى'=>'ي','ة'=>'ه','ؤ'=>'و','ئ'=>'ي']);
        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $value) ?? $value;
        $value = preg_replace('/[\p{P}\p{S}]+/u', ' ', $value) ?? $value;
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }
    public static function ascii(string $value): string {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return self::normalize($value ?: '');
    }
}