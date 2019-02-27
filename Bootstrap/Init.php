<?php
use Bootstrap\Route;

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET');
header('Access-Control-Allow-Headers:X-Requested-With,X-CSRF-TOKEN,Content-Type,Access-Control-Allow-Origin');
$route = new Route;
$route->handle('test');
