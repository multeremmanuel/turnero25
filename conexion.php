<?php
// conexion.php

$host = 'localhost';
$db   = 'turnero';
$user = 'root';
$pass = 'Temporal.2025';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Conexión exitosa";
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
?>
