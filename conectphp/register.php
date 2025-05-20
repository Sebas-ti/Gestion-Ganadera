<?php
// register.php
// Conexión a la base de datos
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
    die('Error de conexión: ' . $e->getMessage());
}

// Función para limpiar datos
function limpiar($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y sanitizar
    $username = isset($_POST['username']) ? limpiar($_POST['username']) : '';
    $email = isset($_POST['email']) ? limpiar($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

    // Validaciones
    if (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'El nombre de usuario debe tener entre 3 y 20 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Correo electrónico no válido.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    // Verificar existencia de usuario
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE username = :username OR email = :email');
        $stmt->execute(['username' => $username, 'email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'El nombre de usuario o correo ya está registrado.';
        }
    }

    // Registrar usuario
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO usuarios (username, email, password) VALUES (:username, :email, :password)');
        $stmt->execute([
            'username' => $username,
            'email'    => $email,
            'password' => $hash
        ]);
        $success = true;
    }
}