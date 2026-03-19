-- =============================================================================
-- Zpot: base de datos única
-- Ejecutar una sola vez: mysql -u root -p < database/zpot_bd.sql
-- Crea: usuario admin, base de datos, tablas y datos de ejemplo (plazas en Málaga)
-- =============================================================================

CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost';
FLUSH PRIVILEGES;

CREATE SCHEMA zpot_bd;
USE zpot_bd;

-- -----------------------------------------------------------------------------
-- Tablas
-- -----------------------------------------------------------------------------

CREATE TABLE USUARIO (
    DNI VARCHAR(20) PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Apellidos VARCHAR(150) NOT NULL,
    Direccion VARCHAR(200),
    Foto VARCHAR(255),
    Telefono VARCHAR(20),
    Email VARCHAR(100) UNIQUE,
    Contrasena_encriptada VARCHAR(255) NOT NULL,
    Token_pago VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE PLAZA (
    ID_plaza INT AUTO_INCREMENT PRIMARY KEY,
    DNI VARCHAR(20),
    Direccion VARCHAR(200),
    Foto VARCHAR(255),
    Ancho DECIMAL(5,2),
    Largo DECIMAL(5,2),
    Descripcion TEXT,
    Escritura VARCHAR(255),
    Precio DECIMAL(10,2) NULL,
    CONSTRAINT fk_plaza_usuario
        FOREIGN KEY (DNI) REFERENCES USUARIO(DNI) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE RESERVA (
    ID_reserva INT AUTO_INCREMENT PRIMARY KEY,
    DNI VARCHAR(20),
    ID_plaza INT,
    Precio DECIMAL(10,2),
    Duracion INT,
    Hora_entrada TIME,
    Hora_salida TIME,
    Fecha DATE,
    CONSTRAINT fk_reserva_usuario
        FOREIGN KEY (DNI) REFERENCES USUARIO(DNI) ON DELETE CASCADE,
    CONSTRAINT fk_reserva_plaza
        FOREIGN KEY (ID_plaza) REFERENCES PLAZA(ID_plaza) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- Datos de ejemplo (para probar el mapa)
-- Usuario demo: email demo@zpot.local, contraseña "password"
-- 8 plazas alrededor del centro de Málaga
-- -----------------------------------------------------------------------------

INSERT INTO USUARIO (DNI, Nombre, Apellidos, Direccion, Telefono, Email, Contrasena_encriptada)
VALUES (
    '12345678A',
    'Demo',
    'Propietario',
    'Calle Larios 1, Málaga',
    '952123456',
    'demo@zpot.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
) ON DUPLICATE KEY UPDATE Nombre = Nombre;

INSERT INTO PLAZA (DNI, Direccion, Foto, Ancho, Largo, Descripcion, Precio) VALUES
('12345678A', 'Calle Marqués de Larios 5, Málaga', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800', 2.50, 5.00, 'Plaza cubierta en el corazón de Málaga. A 2 min de la Catedral. Acceso 24h con llave. Cámara de seguridad.', 4.50),
('12345678A', 'Plaza de la Constitución 3, Málaga', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800', 2.80, 5.20, 'Garaje privado en pleno centro. Ideal para turistas. Cerca de museos y restaurantes.', 5.00),
('12345678A', 'Calle Granada 42, Málaga', 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800', 2.40, 4.80, 'Plaza en edificio residencial con portero. Seguro y tranquilo. Zona peatonal cercana.', 4.00),
('12345678A', 'Pasaje Chinitas 8, Málaga', 'https://picsum.photos/800/600?random=park1', 2.60, 5.10, 'Aparcamiento exterior en callejón. Buen precio. A 5 min del Teatro Romano.', 3.50),
('12345678A', 'Alameda Principal 22, Málaga', 'https://picsum.photos/800/600?random=park2', 2.70, 5.30, 'Plaza cubierta en sótano. Fácil acceso. Cerca del puerto y Paseo del Parque.', 4.80),
('12345678A', 'Calle Carretería 67, Málaga', 'https://picsum.photos/800/600?random=park3', 2.50, 4.90, 'Garaje individual en zona residencial. Silencioso. Ideal para estancias largas.', 3.80),
('12345678A', 'Plaza del Siglo 1, Málaga', 'https://picsum.photos/800/600?random=park4', 2.55, 5.00, 'Plaza en edificio histórico. Centro neurálgico. Perfecta para visitar el casco antiguo.', 5.20),
('12345678A', 'Calle Nueva 15, Málaga', 'https://picsum.photos/800/600?random=park5', 2.45, 5.15, 'Aparcamiento exterior vigilado. Muy céntrico. A un paso de la calle Larios.', 4.20);
