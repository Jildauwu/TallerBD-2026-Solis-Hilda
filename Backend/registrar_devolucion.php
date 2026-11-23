
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

    $idPrestamo = filter_var(
        $datos['id_prestamo'] ?? null,
        FILTER_VALIDATE_INT
    );

    $fechaTexto = $datos['fecha_devolucion'] ?? '';
    $condicion = trim($datos['condicion_retorno'] ?? '');
    $observaciones = trim($datos['observaciones'] ?? '');

    $fecha = is_string($fechaTexto)
        ? DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $fechaTexto)
        : false;

    if (!$idPrestamo || $idPrestamo < 1 ||
        !$fecha ||
        $fecha->format('Y-m-d\TH:i') !== $fechaTexto ||
        $condicion === '' ||
        mb_strlen($condicion) > 100) {

        http_response_code(400);
        echo json_encode(['error' => 'Datos de devolución inválidos']);
        exit;
    }

    try {
        $conexion->beginTransaction();

        // Obtener el equipo relacionado con el préstamo
        $consulta = $conexion->prepare(
            "SELECT id_equipo
            FROM prestamos
            WHERE id_prestamo = ?"
        );
        $consulta->execute([$idPrestamo]);
        $idEquipo = $consulta->fetchColumn();

        if (!$idEquipo) {
            $conexion->rollBack();
            http_response_code(404);
            echo json_encode(['error' => 'Préstamo no encontrado']);
            exit;
        }

        // Bloquear primero el equipo, igual que al registrar préstamos
        $consulta = $conexion->prepare(
            "SELECT id_equipo, estado, activo
            FROM equipos
            WHERE id_equipo = ?
            FOR UPDATE"
        );
        $consulta->execute([$idEquipo]);
        $equipo = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$equipo || $equipo['estado'] !== 'prestado') {
            $conexion->rollBack();
            http_response_code(409);
            echo json_encode(['error' => 'El equipo no está marcado como prestado']);
            exit;
        }

        // Bloquear y verificar el préstamo
        $consulta = $conexion->prepare(
            "SELECT estado, fecha_prestamo
            FROM prestamos
            WHERE id_prestamo = ?
            AND id_equipo = ?
            FOR UPDATE"
        );
        $consulta->execute([$idPrestamo, $idEquipo]);
        $prestamo = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$prestamo || $prestamo['estado'] !== 'activo') {
            $conexion->rollBack();
            http_response_code(409);
            echo json_encode(['error' => 'El préstamo ya no está activo']);
            exit;
        }

        if ($fecha->getTimestamp() <
            (new DateTimeImmutable($prestamo['fecha_prestamo']))->getTimestamp()) {
            $conexion->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'La devolución no puede ser anterior al préstamo'
            ]);
            exit;
        }

        // Registrar la devolución
        $consulta = $conexion->prepare(
            "INSERT INTO devoluciones
            (id_prestamo, fecha_devolucion,
            condicion_retorno, observaciones)
            VALUES (?, ?, ?, ?)"
        );

        $consulta->execute([
            $idPrestamo,
            $fecha->format('Y-m-d H:i:s'),
            $condicion,
            $observaciones !== '' ? $observaciones : null
        ]);

        // Marcar el préstamo como devuelto
        $consulta = $conexion->prepare(
            "UPDATE prestamos
            SET estado = 'devuelto'
            WHERE id_prestamo = ?
            AND estado = 'activo'"
        );
        $consulta->execute([$idPrestamo]);

        if ($consulta->rowCount() !== 1) {
            throw new RuntimeException('No se pudo actualizar el préstamo');
        }

        // Devolver el equipo a disponible
        $consulta = $conexion->prepare(
            "UPDATE equipos
            SET estado = 'disponible',
                condicion = ?
            WHERE id_equipo = ?
            AND estado = 'prestado'
            AND activo = 1"
        );
        $consulta->execute([$condicion, $idEquipo]);

        if ($consulta->rowCount() !== 1) {
            throw new RuntimeException('No se pudo actualizar el equipo');
        }

        $conexion->commit();

        http_response_code(201);
        echo json_encode([
            'mensaje' => 'Devolución registrada correctamente'
        ], JSON_UNESCAPED_UNICODE);

    } catch (Throwable $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }

        error_log($e->getMessage());

        http_response_code(500);
        echo json_encode([
            'error' => 'No se pudo registrar la devolución'
        ]);
    }
