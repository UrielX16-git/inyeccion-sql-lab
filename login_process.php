<?php
// =====================================================
// SQL Injection Lab — Procesamiento de Login
// INTENCIONALMENTE VULNERABLE — Solo para demostración
// =====================================================

header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

// Solo acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Leer datos del formulario
$input = json_decode(file_get_contents('php://input'), true);
$usuario = $input['usuario'] ?? '';
$contrasena = $input['contrasena'] ?? '';

// Validación básica
if (empty($usuario) && empty($contrasena)) {
    echo json_encode([
        'success' => false,
        'message' => 'Por favor, ingresa usuario y contraseña.',
        'query' => '(ninguna consulta ejecutada)'
    ]);
    exit;
}

try {
    $conn = getConnection();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos. ¿Has ejecutado el setup?',
        'query' => '(error de conexión)'
    ]);
    exit;
}

// =====================================================
// CONSULTA VULNERABLE — Concatenación directa de strings
// En producción se usarían prepared statements
// =====================================================
$query = "SELECT * FROM usuarios WHERE usuario = '$usuario' AND contrasena = '$contrasena'";

$response = [
    'success' => false,
    'message' => '',
    'query' => $query,
    'user' => null,
    'all_results' => []
];

// Usamos multi_query para permitir stacked queries (ej: DELETE)
if ($conn->multi_query($query)) {
    $first_result = $conn->store_result();

    if ($first_result && $first_result->num_rows > 0) {
        // Recopilar todos los resultados (útil para UNION attacks)
        $all_rows = [];
        while ($row = $first_result->fetch_assoc()) {
            $all_rows[] = $row;
        }

        $response['success'] = true;
        $response['user'] = $all_rows[0]['usuario'];
        $response['message'] = '¡Bienvenido, ' . htmlspecialchars($all_rows[0]['usuario']) . '!';
        $response['all_results'] = $all_rows;

        $first_result->free();
    } else {
        $response['message'] = 'Credenciales incorrectas. Usuario o contraseña no válidos.';
        if ($first_result) {
            $first_result->free();
        }
    }

    // Consumir resultados restantes (necesario para multi_query)
    while ($conn->more_results()) {
        $conn->next_result();
        $extra = $conn->store_result();
        if ($extra) {
            $extra->free();
        }
    }
} else {
    $response['message'] = 'Error en la consulta SQL: ' . $conn->error;
}

$conn->close();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
