<?php

use App\Core\Router;

$router = new Router();

$router->get(
    '/test',
    'TestController@index'
);

require BASE_PATH . '/routes/guest.php';
require BASE_PATH . '/routes/auth.php';
require BASE_PATH . '/routes/owner.php';
require BASE_PATH . '/routes/pegawai.php';

return $router;