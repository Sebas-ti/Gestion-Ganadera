<?php
// login.php
// Este script maneja la visualización del formulario de inicio de sesión y los mensajes.

// Iniciar sesión PHP al principio de todo
session_start();

// Si el usuario ya está logueado, redirigir a su panel de control
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php'); // Asegúrate de que esta URL sea correcta (ej. tu_panel.php)
    exit();
}

// Recuperar mensajes de error de la sesión (si los hay)
$login_errors = [];
if (isset($_SESSION['login_errors']) && !empty($_SESSION['login_errors'])) {
    $login_errors = $_SESSION['login_errors'];
    unset($_SESSION['login_errors']); // Limpiar los errores después de mostrarlos
}

// Recuperar el valor antiguo del campo 'usernameOrEmail' para rellenar el formulario
$old_usernameOrEmail = $_SESSION['old_usernameOrEmail'] ?? '';
unset($_SESSION['old_usernameOrEmail']); // Limpiar después de usar

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Juan Sebastian Diaz Rey">
    <meta name="description" content="Página web de gestión ganadera">
    <meta name="keywords" content="HTML, CSS, JavaScript, PHP">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Iniciar Sesión</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <link rel="stylesheet" href="loginstyle.css">

    <script src="loginscript.js" defer></script>

    <link rel="icon" type="image/x-icon" href="/imagenes/logo.png">
</head>
<body>

    <div class="container">
        <h2>Iniciar Sesión</h2>

        <?php
        // Mostrar mensaje de registro exitoso (si vienes de register.php con éxito)
        if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
            echo '<div class="alert alert-success" role="alert">¡Registro exitoso! Ahora puedes iniciar sesión.</div>';
        }

        // Mostrar errores de inicio de sesión
        if (!empty($login_errors)) {
            echo '<div class="alert alert-danger" role="alert">';
            foreach ($login_errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        ?>

        <form action="process_login.php" method="post" id="loginForm" novalidate> <div class="form-group">
                <label for="usernameOrEmail">Usuario o Email:</label>
                <input type="text" id="usernameOrEmail" name="usernameOrEmail" required value="<?php echo htmlspecialchars($old_usernameOrEmail); ?>">
                <div class="error" id="userError"></div>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required minlength="6">
                <div class="error" id="passError"></div>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
        <div class="footer">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
        </div>
        <div class="footer">
            <p>¿Olvidaste tu contraseña? <a href="recover_password.php">Recuperar contraseña</a></p> </div>
        <div class="footer">
            <p>Regresa al <a href="index.html">inicio</a></p>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBCEB42z5Mh5E6r5L3E4m9ZJ7g4y6hN4" crossorigin="anonymous"></script>
</body>
</html>