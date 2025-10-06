<?php
session_start();
require '../conexion.php';

// Solo admin puede acceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$mensaje = '';

// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $password = $_POST['password'];

    if(!empty($password)){
        // Encriptar contraseña
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password = :password, fecha_actualizacion = NOW() WHERE id = :id");
        $stmt->execute([
            ':password' => $hash,
            ':id' => $id
        ]);
        $mensaje = "Contraseña modificada correctamente.";
    } else {
        $mensaje = "La contraseña no puede estar vacía.";
    }
}

// Obtener todos los usuarios
$stmtUsuarios = $pdo->query("SELECT id, nombre, usuario FROM usuarios ORDER BY nombre");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cambiar Contraseña - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { margin: 0; font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f6f8; }
.main-content { margin-left: 240px; padding: 22px; }
.header { background: #fff; padding: 12px 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 18px; }
.header h3 { margin: 0; font-size: 1.15rem; color: #333; }
.form-card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); max-width: 500px; }
.form-label { font-weight: 600; color: #333; }
.form-control, .form-select { border-radius: 8px; padding: 10px 12px; }
.btn-primary { border-radius: 8px; padding: 10px 16px; font-weight: 600; }
.alert-custom { margin-bottom: 18px; }
</style>
</head>
<body>

<?php include 'menu_admin.php'; ?>

<div class="main-content">
    <div class="header">
        <h3>Cambiar Contraseña</h3>
        <div>Usuario conectado: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></div>
    </div>

    <div class="form-card">
        <?php if($mensaje): ?>
            <div class="alert alert-success alert-custom"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <form method="post" class="mt-3">
            <div class="mb-3">
                <label for="id" class="form-label">Seleccionar Usuario</label>
                <select name="id" id="id" class="form-select" required>
                    <option value="">-- Selecciona un usuario --</option>
                    <?php foreach($usuarios as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['usuario']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Nueva Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
        </form>
    </div>
</div>

</body>
</html>
