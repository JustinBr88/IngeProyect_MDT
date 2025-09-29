-- Limpia la base y crea la estructura necesaria
DROP DATABASE IF EXISTS cmdb;
CREATE DATABASE cmdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cmdb;

-- Departamentos
CREATE TABLE departamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  ubicacion VARCHAR(150)
);

-- Categorías
CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  descripcion TEXT
);
-- Usuarios
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  rol ENUM('admin','colab') DEFAULT 'colab',
  activo TINYINT(1) DEFAULT 1,
  foto LONGBLOB NULL, -- Campo para almacenar la imagen como datos binarios
  foto_tipo VARCHAR(50) NULL, -- Tipo MIME de la imagen (image/jpeg, image/png, etc.)
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Colaboradores
CREATE TABLE colaboradores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  apellido VARCHAR(50) NOT NULL,
  identificacion VARCHAR(50) NOT NULL UNIQUE,
  foto LONGBLOB,
  foto_tipo VARCHAR(50),
  direccion VARCHAR(150),
  ubicacion VARCHAR(100),
  telefono VARCHAR(20),
  correo VARCHAR(100),
  departamento_id INT,
  activo TINYINT(1) DEFAULT 1,
  FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
);

-- Inventario
CREATE TABLE inventario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre_equipo VARCHAR(100) NOT NULL,
  categoria_id INT NOT NULL,
  marca VARCHAR(50),
  modelo VARCHAR(50),
  numero_serie VARCHAR(100) UNIQUE,
  costo DECIMAL(10,2),
  fecha_ingreso DATE,
  tiempo_depreciacion INT, -- en meses
  estado ENUM('activo','baja','reparacion','descarte','donado','inventario','solicitado','asignado','entrega_pendiente','revision_tecnica','donacion_pendiente') DEFAULT 'activo',
  descripcion TEXT,
  imagen VARCHAR(255), -- Ruta o nombre de archivo de la imagen
  -- Campos para sistema de descarte
  estado_descarte ENUM('activo', 'descarte') DEFAULT 'activo',
  fecha_descarte DATETIME NULL,
  observaciones_descarte TEXT NULL,
  tecnico_descarte VARCHAR(100) NULL,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  INDEX idx_estado_descarte (estado_descarte)
);

-- Solicitudes (solo para admins)
CREATE TABLE solicitudes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  inventario_id INT NOT NULL, -- El equipo/software solicitado
  nombre_equipo VARCHAR(100) NOT NULL,
  colaborador_id INT NOT NULL, -- Quién solicita
  fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  motivo TEXT,
  tipo VARCHAR(30) DEFAULT 'asignacion',
  usuario_admin_id INT, -- Quién responde (NULL hasta que admin actúe)
  fecha_respuesta DATETIME,
  FOREIGN KEY (inventario_id) REFERENCES inventario(id),
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
  FOREIGN KEY (usuario_admin_id) REFERENCES usuarios(id)
);

-- Asignaciones
CREATE TABLE asignaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  inventario_id INT NOT NULL,
  colaborador_id INT NOT NULL,
  fecha_asignacion DATE,
  fecha_retiro DATE,
  motivo_retiro TEXT,
  estado ENUM('asignado','retirado','devuelto','entrega_pendiente','donado') DEFAULT 'asignado',
  observaciones TEXT,
  FOREIGN KEY (inventario_id) REFERENCES inventario(id),
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
);


CREATE TABLE historial_accesos_colaborador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    ip VARCHAR(45),
    user_agent VARCHAR(255),
    FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE
);

CREATE TABLE donaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  colaborador_id INT NOT NULL,
  inventario_id INT NOT NULL,
  destinatario VARCHAR(100) NOT NULL, -- institución o persona a quien se dona
  motivo TEXT,                        -- motivo de la donación
  fecha_donacion DATETIME NOT NULL,
  estado VARCHAR(30) DEFAULT 'pendiente', -- pendiente, aprobada, rechazada
  usuario_admin_id INT,                   -- quien aprueba/rechaza
  fecha_respuesta DATETIME,
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
  FOREIGN KEY (inventario_id) REFERENCES inventario(id),
  FOREIGN KEY (usuario_admin_id) REFERENCES usuarios(id)
);

