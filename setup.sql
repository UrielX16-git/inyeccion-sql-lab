-- =====================================================
-- SQL Injection Lab — Setup Script
-- Proyecto universitario / Demostración educativa
-- =====================================================

CREATE DATABASE IF NOT EXISTS sqli_lab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sqli_lab;

DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL,
    contrasena VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Usuarios de ejemplo iniciales
INSERT INTO usuarios (usuario, contrasena) VALUES
('admin', 'admin123'),
('profesor', 'clave_segura'),
('estudiante', 'password'),
('invitado', '1234');
