<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.inc.php';
require_once __DIR__ . '/../modules/dbconn.module.php';

$baseFilePathAndName = './raw_data/gdocs_csv_community/Upcoming Banners by Servant - %.csv';
$typeNames = ['Saber', 'Archer', 'Lancer', 'Rider', 'Caster', 'Assassin', 'Berserker', 'EXTRA'];
$allBanners = [];

foreach($typeNames as $typeName) {
    $file = new SplFileObject(str_replace('%', $typeName, $baseFilePathAndName));
    $file->setFlags(SplFileObject::READ_CSV);
    $file->setCsvControl(',');

    $titleRow = true;

    foreach($file as $row) {
        if($titleRow) {
            $titleRow = false;
            continue;
        }

        $servantID = $row[0];
        unset($row[0]); // number
        unset($row[1]); // name
        unset($row[2]); // empty cell

        $bannerChunks = array_chunk(iterator_to_array($row), 5);

        foreach($bannerChunks as $bannerChunk) {
            if(empty($bannerChunk[0])) { // no more banners for current servant
                break;
            }

            $bannerName = $bannerChunk[0];
            $bannerSolo = $bannerChunk[1];
            $bannerSchedule = $bannerChunk[2]; // region? US vs JP?
            $startDate = $bannerChunk[3];
            $endDate = $bannerChunk[4];

            if(!array_key_exists($bannerName, $allBanners)) {
                $allBanners[$bannerName] = [
                    'banner_solo' => $bannerSolo,
                    'banner_schedule' => $bannerSchedule,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'wiki_link' => '', // TODO: add wiki link
                    'servants' => [],
                ];
            }

            array_push($allBanners[$bannerName]['servants'], $servantID);
        }
    }
}

uasort($allBanners, function($a, $b) {
    $bannerDateFormat = 'Y/m/d';
    $dateA = DateTimeImmutable::createFromFormat($bannerDateFormat, $a['start_date']);
    $dateB = DateTimeImmutable::createFromFormat($bannerDateFormat, $b['start_date']);
    return $dateA->getTimestamp() - $dateB->getTimestamp();
});

// echo '<pre>';
// print_r($allBanners);

foreach($allBanners as $bannerName => $banner) {
    try {
        $stmt = $pdo->prepare('INSERT INTO banner (`name`, `start_date`, end_date, wiki_link) VALUES (:name, :start_date, :end_date, :wiki_link)');
        $stmt->bindParam(':name', $bannerName);
        $stmt->bindParam(':start_date', $banner['start_date']);
        $stmt->bindParam(':end_date', $banner['end_date']);
        $stmt->bindParam(':wiki_link', $banner['wiki_link']);
        $stmt->execute();

        $bannerID = $pdo->lastInsertId();

        foreach($banner['servants'] as $servantID) {
            $stmt = $pdo->prepare('INSERT INTO banner_servant (`banner_fk`, `servant_fk`) VALUES (:banner_fk, :servant_fk)');
            $stmt->bindParam(':banner_fk', $bannerID);
            $stmt->bindParam(':servant_fk', $servantID);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        echo "Error inserting banner: " . $e->getMessage() . "\n";
        echo "<br><hr><br>";
    }
}
?>