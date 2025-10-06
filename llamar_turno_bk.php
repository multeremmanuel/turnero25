<?php
session_start();
require 'conexion.php';

// Validar que sea operador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'operador') {
    header('Location: login.php');
    exit;
}

// Validar que el operador tenga asignada una caja
if (!isset($_SESSION['idCaja'])) {
    header('Location: seleccionar_caja.php');
    exit;
}

$idCaja = $_SESSION['idCaja'];
$idUsuario = $_SESSION['usuario_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Llamar Turno - Operador</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    .especial { color: blue; font-weight: bold; }
    .prioritario { color: orange; font-weight: bold; }
    .normal { color: green; font-weight: bold; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
<div class="container-fluid">
    <a class="navbar-brand" href="#">Panel de Control</a>
    <div class="d-flex">
        <span class="navbar-text text-white me-3">
            <?= $_SESSION['usuario_nombre']; ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
    </div>
</div>
</nav>

<div class="container mt-5 text-center">
    <h3>Llamar Siguiente Turno</h3>

    <div id="turno-container" class="mt-4">
        <!-- Aquí se mostrará el turno disponible vía AJAX -->
    </div>
</div>

<script>
function cargarTurno() {
    $.ajax({
        url: 'get_siguiente_turno.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if(data.turno) {
                // Determinar clase y texto según tipo
                let tipoClase = '';
                let textoTipo = '';

                switch(data.prioridad) {
                    case 3:
                        tipoClase = 'especial';
                        textoTipo = 'Especial';
                        break;
                    case 1:
                        tipoClase = 'prioritario';
                        textoTipo = 'Prioritario';
                        break;
                    case 0:
                        tipoClase = 'normal';
                        textoTipo = 'Normal';
                        break;
                }

                let html = `
                    <div class="card p-4">
                        <h1 class="${tipoClase}">Turno: ${data.turno}</h1>
                        <p>Tipo: ${textoTipo}</p>
                        <button id="llamar-btn" class="btn btn-success mt-3">Llamar este turno</button>
                    </div>
                `;
                $('#turno-container').html(html);

                // Click para llamar el turno
                $('#llamar-btn').click(function() {
                    $.ajax({
                        url: 'llamar_turno_ajax.php',
                        method: 'POST',
                        data: { idTurno: data.id },
                        success: function(resp) {
                            alert(resp);
                            cargarTurno(); // Recargar el siguiente turno
                        }
                    });
                });
            } else {
                $('#turno-container').html('<div class="alert alert-secondary">No hay turnos disponibles.</div>');
            }
        }
    });
}

// Cargar turno al inicio
cargarTurno();

// Refrescar turno cada 3 segundos
setInterval(cargarTurno, 3000);
</script>
</body>
</html>
