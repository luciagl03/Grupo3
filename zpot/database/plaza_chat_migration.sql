-- =============================================================================
-- Zpot: Migración — Chat por plaza (sin reserva)
-- Ejecutar DESPUÉS de chat_migration.sql
-- =============================================================================

-- Hacer ID_reserva nullable para permitir mensajes sin reserva
ALTER TABLE MENSAJE MODIFY COLUMN ID_reserva INT NULL;

-- Plaza a la que pertenece el chat (NULL cuando hay reserva)
ALTER TABLE MENSAJE ADD COLUMN ID_plaza INT NULL;

-- DNI del usuario no-propietario (identifica la conversación dentro de la plaza)
ALTER TABLE MENSAJE ADD COLUMN DNI_inquilino VARCHAR(20) NULL;

-- Foreign key para plaza
ALTER TABLE MENSAJE ADD CONSTRAINT fk_msg_plaza
    FOREIGN KEY (ID_plaza) REFERENCES PLAZA(ID_plaza) ON DELETE CASCADE;
