
<?php

function validarNombreProducto(string $nombre): bool
{
    $nombre = trim($nombre);

    return mb_strlen($nombre, 'UTF-8') >= 3
        && mb_strlen($nombre, 'UTF-8') <= 50;
}

function validarCantidadProducto($cantidad): bool
{
    if (!is_int($cantidad) && !is_string($cantidad)) {
        return false;
    }

    return filter_var(
        $cantidad,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    ) !== false;
}

function validarEstadoProducto(string $estado): bool
{
    $estadosPermitidos = [
        'Disponible',
        'Agotado',
        'Descontinuado'
    ];

    return in_array($estado, $estadosPermitidos, true);
}

function validarAsignadoProducto(string $asignado): bool
{
    $asignado = trim($asignado);
    $longitud = mb_strlen($asignado, 'UTF-8');

    return $longitud >= 3 && $longitud <= 60;
}