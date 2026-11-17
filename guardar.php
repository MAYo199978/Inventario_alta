<?php

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/src/productos.php';
require_once __DIR__ . '/src/validaciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Obtener los datos del formulario
$nombre = $_POST['nombre'] ?? '';
$cantidad = $_POST['cantidad'] ?? '';
$estadoProducto = $_POST['estado'] ?? '';
$asignado = $_POST['asignado'] ?? '';

// Comprobar que los datos tengan el tipo esperado
if (
    !is_string($nombre)
    || !is_string($cantidad)
    || !is_string($estadoProducto)
    || !is_string($asignado)
) {
    header('Location: index.php?estado=incompleto');
    exit;
}

// Eliminar espacios innecesarios
$nombre = trim($nombre);
$cantidad = trim($cantidad);
$estadoProducto = trim($estadoProducto);
$asignado = trim($asignado);

// Verificar campos vacíos
if (
    $nombre === ''
    || $cantidad === ''
    || $estadoProducto === ''
    || $asignado === ''
) {
    header('Location: index.php?estado=incompleto');
    exit;
}

// Validar que el estado sea uno de los tres permitidos
if (!validarEstadoProducto($estadoProducto)) {
    header('Location: index.php?estado=estado_invalido');
    exit;
}

// Validar y guardar el producto
$error = guardarProducto(
    $conexion,
    $nombre,
    $cantidad,
    $estadoProducto,
    $asignado
);

if ($error !== null) {
    header('Location: index.php?estado=' . urlencode($error));
    exit;
}

// Regresar al formulario
header('Location: index.php?estado=guardado');
exit;