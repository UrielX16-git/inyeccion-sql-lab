<?php

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$response = ['success' => false, 'message' => ''];

switch ($action) {

    // ----- Reiniciar Base de Datos -----
    case 'reset_db':
        $conn = getRawConnection();
        $sql = file_get_contents(__DIR__ . '/setup.sql');
        if ($conn->multi_query($sql)) {
            // Consumir todos los resultados
            do {
                $result = $conn->store_result();
                if ($result) $result->free();
            } while ($conn->more_results() && $conn->next_result());

            $response['success'] = true;
            $response['message'] = 'Base de datos reiniciada correctamente. Se restauraron los usuarios por defecto.';
        } else {
            $response['message'] = 'Error al reiniciar: ' . $conn->error;
        }
        $conn->close();
        break;

    // ----- Vaciar Tabla de Usuarios -----
    case 'clear_users':
        $conn = getConnection();
        if ($conn->query("DELETE FROM usuarios")) {
            $response['success'] = true;
            $response['message'] = 'Tabla de usuarios vaciada. 0 registros restantes.';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $conn->close();
        break;

    // ----- Crear Usuario Manual -----
    case 'create_user':
        $usuario = $input['usuario'] ?? '';
        $contrasena = $input['contrasena'] ?? '';

        if (empty($usuario) || empty($contrasena)) {
            $response['message'] = 'Debes ingresar usuario y contraseña.';
            break;
        }

        $conn = getConnection();
        // Usa prepared statement aquí (admin es seguro)
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasena) VALUES (?, ?)");
        $stmt->bind_param("ss", $usuario, $contrasena);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Usuario '$usuario' creado exitosamente.";
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        break;

    // ----- Crear Usuario Aleatorio -----
    case 'random_user':
        $conn = getConnection();
        $nombres = ['carlos', 'maria', 'juan', 'ana', 'pedro', 'lucia', 'diego', 'sofia', 'andres', 'valentina', 'miguel', 'camila', 'javier', 'laura', 'fernando', 'paula', 'ricardo', 'daniela', 'gustavo', 'isabella'];
        $nombre = $nombres[array_rand($nombres)] . '_' . substr(bin2hex(random_bytes(2)), 0, 4);
        $pass = substr(bin2hex(random_bytes(4)), 0, 8);

        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasena) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $pass);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Usuario aleatorio creado: $nombre / $pass";
            $response['user'] = ['usuario' => $nombre, 'contrasena' => $pass];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        break;

    // ----- Crear Múltiples Usuarios Aleatorios -----
    case 'bulk_users':
        $count = intval($input['count'] ?? 0);
        if ($count < 1 || $count > 100) {
            $response['message'] = 'La cantidad debe ser entre 1 y 100.';
            break;
        }

        $conn = getConnection();
        $nombres = ['carlos', 'maria', 'juan', 'ana', 'pedro', 'lucia', 'diego', 'sofia', 'andres', 'valentina', 'miguel', 'camila', 'javier', 'laura', 'fernando', 'paula', 'ricardo', 'daniela', 'gustavo', 'isabella'];
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasena) VALUES (?, ?)");
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            $nombre = $nombres[array_rand($nombres)] . '_' . substr(bin2hex(random_bytes(2)), 0, 4);
            $pass = substr(bin2hex(random_bytes(4)), 0, 8);
            $stmt->bind_param("ss", $nombre, $pass);
            if ($stmt->execute()) {
                $created++;
            }
        }

        $stmt->close();
        $conn->close();

        $response['success'] = true;
        $response['message'] = "$created usuarios aleatorios creados exitosamente.";
        break;

    // ----- Listar Usuarios -----
    case 'list_users':
        $conn = getConnection();
        $result = $conn->query("SELECT * FROM usuarios ORDER BY id ASC");

        if ($result) {
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            $response['success'] = true;
            $response['users'] = $users;
            $response['count'] = count($users);
            $result->free();
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $conn->close();
        break;

    // ----- Contar Usuarios -----
    case 'count_users':
        $conn = getConnection();
        $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");

        if ($result) {
            $row = $result->fetch_assoc();
            $response['success'] = true;
            $response['count'] = intval($row['total']);
            $result->free();
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $conn->close();
        break;

    // ----- Ejecutar SQL Personalizado -----
    case 'custom_sql':
        $sql = $input['sql'] ?? '';
        if (empty($sql)) {
            $response['message'] = 'Debes ingresar una consulta SQL.';
            break;
        }

        $conn = getConnection();

        // Para SELECT queries, retorna resultados
        if ($conn->multi_query($sql)) {
            $results = [];
            $affected = 0;

            do {
                $result = $conn->store_result();
                if ($result) {
                    $rows = [];
                    while ($row = $result->fetch_assoc()) {
                        $rows[] = $row;
                    }
                    $results[] = $rows;
                    $result->free();
                } else {
                    $affected += $conn->affected_rows;
                }
            } while ($conn->more_results() && $conn->next_result());

            $response['success'] = true;
            $response['results'] = $results;
            $response['affected_rows'] = $affected;
            $response['message'] = !empty($results) 
                ? count($results[0]) . ' filas retornadas.'
                : "$affected filas afectadas.";
        } else {
            $response['message'] = 'Error SQL: ' . $conn->error;
        }

        $conn->close();
        break;

    default:
        $response['message'] = "Acción desconocida: '$action'";
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
