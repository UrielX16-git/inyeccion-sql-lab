<?php
// =====================================================
// SQL Injection Lab — Configuración de Base de Datos
// =====================================================

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'sqli_lab';

/**
 * Crea y retorna una conexión mysqli a la base de datos.
 * Usa mysqli (no PDO) intencionalmente para permitir multi_query().
 */
function getConnection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'message' => 'Error de conexión: ' . $conn->connect_error
        ]));
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}

/**
 * Conexión sin seleccionar base de datos (para crear la BD).
 */
function getRawConnection() {
    global $DB_HOST, $DB_USER, $DB_PASS;
    
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
    
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'message' => 'Error de conexión: ' . $conn->connect_error
        ]));
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
