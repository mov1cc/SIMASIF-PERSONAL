<?php

namespace App\Core;

class Session
{

    /**
     * Set session value
     */
    public static function set(
        string $key,
        mixed $value
    ): void {

        $_SESSION[$key] = $value;

    }



    /**
     * Ambil session value
     */
    public static function get(
        string $key,
        mixed $default = null
    ): mixed {

        return $_SESSION[$key] ?? $default;

    }



    /**
     * Cek session tersedia
     */
    public static function has(
        string $key
    ): bool {

        return isset($_SESSION[$key]);

    }



    /**
     * Hapus session tertentu
     */
    public static function remove(
        string $key
    ): void {

        unset($_SESSION[$key]);

    }



    /**
     * Flash message
     * 
     * Data hanya tersedia sekali request
     */
    public static function flash(
        string $key,
        mixed $value = null
    ): mixed {


        if ($value !== null) {

            $_SESSION['_flash'][$key] = $value;

            return null;

        }


        if (
            isset($_SESSION['_flash'][$key])
        ) {

            $message =
                $_SESSION['_flash'][$key];


            unset(
                $_SESSION['_flash'][$key]
            );


            return $message;

        }


        return null;

    }



    /**
     * Hapus semua session
     */
    public static function destroy(): void
    {

        $_SESSION = [];


        if (
            ini_get("session.use_cookies")
        ) {

            $params =
                session_get_cookie_params();


            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );

        }


        session_destroy();

    }

}