<?php

$host = 'localhost';
$db = 'bookmarks';
$user = 'postgres';
$pass = '89999';
$dsn = "pgsql:host=$host;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) { 
    die("Could not connect to the database: " . $e->getMessage());
}
?>
