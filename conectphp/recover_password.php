<?php
// recoverpassword.php
// Este script maneja la visualización del formulario de recuperación de contraseña y su procesamiento.

session_start(); // Iniciar sesión para manejar mensajes y datos antiguos

// Incluir los archivos de PHPMailer
// Asegúrate que la ruta a 'autoload.php' sea correcta desde la ubicación de este archivo PHP.
// Si recoverpassword.php está en la raíz de tu proyecto junto a la carpeta 'vendor', usa:
require 'vendor/autoload.php';
// Si recoverpassword.php está en una subcarpeta (ej. 'auth/recoverpassword.php'), y 'vendor' en la raíz, usa:
// require __DIR__ . '/../vendor/autoload.php';

// Importar las clases de PHPMailer que vamos a usar
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Necesario si usas SMTP para enviar correos

// Configuración de la base de datos (¡Asegúrate de que sean los correctos!)
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

$pdo = null; // Inicializar $pdo a null
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Loggear el error real para depuración, pero mostrar un mensaje genérico al usuario
    error_log('Error de conexión a la base de datos en recover_password.php: ' . $e->getMessage());
    $_SESSION['recovery_message'] = ['type' => 'danger', 'text' => 'Hubo un problema al conectar con la base de datos. Por favor, inténtalo más tarde.'];
    // No usamos die() aquí para que el HTML del formulario se muestre con el mensaje de error.
}

// Función para limpiar datos
if (!function_exists('limpiar')) {
    define('ENT_FLAGS', ENT_QUOTES | ENT_HTML5);
    function limpiar($data) {
        return htmlspecialchars(trim($data), ENT_FLAGS, 'UTF-8');
    }
}

// Variables para mensajes y datos antiguos
$recovery_message = $_SESSION['recovery_message'] ?? null;
unset($_SESSION['recovery_message']); // Limpiar el mensaje después de recuperarlo

$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']); // Limpiar el email antiguo

