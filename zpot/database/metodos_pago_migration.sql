-- =============================================================================
-- Zpot: Migración — Métodos de pago
-- Ejecutar en MySQL (no toca tablas existentes)
-- =============================================================================

-- Tabla para tarjetas (guardamos solo datos no sensibles + últimos 4 dígitos)
-- Los datos reales los gestiona PayPal, nunca los almacenamos nosotros
CREATE TABLE IF NOT EXISTS METODO_PAGO (
    ID_metodo     INT          AUTO_INCREMENT PRIMARY KEY,
    DNI           VARCHAR(20)  NOT NULL,
    Tipo          ENUM('paypal','tarjeta') NOT NULL,
    Alias         VARCHAR(100) NOT NULL,          -- ej: "Mi PayPal", "Visa personal"
    Ultimos4      CHAR(4)      NULL,              -- solo para tarjetas, ej: "4242"
    Marca         VARCHAR(20)  NULL,              -- "visa", "mastercard", "amex"
    Caducidad     CHAR(5)      NULL,              -- "MM/AA", ej: "12/27"
    Email_paypal  VARCHAR(100) NULL,              -- para cuentas PayPal
    Es_defecto    TINYINT(1)   NOT NULL DEFAULT 0,
    Fecha_alta    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_metodo_usuario
        FOREIGN KEY (DNI) REFERENCES USUARIO(DNI) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de prueba para el usuario demo (PONER EL DNI DE TU CUENTA)
INSERT INTO METODO_PAGO (DNI, Tipo, Alias, Email_paypal, Es_defecto) VALUES
('12345678A', 'paypal', 'Mi PayPal', 'demo@zpot.local', 1);