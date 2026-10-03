<?php

namespace App\Core;

class Request
{

    /**
     * Mendapatkan HTTP Method
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }


    /**
     * Mendapatkan URL path saat ini
     */
    public static function uri(): string
    {
        $uri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        return rtrim($uri, '/') ?: '/';
    }


    /**
     * Mengambil data GET
     */
    public static function get(
        ?string $key = null,
        mixed $default = null
    ): mixed {

        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }


    /**
     * Mengambil data POST
     */
    public static function post(
        ?string $key = null,
        mixed $default = null
    ): mixed {

        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }


    /**
     * Mengambil file upload
     */
    public static function file(
        ?string $key = null
    ): mixed {

        if ($key === null) {
            return $_FILES;
        }

        return $_FILES[$key] ?? null;
    }


    /**
     * Mengecek apakah request AJAX
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            &&
            strtolower(
                $_SERVER['HTTP_X_REQUESTED_WITH']
            ) === 'xmlhttprequest';
    }


    /**
     * Semua input request
     */
    public static function all(): array
    {
        return array_merge(
            $_GET,
            $_POST
        );
    }

}