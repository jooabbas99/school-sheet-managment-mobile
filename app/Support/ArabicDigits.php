<?php

namespace App\Support;

final class ArabicDigits
{
    private const MAP = [
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        // Eastern Arabic (Persian/Urdu) keyboard digits.
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
    ];

    /**
     * Convert Arabic-Indic digits to Latin digits, leaving everything else as is.
     */
    public static function normalize(?string $value): ?string
    {
        return $value === null ? null : strtr($value, self::MAP);
    }

    /**
     * Normalize digits and drop spaces and dashes, for IDs and phone numbers typed by people.
     */
    public static function compact(mixed $value): mixed
    {
        if (! is_string($value) && ! is_int($value)) {
            return $value;
        }

        return preg_replace('/[\s\-]+/u', '', self::normalize((string) $value));
    }
}
