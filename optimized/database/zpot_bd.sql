-- =============================================================================
-- Zpot: base de datos
-- Instalación limpia: mysql -u root -p zpot_bd < database/zpot_bd.sql
-- =============================================================================

CREATE TABLE IF NOT EXISTS USUARIO (
    DNI                  VARCHAR(20)  PRIMARY KEY,
    Nombre               VARCHAR(100) NOT NULL,
    Apellidos            VARCHAR(150) NOT NULL,
    Direccion            VARCHAR(200),
    Foto                 VARCHAR(255),
    Telefono             VARCHAR(20),
    Email                VARCHAR(100) UNIQUE,
    Contrasena_encriptada VARCHAR(255) NOT NULL,
    Token_pago           VARCHAR(255),
    token                VARCHAR(64),
    confirmado           TINYINT(1)   NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS PLAZA (
    ID_plaza   INT          AUTO_INCREMENT PRIMARY KEY,
    DNI        VARCHAR(20),
    Direccion  VARCHAR(200),
    Foto       VARCHAR(255),
    Ancho      DECIMAL(5,2),
    Largo      DECIMAL(5,2),
    Descripcion TEXT,
    Escritura  VARCHAR(255),
    Precio     DECIMAL(10,2) NULL,
    Ubicacion  VARCHAR(20)   NULL,
    Extras     VARCHAR(200)  NULL,
    Lat        DECIMAL(10,7) NULL,
    Lng        DECIMAL(10,7) NULL,
    CONSTRAINT fk_plaza_usuario
        FOREIGN KEY (DNI) REFERENCES USUARIO(DNI) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS RESERVA (
    ID_reserva  INT          AUTO_INCREMENT PRIMARY KEY,
    DNI         VARCHAR(20),
    ID_plaza    INT,
    Precio      DECIMAL(10,2),
    Duracion    INT,
    Hora_entrada TIME,
    Hora_salida  TIME,
    Fecha        DATE,
    Estado       ENUM('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
    CONSTRAINT fk_reserva_usuario
        FOREIGN KEY (DNI)      REFERENCES USUARIO(DNI)    ON DELETE CASCADE,
    CONSTRAINT fk_reserva_plaza
        FOREIGN KEY (ID_plaza) REFERENCES PLAZA(ID_plaza) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- Datos de ejemplo
-- Usuario demo: demo@zpot.local / password  (confirmado=1 para poder hacer login)
-- 8 plazas en el centro de Málaga con coordenadas reales
-- -----------------------------------------------------------------------------

INSERT INTO USUARIO (DNI, Nombre, Apellidos, Direccion, Telefono, Email, Contrasena_encriptada, confirmado)
VALUES (
    '12345678A',
    'Demo',
    'Propietario',
    'Calle Larios 1, Málaga',
    '952123456',
    'demo@zpot.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    1
) ON DUPLICATE KEY UPDATE confirmado = 1;

INSERT INTO PLAZA (DNI, Direccion, Foto, Ancho, Largo, Descripcion, Precio, Ubicacion, Extras, Lat, Lng) VALUES
('12345678A', 'Calle Marqués de Larios 5, Málaga',  'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800', 2.50, 5.00, 'Plaza cubierta en el corazón de Málaga. A 2 min de la Catedral. Acceso 24h con llave. Cámara de seguridad.', 4.50, 'cubierto',  '24h,vigilado',  36.7210400, -4.4213100),
('12345678A', 'Plaza de la Constitución 3, Málaga',  'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', 2.80, 5.20, 'Garaje privado en pleno centro. Ideal para turistas. Cerca de museos y restaurantes.',                   5.00, 'garaje',    'vigilado',      36.7208600, -4.4203800),
('12345678A', 'Calle Granada 42, Málaga',            'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800', 2.40, 4.80, 'Plaza en edificio residencial con portero. Seguro y tranquilo. Zona peatonal cercana.',                    4.00, 'garaje',    NULL,            36.7226700, -4.4195000),
('12345678A', 'Pasaje Chinitas 8, Málaga',           'https://picsum.photos/800/600?random=park1',                          2.60, 5.10, 'Aparcamiento exterior en callejón. Buen precio. A 5 min del Teatro Romano.',                               3.50, 'exterior',  NULL,            36.7204900, -4.4209900),
('12345678A', 'Alameda Principal 22, Málaga',        'https://picsum.photos/800/600?random=park2',                          2.70, 5.30, 'Plaza cubierta en sótano. Fácil acceso. Cerca del puerto y Paseo del Parque.',                             4.80, 'cubierto',  'ev,24h',        36.7197100, -4.4238300),
('12345678A', 'Calle Carretería 67, Málaga',         'https://picsum.photos/800/600?random=park3',                          2.50, 4.90, 'Garaje individual en zona residencial. Silencioso. Ideal para estancias largas.',                           3.80, 'garaje',    '24h',           36.7232600, -4.4231600),
('12345678A', 'Plaza del Siglo 1, Málaga',           'https://picsum.photos/800/600?random=park4',                          2.55, 5.00, 'Plaza en edificio histórico. Centro neurálgico. Perfecta para visitar el casco antiguo.',                  5.20, 'exterior',  'vigilado',      36.7210600, -4.4198100),
('12345678A', 'Calle Nueva 15, Málaga',              'https://picsum.photos/800/600?random=park5',                          2.45, 5.15, 'Aparcamiento exterior vigilado. Muy céntrico. A un paso de la calle Larios.',                              4.20, 'exterior',  'ev,vigilado',   36.7208100, -4.4219700);

-- -----------------------------------------------------------------------------
-- Migraciones para bases de datos existentes (schema anterior)
-- Solo ejecutar si estás actualizando una instalación ya existente.
-- En instalación limpia estas columnas ya existen en los CREATE TABLE.
-- -----------------------------------------------------------------------------
-- ALTER TABLE USUARIO ADD COLUMN token VARCHAR(64);
-- ALTER TABLE USUARIO ADD COLUMN confirmado TINYINT(1) NOT NULL DEFAULT 0;
-- ALTER TABLE PLAZA   ADD COLUMN Ubicacion  VARCHAR(20)   NULL;
-- ALTER TABLE PLAZA   ADD COLUMN Extras     VARCHAR(200)  NULL;
-- ALTER TABLE PLAZA   ADD COLUMN Lat        DECIMAL(10,7) NULL;
-- ALTER TABLE PLAZA   ADD COLUMN Lng        DECIMAL(10,7) NULL;
-- ALTER TABLE RESERVA ADD COLUMN Estado ENUM('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente';
