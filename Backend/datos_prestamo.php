
    <?php
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        exit;
    }

    require_once __DIR__ . '/conexion.php';

    try {
        // Equipos activos y disponibles, sin préstamos activos
        $sqlEquipos = "
            SELECT e.id_equipo, e.num_inventario,
                e.descripcion
            FROM equipos e
            WHERE e.activo = 1
            AND e.estado = 'disponible'
            AND NOT EXISTS (
                SELECT 1
                FROM prestamos p
                WHERE p.id_equipo = e.id_equipo
                    AND p.estado = 'activo'
            )
            ORDER BY e.num_inventario
        ";

        $equipos = $conexion
            ->query($sqlEquipos)
            ->fetchAll(PDO::FETCH_ASSOC);

        // Solicitantes registrados
        $solicitantes = $conexion
            ->query(
                "SELECT id_solicitante, nombre
                FROM solicitantes
                ORDER BY nombre"
            )
            ->fetchAll(PDO::FETCH_ASSOC);

        // Usuarios registrados (sin mostrar contraseñas)
        $usuarios = $conexion
            ->query(
                "SELECT id_usuario, nombre
                FROM usuarios
                WHERE rol = 'administrador'
                ORDER BY nombre"
            )
            ->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'equipos' => $equipos,
            'solicitantes' => $solicitantes,
            'usuarios' => $usuarios
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        error_log($e->getMessage());

        http_response_code(500);
        echo json_encode([
            'error' => 'No se pudieron cargar los datos'
        ]);
    }
