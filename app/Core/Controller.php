<?php

namespace App\Core;

class Controller
{

    /**
     * Render halaman view
     */
    protected function render(
        string $view,
        array $data = []
    ): void {

        Response::view(
            $view,
            $data
        );

    }


    /**
     * Redirect halaman
     */
    protected function redirect(
        string $url
    ): never {

        Response::redirect(
            $url
        );

    }


    /**
     * Response JSON
     */
    protected function json(
        mixed $data,
        int $status = 200
    ): never {

        Response::json(
            $data,
            $status
        );

    }

}