<?php
// dashboard.php
// Página básica del panel de control. Verifica el inicio de sesión.

session_start(); // Iniciar la sesión PHP

// 1. Verificar si el usuario ha iniciado sesión
// Si 'user_id' no está en la sesión, significa que el usuario no está logueado.
if (!isset($_SESSION['user_id'])) {
    // Redirigir al usuario a la página de inicio de sesión
    header('Location: login.php');
    exit(); // Es crucial usar exit() después de una redirección
}

// Si el usuario está logueado, podemos obtener sus datos de la sesión
$username = $_SESSION['username'] ?? 'Usuario'; // Usar 'Usuario' como fallback si no está el username
$email = $_SESSION['email'] ?? ''; // Puedes mostrar el email si también lo guardaste en la sesión

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Juan Sebastian Diaz Rey">
    <meta name="description" content="Panel de control de gestión ganadera">
    <meta name="keywords" content="HTML, CSS, JavaScript, PHP, dashboard">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard - Gestión Ganadera</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <link rel="stylesheet" href="/estilocss/dashboard.css"> <link rel="icon" type="image/x-icon" href="/imagenes/logo.png">
</head>
<body>

    <div class="container mt-5">
        <div class="card p-4 shadow-sm">
            <h1 class="card-title text-center mb-4">Bienvenido al Dashboard</h1>
            <p class="card-text text-center lead">
                ¡Hola, **<?php echo htmlspecialchars($username); ?>**! Has iniciado sesión con éxito.
            </p>
            <?php if (!empty($email)): ?>
                <p class="text-center text-muted">
                    Tu correo electrónico: <?php echo htmlspecialchars($email); ?>
                </p>
            <?php endif; ?>

            <hr>

            <div class="text-center">
                <p>Aquí es donde iría el contenido principal de tu aplicación de gestión ganadera.</p>
                <p>Puedes añadir enlaces a diferentes secciones como:</p>
                <ul>
                    <li><a href="#">Gestión de Ganado</a></li>
                    <li><a href="#">Inventario</a></li>
                    <li><a href="#">Reportes</a></li>
                    <li><a href="#">Configuración</a></li>
                </ul>
            </div>

            <div class="mt-4 text-center">
                <a href="logout.php" class="btn btn-danger btn-lg">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBCEB42z5Mh5E6r5L3E4m9ZJ7g4y6hN4" crossorigin="anonymous"></script>
</body>
</html>