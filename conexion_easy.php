<?php
// conexion_easy.php
$eaHost = 'localhost';
$eaDb   = 'citas';
$eaUser = 'citas_user';
$eaPass = 'TuPasswordSeguro';

try {
    $pdoEA = new PDO("mysql:host={$eaHost};dbname={$eaDb};charset=utf8mb4", $eaUser, $eaPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Error al conectar con la base de citas: ' . $e->getMessage()]));
}
