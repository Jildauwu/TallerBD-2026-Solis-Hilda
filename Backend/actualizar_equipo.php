
<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

if (!is_array($datos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

$id = filter_var(
    $datos['id_equipo'] ?? null,
    FILTER_VALIDATE_INT
);

$descripcion = trim($datos['descripcion'] ?? '');
$marca = trim($datos['marca'] ?? '');
$modelo = trim($datos['modelo'] ?? '');
$condicion = trim($datos['condicion'] ?? '');

if (!$id || $id < 1 || $descripcion === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'Identificador o descripción inválidos'
    ]);
    exit;
}

try {
    $sql = "UPDATE equipos
            SET descripcion = ?,
                marca = ?,
                modelo = ?,
                condicion = ?
            WHERE id_equipo = ?
            AND activo = 1";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        $descripcion,
        $marca !== '' ? $marca : null,
        $modelo !== '' ? $modelo : null,
        $condicion !== '' ? $condicion : null,
        $id
    ]);

    echo json_encode([
        'mensaje' => 'Actualización procesada correctamente',
        'filas_modificadas' => $consulta->rowCount()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudo actualizar el equipo'
    ]);
}
