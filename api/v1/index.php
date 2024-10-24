<?php

// deny all non-GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: GET');
    exit;
}

$uri = $_SERVER['REQUEST_URI']; // to get the full path with query parameters after redirect from .htacess
$components = parse_url($uri);
$path = $components['path']; // /api/v1/banners
$requestedResource = ltrim($path, '/api/v1/');
$query = $components['query'] ?? '';
parse_str($query, $queryParams);

/*
$queryParams contains an array of the query parameters:

    for parameters without specifiers: 
        $queryParams['key'] = value
    for parameters with specifiers: 
        $queryParams['key'][specifier] = value
*/

switch($requestedResource) {
    case 'banners':
        require_once __DIR__ . '/includes/Utility.php';
        require_once __DIR__ . '/includes/BannerHandler.php';

        $bannerHandler = new BannerHandler($pdo);
        $responseData = $bannerHandler->handleRequest($queryParams);

        header('Content-Type: application/json');
        echo json_encode($responseData); // TODO: add more data
        
        break;
    case 'servants':
            // TODO: implement logic for servants
        break;
    default:
            // not supported resource
            header('HTTP/1.1 404 Not Found');
            echo 'Resource not found.';
        break;
}

?>