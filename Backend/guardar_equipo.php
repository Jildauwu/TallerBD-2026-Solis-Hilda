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

$inventario = trim($datos['inventario'] ?? '');
$descripcion = trim($datos['descripcion'] ?? '');
$marca = trim($datos['marca'] ?? '');
$modelo = trim($datos['modelo'] ?? '');
$categoria = $datos['categoria'] ?? '';
$condicion = trim($datos['condicion'] ?? '');

$categorias = [
    'Computadora' => 1,
    'Proyector' => 2,
    'Impresora' => 3
];

if ($inventario === '' || $descripcion === '' ||
    !isset($categorias[$categoria])) {
    http_response_code(400);
    echo json_encode(['error' => 'Completa los campos obligatorios']);
    exit;
}

try {
    $sql = "INSERT INTO equipos
            (num_inventario, descripcion, marca, modelo,
             id_categoria, estado, condicion)
            VALUES (?, ?, ?, ?, ?, 'disponible', ?)";

    $consulta = $conexion->prepare($sql);

    $consulta->execute([
        $inventario,
        $descripcion,
        $marca ?: null,
        $modelo ?: null,
        $categorias[$categoria],
        $condicion ?: null
    ]);

    http_response_code(201);
    echo json_encode([
        'mensaje' => 'Equipo registrado correctamente',
        'id_equipo' => $conexion->lastInsertId()
    ]);

} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        http_response_code(409);
        echo json_encode([
            'error' => 'El registro está duplicado o no cumple una restricción'
        ]);
    } else {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el equipo']);
    }
}