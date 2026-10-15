-- ============================================================
--  DATOS DE EJEMPLO (para pruebas)
--  Ejecutar DESPUES de schema.sql.
--
--  Cuentas de prueba:
--    Preceptora: laura.gomez@escuela.local / Preceptora2026!
--    Alumnos:    alumno01@escuela.local ... alumno26@escuela.local
--                contrasena: alumno123
--    (el admin ya lo crea schema.sql)
--
--  Horario de verano de 6° ano:
--    Franjas 14:00-15:20, 15:25-16:45, 16:55-18:15
--    Refrigerio 18:20-18:35
--    Franjas 18:35-19:55 y 20:00-21:20
--  Modulo libre = fila con materia NULL (jueves: 6° 1era 15:25, 6° 2da 16:55).
--  Despues de la ultima clase real del dia no hay fila: es la salida
--  institucional, no cuenta como retiro ni como falta.
--    - Jueves 6° 2da: termina 19:55
--    - Viernes 6° 1era: termina 19:55
-- ============================================================

USE asistencias;

-- Cursos
INSERT INTO cursos (nombre, anio_lectivo) VALUES ('6° 1era', 2026), ('6° 2da', 2026);

SET @c1 = (SELECT id FROM cursos WHERE nombre = '6° 1era' AND anio_lectivo = 2026);
SET @c2 = (SELECT id FROM cursos WHERE nombre = '6° 2da' AND anio_lectivo = 2026);

-- Materias
INSERT INTO materias (nombre) VALUES
    ('Base de Datos'),
    ('Instalación y Mant. H y S IV'),
    ('Instalación y Mant. H y S'),
    ('Marco Jurídico'),
    ('Matemática Aplicada'),
    ('Economía'),
    ('Inglés Técnico'),
    ('Redes'),
    ('Expresión Oral y Escrita'),
    ('Práctica I'),
    ('Práctica II'),
    ('Proyecto I'),
    ('Proyecto II');

-- Horarios (dia_semana: 1=lunes ... 5=viernes; materia NULL = modulo libre)
INSERT INTO horarios (curso_id, materia_id, dia_semana, hora_inicio, hora_fin) VALUES
    (@c1, (SELECT id FROM materias WHERE nombre = 'Base de Datos'), 1, '14:00:00', '16:05:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Instalación y Mant. H y S IV'), 1, '14:00:00', '16:05:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Instalación y Mant. H y S IV'), 1, '16:05:00', '18:15:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Base de Datos'), 1, '16:05:00', '18:15:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Marco Jurídico'), 1, '18:35:00', '19:55:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Matemática Aplicada'), 1, '18:35:00', '19:55:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Matemática Aplicada'), 1, '20:00:00', '21:20:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Marco Jurídico'), 1, '20:00:00', '21:20:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Marco Jurídico'), 2, '14:00:00', '15:20:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Economía'), 2, '14:00:00', '15:20:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Economía'), 2, '15:25:00', '16:45:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Matemática Aplicada'), 2, '15:25:00', '16:45:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Matemática Aplicada'), 2, '16:55:00', '18:15:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Marco Jurídico'), 2, '16:55:00', '18:15:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Práctica I'), 2, '18:35:00', '19:55:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Práctica II'), 2, '18:35:00', '19:55:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Práctica I'), 2, '20:00:00', '21:20:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Práctica II'), 2, '20:00:00', '21:20:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Inglés Técnico'), 3, '14:00:00', '16:05:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Redes'), 3, '14:00:00', '16:05:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Redes'), 3, '16:05:00', '18:15:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Inglés Técnico'), 3, '16:05:00', '18:15:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Práctica I'), 3, '18:35:00', '19:55:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Práctica II'), 3, '18:35:00', '19:55:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Proyecto I'), 3, '20:00:00', '21:20:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Proyecto II'), 3, '20:00:00', '21:20:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Economía'), 4, '14:00:00', '15:20:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Instalación y Mant. H y S'), 4, '14:00:00', '15:20:00'),
    (@c1, NULL, 4, '15:25:00', '16:45:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Economía'), 4, '15:25:00', '16:45:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Instalación y Mant. H y S'), 4, '16:55:00', '18:15:00'),
    (@c2, NULL, 4, '16:55:00', '18:15:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Base de Datos'), 4, '18:35:00', '19:55:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Proyecto II'), 4, '18:35:00', '19:55:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Proyecto I'), 4, '20:00:00', '21:20:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Expresión Oral y Escrita'), 5, '14:00:00', '16:05:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Redes'), 5, '14:00:00', '16:05:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Redes'), 5, '16:05:00', '18:15:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Expresión Oral y Escrita'), 5, '16:05:00', '18:15:00'),
    (@c1, (SELECT id FROM materias WHERE nombre = 'Proyecto I'), 5, '18:35:00', '19:55:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Base de Datos'), 5, '18:35:00', '19:55:00'),
    (@c2, (SELECT id FROM materias WHERE nombre = 'Proyecto II'), 5, '20:00:00', '21:20:00');

