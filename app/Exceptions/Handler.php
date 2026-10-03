<?php

namespace App\Exceptions;

use App\Core\Response;
use Throwable;

class Handler
{
    public static function register(): void
    {
        set_exception_handler(
            [self::class, 'handleException']
        );

        set_error_handler(
            [self::class, 'handleError']
        );

        register_shutdown_function(
            [self::class, 'handleShutdown']
        );
    }

    public static function handleException(
        Throwable $exception
    ): void {

        $code = $exception->getCode();

        if (!in_array($code, [403, 404, 500])) {
            $code = 500;
        }

        Response::status($code);

        Response::view(
            "errors/{$code}",
            [
                'exception' => $exception
            ]
        );
    }

    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {

        throw new \ErrorException(
            $message,
            0,
            $severity,
            $file,
            $line
        );
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        Response::status(500);

        Response::view(
            'errors/500',
            [
                'error' => $error
            ]
        );
    }
}