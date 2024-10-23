<?php

$method = $_SERVER['REQUEST_METHOD'];

if($method !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: GET');
    exit;
}

// ISO 8601 (YYYY-MM-DD) is widely used and recommended
// eq equal, gt greater than, lt less than, gte greater than or equal, lte less than or equal

/*
- banner
    - id: comma-separated
    - status: active, inactive, expired, upcoming
    - 
    - type: limited, story, permanent
    - servant: by id, comma-separated
- servant
    - id
    - rarity
    - class
------------------------------------------------------------
/api/v1/banners?id=1,2,3
/api/v1/banners?status=active
/api/v1/banners?servant=1,2,3
/api/v1/banners?id=1&servant=1,2,3&status=active
GET /api/v1/banners?status=active&start_date[gte]=2024-08-01&end_date[lte]=2024-12-31
*/

$uri = $_SERVER['REQUEST_URI']; // to get the full path with query parameters after redirect from .htacess
$components = parse_url($uri, PHP_URL_PATH);
$path = $components['path']; // /api/v1/banners
$requestedResource = ltrim($path, '/api/v1/');
$query = $components['query']; 
parse_str($query, $queryParams);
// queryParams outputs: 
// Array
// (
//     [status] => active
//     [start_date] => Array
//         (
//             [gte] => 2024-08-01
//         )
// 
//     [end_date] => Array
//         (
//             [lte] => 2024-12-31
//         )
// 
// )

switch($requestedResource) {
    case 'banner':
            // create sub-folder for each resource (to keep the code clean)

            $bannerQuery = "SELECT * FROM `banner` WHERE 1=1 ";
            $queryParams = [];
            $responseData = [];

            if(isset($queryParams['id']) && isValidIntegerArrayQueryParameter($queryParams['id'])) {
                $bannerQuery .= "AND `id` IN (:bannerIds) ";
                $queryParams['bannerIds'] = $queryParams['id'];
            }

            if(isset($queryParams['servant']) && isValidIntegerArrayQueryParameter($queryParams['servant'])) {
                $bannerQuery .= "AND `servant_fk` IN (:servantIds) ";
                $queryParams['servantIds'] = $queryParams['servant'];
            }

            // if status equals 'all' or '*', do nothing
            // status is mutually exclusive with each other
            if (isset($queryParams['status']) && in_array($queryParams['status'], ['active', 'inactive', 'expired', 'upcoming'])) {
                $currentDate = date('Y-m-d');

                switch($queryParams['status']) {
                    case 'active':
                        $bannerQuery .= "AND `start_date` <= '$currentDate' AND `end_date` >= '$currentDate' ";
                        break;
                    case 'inactive':
                        $bannerQuery .= "AND `start_date` > '$currentDate' OR `end_date` < '$currentDate' ";
                        break;
                    case 'expired':
                        $bannerQuery .= "AND `end_date` < '$currentDate' ";
                        break;
                    case 'upcoming':
                        $bannerQuery .= "AND `start_date` > '$currentDate' ";
                        break;
                    default:
                            // not supported status
                            // TODO: return error message
                        break;
                }
            }

            /**********************************************************************************************************/

            $dateOperators = [
                'gte' => '>=',
                'gt' => '>',
                'lte' => '<=',
                'lt' => '<',
                'eq' => '=',
            ];

            $dateFormat = 'Y-m-d';

            foreach(array('start_date', 'end_date') as $dateType) {
                if (isset($queryParams[$dateType]) && !is_array($queryParams[$dateType])) {
                    $queryParams[$dateType] = ['eq' => $queryParams[$dateType]];
                }
    
                if(isset($queryParams[$dateType])) {
                    $dateString = $queryParams[$dateType][0];
                    $date = DateTime::createFromFormat($dateFormat, $dateString);

                    if(!$date || $date->format($dateFormat) !== $dateString) {
                        // date is invalid or not in the correct format
                        throw new Exception("Invalid date format for $dateType");
                    }

                    $bannerQuery .= "AND `".$dateType."` ".$dateOperators[$queryParams[$dateType][0]]." :".$dateType." ";
                    $queryParams[$dateType] = $queryParams[$dateType][0];
                }
            }

            /**********************************************************************************************************/

            $stmt = $pdo->prepare($bannerQuery); // after the query has been concatenated

            foreach ($queryParams as $key => $value) {
                $stmt->bindValue(":" . $key, $value);
            }

            try {
                $stmt->execute();
                $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $responseData['banners'] = $banners;
            } catch(PDOException $e) {
                // TODO: handle error better (no direct output to the browser)
                // TODO: add logging

                http_response_code(500);
                $responseData['error'] = [
                    'code' => $e->getCode(),
                    'error' => $e->getMessage(),
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($responseData); // TODO: add more data

        break;
    case 'servant':
            // TODO: implement logic for servants
        break;
    default:
            // not supported resource
            // TODO: return error message
        break;
}

function isValidIntegerArrayQueryParameter($parameter) {
    $elements = explode(',', $parameter);

    foreach ($elements as $value) {
        if (!ctype_digit($value)) {
            return false;
        }
    }

    return true;
}

?>