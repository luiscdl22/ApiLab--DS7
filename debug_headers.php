<?php
header('Content-Type: application/json');
echo json_encode([
    'getallheaders' => function_exists('getallheaders') ? getallheaders() : 'no existe',
    'SERVER_AUTH' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'NO ESTÁ EN $_SERVER',
    'redirect_auth' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'NO ESTÁ TAMPOCO'
]);