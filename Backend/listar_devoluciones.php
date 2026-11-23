
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
            d.id_devolucion,
            d.id_prestamo,
            e.num_inventario,
            e.descripcion AS equipo,
            d.fecha_devolucion,
            d.condicion_retorno,
            d.observaciones
        FROM devoluciones AS d
        INNER JOIN prestamos AS p
            ON d.id_prestamo = p.id_prestamo
        INNER JOIN equipos AS e
            ON p.id_equipo = e.id_equipo
        ORDER BY d.fecha_devolucion DESC,
                d.id_devolucion DESC
    ";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    $devoluciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'devoluciones' => $devoluciones
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudieron consultar las devoluciones'
    ]);
}
