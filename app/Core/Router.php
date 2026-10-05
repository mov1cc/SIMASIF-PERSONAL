<?php

namespace App\Core;

class Router
{
    private array $routes = [];


    /**
     * Tambah route GET
     */
    public function get(
        string $path,
        string $action,
        array $middleware = []
    ): void {

        $this->add(
            'GET',
            $path,
            $action,
            $middleware
        );

    }


    /**
     * Tambah route POST
     */
    public function post(
        string $path,
        string $action,
        array $middleware = []
    ): void {

        $this->add(
            'POST',
            $path,
            $action,
            $middleware
        );

    }


    /**
     * Simpan route
     */
    private function add(
        string $method,
        string $path,
        string $action,
        array $middleware
    ): void {

        $this->routes[] = [

            'method' => $method,

            'path' => $path,

            'action' => $action,

            'middleware' => $middleware

        ];

    }


    /**
     * Jalankan router
     */
    public function dispatch(): void
    {
        $method = Request::method();

        $uri = Request::uri();


        foreach ($this->routes as $route) {

            if (
                $route['method'] === $method
                &&
                $route['path'] === $uri
            ) {

                $this->runMiddleware(
                    $route['middleware']
                );


                $this->runController(
                    $route['action']
                );


                return;

            }

        }


        Response::status(404);


        Response::view(
            'errors/404'
        );
    }


        /**
     * Jalankan middleware
     *
     * Format: NamaClass::class atau NamaClass::class . ':param1,param2'
     */
    private function runMiddleware(
        array $middlewares
    ): void {

        foreach ($middlewares as $middleware) {

            [$class, $params] = array_pad(
                explode(':', $middleware, 2),
                2,
                null
            );

            $args = $params !== null
                ? explode(',', $params)
                : [];

            $instance = new $class(...$args);

            $instance->handle();

        }

    }


    /**
 * Jalankan controller
 */
private function runController(
    string $action
): void {

    if (!str_contains($action, '@')) {

        throw new \Exception(
            "Format route tidak valid. Gunakan Controller@method."
        );

    }

    [
        $controller,
        $method
    ] = explode('@', $action, 2);


    $controllerClass =
        "App\\Controllers\\{$controller}";


    if (!class_exists($controllerClass)) {

        throw new \Exception(
            "Controller {$controllerClass} tidak ditemukan."
        );

    }


    $controllerInstance =
        new $controllerClass();


    if (!method_exists(
        $controllerInstance,
        $method
    )) {

        throw new \Exception(
            "Method {$method} tidak ditemukan pada {$controllerClass}."
        );

    }


    $controllerInstance->$method();

    }

}