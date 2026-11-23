
<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/conexion.php';

$datos = json_decode(file_get_contents('php://input'), true);

$id = filter_var(
    $datos['id_equipo'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de equipo inválido']);
    exit;
}

try {
    $conexion->beginTransaction();

    // Bloquear el equipo durante la operación
    $consulta = $conexion->prepare(
        "SELECT id_equipo, activo
         FROM equipos
         WHERE id_equipo = ?
         FOR UPDATE"
    );
    $consulta->execute([$id]);
    $equipo = $consulta->fetch();

    if (!$equipo || (int)$equipo['activo'] !== 1) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Equipo no encontrado o inactivo']);
        exit;
    }

    // Comprobar si el equipo tiene préstamos activos
    $consulta = $conexion->prepare(
        "SELECT COUNT(*)
         FROM prestamos
         WHERE id_equipo = ?
           AND estado = 'activo'"
    );
    $consulta->execute([$id]);

    if ((int)$consulta->fetchColumn() > 0) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            'error' => 'No se puede desactivar un equipo con préstamos activos'
        ]);
        exit;
    }

    // Desactivación lógica: conservar el registro
    $consulta = $conexion->prepare(
        "UPDATE equipos
        SET activo = 0
        WHERE id_equipo = ?
        AND activo = 1"
    );
    $consulta->execute([$id]);

    $conexion->commit();

    echo json_encode([
        'mensaje' => 'Equipo desactivado correctamente'
    ]);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudo desactivar el equipo'
    ]);
}
