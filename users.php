<?php

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "proyectjard";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Leer el JSON del cuerpo de la solicitud
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Verificar si la acción está presente en los datos
if (isset($data['action'])) {
    $action = $data['action'];

    if ($action === 'register') {
        // Obtener los datos del usuario enviados por POST
        $nombre = $data['nombre'];
        $identificacion = $data['identificacion'];
        $contrasena = $data['contrasena'];
        $rol = $data['rol'];

        // Generar el hash de la contraseña
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar en la tabla informacion_roles
        $sql_informacion_roles = "INSERT INTO informacion_roles (id_rol, nombre, identificacion, contraseña) VALUES ('', '$nombre', '$identificacion', '$hashed_password')";

        if ($conn->query($sql_informacion_roles) === TRUE) {
            // Obtener el ID generado en la inserción anterior
            $informacion_roles_id = $conn->insert_id;

            // Insertar en la tabla nombre_rol
            $sql_nombre_rol = "INSERT INTO nombre_rol (tecnico_sistemas, empleado, gerencia) VALUES (";
            if ($rol == "tecnico_sistemas") {
                $sql_nombre_rol .= "'$informacion_roles_id', NULL, NULL)";
            } elseif ($rol == "empleado") {
                $sql_nombre_rol .= "NULL, '$informacion_roles_id', NULL)";
            } elseif ($rol == "gerencia") {
                $sql_nombre_rol .= "NULL, NULL, '$informacion_roles_id')";
            }

            if ($conn->query($sql_nombre_rol) === TRUE) {
                echo json_encode(array("message" => "Usuario registrado con éxito."));
            } else {
                http_response_code(500); // Error de servidor interno
                echo json_encode(array("error" => "Error al insertar datos en la tabla nombre_rol: " . $conn->error));
            }
        } else {
            http_response_code(500); // Error de servidor interno
            echo json_encode(array("error" => "Error al insertar datos en la tabla informacion_roles: " . $conn->error));
        }
    } elseif ($action === 'login') {
        // Obtener los datos del inicio de sesión enviados por POST
        $identificacion = $data['identificacion'];
        $contrasena = $data['contrasena'];

        // Consulta para buscar la identificación y contraseña
        $sql = "SELECT id_rol, contraseña FROM informacion_roles WHERE identificacion = '$identificacion'";
        $result = $conn->query($sql);

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $stored_hashed_password = $row['contraseña'];

            if (password_verify($contrasena, $stored_hashed_password)) {
                // Inicio de sesión exitoso
                echo json_encode(array("message" => "Inicio de sesión exitoso."));
            } else {
                http_response_code(401); // No autorizado
                echo json_encode(array("error" => "Contraseña incorrecta."));
            }
        } else {
            http_response_code(401); // No autorizado
            echo json_encode(array("error" => "Usuario no encontrado, identificación incorrecta."));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("error" => "Acción no válida."));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("error" => "Falta el parámetro 'action'."));
}

$conn->close();

?>

