<?php
session_start();
require 'conexion.php';

if (!isset($_REQUEST['tipo']) || empty($_REQUEST['tipo'])) {
    die("❌ Tipo de turno no especificado.");
}

$tipo = $_REQUEST['tipo'];
$hoy = date('Y-m-d');
$prioridad = 0;
$letra = '';
$turnoFormateado = '';

switch($tipo) {
    case 'especial':
        $prioridad = 3;
        $letra = 'M';
        $stmt = $pdo->prepare("SELECT turno FROM turnos WHERE tipo='especial' AND DATE(fechaRegistro)=CURDATE() ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $ultimo = $stmt->fetch();
        $numero = $ultimo ? intval(substr($ultimo['turno'],1)) + 1 : 1;
        $turnoFormateado = $letra . str_pad($numero, 3, '0', STR_PAD_LEFT);
        break;

    case 'prioritario':
        $prioridad = 2;
        $letra = 'S';
        $stmt = $pdo->prepare("SELECT turno FROM turnos WHERE tipo='prioritario' AND DATE(fechaRegistro)=CURDATE() ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $ultimo = $stmt->fetch();
        $numero = $ultimo ? intval(substr($ultimo['turno'],1)) + 1 : 1;
        $turnoFormateado = $letra . str_pad($numero, 3, '0', STR_PAD_LEFT);
        break;

    case 'normal':
    default:
        $prioridad = 0;
        $stmt = $pdo->prepare("SELECT turno FROM turnos WHERE tipo='normal' AND DATE(fechaRegistro)=CURDATE() ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $ultimo = $stmt->fetch();
        $numero = $ultimo ? intval($ultimo['turno']) + 1 : 1;
        $turnoFormateado = str_pad($numero, 3, '0', STR_PAD_LEFT);
        break;
}

// Insertar el turno en la base de datos
$sql = "INSERT INTO turnos (turno, tipo, atendido, fechaRegistro, prioridad) VALUES (:turno, :tipo, 0, NOW(), :prioridad)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':turno' => $turnoFormateado,
    ':tipo' => $tipo,
    ':prioridad' => $prioridad
]);

// Mostrar turno generado
$colorHeader = '#28a745';
if($tipo==='prioritario') $colorHeader='#ffc107';
if($tipo==='especial') $colorHeader='#007bff';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Turno Generado</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.container { max-width: 600px; margin-top: 50px; }
.card-header { font-size: 2rem; padding: 1.5rem; }
.card-body { font-size: 2rem; padding: 2rem; }
.btn { font-size: 2rem; padding: 1rem; width: 100%; }
</style>
</head>
<body>
<div class="container">
    <div class="card shadow text-center">
        <div class="card-header text-white" style="background-color: <?= $colorHeader ?>;">
            ✅ Turno Generado
        </div>
        <div class="card-body">
            <p>Su turno es: <strong><?= $turnoFormateado ?></strong></p>
            <p>Tipo de turno: <strong><?= ucfirst($tipo) ?></strong></p>
            <a href="registrar_turno.php" class="btn btn-success mt-3">Registrar Nuevo Turno</a>
        </div>
    </div>
</div>
</body>
</html>
