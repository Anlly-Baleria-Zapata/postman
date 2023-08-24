<?php

$apiBasePath = '/api';

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$relativePath = str_replace($apiBasePath, '', $requestPath);

$endpoints = array(
    '/users' => 'users.php', 
);

$databaseConfigPath = __DIR__ . '/config/database.php';

if (array_key_exists($relativePath, $endpoints)) {
    include $databaseConfigPath;
    include $endpoints[$relativePath];
} else {
    header('Content-Type: application/json', true, 404);
    echo json_encode(array("error" => "Endpoint no encontrado."));
}
?>