-- Preceptora de prueba (asignada a los dos cursos)
INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol, estado, debe_cambiar_clave) VALUES
    ('Milena', 'Gómez', '30000001', 'laura.gomez@escuela.local',
     '$2y$10$2BAgmNF2XhwTp2zkjtrRQOsx/51V9oQ.jWSBCQqOTrlTyyAGu8Olm', 'preceptora', 'activo', 0);
INSERT INTO preceptora_curso (preceptora_id, curso_id)
    SELECT id, @c1 FROM usuarios WHERE email = 'laura.gomez@escuela.local';
INSERT INTO preceptora_curso (preceptora_id, curso_id)
    SELECT id, @c2 FROM usuarios WHERE email = 'laura.gomez@escuela.local';

-- ------------------------------------------------------------
-- ALUMNOS (14 en 6° 1era, 12 en 6° 2da)
-- Para poner los nombres reales, cambia nombre / apellido / dni / email
-- en estas lineas y volve a ejecutar schema.sql y este archivo.
-- La contrasena de todos es "alumno123"; abajo se guarda ya convertida
-- en hash (eso es lo unico que ve la base de datos).
-- Mas adelante el admin podra editar alumnos desde el panel.
-- ------------------------------------------------------------

-- 6° 1era: 14 alumnos
INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol, estado, debe_cambiar_clave) VALUES
    ('Bautista Nahir', 'Barrionuevo Alamo', '40000001', 'alumno01@escuela.local', '$2y$10$m97AEqYPEDURTo/TIuYz0.9mu3rK5yCQ9cGthuD0cfbJ8sr/u3t.6', 'alumno', 'activo', 0),
    ('Caliva Chancalay', 'Elias David', '40000002', 'alumno02@escuela.local', '$2y$10$MTdldWKkAj/EKa7cFU5MeeCedfvnxnKs/BekE6EMs2VH0GZmycLkG', 'alumno', 'activo', 0),
    ('Emiliano Exequiel', 'Gonzalez Fredes', '40000003', 'alumno03@escuela.local', '$2y$10$0CmqbyqQmVOLaZam9xMGGemFEtbN/1b3cuMmN.hG4ApOltDTz9rg6', 'alumno', 'activo', 0),
    ('Sonia Romina', 'Mendoza Mamani', '40000004', 'alumno04@escuela.local', '$2y$10$LaDyMVKE6ASU5KOgdjRn/OR6DIt21KHMNeaGN7WrW9eEB6W5O5OA6', 'alumno', 'activo', 0),
    ('Sebastian Humberto', 'Ormeño Mierez', '40000005', 'alumno05@escuela.local', '$2y$10$.vFUHzAfI9I//j3GRNolWeYscZ9GYgmTTXfYH56d6wbO5UImDCXUi', 'alumno', 'activo', 0),
    ('Jessica Cinthia', 'Ovando Mamani', '40000006', 'alumno06@escuela.local', '$2y$10$FL/Asv1NOmo0lJzRLx.MxuGor80RgmBBor.lMRvqjjM9lKj5ky1Nq', 'alumno', 'activo', 0),
    ('Jacquelin Esmeralda', 'Quispe', '40000007', 'alumno07@escuela.local', '$2y$10$CmlsUOidCmcuUgPMzrOi8eeUN6P8PUyzCCRDlvdrnYbCxEGxUjrEG', 'alumno', 'activo', 0),
    ('Yenhy Iris', 'Ramos Zuna', '40000008', 'alumno08@escuela.local', '$2y$10$Wnb6g5SteKh2JKnFNPC9E.UrldVOodOgQc8lTIS7i3tNcYi7W6Y42', 'alumno', 'activo', 0),
    ('Rut Priscila', 'Romero', '40000009', 'alumno09@escuela.local', '$2y$10$1Muzl5SlDCc3VWtPu8NJ2OqpzfJLZ6GmfbPuqEX6GEfQcJ53/fz5.', 'alumno', 'activo', 0),
    ('Carmelo Cesar', 'Sullca Quispe', '40000010', 'alumno10@escuela.local', '$2y$10$77d3T/LwHBIjrvkC3ObV0uSLprmpb6redRviPIBygHuaEIcVirLsS', 'alumno', 'activo', 0),
    ('Tiago Adrian', 'Torres', '40000011', 'alumno11@escuela.local', '$2y$10$UhOliZQl.p0Jpg7dGa8MS.y9xdSlrRYN9vAbDZ4BTgguZhXlMt15y', 'alumno', 'activo', 0),
    ('Juan Maximo', 'Villca Ovando', '40000012', 'alumno12@escuela.local', '$2y$10$cXhoTVo4T/CROyW4cCJFmuXv.CwhtdDsIDE.629vTCd/kMxjDy62K', 'alumno', 'activo', 0),
    ('Zamira Jacquelin', 'Yanaje Martinez', '40000013', 'alumno13@escuela.local', '$2y$10$eud3i0fmCL54Qz8Yc7s0WuQhetP7bsxnxKMGDcpq/lJnxvc65YHAO', 'alumno', 'activo', 0),
    ('Gimena Belen', 'Ramirez', '40000014', 'alumno14@escuela.local', '$2y$10$tT13BhSJU8p1j7ig76kRLOxWyiL3mBbBtBsq9Bd9tEGad0zu.JZoC', 'alumno', 'activo', 0);
