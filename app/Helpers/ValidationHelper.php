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

    /**
     * Validasi sekumpulan field.
     *
     * Contoh:
     *   validate(
     *       ['email' => $email],
     *       ['email' => ['required', 'email', 'max:100']],
     *       ['email' => 'Email']
     *   );
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
                    default    => true,
                };

                if (!$valid) {

                    $errors[$field] = match ($name) {
                        'required' => "{$label} wajib diisi.",
                        'email'    => "Format {$label} tidak valid.",
                        'min'      => "{$label} minimal {$param} karakter.",
                        'max'      => "{$label} maksimal {$param} karakter.",
                        default    => "{$label} tidak valid.",
                    };

                    break; // satu pesan per field
                }
            }
        }

        return $errors;
    }
}