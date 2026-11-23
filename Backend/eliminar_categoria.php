
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
    $datos['id_categoria'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Categoría inválida']);
    exit;
}

try {
    $conexion->beginTransaction();

    // Bloquear la categoría mientras se verifica
    $consulta = $conexion->prepare(
        "SELECT id_categoria FROM categorias
        WHERE id_categoria = ? FOR UPDATE"
    );
    $consulta->execute([$id]);

    if (!$consulta->fetch()) {
        $conexion->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Categoría no encontrada']);
        exit;
    }

    // Comprobar si tiene equipos relacionados
    $consulta = $conexion->prepare(
        "SELECT COUNT(*) FROM equipos WHERE id_categoria = ?"
    );
    $consulta->execute([$id]);

    if ((int)$consulta->fetchColumn() > 0) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            'error' => 'No se puede eliminar una categoría utilizada por equipos'
        ]);
        exit;
    }

    // DELETE real y parametrizado
    $consulta = $conexion->prepare(
        "DELETE FROM categorias WHERE id_categoria = ?"
    );
    $consulta->execute([$id]);

    $conexion->commit();

    echo json_encode([
        'mensaje' => 'Categoría eliminada correctamente'
    ]);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudo eliminar la categoría'
    ]);
}
