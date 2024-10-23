<?php

// deny all non-GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: GET');
    exit;
}

/*
    possible resources and their parameters

    - banners
        - id: comma-separated
        - status: active, inactive, expired, upcoming
        - servant: by id, comma-separated
        - start_date, end_date: 
            - in the format YYYY-MM-DD
            - with possible specifiers: 
                - gte: greater than or equal
                - lte: less than or equal
                - gt: greater than
                - lt: less than
                - eq: equal

    Example requests fo banners resource:
        - /api/v1/banners
        - /api/v1/banners?id=1,2,3
        - /api/v1/banners?status=active
        - /api/v1/banners?servant=1,2,3
        - /api/v1/banners?start_date=2024-01-01&end_date=2024-12-31 // no specifiers given, defaults to [eq]
        - /api/v1/banners?id=1&servant=1,2,3&status=active&start_date[gte]=2024-01-03&end_date[lte]=2024-01-07
 */

$uri = $_SERVER['REQUEST_URI']; // to get the full path with query parameters after redirect from .htacess
$components = parse_url($uri, PHP_URL_PATH);
$path = $components['path']; // /api/v1/banners
$requestedResource = ltrim($path, '/api/v1/');
$query = $components['query']; 
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
            // TODO: return error message
        break;
}

?>