// Procesar el formulario si se envió por POST y la conexión a la DB es exitosa
if ($_SERVER["REQUEST_METHOD"] == "POST" && $pdo) {
    $email = limpiar($_POST['email'] ?? '');

    if (empty($email)) {
        $recovery_message = ['type' => 'danger', 'text' => 'Por favor, introduce tu correo electrónico.'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $recovery_message = ['type' => 'danger', 'text' => 'Formato de correo electrónico inválido.'];
    } else {
        // Verificar si el correo existe en la base de datos de usuarios
        try {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                // Email existe, proceder a generar y almacenar el token
                $token = bin2hex(random_bytes(32)); // Genera un token seguro y aleatorio
                $expires = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token válido por 1 hora

                // Insertar/actualizar el token en la tabla `restablecimientos_contrasena`
                // Asegúrate de que esta tabla exista y tenga las columnas 'email', 'token', 'expira'.
                // Puedes usar INSERT IGNORE para evitar duplicados si ya hay un token para ese email,
                // o DELETE/UPDATE si solo permites un token activo por email.
                // Aquí usamos INSERT y asumimos que la tabla permite múltiples tokens (lo cual es menos ideal)
                // O mejor aún, primero elimina tokens viejos para ese email:
                $delete_old_tokens = $pdo->prepare("DELETE FROM restablecimientos_contrasena WHERE email = :email");
                $delete_old_tokens->execute(['email' => $email]);

                $insert_sql = "INSERT INTO restablecimientos_contrasena (email, token, expira) VALUES (:email, :token, :expira)";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([
                    'email' => $email,
                    'token' => $token,
                    'expira' => $expires
                ]);

                // --- INICIO DEL BLOQUE PHPMailer para enviar el correo ---
                $mail = new PHPMailer(true); // Pasar 'true' habilita excepciones para depuración

                try {
                    // Configuración del servidor SMTP (¡AJUSTA ESTO A TUS DATOS REALES!)
                    $mail->isSMTP();                                            // Enviar usando SMTP
                    $mail->Host       = 'smtp.gmail.com';                       // Servidor SMTP (ej. 'smtp.gmail.com' para Gmail, 'smtp.office365.com' para Outlook/Hotmail)
                    $mail->SMTPAuth   = true;                                   // Habilitar autenticación SMTP
                    $mail->Username   = 'judiazrey01@gmail.com';                  // ¡Tu dirección de correo real que usas para enviar!
                    // Para Gmail con 2FA, usa una "contraseña de aplicación". Si no tienes 2FA, tu contraseña normal.
                    $mail->Password   = 'qtcj ayej vkxd xksh'; // ¡Tu contraseña o contraseña de aplicación!
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Habilitar encriptación TLS (o PHPMailer::ENCRYPTION_SMTPS para SSL en puerto 465)
                    $mail->Port       = 587;                                    // Puerto TCP a conectar; 587 para TLS/STARTTLS, 465 para SSL

                    // Configuración de Caracteres y Idioma (para mensajes de error de PHPMailer)
                    $mail->CharSet = 'UTF-8';
                    // La ruta del idioma puede variar si moviste la carpeta 'language'
                    $mail->setLanguage('es', 'vendor/phpmailer/phpmailer/language/');

                    // Remitente del correo
                    $mail->setFrom('no-reply@tudominio.com', 'Tu Aplicación - Gestión Ganadera'); // ¡Tu dirección de correo no-reply y nombre de la app!

                    // Destinatario del correo
                    $mail->addAddress($email); // El correo del usuario que solicitó el reseteo

                    // Contenido del correo
                    $mail->isHTML(false); // Estamos enviando texto plano, no HTML
                    $mail->Subject = "Restablecer tu Contraseña - Gestión Ganadera";
                    // ¡Importante! Cambia "http://localhost" por el dominio de tu sitio en producción
                    $resetLink = "http://localhost/hola/reset_password.php?token=" . $token;
                    $mail->Body    = "Hola,\n\nHaz solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:\n\n" . $resetLink . "\n\nEste enlace expirará en 1 hora.\n\nSi no solicitaste esto, ignora este correo.\n\nGracias,\nEl Equipo de Gestión Ganadera";

                    $mail->send();
                    $recovery_message = ['type' => 'success', 'text' => 'Se ha enviado un enlace de recuperación a tu correo electrónico. Por favor, revisa tu bandeja de entrada (y la carpeta de spam).'];

                } catch (Exception $e) {
                    $recovery_message = ['type' => 'danger', 'text' => 'Hubo un problema al enviar el correo electrónico. Por favor, inténtalo de nuevo más tarde.'];
                    // Registrar el error detallado de PHPMailer para depuración
                    error_log("Error al enviar correo con PHPMailer: " . $mail->ErrorInfo);
                }
                // --- FIN DEL BLOQUE PHPMailer ---

            } else {
                // Mensaje genérico por seguridad (correo no encontrado en la DB de usuarios)
                $recovery_message = ['type' => 'info', 'text' => 'Si tu correo electrónico está en nuestro sistema, te enviaremos un enlace de recuperación.'];
            }

        } catch (PDOException $e) {
            error_log('Error en el proceso de recuperación de contraseña (DB): ' . $e->getMessage());
            $recovery_message = ['type' => 'danger', 'text' => 'Ocurrió un error inesperado. Por favor, inténtalo de nuevo más tarde.'];
        }
    }
    // Almacenar el mensaje y el email antiguo en sesión para que se muestren después de la redirección POST-GET
    $_SESSION['recovery_message'] = $recovery_message;
    $_SESSION['old_email'] = $email;
    header('Location: recover_password.php'); // Redirigir para evitar re-envío del formulario
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Juan Sebastian Diaz Rey">
    <meta name="description" content="Página web de recuperación de contraseña para gestión ganadera">
    <meta name="keywords" content="HTML, CSS, JavaScript, PHP, recuperar contraseña">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Recuperar Contraseña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <link rel="stylesheet" href="/estilocss/recover_password.css">

    <script src="recover-password-script.js" defer></script>

    <link rel="icon" type="image/x-icon" href="/imagenes/logo.png">
</head>
<body>

    <div class="container">
        <h2>Recuperar Contraseña</h2>

        <?php
        // Mostrar mensajes de éxito o error
        if ($recovery_message):
        ?>
            <div class="alert alert-<?php echo htmlspecialchars($recovery_message['type']); ?>" role="alert">
                <?php echo htmlspecialchars($recovery_message['text']); ?>
            </div>
        <?php endif; ?>

        <form action="recover_password.php" method="post" id="recover_PasswordForm" novalidate>
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($old_email); ?>">
                <div class="error" id="emailError"></div>
            </div>
            <button type="submit" class="btn">Enviar Enlace de Recuperación</button>
        </form>
        <div class="footer">
            <p>¿Recordaste tu contraseña? <a href="login.php">Iniciar Sesión</a></p>
        </div>
        <div class="footer">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
        </div>
        <div class="footer">
            <p>Regresar al <a href="index.html">inicio</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBCEB42z5Mh5E6r5L3E4m9ZJ7g4y6hN4" crossorigin="anonymous"></script>
</body>
</html>