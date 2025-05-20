<?php
// login.php
// Conexión a la base de datos
$host = 'localhost';
$db   = 'registros';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Función limpiar datos
define('ENT_FLAGS', ENT_QUOTES | ENT_HTML5);
function limpiar($data) {
    return htmlspecialchars(trim($data), ENT_FLAGS, 'UTF-8');
}

$errors = [];
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = isset($_POST['usernameOrEmail']) ? limpiar($_POST['usernameOrEmail']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($usernameOrEmail) || empty($password)) {
        $errors[] = 'Todos los campos son obligatorios.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'SELECT id, username, email, password FROM usuarios
             WHERE username = :ue OR email = :ue'
        );
        $stmt->execute(['ue' => $usernameOrEmail]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Credenciales inválidas.';
        }
    }
}
?>