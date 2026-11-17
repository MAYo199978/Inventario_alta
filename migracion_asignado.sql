ALTER TABLE productos
    ADD COLUMN asignado VARCHAR(60) NULL
    AFTER estado;

UPDATE productos
SET asignado = 'Sin asignar'
WHERE asignado IS NULL;

ALTER TABLE productos
    MODIFY asignado VARCHAR(60) NOT NULL;
