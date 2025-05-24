<?php

// Conexión a la Base de Datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registros"; // **CAMBIA ESTO**

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "Las contraseñas no coinciden.";
        exit();
    }

    // ¡Hashear la nueva contraseña antes de almacenarla!
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Validar el token de nuevo
    $sql = "SELECT email FROM restablecimientos_contrasena WHERE token = ? AND expira > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_email = $row['email'];

        // Actualizar la contraseña del usuario
        $update_sql = "UPDATE usuarios SET password = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_password, $user_email);

        if ($update_stmt->execute()) {
            // Eliminar el token utilizado de la tabla restablecimientos_contrasena
            $delete_sql = "DELETE FROM restablecimientos_contrasena WHERE token = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("s", $token);
            $delete_stmt->execute();
            $delete_stmt->close();

            echo "Tu contraseña ha sido restablecida con éxito. Ahora puedes <a href='login.html'>iniciar sesión</a>.";
        } else {
            echo "Error al actualizar la contraseña: " . $update_stmt->error;
        }
        $update_stmt->close();

    } else {
        echo "El enlace de restablecimiento de contraseña es inválido o ha expirado.";
    }

    $stmt->close();
}

$conn->close();

?>