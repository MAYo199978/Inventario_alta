
<?php

$conexion = null;
$codigoSalida = 0;

try {
    require __DIR__ . '/conexion_pruebas.php';
    require_once __DIR__ . '/../src/productos.php';

    echo "Conectado: Se conectó a la base de pruebas."
        . PHP_EOL;

    // Iniciar transacción para limpiar los datos al finalizar
    $conexion->beginTransaction();

    $sufijoPrueba = uniqid('', true);

    // ==========================================
    // PRUEBA DE INTEGRACIÓN 1:
    // Guardar y recuperar un producto válido
    // ==========================================

    $nombre = 'IT válida ' . $sufijoPrueba;
    $cantidad = 5;
    $estado = 'Disponible';
    $asignado = 'Ana López';

    $error = guardarProducto(
        $conexion,
        $nombre,
        $cantidad,
        $estado,
        $asignado
    );

    if ($error !== null) {
        throw new RuntimeException(
            'El producto válido fue rechazado.'
        );
    }

    $id = $conexion->lastInsertId();

    echo "Correcto: inserción realizada. ID: $id"
        . PHP_EOL;

    // Consultar el producto guardado
    $consulta = $conexion->prepare(
        'SELECT nombre, cantidad, estado, asignado
         FROM productos
         WHERE id = :id'
    );

    $consulta->execute(['id' => $id]);

    $producto = $consulta->fetch();

    if (
        $producto !== false
        && $producto['nombre'] === $nombre
        && (int) $producto['cantidad'] === $cantidad
        && $producto['estado'] === $estado
        && $producto['asignado'] === $asignado
    ) {
        echo "CORRECTO: nombre, cantidad, estado y asignado coinciden."
            . PHP_EOL;
    } else {
        echo "ERROR: producto no encontrado o datos diferentes."
            . PHP_EOL;

        $codigoSalida = 1;
    }

    // ==========================================
    // PRUEBA DE INTEGRACIÓN 2:
    // Rechazar un estado inválido
    // ==========================================

    $nombreInvalido = 'IT inválida ' . $sufijoPrueba;
    $estadoInvalido = 'Reservado';
    $asignadoValido = 'Ana López';

    $error = guardarProducto(
        $conexion,
        $nombreInvalido,
        $cantidad,
        $estadoInvalido,
        $asignadoValido
    );

    // Comprobar que no se haya creado el registro
    $consulta = $conexion->prepare(
        'SELECT COUNT(*)
         FROM productos
         WHERE nombre = :nombre'
    );

    $consulta->execute([
        'nombre' => $nombreInvalido
    ]);

    $registrosInvalidos = (int) $consulta->fetchColumn();

    if (
        $error === 'estado_invalido'
        && $registrosInvalidos === 0
    ) {
        echo "CORRECTO: estado inválido rechazado sin crear registro."
            . PHP_EOL;
    } else {
        echo "ERROR: estado inválido no fue rechazado correctamente."
            . PHP_EOL;

        $codigoSalida = 1;
    }

} catch (PDOException | RuntimeException $error) {

    echo "ERROR: " . $error->getMessage() . PHP_EOL;

    $codigoSalida = 1;

} finally {

    // Limpiar los registros creados durante las pruebas
    if (
        $conexion !== null
        && $conexion->inTransaction()
    ) {
        $conexion->rollBack();

        echo "Limpieza: inserciones de prueba eliminadas."
            . PHP_EOL;
    }
}

exit($codigoSalida);