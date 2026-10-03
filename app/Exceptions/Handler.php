<?php

namespace App\Exceptions;

class Handler
{

    public static function handle(
        \Throwable $exception
    ): void {

        http_response_code(500);


        if (
            ($_ENV['APP_DEBUG'] ?? false) === 'true'
        ) {

            echo "<h1>Application Error</h1>";

            echo "<pre>";
            echo $exception->getMessage();
            echo "\n\n";

            echo $exception->getTraceAsString();

            echo "</pre>";

        } else {

            require dirname(__DIR__)
                . '/Views/errors/500.php';

        }

    }


}