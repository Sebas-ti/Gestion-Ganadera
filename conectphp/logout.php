<?php
// logout.php
// Script para cerrar la sesión del usuario.

session_start(); // Iniciar la sesión

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se usa session_id() para el id de sesión, también debe ser reseteado.
// Nota: Esto borrará la cookie de sesión y requerirá una nueva sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();

// Redirigir al usuario a la página de inicio de sesión
header('Location: login.php');
exit();
?>