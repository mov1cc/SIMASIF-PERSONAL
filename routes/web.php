<?php

use App\Core\Router;


$router = new Router();


$router->get(
    '/test',
    'TestController@index'
);


return $router;