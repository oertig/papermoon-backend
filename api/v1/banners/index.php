<?php
error_reporting(E_ALL);

$requestMethod = $_SERVER['REQUEST_METHOD'];

// if($requestMethod !== 'POST') {
//     http_response_code(405); // 405 = method not supported
//     die();
// }

// if(!in_array($_POST['request'], ['banner', 'servant'])) {
//     http_response_code(400); // 400 = bad request
//     die();
// }

require_once __DIR__ . '/../../../config/config.inc.php';
require_once __DIR__ . '/../../../modules/dbconn.module.php';

/*
example requests:

/banner : get all banners
/banners?id=* : get all banners
/banners?id=1 : get banner 1
/banners?status=active : get active banners // TODO: define possible values
/banners?servant=1 : get banners for servant 1
/banners?servant=1,2,3 : get banners for servants 1,2,3
/banners?servant=all
*/

$bannerID = $_GET['id'] ?? null;
$bannerStatus = $_GET['status'] ?? null;
$bannerServants = $_GET['servant'] ?? null;
$bannerServantIDs = [];

if($bannerServants) { $bannerServantIDs = explode(',', $bannerServants); }

$bannerQuery = "SELECT * FROM `banner` ORDER BY `start_date` ASC, `end_date` ASC"; // default to fetch all banners

// TODO: implement better permutation-checking
// if($bannerID === '*') {
//     if($bannerStatus === 'active') {
//         if(!empty($bannerServantIDs)) {
//             // get active banners for specified servants
//         } else {
//             // get all active banners
//         }
//     } else {
//         // get all banners
//     }
// } else {
//     // get specific banners
// }

$status = 'success';

try {
    $stmt = $pdo->prepare($bannerQuery);
    $stmt->execute();
    $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $status = 'error';
    $errorCode = $pdo->errorCode();
    $errorMessage = $pdo->errorInfo();
}

$apiResponse = [
    'api_version' => $apiV1Version,
    'status' => $status,
    'amount_banners' => count($banners),
    'data' => $banners,
];

if($status !== 'success') {
    $apiResponse['error'] = [ // TODO: implement risk-free error message (don't show pdo message and code to users)
        'code' => $errorCode,
        'message' => $errorMessage,
    ];
}

echo json_encode($apiResponse, JSON_UNESCAPED_SLASHES);
?>