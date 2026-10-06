<?php

namespace App\Helpers;

class ValidationHelper
{
    public static function required(mixed $value): bool
    {
        return trim((string) $value) !== '';
    }

    public static function email(mixed $value): bool
    {
        return filter_var((string) $value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function minLength(mixed $value, int $min): bool
    {
        return mb_strlen((string) $value) >= $min;
    }

    public static function maxLength(mixed $value, int $max): bool
    {
        return mb_strlen((string) $value) <= $max;
    }

    public static function numeric(mixed $value): bool
    {
        return is_numeric(trim((string) $value));
    }

    public static function integer(mixed $value): bool
    {
        return filter_var(trim((string) $value), FILTER_VALIDATE_INT) !== false;
    }

    /** Nilai >= batas */
    public static function gte(mixed $value, float $limit): bool
    {
        $value = trim((string) $value);

        return is_numeric($value) && (float) $value >= $limit;
    }

    /** Nilai > batas */
    public static function gt(mixed $value, float $limit): bool
    {
        $value = trim((string) $value);

        return is_numeric($value) && (float) $value > $limit;
    }

    /** Nilai <= batas */
    public static function lte(mixed $value, float $limit): bool
    {
        $value = trim((string) $value);

        return is_numeric($value) && (float) $value <= $limit;
    }

    /**
     * Validasi sekumpulan field.
     *
     * Rule tersedia:
     *   required, email, min:N (panjang minimal), max:N (panjang maksimal),
     *   numeric, integer, gte:N, gt:N, lte:N
     *
     * @return array<string, string> field => pesan error pertama
     */
    public static function validate(
        array $data,
        array $rules,
        array $labels = []
    ): array {

        $errors = [];

        foreach ($rules as $field => $fieldRules) {

            $value = $data[$field] ?? '';
            $label = $labels[$field] ?? ucfirst($field);

            foreach ($fieldRules as $rule) {

                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

                $valid = match ($name) {
                    'required' => self::required($value),
                    'email'    => self::email($value),
                    'min'      => self::minLength($value, (int) $param),
                    'max'      => self::maxLength($value, (int) $param),
                    'numeric'  => self::numeric($value),
                    'integer'  => self::integer($value),
                    'gte'      => self::gte($value, (float) $param),
                    'gt'       => self::gt($value, (float) $param),
                    'lte'      => self::lte($value, (float) $param),
                    default    => true,
                };

                if (!$valid) {

                    $errors[$field] = match ($name) {
                        'required' => "{$label} wajib diisi.",
                        'email'    => "Format {$label} tidak valid.",
                        'min'      => "{$label} minimal {$param} karakter.",
                        'max'      => "{$label} maksimal {$param} karakter.",
                        'numeric'  => "{$label} harus berupa angka.",
                        'integer'  => "{$label} harus berupa bilangan bulat.",
                        'gte'      => "{$label} tidak boleh kurang dari {$param}.",
                        'gt'       => "{$label} harus lebih dari {$param}.",
                        'lte'      => "{$label} tidak boleh lebih dari {$param}.",
                        default    => "{$label} tidak valid.",
                    };

                    break; // satu pesan per field
                }
            }
        }

        return $errors;
    }
}