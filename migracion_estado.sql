ALTER TABLE productos
    ADD COLUMN estado ENUM('Disponible', 'Sin existencia')
    NOT NULL DEFAULT 'Disponible'
    AFTER precio;

UPDATE productos
SET estado = CASE
    WHEN cantidad > 0 THEN 'Disponible'
    ELSE 'Sin existencia'
END;

ALTER TABLE productos
    MODIFY estado ENUM('Disponible', 'Sin existencia') NOT NULL;