INSERT INTO alumno_curso (alumno_id, curso_id, anio_lectivo)
    SELECT id, @c1, 2026 FROM usuarios
    WHERE rol = 'alumno' AND id NOT IN (SELECT alumno_id FROM alumno_curso);

-- 6° 2da: 12 alumnos
INSERT INTO usuarios (nombre, apellido, dni, email, password_hash, rol, estado, debe_cambiar_clave) VALUES
    ('Luciana Miranda', 'Baquinzay Ormeño', '40000015', 'alumno15@escuela.local', '$2y$10$aBtd.pTXK9zHyoimVhoI7uY2NavpzW7W2KsuWacczXtvVjJQjrQgu', 'alumno', 'activo', 0),
    ('Jenifer Alegra', 'Caceres Quispe', '40000016', 'alumno16@escuela.local', '$2y$10$PxMnOiWBxaLRMyyJWlMrVuhFQwED6WnF58dpau9b5ZHoPbQKqpPaK', 'alumno', 'activo', 0),
    ('Anahi del Carmen', 'Conde', '40000017', 'alumno17@escuela.local', '$2y$10$Osw2a43/gAVd2WULtiO9Nuxjn3e.G0SQ3BRCehAGsZf/GRw354hK.', 'alumno', 'activo', 0),
    ('Tisiano Valentin', 'Diaz', '40000018', 'alumno18@escuela.local', '$2y$10$KR0X0YT5cRor8WPB.6BhlOJQYXHWpjV5kVxAm5E6BKhTuxhDpoCYW', 'alumno', 'activo', 0),
    ('Angela Guadalupe', 'Helguero Gudiño', '40000019', 'alumno19@escuela.local', '$2y$10$IqZg.H6wV1j62o4rlKqhbeQQ3tPmqxWr5.ycrtlFfseTttWWbNsIS', 'alumno', 'activo', 0),
    ('Carla Fernanda', 'Martinez Maita', '40000020', 'alumno20@escuela.local', '$2y$10$7wT7ZwgfB61U04R9xh8PBOK20GIF04O6MYXe4LUvVktvPRrahrvRu', 'alumno', 'activo', 0),
    ('Alexis Leonel', 'Quiroga Moscoso', '40000021', 'alumno21@escuela.local', '$2y$10$FMSTfn61aX3oqQXWrC72VeNdbW/mli1XKiHKjyKD9./Ol/uvqPcyK', 'alumno', 'activo', 0),
    ('Julia Mabel', 'Quispe Caihuara', '40000022', 'alumno22@escuela.local', '$2y$10$HIhHbkMRLDEWv495IT8sMOkwF1qpmb7xnuRjjxKfzsF7a0OZ1Q9Xu', 'alumno', 'activo', 0),
    ('Jose David', 'Sanchez Quispe', '40000023', 'alumno23@escuela.local', '$2y$10$vR.xUYVaLyhOKmr4HRGDKuBUlGq7QZAJ2CO/zhk7ZYKkQU4UPSs0C', 'alumno', 'activo', 0),
    ('Bruno Stefano', 'Sanguineti', '40000024', 'alumno24@escuela.local', '$2y$10$kcc9Mec9rVtL4yY1ajbB/.AlayipZKeghcy305VZldF/LtzQJMJFG', 'alumno', 'activo', 0),
    ('Antonela Madelein', 'Vargas', '40000025', 'alumno25@escuela.local', '$2y$10$G5vSRNJKJfi/uMA2u8dYV.D5.3EjFHQkKwYCR.TCl/wxp2rP11vju', 'alumno', 'activo', 0),
    ('Marcos Emanuel', 'Yaleva Cruz', '40000026', 'alumno26@escuela.local', '$2y$10$o0hKYJwHn6eP8ce7HP4mTekfskBpZBHIJVcWWBrPu6l8/1s.jQQhu', 'alumno', 'activo', 0);
