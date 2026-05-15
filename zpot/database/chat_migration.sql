-- =============================================================================
-- Zpot: Migración — Sistema de Chat
-- Ejecutar en MySQL (no toca tablas existentes)
-- =============================================================================

CREATE TABLE IF NOT EXISTS MENSAJE (
    ID_mensaje   INT          AUTO_INCREMENT PRIMARY KEY,
    ID_reserva   INT          NOT NULL,
    DNI_emisor   VARCHAR(20)  NOT NULL,
    Contenido    TEXT         NOT NULL,
    Leido        TINYINT(1)   NOT NULL DEFAULT 0,
    Fecha        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_msg_reserva
        FOREIGN KEY (ID_reserva) REFERENCES RESERVA(ID_reserva) ON DELETE CASCADE,
    CONSTRAINT fk_msg_usuario
        FOREIGN KEY (DNI_emisor) REFERENCES USUARIO(DNI) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
