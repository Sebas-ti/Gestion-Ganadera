<?php
// register.php
// Este script maneja la visualización del formulario de registro y los mensajes.

// Iniciar sesión PHP al principio de todo para acceder a los mensajes
session_start();

// Recuperar mensajes de error de la sesión (si los hay)
$registration_errors = [];
if (isset($_SESSION['registration_errors']) && !empty($_SESSION['registration_errors'])) {
    $registration_errors = $_SESSION['registration_errors'];
    unset($_SESSION['registration_errors']); // Limpiar los errores después de mostrarlos
}

// Recuperar datos antiguos del formulario para rellenar (si los hay)
$old_form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Limpiar después de usar

// Verificar si hay un mensaje de éxito (esto vendría de process_register.php si lo creaste, o de aquí mismo)
$registration_success = isset($_GET['registration']) && $_GET['registration'] == 'success';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Juan Sebastian Diaz Rey">
    <meta name="description" content="Página web de gestión ganadera">
    <meta name="keywords" content="HTML, CSS, JavaScript, PHP">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Registro de Usuario</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <link rel="stylesheet" href="registerstyle.css">

    <script src="registerscript.js" defer></script>

    <link rel="icon" type="image/x-icon" href="/imagenes/logo.png">
</head>
<body>
    <div class="container">
        <h2>Registro de Nuevo Usuario</h2>

        <?php
        // Mostrar mensaje de éxito
        if ($registration_success) {
            echo '<div class="alert alert-success" role="alert">¡Registro exitoso!</div>';
        }

        // Mostrar mensajes de error
        if (!empty($registration_errors)) {
            echo '<div class="alert alert-danger" role="alert">';
            foreach ($registration_errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        ?>

        <form action="process_register.php" method="post" id="registerForm" novalidate>
            <div class="form-group">
                <label for="username">Nombre de usuario:</label>
                <input type="text" id="username" name="username" required minlength="3" maxlength="20" value="<?php echo htmlspecialchars($old_form_data['username'] ?? ''); ?>">
                <div class="error" id="usernameError"></div>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($old_form_data['email'] ?? ''); ?>">
                <div class="error" id="emailError"></div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required minlength="6">
                <div class="error" id="passwordError"></div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirmar contraseña:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6">
                <div class="error" id="confirmPasswordError"></div>
            </div>

            <button type="submit" class="btn">Registrarse</button>
        </form>

        <div class="footer">
            <p>¿Ya tienes una cuenta? <a href="login.php" class="btn btn-primary mt-3">Iniciar Sesión</a></p>
        </div>
        <div class="footer">
            <p>Regresa al <a href="index.html">inicio</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBCEB42z5Mh5E6r5L3E4m9ZJ7g4y6hN4" crossorigin="anonymous"></script>
</body>
</html>