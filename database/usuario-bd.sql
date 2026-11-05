CREATE USER IF NOT EXISTS 'dawlloween'@'localhost' IDENTIFIED BY 'rocketleagueatlantidafp';
GRANT SELECT, INSERT, UPDATE ON halloween_atlantida.usuarios TO 'dawlloween'@'localhost';
FLUSH PRIVILEGES;