<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$baseFilePathAndName = './raw_data/gdocs_csv_community/Upcoming Banners by Servant - %.csv';
$typeNames = ['Saber', 'Archer', 'Lancer', 'Rider', 'Caster', 'Assassin', 'Berserker', 'Extra'];

foreach($typeNames as $typeName) {
    $file = new SplFileObject(str_replace('%', $typeName, $baseFilePathAndName));
    $file->setFlags(SplFileObject::READ_CSV);
    $file->setCsvControl(',');

    $titleRow = true;

    foreach($file as $row) {
        // go through rows
        // print_r($row);

        if($titleRow) {
            $titleRow = false;
            continue;
        }

        $bannerChunks = array_chunk(iterator_to_array($row), 3); // careful: there are non-visible elements! 3 is not correct!
        $servantID = $bannerChunks[0][0];
        unset($bannerChunks[0]);

        foreach($bannerChunks as $bannerChunk) {
            if(empty($bannerChunk[0])) { // no more banners for current servant
                break;
            }

            $bannerName = $bannerChunk[0];
            $startDate = $bannerChunk[1];
            $endDate = $bannerChunk[2];
        }
    }

    die;
}
?>