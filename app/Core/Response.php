<?php

namespace App\Core;

class Response
{
    /**
     * Redirect ke URL tertentu
     */
    public static function redirect(string $url): never
    {
        if (!headers_sent()) {
            header("Location: {$url}");
        }

        exit;
    }

    /**
     * Response JSON
     */
    public static function json(
        mixed $data,
        int $status = 200
    ): never {

        http_response_code($status);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
        }

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    /**
     * Render View
     */
    public static function view(
        string $view,
        array $data = []
    ): void {

        $viewPath = dirname(__DIR__) . "/Views/{$view}.php";

        if (!file_exists($viewPath)) {
            throw new \Exception(
                "View {$view} tidak ditemukan."
            );
        }

        extract($data);

        require $viewPath;
    }

    /**
     * Set HTTP Status Code
     */
    public static function status(int $code): void
    {
        http_response_code($code);
    }
}