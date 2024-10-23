<?php

class BannerHandler {
    private $pdo;
    private $dateFormat = 'Y-m-d';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function handleRequest($queryParams) {
        $bannerQuery = "SELECT * FROM `banner` WHERE 1=1 ";
        $queryParams = [];
        $responseData = [];

        // check if request is for specific banners by id
        if(isset($queryParams['id']) && Utility::isValidIntegerArrayQueryParameter($queryParams['id'])) {
            $bannerQuery .= "AND `id` IN (:bannerIds) ";
            $queryParams['bannerIds'] = $queryParams['id'];
        }

        // check if request is for specific banners by servant ids
        if(isset($queryParams['servant']) && Utility::isValidIntegerArrayQueryParameter($queryParams['servant'])) {
            $bannerQuery .= "AND `servant_fk` IN (:servantIds) ";
            $queryParams['servantIds'] = $queryParams['servant'];
        }

        // check if request is for specific banners by status
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
        // check if request specifies start and end date, potentially with specifiers

        $dateOperators = [
            'gte' => '>=',
            'gt' => '>',
            'lte' => '<=',
            'lt' => '<',
            'eq' => '=',
        ];

        foreach(array('start_date', 'end_date') as $dateType) {
            if (isset($queryParams[$dateType]) && !is_array($queryParams[$dateType])) {
                $queryParams[$dateType] = ['eq' => $queryParams[$dateType]];
            }

            if (isset($queryParams[$dateType])) {
                $dateString = $queryParams[$dateType][0];
                $date = DateTime::createFromFormat($this->dateFormat, $dateString);

                if (!$date || $date->format($this->dateFormat) !== $dateString) {
                    // date is invalid or not in the correct format
                    throw new Exception("Invalid date format for $dateType");
                }

                $bannerQuery .= "AND `".$dateType."` ".$dateOperators[$queryParams[$dateType][0]]." :".$dateType." ";
                $queryParams[$dateType] = $queryParams[$dateType][0];
            }
        }

        /**********************************************************************************************************/

        // prepare and execute the sql query
        $stmt =$this->pdo->prepare($bannerQuery);

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

        return $responseData;
    }
}

?>