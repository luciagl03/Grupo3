-- =============================================================================
-- Zpot: Migración del campo Foto para soportar imágenes Base64
-- =============================================================================
-- 
-- PROPÓSITO:
-- Cambiar el campo Foto de VARCHAR(255) a MEDIUMTEXT para permitir
-- almacenar imágenes codificadas en Base64.
--
-- RAZÓN:
-- - VARCHAR(255) solo permite 255 caracteres
-- - Una imagen Base64 de 100KB ocupa ~133,000 caracteres
-- - MEDIUMTEXT permite hasta 16MB de texto (~12MB de imagen original)
--
-- EJECUCIÓN:
-- mysql -u root -p zpot_bd < database/migrate_foto_field.sql
--
-- O desde phpMyAdmin/MySQL Workbench:
-- Copiar y ejecutar el comando ALTER TABLE a continuación
-- =============================================================================

USE zpot_bd;

-- Cambiar el tipo de dato del campo Foto en la tabla PLAZA
ALTER TABLE PLAZA MODIFY COLUMN Foto MEDIUMTEXT;

-- Cambiar el tipo de dato del campo Foto en la tabla USUARIO (por consistencia)
ALTER TABLE USUARIO MODIFY COLUMN Foto MEDIUMTEXT;

-- Verificar los cambios
DESCRIBE PLAZA;
DESCRIBE USUARIO;

-- =============================================================================
-- NOTAS:
-- - Esta migración es compatible con datos existentes (URLs seguirán funcionando)
-- - MEDIUMTEXT soporta tanto URLs como imágenes Base64
-- - No se pierden datos durante la migración
-- - El cambio es instantáneo en tablas pequeñas
-- =============================================================================
