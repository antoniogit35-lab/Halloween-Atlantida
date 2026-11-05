CREATE USER IF NOT EXISTS 'dawlloween'@'localhost' IDENTIFIED BY 'CAMBIA_ESTA_CLAVE';
GRANT SELECT, INSERT, UPDATE ON halloween_atlantida.usuarios TO 'dawlloween'@'localhost';
FLUSH PRIVILEGES;