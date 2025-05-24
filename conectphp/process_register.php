<?php
// process_register.php
// Este script procesa el envío del formulario de registro.

session_start(); // Iniciar sesión al principio para manejar mensajes y redirecciones

// Redireccionar si el usuario ya está logueado (opcional, pero buena práctica)
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php'); // O a la página principal después del login
    exit();
}

// Conexión a la base de datos (asegúrate de que estos datos sean correctos)
$host = 'localhost';
$db   = 'registros';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Loggear el error real, pero mostrar un mensaje genérico al usuario
    error_log('Error de conexión a la base de datos en registro: ' . $e->getMessage());
    $_SESSION['registration_errors'] = ['Hubo un problema con la base de datos. Por favor, inténtalo más tarde.'];
    $_SESSION['form_data'] = $_POST; // Guardar datos para rellenar
    header('Location: register.php');
    exit();
}

// Función para limpiar datos (definida aquí para que sea accesible)
if (!function_exists('limpiar')) {
    define('ENT_FLAGS', ENT_QUOTES | ENT_HTML5);
    function limpiar($data) {
        return htmlspecialchars(trim($data), ENT_FLAGS, 'UTF-8');
    }
}

$errors = []; // Array para almacenar los errores de validación

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y sanitizar
    $username = limpiar($_POST['username'] ?? '');
    $email = limpiar($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validaciones
    if (empty($username)) {
        $errors[] = 'El nombre de usuario es obligatorio.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'El nombre de usuario debe tener entre 3 y 20 caracteres.';
    }

    if (empty($email)) {
        $errors[] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correo electrónico no es válido.';
    }

    if (empty($password)) {
        $errors[] = 'La contraseña es obligatoria.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    // Verificar existencia de usuario o email si no hay errores previos
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE username = :username OR email = :email LIMIT 1');
            $stmt->execute(['username' => $username, 'email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'El nombre de usuario o correo electrónico ya está registrado.';
            }
        } catch (PDOException $e) {
            error_log('Error al verificar usuario/email existente: ' . $e->getMessage());
            $errors[] = 'Ocurrió un error al verificar los datos. Por favor, inténtalo de nuevo.';
        }
    }

    // Registrar usuario si no hay errores
    if (empty($errors)) {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (username, email, password) VALUES (:username, :email, :password)');
            $stmt->execute([
                'username' => $username,
                'email'    => $email,
                'password' => $hash
            ]);
            // Redirigir a login.php con un mensaje de éxito
            header('Location: login.php?registration=success');
            exit(); // Muy importante después de header()
        } catch (PDOException $e) {
            error_log('Error al registrar usuario: ' . $e->getMessage());
            $errors[] = 'Ocurrió un error al registrar tu cuenta. Por favor, inténtalo de nuevo.';
        }
    }

    // Si hay errores en este punto, almacenarlos en la sesión y redirigir
    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['form_data'] = ['username' => $username, 'email' => $email]; // Para rellenar el formulario
        header('Location: register.php');
        exit();
    }

} else {
    // Si se accede a este archivo directamente por GET, redirigir al formulario
    header('Location: register.php');
    exit();
}
?>