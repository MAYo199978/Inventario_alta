<?php

require_once __DIR__ . '/../src/validaciones.php';

// PRUEBAS PARA EL NOMBRE DEL PRODUCTO
$casosNombre = [
    ["nombre válido: Teclado", "Teclado", true],
    ["nombre vacío", "", false],
    ["nombre con espacios", "  ", false],
    ["nombre válido: Mouse", "Mouse", true],
    ["nombre válido: Monitor", "Monitor", true]
];

// PRUEBAS PARA LA CANTIDAD DEL PRODUCTO
$casosCantidad = [
    ["cantidad válida: 10", "10", true],
    ["cantidad válida: 1", "1", true],
    ["cantidad vacía", "", false],
    ["cantidad con espacios", "  ", false],
    ["cantidad cero", "0", false],
    ["cantidad negativa", "-5", false],
    ["cantidad con letras", "abc", false],
    ["cantidad decimal", "2.5", false]
];


$casosEstado = [
    ["estado válido: Disponible", "Disponible", true],
    ["estado válido: Agotado", "Agotado", true],
    ["estado válido: Descontinuado", "Descontinuado", true],
    ["estado inválido: disponible", "disponible", false],
    ["estado inválido: Sin existencia", "Sin existencia", false]
];

$casosAsignado = [
    ["asignado válido mínimo: Ana", "Ana", true],
    ["asignado válido máximo: 60 caracteres", str_repeat('A', 60), true],
    ["asignado vacío", "", false],
    ["asignado debajo del mínimo", "Lu", false],
    ["asignado excede el máximo", str_repeat('A', 61), false]
];

$correctas = 0;
$fallidas = 0;


echo "\n----- PRUEBAS DE NOMBRE -----\n";

foreach ($casosNombre as [$descripcion, $entrada, $esperado]) {

    $obtenido = validarNombreProducto($entrada);

    if ($obtenido === $esperado) {
        echo "PASS - $descripcion\n";
        $correctas++;
    } else {
        echo "FAIL - $descripcion\n";
        echo " Esperado: " . var_export($esperado, true) . "\n";
        echo " Obtenido: " . var_export($obtenido, true) . "\n";
        $fallidas++;
    }
}


echo "\n----- PRUEBAS DE CANTIDAD -----\n";

foreach ($casosCantidad as [$descripcion, $entrada, $esperado]) {

    $obtenido = validarCantidadProducto($entrada);

    if ($obtenido === $esperado) {
        echo "PASS - $descripcion\n";
        $correctas++;
    } else {
        echo "FAIL - $descripcion\n";
        echo " Esperado: " . var_export($esperado, true) . "\n";
        echo " Obtenido: " . var_export($obtenido, true) . "\n";
        $fallidas++;
    }
}

echo "\n----- PRUEBAS DE ESTADO -----\n";

foreach ($casosEstado as [$descripcion, $entrada, $esperado]) {
    $obtenido = validarEstadoProducto($entrada);

    if ($obtenido === $esperado) {
        echo "PASS - $descripcion\n";
        $correctas++;
    } else {
        echo "FAIL - $descripcion\n";
        echo " Esperado: " . var_export($esperado, true) . "\n";
        echo " Obtenido: " . var_export($obtenido, true) . "\n";
        $fallidas++;
    }
}

echo "\n----- PRUEBAS DE ASIGNADO -----\n";

foreach ($casosAsignado as [$descripcion, $entrada, $esperado]) {
    $obtenido = validarAsignadoProducto($entrada);

    if ($obtenido === $esperado) {
        echo "PASS - $descripcion\n";
        $correctas++;
    } else {
        echo "FAIL - $descripcion\n";
        echo " Esperado: " . var_export($esperado, true) . "\n";
        echo " Obtenido: " . var_export($obtenido, true) . "\n";
        $fallidas++;
    }
}

// RESUMEN GENERAL
echo "\n------- RESUMEN -------\n";
echo "Total: " . (
    count($casosNombre)
    + count($casosCantidad)
    + count($casosEstado)
    + count($casosAsignado)
) . "\n";
echo "Correctas: $correctas\n";
echo "Fallidas: $fallidas\n";

exit($fallidas > 0 ? 1 : 0);
