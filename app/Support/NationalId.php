<?php

namespace App\Support;

use App\Enums\Gender;
use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Egyptian national ID (14 digits):
 *   digit 1      century (2 = 1900s, 3 = 2000s)
 *   digits 2-7   birth date YYMMDD
 *   digits 8-9   governorate code
 *   digits 10-13 serial; digit 13 is odd for males, even for females
 *   digit 14     check digit
 */
final class NationalId
{
    public static function isValid(?string $id): bool
    {
        return $id !== null
            && preg_match('/^[23]\d{13}$/', $id) === 1
            && self::birthDate($id) !== null;
    }

    /**
     * Birth date encoded in the ID, or null when the digits don't form a real past date.
     */
    public static function birthDate(string $id): ?CarbonImmutable
    {
        if (preg_match('/^([23])(\d{2})(\d{2})(\d{2})/', $id, $m) !== 1) {
            return null;
        }

        $year = ($m[1] === '2' ? 1900 : 2000) + (int) $m[2];

        if (! checkdate((int) $m[3], (int) $m[4], $year)) {
            return null;
        }

        $date = CarbonImmutable::create($year, (int) $m[3], (int) $m[4]);

        return $date->isFuture() ? null : $date;
    }

    public static function gender(string $id): ?Gender
    {
        if (strlen($id) !== 14 || ! ctype_digit($id)) {
            return null;
        }

        return ((int) $id[12]) % 2 === 1 ? Gender::Male : Gender::Female;
    }

    /**
     * Build a structurally valid ID (for factories and tests).
     */
    public static function fake(DateTimeInterface $birthDate, Gender $gender, int $governorate = 1): string
    {
        $century = (int) $birthDate->format('Y') >= 2000 ? '3' : '2';
        $serial = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        $genderDigit = $gender === Gender::Male ? random_int(0, 4) * 2 + 1 : random_int(0, 4) * 2;

        return $century
            .$birthDate->format('ymd')
            .str_pad((string) $governorate, 2, '0', STR_PAD_LEFT)
            .$serial
            .$genderDigit
            .random_int(0, 9);
    }
}
