
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
            u.nombre AS responsable,
            p.fecha_prestamo,
            p.fecha_prevista,
            p.estado
        FROM prestamos AS p
        INNER JOIN equipos AS e
            ON p.id_equipo = e.id_equipo
        INNER JOIN solicitantes AS s
            ON p.id_solicitante = s.id_solicitante
        INNER JOIN usuarios AS u
            ON p.id_usuario = u.id_usuario
        ORDER BY p.fecha_prestamo DESC, p.id_prestamo DESC
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
        'error' => 'No se pudieron consultar los préstamos'
    ]);
}
