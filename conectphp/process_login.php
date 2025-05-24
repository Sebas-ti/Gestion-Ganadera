<?php
// process_login.php
// Este script procesa el envío del formulario de inicio de sesión.

// Iniciar sesión PHP al principio de todo
session_start();

// 1. Configuración de la base de datos
$host = 'localhost';
$db   = 'registros'; // Asegúrate de que esta sea tu base de datos
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Importante para la seguridad de PDO
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Loggear el error real para depuración, pero no mostrarlo al usuario por seguridad
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    $_SESSION['login_errors'] = ['Hubo un problema con la base de datos. Por favor, inténtalo más tarde.'];
    $_SESSION['old_usernameOrEmail'] = $_POST['usernameOrEmail'] ?? '';
    header('Location: login.php');
    exit();
}

// 2. Función limpiar datos
// Es buena práctica definirla una vez si se usa en varios scripts
if (!function_exists('limpiar')) {
    define('ENT_FLAGS', ENT_QUOTES | ENT_HTML5);
    function limpiar($data) {
        return htmlspecialchars(trim($data), ENT_FLAGS, 'UTF-8');
    }
}

$errors = []; // Array para almacenar los errores de inicio de sesión

// 3. Procesar solo si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = limpiar($_POST['usernameOrEmail'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validar campos obligatorios
    if (empty($usernameOrEmail) || empty($password)) {
        $errors[] = 'Por favor, introduce tu usuario/email y tu contraseña.';
    }

    // Si no hay errores iniciales, intentar autenticar
    if (empty($errors)) {
        try {
            // Buscar el usuario por nombre de usuario o correo electrónico
           $stmt = $pdo->prepare(
                'SELECT id, username, email, password FROM usuarios
                 WHERE username = ? OR email = ?'
            );
            // Pasar el valor dos veces porque hay dos placeholders '?'
            $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
            $user = $stmt->fetch();

            // Verificar credenciales
            // La comparación de contraseña se hace SIEMPRE para evitar ataques de enumeración de usuarios.
            if ($user && password_verify($password, $user['password'])) {
                // Inicio de sesión exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email']; // Almacena el email si lo necesitas en la sesión

                // Redirigir al usuario al panel de control
                header('Location: dashboard.php'); // Asegúrate de que esta URL sea correcta
                exit(); // Crucial para detener la ejecución del script
            } else {
                // Credenciales inválidas (mensaje genérico por seguridad)
                $errors[] = 'Usuario o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            // Loggear el error de la base de datos, pero mostrar un mensaje genérico al usuario
            error_log('Error en la autenticación SQL: ' . $e->getMessage());
            $errors[] = 'Ocurrió un error al intentar iniciar sesión. Por favor, inténtalo de nuevo más tarde.';
        }
    }

    // Si hay errores, almacenarlos en la sesión y redirigir de vuelta al formulario de login
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['old_usernameOrEmail'] = $usernameOrEmail; // Guardar el valor para rellenar
        header('Location: login.php'); // Redirigir a login.php para mostrar los errores
        exit();
    }

} else {
    // Si alguien intenta acceder a process_login.php directamente sin un POST,
    // puedes redirigirlo al formulario de login.
    header('Location: login.php');
    exit();
}
?>