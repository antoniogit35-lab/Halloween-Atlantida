CREATE DATABASE IF NOT EXISTS halloween_atlantida
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE halloween_atlantida;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(30) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    perfil ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    pregunta VARCHAR(150) NOT NULL,
    respuesta_hash VARCHAR(255) NOT NULL,
    asesino VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    fecha_alta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;