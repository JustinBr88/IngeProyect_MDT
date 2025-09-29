-- Agregar campos para sistema de descarte
ALTER TABLE inventario 
ADD COLUMN estado_descarte ENUM('activo', 'descarte') DEFAULT 'activo',
ADD COLUMN fecha_descarte DATETIME NULL,
ADD COLUMN observaciones_descarte TEXT NULL,
ADD COLUMN tecnico_descarte VARCHAR(100) NULL;

-- Agregar índice para mejor rendimiento
CREATE INDEX idx_estado_descarte ON inventario(estado_descarte);

-- Actualizar equipos existentes
UPDATE inventario SET estado_descarte = 'activo' WHERE estado_descarte IS NULL;