-- Ejemplo de datos base
INSERT INTO categorias (nombre, descripcion) VALUES
('Software', 'Programas informáticos'),
('Hardware', 'Equipo o componentes electrónicos'),
('Equipo de red', 'Dispositivos de red'),
('Equipo de cómputo', 'Computadoras y periféricos'),
('Equipo de telefonía', 'Teléfonos y accesorios');

-- 1. Software
INSERT INTO inventario (nombre_equipo, categoria_id, marca, modelo, numero_serie, costo, fecha_ingreso, tiempo_depreciacion, estado, descripcion, imagen, estado_descarte)
VALUES
('Microsoft Office 2021', 1, 'Microsoft', 'Office 2021', 'LICMSOFF2021', 240.00, '2024-05-10', 36, 'inventario', 'Licencia de suite ofimática actualizada.', 'office2021.png', 'activo'),
('Adobe Photoshop CC', 1, 'Adobe', 'Photoshop CC 2024', 'LICADPS2024', 360.00, '2024-06-15', 24, 'inventario', 'Licencia de edición de imágenes profesional.', 'photoshopcc.png', 'activo');

-- 2. Hardware
INSERT INTO inventario (nombre_equipo, categoria_id, marca, modelo, numero_serie, costo, fecha_ingreso, tiempo_depreciacion, estado, descripcion, imagen, estado_descarte)
VALUES
('Disco Duro SSD 1TB', 2, 'Kingston', 'A2000', 'SNKINGSSD1TB', 95.00, '2024-01-20', 36, 'inventario', 'SSD NVMe Kingston de alto rendimiento.', 'ssd1tb.png', 'activo'),
('Fuente de poder 600W', 2, 'EVGA', '600 BR', 'SNEVGA600BR', 60.00, '2023-12-10', 48, 'inventario', 'Fuente de poder certificada para PC.', 'fuente600w.png', 'activo');

-- 3. Equipo de red
INSERT INTO inventario (nombre_equipo, categoria_id, marca, modelo, numero_serie, costo, fecha_ingreso, tiempo_depreciacion, estado, descripcion, imagen, estado_descarte)
VALUES
('Switch Cisco 16 puertos', 3, 'Cisco', 'SF110D-16', 'SNCISCO16', 150.00, '2024-03-18', 48, 'inventario', 'Switch no gestionable para oficina.', 'cisco16p.png', 'activo'),
('Router TP-Link Archer', 3, 'TP-Link', 'Archer C6', 'SNTPARCHERC6', 70.00, '2024-02-05', 36, 'inventario', 'Router doble banda para red principal.', 'tplinkarcher.png', 'activo');

-- 4. Equipo de cómputo
INSERT INTO inventario (nombre_equipo, categoria_id, marca, modelo, numero_serie, costo, fecha_ingreso, tiempo_depreciacion, estado, descripcion, imagen, estado_descarte)
VALUES
('Laptop Dell Latitude', 4, 'Dell', 'Latitude 5410', 'SNDLAT5410', 1200.00, '2023-11-22', 36, 'inventario', 'Laptop para usuario administrativo.', 'delllatitude.png', 'activo'),
('PC HP ProDesk', 4, 'HP', 'ProDesk 400', 'SNHPPD400', 850.00, '2024-04-10', 36, 'inventario', 'Equipo de escritorio para oficina.', 'hpprodesk.png', 'activo');

-- 5. Equipo de telefonía
INSERT INTO inventario (nombre_equipo, categoria_id, marca, modelo, numero_serie, costo, fecha_ingreso, tiempo_depreciacion, estado, descripcion, imagen, estado_descarte)
VALUES
('Teléfono IP Yealink', 5, 'Yealink', 'T21P E2', 'SNYEALINKT21', 55.00, '2024-07-01', 24, 'inventario', 'Teléfono IP para sala de reuniones.', 'yealinkt21p.png', 'activo'),
('Auriculares Jabra Evolve', 5, 'Jabra', 'Evolve 20', 'SNJABRAEV20', 70.00, '2024-06-20', 24, 'inventario', 'Auriculares con micrófono para call center.', 'jabraevolve20.png', 'activo');

