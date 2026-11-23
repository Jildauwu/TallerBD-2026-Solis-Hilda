
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
            id_departamento,
            nombre
        FROM departamentos
        ORDER BY nombre ASC
    ";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    $departamentos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'departamentos' => $departamentos
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudieron consultar los departamentos'
    ]);
}
