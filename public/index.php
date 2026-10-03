<?php

use Dotenv\Dotenv;

/*
|--------------------------------------------------------------------------
| Composer Autoload
|--------------------------------------------------------------------------
*/

require __DIR__ . '/../vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| Load Environment Variable
|--------------------------------------------------------------------------
*/

$dotenv = Dotenv::createImmutable(
    dirname(__DIR__)
);

$dotenv->load();


/*
|--------------------------------------------------------------------------
| Load Configuration
|--------------------------------------------------------------------------
*/

$appConfig = require dirname(__DIR__) . '/app/Config/app.php';

$dbConfig = require dirname(__DIR__) . '/app/Config/database.php';

$mailConfig = require dirname(__DIR__) . '/app/Config/mail.php';

require dirname(__DIR__) . '/app/Config/constants.php';


/*
|--------------------------------------------------------------------------
| Application Timezone
|--------------------------------------------------------------------------
*/

date_default_timezone_set(
    $appConfig['timezone']
);


/*
|--------------------------------------------------------------------------
| Session Start
|--------------------------------------------------------------------------
*/

session_name(
    $appConfig['session']['name']
);

session_start();


/*
|--------------------------------------------------------------------------
| Load Exception Handler
|--------------------------------------------------------------------------
*/

require dirname(__DIR__) . '/app/Exceptions/Handler.php';


/*
|--------------------------------------------------------------------------
| Load Routes
|--------------------------------------------------------------------------
*/

// sementara belum pakai Router
// nanti diganti ketika Core Router selesai

require dirname(__DIR__) . '/routes/web.php';


echo "SIMASIF Framework Running";