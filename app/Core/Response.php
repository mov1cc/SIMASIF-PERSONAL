<?php

namespace App\Core;

class Response
{

    /**
     * Redirect ke URL tertentu
     */
    public static function redirect(string $url): never
    {
        header(
            "Location: " . $url
        );

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

        header(
            'Content-Type: application/json'
        );

        echo json_encode(
            $data,
            JSON_PRETTY_PRINT
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

        $viewPath =
            BASE_PATH .
            '/app/Views/' .
            $view .
            '.php';


        if (!file_exists($viewPath)) {

            throw new \Exception(
                "View tidak ditemukan: " . $view
            );

        }


        extract($data);


        require $viewPath;

    }


    /**
     * Mengirim status HTTP
     */
    public static function status(
        int $code
    ): void {

        http_response_code($code);

    }


}