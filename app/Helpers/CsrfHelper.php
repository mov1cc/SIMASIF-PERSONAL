<?php

namespace App\Helpers;

use App\Core\Session;

class CsrfHelper
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            Session::set(self::KEY, bin2hex(random_bytes(32)));
        }

        return Session::get(self::KEY);
    }

    /**
     * Input hidden siap pakai di form
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }

    public static function verify(?string $token): bool
    {
        $stored = Session::get(self::KEY);

        return is_string($stored)
            && is_string($token)
            && hash_equals($stored, $token);
    }
}