INSERT INTO alumno_curso (alumno_id, curso_id, anio_lectivo)
    SELECT id, @c2, 2026 FROM usuarios
    WHERE rol = 'alumno' AND id NOT IN (SELECT alumno_id FROM alumno_curso);


-- Normas de convivencia (el admin las puede editar desde el panel)
INSERT INTO normas (titulo, contenido, orden) VALUES
    ('Presentación personal', 'Los varones deben asistir con el cabello corto y las mujeres con el cabello recogido. Se asiste con el uniforme completo y en condiciones de prolijidad.', 1),
    ('Uso del celular', 'No se permite el uso del celular durante las horas de clase, salvo que el profesor lo solicite para realizar una tarea.', 2),
    ('Puntualidad', 'El ingreso es a las 14:00. Las llegadas tarde se registran y cuentan como media falta.', 3),
    ('Inasistencias', 'Las ausencias deben justificarse con el responsable o con certificado, dentro de las 48 horas.', 4),
    ('Retiros anticipados', 'Un alumno solo puede retirarse antes de terminar la jornada con autorización, y el retiro queda registrado por la preceptora.', 5),
    ('Refrigerio', 'El refrigerio es de 18:20 a 18:35. Se debe regresar al aula en horario.', 6),
    ('Respeto y convivencia', 'Se espera un trato respetuoso hacia compañeros, docentes y personal de la escuela, y el cuidado de las instalaciones.', 7);