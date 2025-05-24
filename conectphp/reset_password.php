<?php
// reset_password.php
// Este script maneja la verificación del token de restablecimiento y la actualización de la contraseña.

session_start(); // Iniciar sesión para mensajes y datos antiguos

// --- 1. Configuración de la base de datos ---
$host = 'localhost';
$db   = 'registros'; // Tu base de datos de usuarios
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null; // Inicializar $pdo
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Error de conexión a la base de datos en reset_password.php: ' . $e->getMessage());
    $_SESSION['reset_message'] = ['type' => 'danger', 'text' => 'Hubo un problema al conectar con la base de datos. Por favor, inténtalo más tarde.'];
    // Redirigir al login o a una página de error genérica
    header('Location: login.php');
    exit();
}

// --- 2. Función limpiar datos ---
if (!function_exists('limpiar')) {
    define('ENT_FLAGS', ENT_QUOTES | ENT_HTML5);
    function limpiar($data) {
        return htmlspecialchars(trim($data), ENT_FLAGS, 'UTF-8');
    }
}

$reset_message = $_SESSION['reset_message'] ?? null;
unset($_SESSION['reset_message']); // Limpiar el mensaje después de recuperarlo

$token = $_GET['token'] ?? null; // Obtener el token de la URL

$user_id_to_reset = null; // Para almacenar el ID del usuario cuya contraseña se va a cambiar
$display_form = false;    // Controla si se muestra el formulario de nueva contraseña

// --- 3. Validación Inicial del Token (cuando se accede vía GET) ---
if ($token) {
    try {
        $stmt = $pdo->prepare("SELECT email, expira, usado FROM restablecimientos_contrasena WHERE token = ?");
        $stmt->execute([$token]);
        $reset_request = $stmt->fetch();

        if ($reset_request) {
            $now = new DateTime();
            $expires = new DateTime($reset_request['expira']);

            if ($now > $expires) {
                $reset_message = ['type' => 'danger', 'text' => 'El enlace de recuperación ha expirado. Por favor, solicita uno nuevo.'];
            } elseif ($reset_request['usado']) { // Asumiendo que añades una columna 'usado' (BOOLEAN, DEFAULT FALSE)
                $reset_message = ['type' => 'danger', 'text' => 'Este enlace de recuperación ya ha sido utilizado.'];
            } else {
                // Token es válido, obtener el ID del usuario
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$reset_request['email']]);
                $user = $stmt->fetch();

                if ($user) {
                    $user_id_to_reset = $user['id'];
                    $display_form = true; // Mostrar el formulario
                } else {
                    $reset_message = ['type' => 'danger', 'text' => 'Usuario no encontrado asociado al token.'];
                }
            }
        } else {
            $reset_message = ['type' => 'danger', 'text' => 'Token de recuperación inválido o no encontrado.'];
        }
    } catch (PDOException $e) {
        error_log('Error al validar token de restablecimiento: ' . $e->getMessage());
        $reset_message = ['type' => 'danger', 'text' => 'Ocurrió un error al validar el enlace. Por favor, inténtalo de nuevo.'];
    }
} else {
    // Si no hay token en la URL
    $reset_message = ['type' => 'danger', 'text' => 'No se proporcionó un token de recuperación válido.'];
}

