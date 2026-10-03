<?php

namespace App\Core;

class Router
{

    private array $routes = [];


    private array $middlewares = [];


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
     */
    private function runMiddleware(
        array $middlewares
    ): void {

        foreach ($middlewares as $middleware) {


            $instance = new $middleware();


            $instance->handle();

        }

    }


    /**
     * Jalankan controller
     */
    private function runController(
        string $action
    ): void {


        [$controller, $method] =
            explode('@', $action);



        $controllerClass =
            "App\\Controllers\\{$controller}";


        if (!class_exists($controllerClass)) {

            throw new \Exception(
                "Controller {$controllerClass} tidak ditemukan"
            );

        }



        $controllerInstance =
            new $controllerClass();



        if (!method_exists(
            $controllerInstance,
            $method
        )) {

            throw new \Exception(
                "Method {$method} tidak ditemukan"
            );

        }


        $controllerInstance->$method();

    }


}