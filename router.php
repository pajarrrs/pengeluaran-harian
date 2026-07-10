<?php
if (!getenv('APP_KEY') || getenv('APP_KEY') === '') {
    putenv('APP_KEY=base64:'.base64_encode(random_bytes(32)));
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

$_SERVER['REQUEST_URI'] = '/index.php';
require __DIR__.'/public/index.php';