-- Ejemplos de equipos en descarte para demostrar funcionalidad
INSERT INTO inventario (nombre_equipo, categoria_id, marca, modelo, numero_serie, costo, fecha_ingreso, tiempo_depreciacion, estado, descripcion, imagen, estado_descarte, fecha_descarte, observaciones_descarte, tecnico_descarte)
VALUES
('PC Dell Viejo', 4, 'Dell', 'OptiPlex 3020', 'SNDELLOPT3020', 400.00, '2020-01-15', 36, 'inventario', 'Equipo de escritorio obsoleto.', 'delloptiplex.png', 'descarte', '2024-12-01 10:30:00', 'Equipo presenta múltiples fallas: placa madre dañada, fuente de poder quemada, disco duro con sectores defectuosos. No es rentable repararlo debido a su antigüedad y costo de repuestos. Se recomienda descarte definitivo.', 'Juan Pérez'),
('Impresora HP LaserJet', 2, 'HP', 'LaserJet P1006', 'SNHPLJ1006', 120.00, '2019-05-10', 24, 'inventario', 'Impresora láser monocromática.', 'hplaserjet.png', 'descarte', '2024-11-28 14:15:00', 'Impresora con daño severo en el fusor y tambor fotosensible. Los repuestos cuestan más que una impresora nueva. Presenta atascos constantes y calidad de impresión deficiente. No recomendable para uso productivo.', 'Ana García');

-- Inserta primero un departamento para la FK (ajusta el id si ya tienes uno)
INSERT INTO departamentos (nombre, ubicacion) VALUES ('Sistemas', 'Planta Baja');

-- Usuario colaborador (en ambas tablas)
INSERT INTO usuarios (nombre, correo, contrasena, rol, activo)
VALUES ('Juan Pérez', 'colaborador@midominio.com', '$2a$12$KQx/izQ4wb5vKAdk32EdjOm61zK2T3AchFNU1MCFrb0DRTePnH.xG', 'colab', 1);

-- Colaborador vinculado (puedes poner el mismo correo y un departamento_id válido)
INSERT INTO colaboradores (nombre, apellido, identificacion, foto, direccion, ubicacion, telefono, correo, departamento_id, activo)
VALUES ('Juan', 'Pérez', 'C123456', 'juan.jpg', 'Calle 1 #123', 'Oficina A', '5551234567', 'colaborador@midominio.com', 1, 1);

-- Usuario administrador
INSERT INTO usuarios (nombre, correo, contrasena, rol, activo)
VALUES ('Ana García', 'admin@midominio.com', '$2a$12$Pwe/s1iED4iFzrSkKm74veYUzJoimXyfus2q9QB7Opt2ZDKPX26Sa', 'admin', 1);


-- Tabla para entregas de equipos por colaboradores
CREATE TABLE entregas_colaborador (
  id INT AUTO_INCREMENT PRIMARY KEY,
  asignacion_id INT NOT NULL,
  colaborador_id INT NOT NULL,
  inventario_id INT NOT NULL,
  motivo_entrega TEXT NOT NULL,
  tipo_entrega ENUM('traslado','salida','mal_estado','reasignacion','otro') NOT NULL,
  observaciones TEXT,
  fecha_entrega DATETIME NOT NULL,
  estado ENUM('pendiente_validacion','aprobada','rechazada') DEFAULT 'pendiente_validacion',
  usuario_admin_id INT NULL, -- Usuario que valida la entrega
  fecha_validacion DATETIME NULL,
  observaciones_admin TEXT NULL,
  FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id),
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
  FOREIGN KEY (inventario_id) REFERENCES inventario(id),
  FOREIGN KEY (usuario_admin_id) REFERENCES usuarios(id)
);

-- Triggers para automatizar el manejo de descartes
DELIMITER $$

CREATE TRIGGER actualizar_estado_descarte 
BEFORE UPDATE ON inventario
FOR EACH ROW
BEGIN
    -- Si se marca como descarte, actualizar fecha automáticamente
    IF NEW.estado_descarte = 'descarte' AND OLD.estado_descarte = 'activo' THEN
        SET NEW.fecha_descarte = NOW();
    END IF;
    
    -- Si se restaura del descarte, limpiar campos
    IF NEW.estado_descarte = 'activo' AND OLD.estado_descarte = 'descarte' THEN
        SET NEW.fecha_descarte = NULL;
        SET NEW.observaciones_descarte = NULL;
        SET NEW.tecnico_descarte = NULL;
    END IF;
END$$

DELIMITER ;

COMMIT;