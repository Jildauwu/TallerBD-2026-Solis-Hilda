
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

$nombre = $datos['nombre'] ?? '';
$numero = $datos['numero'] ?? '';
$idDepartamento = filter_var(
    $datos['id_departamento'] ?? null,
    FILTER_VALIDATE_INT
);

// Validar tipos de datos
if (!is_string($nombre) || !is_string($numero)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nombre o número inválido']);
    exit;
}

$nombre = trim($nombre);
$numero = trim($numero);

// Validar campos obligatorios
if (
    $nombre === '' ||
    $numero === '' ||
    !$idDepartamento ||
    $idDepartamento < 1 ||
    mb_strlen($nombre) > 150 ||
    mb_strlen($numero) > 50
) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Verifica los datos del solicitante'
    ]);
    exit;
}

try {
    // Verificar que el departamento exista
    $consulta = $conexion->prepare(
        "SELECT id_departamento
         FROM departamentos
         WHERE id_departamento = ?"
    );

    $consulta->execute([$idDepartamento]);

    if (!$consulta->fetchColumn()) {
        http_response_code(400);
        echo json_encode([
            'error' => 'El departamento seleccionado no existe'
        ]);
        exit;
    }

    // Registrar solicitante
    $sql = "
        INSERT INTO solicitantes
            (nombre, num_institucional, id_departamento)
        VALUES
            (?, ?, ?)
    ";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        $nombre,
        $numero,
        $idDepartamento
    ]);

    http_response_code(201);

    echo json_encode([
        'mensaje' => 'Solicitante registrado correctamente',
        'id_solicitante' => $conexion->lastInsertId()
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    // Evitar números institucionales duplicados
    if ($e->getCode() === '23000' &&
        ($e->errorInfo[1] ?? null) == 1062) {

        http_response_code(409);

        echo json_encode([
            'error' => 'El número institucional ya está registrado'
        ]);

    } else {
        error_log($e->getMessage());

        http_response_code(500);

        echo json_encode([
            'error' => 'No se pudo registrar el solicitante'
        ]);
    }
}
