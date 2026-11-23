
<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/conexion.php';

try {
    $sql = "SELECT
                e.id_equipo,
                e.num_inventario,
                e.descripcion,
                e.marca,
                e.modelo,
                c.nombre AS categoria,
                e.estado,
                e.condicion
            FROM equipos AS e
            INNER JOIN categorias AS c
                ON e.id_categoria = c.id_categoria
            WHERE e.activo = 1
            ORDER BY e.id_equipo DESC";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    $equipos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'equipos' => $equipos
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudieron consultar los equipos'
    ]);
}
