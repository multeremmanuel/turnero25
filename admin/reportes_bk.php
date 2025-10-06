<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require '../conexion.php';
?>

<?php include 'menu_admin.php'; ?>

<!-- === Librerías === -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* Ajustar altura del canvas para que ocupe 70% de la pantalla */
#grafica-reportes {
    height: 70vh !important; /* 70% de la altura de la ventana */
}
</style>

<div class="main-content">
    <!-- Header -->
    <div class="header">
        <h3>Reportes</h3>
        <div>Usuario conectado: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></div>
    </div>
    <!-- Botones de reportes -->
<br><center>    <button class="btn btn-primary m-2" id="btn-reporte-semanal">Reporte Semanal</button>
    <button class="btn btn-primary m-2" id="btn-reporte-caja">Reporte por Caja</button>
    <button class="btn btn-primary m-2" id="btn-reporte-usuario">Reporte por Usuario</button>

    <!-- Contenedor de gráficas -->
    <div class="mt-4">
        <canvas id="grafica-reportes"></canvas>
    </div>
</div>

<script>
$(document).ready(function(){
    let chartInstance = null;

    function mostrarGrafica(data, title, datasets) {
        if(chartInstance) chartInstance.destroy();

        const ctx = document.getElementById('grafica-reportes').getContext('2d');
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // permite respetar altura del canvas
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: title }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Función para convertir fecha a "Día DD/MM" en español
    function formatearFechas(data) {
        return data.map(d => {
            const fecha = new Date(d.fecha);
            const opciones = { weekday: 'short', day: '2-digit', month: '2-digit' };
            return fecha.toLocaleDateString('es-ES', opciones);
        });
    }

    // === REPORTE SEMANAL ===
    $('#btn-reporte-semanal').click(function() {
        $.ajax({
            url: 'get_reporte_semanal.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const etiquetas = formatearFechas(data); // etiquetas en español
                const datasets = [
                    { label: 'Normal', data: data.map(d => d.normal), backgroundColor: 'green' },
                    { label: 'Especial', data: data.map(d => d.especial), backgroundColor: 'blue' },
                    { label: 'Prioritario/Cita', data: data.map(d => d.prioritario), backgroundColor: 'red' }
                ];
                mostrarGrafica(etiquetas, 'Turnos últimos 7 días', datasets);
            },
            error: function() {
                alert('Error al cargar los datos del reporte semanal.');
            }
        });
    });

    // === REPORTE POR CAJA ===
    $('#btn-reporte-caja').click(function() {
        $.ajax({
            url: 'get_reporte_por_caja.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const etiquetas = data.fechas.map(f => {
                    const fecha = new Date(f);
                    return fecha.toLocaleDateString('es-ES', { weekday: 'short', day: '2-digit', month: '2-digit' });
                });

                const datasets = data.cajas.map(caja => ({
                    label: caja.nombre,
                    data: caja.turnos,
                    backgroundColor: caja.color
                }));
                mostrarGrafica(etiquetas, 'Turnos atendidos por caja (últimos 7 días)', datasets);
            },
            error: function() {
                alert('Error al cargar los datos del reporte por caja.');
            }
        });
    });

    // === REPORTE POR USUARIO ===
    $('#btn-reporte-usuario').click(function() {
        $.ajax({
            url: 'get_reporte_por_usuario.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const etiquetas = data.fechas.map(f => {
                    const fecha = new Date(f);
                    return fecha.toLocaleDateString('es-ES', { weekday: 'short', day: '2-digit', month: '2-digit' });
                });

                const datasets = data.usuarios.map(usuario => ({
                    label: usuario.usuario,
                    data: usuario.turnos,
                    backgroundColor: usuario.color
                }));
                mostrarGrafica(etiquetas, 'Turnos atendidos por usuario (últimos 7 días)', datasets);
            },
            error: function() {
                alert('Error al cargar los datos del reporte por usuario.');
            }
        });
    });
});
</script>
