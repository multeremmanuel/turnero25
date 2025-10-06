<?php
// Duración de la sesión: 6 horas (21600 segundos)
ini_set('session.gc_maxlifetime', 10000);
session_set_cookie_params(10000);
session_start();

require 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1");
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        // Actualizamos login = 1 y login_fecha = NOW()
        $stmtLogin = $pdo->prepare("UPDATE usuarios SET login = 1, login_fecha = NOW() WHERE id = :id");
        $stmtLogin->execute([':id' => $user['id']]);

        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];

        if ($user['rol'] === 'admin') {
            header('Location: admin/control_panel.php');
        } elseif ($user['rol'] === 'operador') {
            header('Location: seleccionar_caja.php');
        } elseif ($user['rol'] === 'despachador') {
            header('Location: registrar_turno.php');
        } else {
            $error = "Rol de usuario no válido";
            session_destroy();
        }
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f4f6f8;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

.login-container {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card-login {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    max-width: 400px;
    width: 100%;
}

.card-login .card-header {
    background-color: #005BAC; /* Azul PAN */
    color: #fff;
    text-align: center;
    font-weight: 600;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.form-label {
    font-weight: 600;
    color: #333;
}

.btn-login {
    background-color: #005BAC; /* Azul PAN */
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    width: 100%;
    transition: background-color 0.2s ease, transform 0.15s ease;
    cursor: pointer;
}

.btn-login:hover {
    background-color: #004a91;
    transform: translateY(-1px);
}

.alert-custom {
    margin-bottom: 15px;
}
</style>
</head>
<body>

<div class="login-container">
    <div class="card card-login">
        <div class="card-header">
            <h3>Iniciar Sesión</h3>
        </div>
        <div class="card-body">
            <?php if($error): ?>
                <div class="alert alert-danger alert-custom"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" name="usuario" id="usuario" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <button type="submit" class="btn-login">Entrar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
