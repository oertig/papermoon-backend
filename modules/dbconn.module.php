<?php

if (!isset($host) || empty($host)) { throw new Exception('Database host is not set'); }
if (!isset($dbname) || empty($dbname)) { throw new Exception('Database name is not set'); }
if (!isset($username) || empty($username)) { throw new Exception('Database username is not set'); }
if (!isset($password) || empty($password)) { throw new Exception('Database password is not set'); }

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    die;
}

?>