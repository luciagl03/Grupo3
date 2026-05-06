-- =============================================================================
-- Zpot: base de datos COMPLETA (limpia + reseñas)
-- Uso: mysql -u root -p zpot_bd < zpot_bd_completo.sql
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar tablas en orden inverso a las FK
DROP TABLE IF EXISTS RESENA;
DROP TABLE IF EXISTS RESERVA;
DROP TABLE IF EXISTS PLAZA;
DROP TABLE IF EXISTS USUARIO;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- TABLAS
-- =============================================================================

CREATE TABLE USUARIO (
    DNI                   VARCHAR(20)  PRIMARY KEY,
    Nombre                VARCHAR(100) NOT NULL,
    Apellidos             VARCHAR(150) NOT NULL,
    Direccion             VARCHAR(200),
    Foto                  VARCHAR(255),
    Telefono              VARCHAR(20),
    Email                 VARCHAR(100) UNIQUE,
    Contrasena_encriptada VARCHAR(255) NOT NULL,
    Token_pago            VARCHAR(255),
    token                 VARCHAR(64),
    confirmado            TINYINT(1)   NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE PLAZA (
    ID_plaza    INT           AUTO_INCREMENT PRIMARY KEY,
    DNI         VARCHAR(20),
    Direccion   VARCHAR(200),
    Foto        MEDIUMTEXT,
    Ancho       DECIMAL(5,2),
    Largo       DECIMAL(5,2),
    Descripcion TEXT,
    Escritura   VARCHAR(255),
    Precio      DECIMAL(10,2) NULL,
    Ubicacion   VARCHAR(20)   NULL,
    Extras      VARCHAR(200)  NULL,
    Lat         DECIMAL(10,7) NULL,
    Lng         DECIMAL(10,7) NULL,
    CONSTRAINT fk_plaza_usuario
        FOREIGN KEY (DNI) REFERENCES USUARIO(DNI) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE RESERVA (
    ID_reserva   INT           AUTO_INCREMENT PRIMARY KEY,
    DNI          VARCHAR(20),
    ID_plaza     INT,
    Precio       DECIMAL(10,2),
    Duracion     INT,
    Hora_entrada TIME,
    Hora_salida  TIME,
    Fecha        DATE,
    Estado       ENUM('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
    CONSTRAINT fk_reserva_usuario
        FOREIGN KEY (DNI)      REFERENCES USUARIO(DNI)    ON DELETE CASCADE,
    CONSTRAINT fk_reserva_plaza
        FOREIGN KEY (ID_plaza) REFERENCES PLAZA(ID_plaza) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE RESENA (
    ID_resena  INT          AUTO_INCREMENT PRIMARY KEY,
    ID_plaza   INT          NOT NULL,
    DNI        VARCHAR(20)  NOT NULL,
    Puntuacion TINYINT      NOT NULL CHECK (Puntuacion BETWEEN 1 AND 5),
    Comentario TEXT,
    Fecha      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Un usuario solo puede dejar UNA reseña por plaza
    UNIQUE KEY uq_resena_usuario_plaza (ID_plaza, DNI),

    CONSTRAINT fk_resena_plaza
        FOREIGN KEY (ID_plaza) REFERENCES PLAZA(ID_plaza) ON DELETE CASCADE,
    CONSTRAINT fk_resena_usuario
        FOREIGN KEY (DNI) REFERENCES USUARIO(DNI) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- DATOS DE PRUEBA
-- =============================================================================

-- Usuario propietario de las plazas demo
-- Contraseña: password
INSERT INTO USUARIO (DNI, Nombre, Apellidos, Direccion, Telefono, Email, Contrasena_encriptada, confirmado) VALUES
('12345678A', 'Demo',   'Propietario',      'Calle Larios 1, Málaga',       '952123456', 'demo@zpot.local',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Usuarios que dejarán reseñas (contraseña: password en todos)
INSERT INTO USUARIO (DNI, Nombre, Apellidos, Direccion, Telefono, Email, Contrasena_encriptada, confirmado) VALUES
('87654321B', 'Laura',  'Martínez Ruiz',    'Av. Andalucía 10, Málaga',     '952000001', 'laura@zpot.local',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('11223344C', 'Carlos', 'López Sánchez',    'Calle Sol 5, Málaga',          '952000002', 'carlos@zpot.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('55667788D', 'Ana',    'García Fernández', 'Plaza Mayor 3, Málaga',        '952000003', 'ana@zpot.local',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('99887766E', 'Miguel', 'Torres Vega',      'Calle Real 22, Málaga',        '952000004', 'miguel@zpot.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('44332211F', 'Sofía',  'Ruiz Moreno',      'Av. Libertad 7, Málaga',       '952000005', 'sofia@zpot.local',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- 8 plazas en el centro de Málaga
INSERT INTO PLAZA (DNI, Direccion, Foto, Ancho, Largo, Descripcion, Precio, Ubicacion, Extras, Lat, Lng) VALUES
('12345678A', 'Calle Marqués de Larios 5, Málaga',  'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800', 2.50, 5.00, 'Plaza cubierta en el corazón de Málaga. A 2 min de la Catedral. Acceso 24h con llave. Cámara de seguridad.', 4.50, 'cubierto',  '24h,vigilado',  36.7210400, -4.4213100),
('12345678A', 'Plaza de la Constitución 3, Málaga',  'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', 2.80, 5.20, 'Garaje privado en pleno centro. Ideal para turistas. Cerca de museos y restaurantes.',                   5.00, 'garaje',    'vigilado',      36.7208600, -4.4203800),
('12345678A', 'Calle Granada 42, Málaga',            'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800', 2.40, 4.80, 'Plaza en edificio residencial con portero. Seguro y tranquilo. Zona peatonal cercana.',                    4.00, 'garaje',    NULL,            36.7226700, -4.4195000),
('12345678A', 'Pasaje Chinitas 8, Málaga',           'https://picsum.photos/800/600?random=park1',                          2.60, 5.10, 'Aparcamiento exterior en callejón. Buen precio. A 5 min del Teatro Romano.',                               3.50, 'exterior',  NULL,            36.7204900, -4.4209900),
('12345678A', 'Alameda Principal 22, Málaga',        'https://picsum.photos/800/600?random=park2',                          2.70, 5.30, 'Plaza cubierta en sótano. Fácil acceso. Cerca del puerto y Paseo del Parque.',                             4.80, 'cubierto',  'ev,24h',        36.7197100, -4.4238300),
('12345678A', 'Calle Carretería 67, Málaga',         'https://picsum.photos/800/600?random=park3',                          2.50, 4.90, 'Garaje individual en zona residencial. Silencioso. Ideal para estancias largas.',                           3.80, 'garaje',    '24h',           36.7232600, -4.4231600),
('12345678A', 'Plaza del Siglo 1, Málaga',           'https://picsum.photos/800/600?random=park4',                          2.55, 5.00, 'Plaza en edificio histórico. Centro neurálgico. Perfecta para visitar el casco antiguo.',                  5.20, 'exterior',  'vigilado',      36.7210600, -4.4198100),
('12345678A', 'Calle Nueva 15, Málaga',              'https://picsum.photos/800/600?random=park5',                          2.45, 5.15, 'Aparcamiento exterior vigilado. Muy céntrico. A un paso de la calle Larios.',                              4.20, 'exterior',  'ev,vigilado',   36.7208100, -4.4219700);

-- Reservas confirmadas (necesarias para que los usuarios puedan reseñar)
INSERT INTO RESERVA (DNI, ID_plaza, Precio, Duracion, Hora_entrada, Hora_salida, Fecha, Estado) VALUES
('87654321B', 1,  9.00, 2, '10:00:00', '12:00:00', '2025-03-10', 'confirmada'),
('11223344C', 1,  4.50, 1, '14:00:00', '15:00:00', '2025-03-11', 'confirmada'),
('55667788D', 2, 10.00, 2, '09:00:00', '11:00:00', '2025-03-12', 'confirmada'),
('99887766E', 2,  5.00, 1, '16:00:00', '17:00:00', '2025-03-13', 'confirmada'),
('44332211F', 3,  8.00, 2, '11:00:00', '13:00:00', '2025-03-14', 'confirmada'),
('87654321B', 3,  4.00, 1, '15:00:00', '16:00:00', '2025-03-15', 'confirmada'),
('11223344C', 4,  7.00, 2, '08:00:00', '10:00:00', '2025-03-16', 'confirmada'),
('55667788D', 5,  9.60, 2, '12:00:00', '14:00:00', '2025-03-17', 'confirmada'),
('99887766E', 6,  7.60, 2, '10:00:00', '12:00:00', '2025-03-18', 'confirmada'),
('44332211F', 7, 10.40, 2, '13:00:00', '15:00:00', '2025-03-19', 'confirmada'),
('87654321B', 8,  8.40, 2, '09:00:00', '11:00:00', '2025-03-20', 'confirmada');

-- Reseñas de prueba
INSERT INTO RESENA (ID_plaza, DNI, Puntuacion, Comentario, Fecha) VALUES
(1, '87654321B', 5, 'Perfecta ubicación, muy céntrica y limpia. Volvería sin duda.',            '2025-03-10 13:00:00'),
(1, '11223344C', 4, 'Buen acceso y zona segura. La entrada es un poco estrecha.',               '2025-03-11 16:00:00'),
(2, '55667788D', 5, 'Ideal para visitar el centro. El garaje está muy bien señalizado.',        '2025-03-12 12:00:00'),
(2, '99887766E', 4, 'Precio justo y bien ubicado. Lo recomiendo.',                              '2025-03-13 18:00:00'),
(3, '44332211F', 3, 'Correcta pero el acceso desde la calle es un poco incómodo.',              '2025-03-14 14:00:00'),
(3, '87654321B', 4, 'Tranquila y segura. El portero es muy amable.',                            '2025-03-15 17:00:00'),
(4, '11223344C', 5, 'Precio imbatible para estar tan cerca del centro histórico.',              '2025-03-16 11:00:00'),
(5, '55667788D', 4, 'Tiene cargador eléctrico, perfecto para mi coche. Muy recomendable.',     '2025-03-17 15:00:00'),
(6, '99887766E', 3, 'Zona residencial tranquila, aunque un poco lejos del centro.',             '2025-03-18 13:00:00'),
(7, '44332211F', 5, 'Excelente para ir al casco antiguo. Muy fácil de encontrar.',              '2025-03-19 16:00:00'),
(8, '87654321B', 4, 'Muy céntrica, a un paso de la calle Larios. El vigilante siempre presente.', '2025-03-20 12:00:00');
