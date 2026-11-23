
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

$idEquipo = filter_var(
    $datos['id_equipo'] ?? null,
    FILTER_VALIDATE_INT
);

$idSolicitante = filter_var(
    $datos['id_solicitante'] ?? null,
    FILTER_VALIDATE_INT
);

$idUsuario = filter_var(
    $datos['id_usuario'] ?? null,
    FILTER_VALIDATE_INT
);

$fechaPrestamo = $datos['fecha_prestamo'] ?? '';
$fechaPrevista = $datos['fecha_prevista'] ?? '';

if (!$idEquipo || !$idSolicitante || !$idUsuario) {
    http_response_code(400);
    echo json_encode(['error' => 'Selecciona equipo, solicitante y usuario']);
    exit;
}

function validarFecha($valor) {
    if (!is_string($valor)) {
        return false;
    }

    $fecha = DateTimeImmutable::createFromFormat(
        '!Y-m-d\TH:i',
        $valor
    );

    return $fecha &&
        $fecha->format('Y-m-d\TH:i') === $valor
        ? $fecha
        : false;
}

$inicio = validarFecha($fechaPrestamo);
$fin = validarFecha($fechaPrevista);

if (!$inicio || !$fin || $fin < $inicio) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Las fechas son inválidas o la devolución es anterior al préstamo'
    ]);
    exit;
}

try {
    $conexion->beginTransaction();

    // Bloquear el equipo durante el registro
    $consulta = $conexion->prepare(
        "SELECT estado, activo
         FROM equipos
         WHERE id_equipo = ?
         FOR UPDATE"
    );
    $consulta->execute([$idEquipo]);

    $equipo = $consulta->fetch();

    if (!$equipo ||
        (int)$equipo['activo'] !== 1 ||
        $equipo['estado'] !== 'disponible') {

        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            'error' => 'El equipo no está disponible para préstamo'
        ]);
        exit;
    }

    // Impedir préstamos activos duplicados
    $consulta = $conexion->prepare(
        "SELECT COUNT(*)
         FROM prestamos
         WHERE id_equipo = ?
           AND estado = 'activo'"
    );
    $consulta->execute([$idEquipo]);

    if ((int)$consulta->fetchColumn() > 0) {
        $conexion->rollBack();
        http_response_code(409);
        echo json_encode([
            'error' => 'El equipo ya tiene un préstamo activo'
        ]);
        exit;
    }

    // Registrar el préstamo
    $consulta = $conexion->prepare(
        "INSERT INTO prestamos
         (id_equipo, id_solicitante, id_usuario,
          fecha_prestamo, fecha_prevista, estado)
         VALUES (?, ?, ?, ?, ?, 'activo')"
    );

    $consulta->execute([
        $idEquipo,
        $idSolicitante,
        $idUsuario,
        $inicio->format('Y-m-d H:i:s'),
        $fin->format('Y-m-d H:i:s')
    ]);

    $idPrestamo = $conexion->lastInsertId();

    // Actualizar disponibilidad del equipo
    $consulta = $conexion->prepare(
        "UPDATE equipos
         SET estado = 'prestado'
         WHERE id_equipo = ?
           AND estado = 'disponible'
           AND activo = 1"
    );
    $consulta->execute([$idEquipo]);

    if ($consulta->rowCount() !== 1) {
        throw new RuntimeException(
            'No se pudo actualizar la disponibilidad'
        );
    }

    $conexion->commit();

    http_response_code(201);
    echo json_encode([
        'mensaje' => 'Préstamo registrado correctamente',
        'id_prestamo' => $idPrestamo
    ]);

} catch (Throwable $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudo registrar el préstamo'
    ]);
}
