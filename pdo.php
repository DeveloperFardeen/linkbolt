<?php
$host = 'localhost';
$db   = 'link_bolt';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) { die(json_encode(['error' => 'DB Connect Failed'])); }
?>