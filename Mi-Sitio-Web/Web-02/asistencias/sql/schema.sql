-- ============================================================
--  SISTEMA DE ASISTENCIAS - Estructura de la base de datos
--  Compatible con MySQL 8.0 y MariaDB (XAMPP)
--
--  Cómo ejecutarlo en Workbench:
--  File > Open SQL Script > elegir este archivo > icono del rayo.
--  Después ejecutar datos_ejemplo.sql (opcional, para pruebas).
-- ============================================================

DROP DATABASE IF EXISTS asistencias;
CREATE DATABASE asistencias
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE asistencias;
SET NAMES utf8mb4;   -- los acentos y la ° se guardan bien aunque el cliente use otra codificación

-- ------------------------------------------------------------
-- USUARIOS (alumnos, preceptoras y admin en una sola tabla)
-- ------------------------------------------------------------
CREATE TABLE usuarios (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre              VARCHAR(80)  NOT NULL,
    apellido            VARCHAR(80)  NOT NULL,
    dni                 VARCHAR(15)  NOT NULL,
    email               VARCHAR(150) NOT NULL,
    password_hash       VARCHAR(255) NOT NULL,
    rol                 ENUM('alumno','preceptora','admin') NOT NULL,
    estado              ENUM('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
    debe_cambiar_clave  TINYINT(1)   NOT NULL DEFAULT 0,
    intentos_fallidos   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_hasta     DATETIME NULL,
    creado_en           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_dni (dni),
    KEY idx_usuarios_rol (rol)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CURSOS
-- ------------------------------------------------------------
CREATE TABLE cursos (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(50) NOT NULL,          -- ej: "6° 1era"
    anio_lectivo  SMALLINT UNSIGNED NOT NULL,    -- ej: 2026
    PRIMARY KEY (id),
    UNIQUE KEY uq_cursos_nombre_anio (nombre, anio_lectivo)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- MATERIAS (solo el nombre, sin docentes)
-- ------------------------------------------------------------
CREATE TABLE materias (
    id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre  VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_materias_nombre (nombre)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- HORARIOS (grilla semanal de cada curso)
--   materia_id NULL = módulo libre (no genera asistencia)
--   La "salida institucional" no tiene fila: la jornada termina
--   en la hora_fin de la última clase real del día.
--   dia_semana: 1=lunes ... 5=viernes
-- ------------------------------------------------------------
CREATE TABLE horarios (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    curso_id     INT UNSIGNED NOT NULL,
    materia_id   INT UNSIGNED NULL,
    dia_semana   TINYINT UNSIGNED NOT NULL,
    hora_inicio  TIME NOT NULL,
    hora_fin     TIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_horarios_bloque (curso_id, dia_semana, hora_inicio),
    KEY idx_horarios_materia (materia_id),
    CONSTRAINT fk_horarios_curso
        FOREIGN KEY (curso_id) REFERENCES cursos (id) ON DELETE CASCADE,
    CONSTRAINT fk_horarios_materia
        FOREIGN KEY (materia_id) REFERENCES materias (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ALUMNO <-> CURSO (un alumno, un curso por año lectivo)
-- ------------------------------------------------------------
CREATE TABLE alumno_curso (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    alumno_id     INT UNSIGNED NOT NULL,
    curso_id      INT UNSIGNED NOT NULL,
    anio_lectivo  SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_alumno_anio (alumno_id, anio_lectivo),
    KEY idx_alumno_curso_curso (curso_id),
    CONSTRAINT fk_ac_alumno
        FOREIGN KEY (alumno_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_ac_curso
        FOREIGN KEY (curso_id) REFERENCES cursos (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PRECEPTORA <-> CURSO (los asigna el admin)
-- ------------------------------------------------------------
CREATE TABLE preceptora_curso (
    preceptora_id  INT UNSIGNED NOT NULL,
    curso_id       INT UNSIGNED NOT NULL,
    PRIMARY KEY (preceptora_id, curso_id),
    KEY idx_pc_curso (curso_id),
    CONSTRAINT fk_pc_preceptora
        FOREIGN KEY (preceptora_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_pc_curso
        FOREIGN KEY (curso_id) REFERENCES cursos (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ASISTENCIA DIARIA (nivel 1: ingreso, una por alumno y día)
-- ------------------------------------------------------------
CREATE TABLE asistencia_diaria (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    alumno_id       INT UNSIGNED NOT NULL,
    curso_id        INT UNSIGNED NOT NULL,
    fecha           DATE NOT NULL,
    estado          ENUM('presente','ausente','tarde','justificada') NOT NULL,
    hora_llegada    TIME NULL,                    -- solo si estado = 'tarde'
    observacion     VARCHAR(255) NULL,
    registrada_por  INT UNSIGNED NOT NULL,
    creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_diaria_alumno_fecha (alumno_id, fecha),
    KEY idx_diaria_curso_fecha (curso_id, fecha),
    CONSTRAINT fk_diaria_alumno
        FOREIGN KEY (alumno_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_diaria_curso
        FOREIGN KEY (curso_id) REFERENCES cursos (id) ON DELETE CASCADE,
    CONSTRAINT fk_diaria_registrada
        FOREIGN KEY (registrada_por) REFERENCES usuarios (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- RETIROS (el alumno se va desde una clase en adelante)
-- ------------------------------------------------------------
CREATE TABLE retiros (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    alumno_id          INT UNSIGNED NOT NULL,
    fecha              DATE NOT NULL,
    desde_horario_id   INT UNSIGNED NOT NULL,    -- primera clase que ya no cursa
    motivo             VARCHAR(255) NOT NULL,
    registrada_por     INT UNSIGNED NOT NULL,
    creado_en          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_retiro_alumno_fecha (alumno_id, fecha),
    CONSTRAINT fk_retiros_alumno
        FOREIGN KEY (alumno_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_retiros_horario
        FOREIGN KEY (desde_horario_id) REFERENCES horarios (id),
    CONSTRAINT fk_retiros_registrada
        FOREIGN KEY (registrada_por) REFERENCES usuarios (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ASISTENCIA POR CLASE (nivel 2: se genera sola desde el ingreso
-- y los retiros; se guarda para que los reportes sean simples)
-- ------------------------------------------------------------
CREATE TABLE asistencia_clase (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    alumno_id   INT UNSIGNED NOT NULL,
    horario_id  INT UNSIGNED NOT NULL,
    materia_id  INT UNSIGNED NOT NULL,           -- copia, para no perder el dato si cambia el horario
    fecha       DATE NOT NULL,
    estado      ENUM('presente','ausente','retirado','justificada') NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clase_alumno_horario_fecha (alumno_id, horario_id, fecha),
    KEY idx_clase_fecha (fecha),
    KEY idx_clase_materia (materia_id),
    CONSTRAINT fk_clase_alumno
        FOREIGN KEY (alumno_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_clase_horario
        FOREIGN KEY (horario_id) REFERENCES horarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_clase_materia
        FOREIGN KEY (materia_id) REFERENCES materias (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- HISTORIAL DE CAMBIOS (auditoría de ediciones)
-- ------------------------------------------------------------
CREATE TABLE historial_cambios (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tabla           VARCHAR(50)  NOT NULL,
    registro_id     INT UNSIGNED NOT NULL,
    valor_anterior  VARCHAR(255) NULL,
    valor_nuevo     VARCHAR(255) NULL,
    usuario_id      INT UNSIGNED NOT NULL,
    fecha           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_hist_tabla_registro (tabla, registro_id),
    CONSTRAINT fk_hist_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- RECUPERACIÓN DE CONTRASEÑA
-- ------------------------------------------------------------
CREATE TABLE password_resets (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL,
    token_hash  CHAR(64) NOT NULL,               -- SHA-256 del token enviado
    expira_en   DATETIME NOT NULL,
    usado       TINYINT(1) NOT NULL DEFAULT 0,
    creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reset_token (token_hash),
    KEY idx_reset_usuario (usuario_id),
    CONSTRAINT fk_reset_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- NORMAS DE CONVIVENCIA (las ven los 3 roles, las edita el admin)
-- ------------------------------------------------------------
CREATE TABLE normas (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo     VARCHAR(120) NOT NULL,
    contenido  TEXT NOT NULL,
    orden      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_normas_orden (orden)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONFIGURACIÓN (clave de registro de preceptoras, etc.)
-- Solo el admin la ve y la cambia.
-- ------------------------------------------------------------
CREATE TABLE configuracion (
    clave  VARCHAR(60)  NOT NULL,
    valor  VARCHAR(255) NOT NULL,
    PRIMARY KEY (clave)
) ENGINE=InnoDB;

INSERT INTO configuracion (clave, valor) VALUES
    ('clave_registro_preceptoras', 'ESCUELA2026');

-- ------------------------------------------------------------
-- USUARIO DE LA APLICACIÓN (permisos limitados, no usar root)
-- Cambiá la contraseña por una propia y la misma en config/database.php
-- ------------------------------------------------------------
CREATE USER IF NOT EXISTS 'asistencias_app'@'localhost' IDENTIFIED BY 'Asist_App_2026!';
CREATE USER IF NOT EXISTS 'asistencias_app'@'127.0.0.1' IDENTIFIED BY 'Asist_App_2026!';
GRANT SELECT, INSERT, UPDATE, DELETE ON asistencias.* TO 'asistencias_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON asistencias.* TO 'asistencias_app'@'127.0.0.1';
FLUSH PRIVILEGES;

-- ------------------------------------------------------------
-- ADMIN INICIAL (se crea directo en la base, no hay registro público)
--   Email:      admin@escuela.local
--   Contraseña: Admin2026!   (cambiala apenas entres)
-- ------------------------------------------------------------
INSERT INTO usuarios
    (nombre, apellido, dni, email, password_hash, rol, estado, debe_cambiar_clave)
VALUES
    ('Administrador', 'General', '10000000', 'admin@escuela.local',
     '$2y$10$upWbMfbsMu7CKaYDvA8MYuwOpzP.UR/iU.6ilgbs3b/BxBxFBx9nq',
     'admin', 'activo', 1);
