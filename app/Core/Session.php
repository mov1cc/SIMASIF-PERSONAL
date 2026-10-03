<?php

namespace App\Core;

class Session
{

    /**
     * Memulai session
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {

            $config = require BASE_PATH . '/app/Config/app.php';


            session_name(
                $config['session']['name']
            );


            session_start([
                'cookie_lifetime' => 0,
                'cookie_httponly' => true,
                'cookie_secure' => false,
                'cookie_samesite' => 'Lax'
            ]);

        }
    }


    /**
     * Menyimpan data session
     */
    public static function set(
        string $key,
        mixed $value
    ): void {

        self::start();

        $_SESSION[$key] = $value;

    }


    /**
     * Mengambil data session
     */
    public static function get(
        string $key,
        mixed $default = null
    ): mixed {

        self::start();

        return $_SESSION[$key] ?? $default;

    }


    /**
     * Mengecek session tersedia
     */
    public static function has(
        string $key
    ): bool {

        self::start();

        return isset($_SESSION[$key]);

    }


    /**
     * Menghapus session tertentu
     */
    public static function remove(
        string $key
    ): void {

        self::start();

        unset($_SESSION[$key]);

    }


    /**
     * Flash message
     * hanya muncul sekali
     */
    public static function flash(
        string $key,
        mixed $value = null
    ): mixed {

        self::start();


        if ($value !== null) {

            $_SESSION['_flash'][$key] = $value;

            return null;

        }


        $message =
            $_SESSION['_flash'][$key] ?? null;


        unset(
            $_SESSION['_flash'][$key]
        );


        return $message;

    }


    /**
     * Menghapus seluruh session
     */
    public static function destroy(): void
    {
        self::start();


        $_SESSION = [];


        if (
            ini_get("session.use_cookies")
        ) {

            $params = session_get_cookie_params();


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