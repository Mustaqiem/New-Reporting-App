<?php

session_start();

require __DIR__. '/../vendor/autoload.php';

use Slim\App;

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}



$app = new App([
	'settings'	=> require __DIR__. '/setting.php'
	]);

require __DIR__. '/container.php';
require __DIR__. '/routes/api.php';
require __DIR__. '/routes/web.php';
