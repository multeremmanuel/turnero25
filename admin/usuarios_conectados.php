<?php
session_start();
require '../conexion.php'; // Ajusta la ruta si es necesario

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Obtener usuarios conectados
$stmt = $pdo->query("SELECT nombre, usuario, login_fecha FROM usuarios WHERE login = 1 ORDER BY login_fecha DESC");
$usuarios_conectados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Usuarios Conectados</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f4f6f8;
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    .container-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); /* ✅ Cards más pequeñas */
        gap: 12px; /* Menos espacio entre cards */
        margin-top: 20px;
    }
    .card-user {
        position: relative;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        text-align: center;
        transition: transform 0.2s ease;
        aspect-ratio: 1 / 1; /* ✅ Mantiene formato cuadrado */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 8px; /* ✅ Menos padding */
    }
    .card-user:hover {
        transform: translateY(-3px);
    }
    .dot-online {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 12px;
        height: 12px;
        background: #28a745; /* Verde */
        border-radius: 50%;
        box-shadow: 0 0 5px rgba(40,167,69,0.6);
    }
    .user-name {
        font-weight: 600;
        font-size: 0.9rem; /* ✅ Texto más pequeño */
        color: #333;
        margin-bottom: 3px;
    }
    .user-username {
        font-size: 0.75rem;
        color: #666;
        margin-bottom: 3px;
    }
    .login-time {
        font-size: 0.7rem;
        color: #888;
    }
    .title {
        text-align: center;
        margin-top: 10px;
        font-weight: 600;
        color: #005BAC;
        font-size: 1.2rem;
    }
</style>
</head>
<body>

<script>
setInterval(() => {
    location.reload(); // Recarga la página
}, 10000); // 180000 ms = 10 segundos
</script>


<?php include 'menu_admin.php'; ?>

<div class="main-content">
    <div class="header">
        <h3>Usuarios Conectados</h3>
        <div>Usuario conectado: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></div>
    </div>

<div class="container">

    <div class="container-cards">
        <?php if (count($usuarios_conectados) > 0): ?>
            <?php foreach ($usuarios_conectados as $usuario): ?>
                <div class="card-user">
                    <div class="dot-online"></div>
                    <div class="user-name"><?= htmlspecialchars($usuario['nombre']) ?></div>
                    <div class="user-username">@<?= htmlspecialchars($usuario['usuario']) ?></div>
                    <div class="login-time">
                        <?= date("d/m H:i", strtotime($usuario['login_fecha'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">No hay usuarios conectados.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
