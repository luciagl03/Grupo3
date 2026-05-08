-- =============================================================================
-- Zpot: Migración — Tabla NOTIFICACION
-- Ejecutar en el MySQL de InfinityFree (no toca tablas existentes)
-- =============================================================================

CREATE TABLE IF NOT EXISTS NOTIFICACION (
    ID_notif  INT          AUTO_INCREMENT PRIMARY KEY,
    DNI       VARCHAR(20)  NOT NULL,
    Tipo      VARCHAR(40)  NOT NULL,
    Titulo    VARCHAR(150) NOT NULL,
    Mensaje   TEXT         NOT NULL,
    Leida     TINYINT(1)   NOT NULL DEFAULT 0,
    Fecha     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ID_ref    INT          NULL,
    CONSTRAINT fk_notif_usuario
        FOREIGN KEY (DNI) REFERENCES USUARIO(DNI) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de prueba para el usuario demo
INSERT INTO NOTIFICACION (DNI, Tipo, Titulo, Mensaje, Leida, Fecha) VALUES
('12345678A', 'reserva_confirmada', '¡Reserva confirmada!',       'Tu reserva en Calle Marqués de Larios 5 el 10/03/2025 ha sido confirmada.',  1, '2025-03-10 13:05:00'),
('12345678A', 'nueva_resena',       'Nueva reseña en tu plaza',   'Laura M. dejó 5★ en Calle Marqués de Larios 5.',                              1, '2025-03-10 13:10:00'),
('12345678A', 'reserva_confirmada', '¡Reserva confirmada!',       'Tu reserva en Plaza de la Constitución 3 el 12/03/2025 ha sido confirmada.',   0, '2025-03-12 12:05:00'),
('12345678A', 'nueva_resena',       'Nueva reseña en tu plaza',   'Ana G. dejó 5★ en Plaza de la Constitución 3.',                               0, '2025-03-12 12:15:00'),
('12345678A', 'reserva_cancelada',  'Reserva cancelada',          'Tu reserva en Calle Granada 42 el 14/03/2025 ha sido cancelada.',              0, '2025-03-14 15:00:00');

-- Para forzar noti de confirmación de pago EN EL DNI SE PONE EL DNI D LA CUENTA
INSERT INTO NOTIFICACION (DNI, Tipo, Titulo, Mensaje, Leida) 
VALUES ('12345678A', 'reserva_confirmada', '¡Reserva confirmada!', 'Tu reserva en Calle Larios ha sido confirmada.', 0);

-- Para forzar noti de que quedan 15 min de la reserva EN LA HORA SE PONE UNA CERCANA A LA ACTUAL Y EL DNI DE LA CUENTA
INSERT INTO RESERVA (DNI, ID_plaza, Precio, Duracion, Hora_entrada, Hora_salida, Fecha, Estado)
VALUES ('12345678A', 1, 4.50, 1, 
    SUBTIME(CURTIME(), '00:40:00'),
    ADDTIME(CURTIME(), '00:20:00'),
    CURDATE(), 'confirmada');