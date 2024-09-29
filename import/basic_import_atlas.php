<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.inc.php';
require_once __DIR__ . '/../modules/dbconn.module.php';

/* using atlasacademy's basic json to populate a stock database */
$jsonData = file_get_contents('basic_servant.json');
$json = json_decode($jsonData, true);

$classes = [];

foreach($json as $servant) {
    $classes[$servant['classId']] = $servant['className'];

    $servantData = [
        'id' => $servant['collectionNo'],
        'name' => $servant['name'],
        'class_fk' => $servant['classId'],
        'rarity' => $servant['rarity'],
    ];

    downloadFaceImage($servant['collectionNo'], $servant['face']);
    importSingleServant($pdo, $servantData);
}

importClasses($pdo, $classes);

/* -------------------------------------------------------------------------- */

function downloadFaceImage($servantId, $url) {
    file_put_contents(
        '../images/faces/face_' . $servantId . '.png',
        file_get_contents($url)
    );
}

function importSingleServant($pdo, $servantData) {
    $stmt = $pdo->prepare('INSERT INTO servant (id, name, class_fk, rarity) VALUES (:id, :name, :class_fk, :rarity)');
    $stmt->bindParam(':id', $servantData['id']);
    $stmt->bindParam(':name', $servantData['name']);
    $stmt->bindParam(':class_fk', $servantData['class_fk']);
    $stmt->bindParam(':rarity', $servantData['rarity']);
    $stmt->execute($servantData);
}

function importClasses($pdo, $classData) {
    $stmt = $pdo->prepare('INSERT INTO class (id, name) VALUES (:id, :name)');

    foreach($classData as $classId => $className) {
        $stmt->bindParam(':id', $classId);
        $stmt->bindParam(':name', $className);
        $stmt->execute();
    }
}

?>