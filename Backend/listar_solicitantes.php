
<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Método no permitido'
    ]);
    exit;
}

require_once __DIR__ . '/conexion.php';

try {
    $sql = "
        SELECT
            s.id_solicitante,
            s.nombre,
            s.num_institucional,
            d.nombre AS departamento
        FROM solicitantes AS s
        INNER JOIN departamentos AS d
            ON s.id_departamento = d.id_departamento
        ORDER BY s.id_solicitante DESC
    ";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    $solicitantes = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'solicitantes' => $solicitantes
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudieron consultar los solicitantes'
    ]);
}
