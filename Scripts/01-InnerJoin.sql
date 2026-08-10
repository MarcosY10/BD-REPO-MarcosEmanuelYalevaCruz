-- BASE DE DATOS: Provincias y Ciudades
-- Tema: Relaciones e INNER JOIN


CREATE DATABASE SegCuatri;

USE SegCuatri;
--Usan o crean su base de datos. 

-- TABLA PROVINCIA

CREATE TABLE Provincia (
    idProvincia INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
);


-- TABLA CIUDAD

CREATE TABLE Ciudad (
    idCiudad INT NOT NULL AUTO_INCREMENT PRIMARY KEY
    nombre VARCHAR(100) NOT NULL,
    idProvincia INT NOT NULL,
    FOREIGN KEY (idProvincia) REFERENCES Provincia(idProvincia)
);

-- INSERTAMOS LA PROVINCIA

INSERT INTO Provincia (nombre)
VALUES ('La Rioja');


-- INSERTAMOS CIUDADES DE LA RIOJA

INSERT INTO Ciudad (nombre, idProvincia) VALUES
('La Rioja', 1),
('Chilecito', 1),
('Nonogasta', 1),
('Famatina', 1),
('Villa Unión', 1),
('Aimogasta', 1),
('Chamical', 1),
('Chepes', 1),
('Olta', 1),
('Villa Castelli', 1),
('Vinchina', 1),
('Sanagasta', 1);


-- CONSULTAMOS LAS TABLAS

SELECT * FROM Provincia;
SELECT * FROM Ciudad;


-- INNER JOIN
-- Mostrar cada ciudad junto a su provincia

SELECT
    Ciudad.nombre AS Ciudad,
    Provincia.nombre AS Provincia
FROM Ciudad
INNER JOIN Provincia
    ON Ciudad.idProvincia = Provincia.idProvincia;