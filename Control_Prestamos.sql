============================================================
-- PROYECTO - TALLER DE BASE DE DATOS
-- MOTOR: MariaDB
-- CONTEXTO: control de prestamos "control_prestamos"
-- Hilda Yolanda Solis Mosqueda 
-- 24460418
-- 8:00-9:00
-- ============================================================

DROP DATABASE IF EXISTS control_prestamos;

CREATE DATABASE control_prestamos
CHARACTER SET utf8mb4
COLLATE utf8mb4_spanish_ci;

USE control_prestamos;

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(200)
);
-- Almacena los departamentos de la institución. Permite que varios solicitantes pertenezcan al mismo departamento sin repetir su nombre.
CREATE TABLE departamentos (
    id_departamento INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE equipos (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    num_inventario VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NOT NULL,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    id_categoria INT NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'disponible',
    condicion VARCHAR(100),
    activo BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT chk_estado_equipo
    CHECK (estado IN (
        'disponible',
        'prestado',
        'mantenimiento'
    )),

    CONSTRAINT fk_equipo_categoria
    FOREIGN KEY (id_categoria)
    REFERENCES categorias(id_categoria)
);

CREATE TABLE solicitantes (
    id_solicitante INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    num_institucional VARCHAR(50) NOT NULL UNIQUE,
    id_departamento INT NOT NULL,

    CONSTRAINT fk_solicitante_departamento
    FOREIGN KEY (id_departamento)
    REFERENCES departamentos(id_departamento)
);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(30) NOT NULL,

    CONSTRAINT chk_rol_usuario
    CHECK (rol IN ('administrador', 'consulta'))
);

CREATE TABLE prestamos (
    id_prestamo INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    id_solicitante INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_prestamo DATETIME NOT NULL,
    fecha_prevista DATETIME NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'activo',

    CONSTRAINT chk_estado_prestamo
    CHECK (estado IN ('activo', 'devuelto')),

    CONSTRAINT chk_fechas_prestamo
    CHECK (fecha_prevista >= fecha_prestamo),

    CONSTRAINT fk_prestamo_equipo
    FOREIGN KEY (id_equipo)
    REFERENCES equipos(id_equipo),

    CONSTRAINT fk_prestamo_solicitante
    FOREIGN KEY (id_solicitante)
    REFERENCES solicitantes(id_solicitante),

    CONSTRAINT fk_prestamo_usuario
    FOREIGN KEY (id_usuario)
    REFERENCES usuarios(id_usuario)
);

CREATE TABLE devoluciones (
    id_devolucion INT AUTO_INCREMENT PRIMARY KEY,
    id_prestamo INT NOT NULL UNIQUE,
    fecha_devolucion DATETIME NOT NULL,
    condicion_retorno VARCHAR(100) NOT NULL,
    observaciones TEXT,

    CONSTRAINT fk_devolucion_prestamo
    FOREIGN KEY (id_prestamo)
    REFERENCES prestamos(id_prestamo)
);

SHOW TABLES;
DROP TABLE categorias;