// --- 4. Procesamiento del Formulario de Nueva Contraseña (cuando se envía vía POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && $pdo && $token) {
    // Revalidar el token para evitar ataques de reenvío si el token ya expiró/usó
    try {
        $stmt = $pdo->prepare("SELECT email, expira, usado FROM restablecimientos_contrasena WHERE token = ?");
        $stmt->execute([$token]);
        $reset_request = $stmt->fetch();

        if ($reset_request) {
            $now = new DateTime();
            $expires = new DateTime($reset_request['expira']);

            if ($now > $expires || $reset_request['usado']) {
                $reset_message = ['type' => 'danger', 'text' => 'El enlace de recuperación ha expirado o ya fue utilizado. Por favor, solicita uno nuevo.'];
                $display_form = false; // No mostrar el formulario
            } else {
                // El token sigue siendo válido
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$reset_request['email']]);
                $user = $stmt->fetch();

                if ($user) {
                    $user_id_to_reset = $user['id'];
                    $display_form = true; // Mantener formulario si hay errores de validación de contraseña
                } else {
                    $reset_message = ['type' => 'danger', 'text' => 'Usuario no encontrado asociado al token.'];
                    $display_form = false;
                }
            }
        } else {
            $reset_message = ['type' => 'danger', 'text' => 'Token de recuperación inválido o no encontrado.'];
            $display_form = false;
        }

    } catch (PDOException $e) {
        error_log('Error al revalidar token en POST: ' . $e->getMessage());
        $reset_message = ['type' => 'danger', 'text' => 'Ocurrió un error. Por favor, inténtalo de nuevo.'];
        $display_form = false;
    }

    // Si el token sigue siendo válido y tenemos un user_id_to_reset
    if ($display_form && $user_id_to_reset) {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $form_errors = [];

        if (empty($password) || empty($confirmPassword)) {
            $form_errors[] = 'Ambos campos de contraseña son obligatorios.';
        } elseif (strlen($password) < 6) {
            $form_errors[] = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif ($password !== $confirmPassword) {
            $form_errors[] = 'Las contraseñas no coinciden.';
        }

        if (empty($form_errors)) {
            try {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Actualizar la contraseña del usuario
                $update_user_stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $update_user_stmt->execute([$hashed_password, $user_id_to_reset]);

                // Marcar el token como usado (o eliminarlo)
                $mark_token_used_stmt = $pdo->prepare("UPDATE restablecimientos_contrasena SET usado = TRUE WHERE token = ?");
                $mark_token_used_stmt->execute([$token]);
                // O puedes eliminarlo: $pdo->prepare("DELETE FROM restablecimientos_contrasena WHERE token = ?")->execute([$token]);

                $_SESSION['reset_message'] = ['type' => 'success', 'text' => '¡Contraseña actualizada con éxito! Ya puedes iniciar sesión con tu nueva contraseña.'];
                header('Location: login.php');
                exit();

            } catch (PDOException $e) {
                error_log('Error al actualizar contraseña o marcar token: ' . $e->getMessage());
                $reset_message = ['type' => 'danger', 'text' => 'Ocurrió un error al actualizar tu contraseña. Por favor, inténtalo de nuevo.'];
            }
        } else {
            // Si hay errores de formulario, mostrar el formulario con los errores
            $reset_message = ['type' => 'danger', 'text' => implode('<br>', $form_errors)];
        }
    }
    // Si llegamos aquí y hay un mensaje, lo guardamos para la redirección
    $_SESSION['reset_message'] = $reset_message;
    header('Location: reset_password.php?token=' . urlencode($token)); // Redirigir de nuevo con el token
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Juan Sebastian Diaz Rey">
    <meta name="description" content="Página para restablecer la contraseña">
    <meta name="keywords" content="HTML, CSS, JavaScript, PHP, restablecer contraseña">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Restablecer Contraseña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <link rel="stylesheet" href="/estilocss/loginstyle.css"> <link rel="icon" type="image/x-icon" href="/imagenes/logo.png">
</head>
<body>

    <div class="container">
        <h2>Restablecer Contraseña</h2>

        <?php if ($reset_message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($reset_message['type']); ?>" role="alert">
                <?php echo $reset_message['text']; ?> </div>
        <?php endif; ?>

        <?php if ($display_form): ?>
            <form action="reset_password.php?token=<?php echo urlencode($token); ?>" method="post" id="resetPasswordForm" novalidate>
                <div class="form-group">
                    <label for="password">Nueva Contraseña:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <div class="error" id="passwordError"></div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6">
                    <div class="error" id="confirmPasswordError"></div>
                </div>
                <button type="submit" class="btn">Actualizar Contraseña</button>
            </form>
        <?php else: ?>
            <div class="footer mt-4">
                <p>¿Necesitas un nuevo enlace? <a href="recoverpassword.php">Solicitar recuperación</a></p>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>¿Recordaste tu contraseña? <a href="login.php">Iniciar Sesión</a></p>
        </div>
        <div class="footer">
            <p>Regresar al <a href="index.html">inicio</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBCEB42z5Mh5E6r5L3E4m9ZJ7g4y6hN4" crossorigin="anonymous"></script>
</body>
</html>