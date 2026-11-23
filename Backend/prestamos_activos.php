
<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/conexion.php';

try {
    $sql = "
        SELECT
            p.id_prestamo,
            p.id_equipo,
            e.num_inventario,
            e.descripcion AS equipo,
            s.nombre AS solicitante,
            p.fecha_prestamo,
            p.fecha_prevista
        FROM prestamos AS p
        INNER JOIN equipos AS e
            ON p.id_equipo = e.id_equipo
        INNER JOIN solicitantes AS s
            ON p.id_solicitante = s.id_solicitante
        WHERE p.estado = 'activo'
        ORDER BY p.fecha_prestamo ASC
    ";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    $prestamos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'prestamos' => $prestamos
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudieron consultar los préstamos activos'
    ]);
}
