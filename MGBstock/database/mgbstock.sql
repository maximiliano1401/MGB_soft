-- Base de datos para Sistema de Inventario MGBStock
-- Creado: Julio 2025

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS mgbstock;
USE mgbstock;

-- Tabla de administradores
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
);

-- Tabla de empresas
CREATE TABLE empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    ruc VARCHAR(20),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
);

-- Tabla de categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_categoria_empresa (empresa_id, nombre)
);

-- Tabla de productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    categoria_id INT NOT NULL,
    codigo VARCHAR(50),
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_compra DECIMAL(10,2) DEFAULT 0.00,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_codigo_empresa (empresa_id, codigo)
);

-- Tabla de ventas
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de compras
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    proveedor VARCHAR(100),
    notas TEXT,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de movimientos de stock (para auditoria)
CREATE TABLE movimientos_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    producto_id INT NOT NULL,
    tipo_movimiento ENUM('VENTA', 'COMPRA', 'AJUSTE') NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    referencia_id INT, -- ID de la venta, compra o ajuste
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Índices para mejorar rendimiento
CREATE INDEX idx_productos_empresa ON productos(empresa_id);
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_ventas_empresa ON ventas(empresa_id);
CREATE INDEX idx_ventas_producto ON ventas(producto_id);
CREATE INDEX idx_ventas_fecha ON ventas(fecha_venta);
CREATE INDEX idx_compras_empresa ON compras(empresa_id);
CREATE INDEX idx_compras_producto ON compras(producto_id);
CREATE INDEX idx_compras_fecha ON compras(fecha_compra);
CREATE INDEX idx_movimientos_empresa ON movimientos_stock(empresa_id);
CREATE INDEX idx_movimientos_producto ON movimientos_stock(producto_id);

-- Insertar administrador por defecto
INSERT INTO administradores (usuario, password, nombre, email) 
VALUES ('admin', MD5('admin123'), 'Administrador Principal', 'admin@mgbstock.com');

-- Insertar datos de ejemplo
INSERT INTO empresas (nombre, direccion, telefono, email, ruc) VALUES
('TechnoSoft S.A.', 'Av. Principal 123, Lima', '01-2345678', 'info@technosoft.com', '20123456789'),
('Comercial El Dorado', 'Jr. Los Olivos 456, Arequipa', '054-987654', 'ventas@eldorado.com', '20987654321');

INSERT INTO categorias (empresa_id, nombre, descripcion) VALUES
(1, 'Software', 'Productos de software y licencias'),
(1, 'Hardware', 'Equipos y componentes de hardware'),
(2, 'Electrodomésticos', 'Electrodomésticos para el hogar'),
(2, 'Electrónicos', 'Dispositivos electrónicos');

INSERT INTO productos (empresa_id, categoria_id, codigo, nombre, descripcion, precio_venta, precio_compra, stock_actual, stock_minimo) VALUES
(1, 1, 'SW001', 'Microsoft Office 365', 'Suite de oficina completa', 299.99, 200.00, 50, 10),
(1, 2, 'HW001', 'Laptop Dell Inspiron', 'Laptop 15.6 pulgadas, 8GB RAM', 2599.99, 2000.00, 25, 5),
(2, 3, 'ED001', 'Refrigerador LG', 'Refrigerador 300L No Frost', 1899.99, 1400.00, 15, 3),
(2, 4, 'EL001', 'Smartphone Samsung', 'Galaxy A54 128GB', 899.99, 650.00, 30, 8);

-- Triggers para mantener el control de stock
DELIMITER //

CREATE TRIGGER after_venta_insert 
AFTER INSERT ON ventas
FOR EACH ROW
BEGIN
    DECLARE stock_anterior INT;
    
    -- Obtener stock anterior
    SELECT stock_actual INTO stock_anterior 
    FROM productos 
    WHERE id = NEW.producto_id;
    
    -- Actualizar stock del producto
    UPDATE productos 
    SET stock_actual = stock_actual - NEW.cantidad 
    WHERE id = NEW.producto_id;
    
    -- Registrar movimiento
    INSERT INTO movimientos_stock (empresa_id, producto_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, referencia_id, notas)
    VALUES (NEW.empresa_id, NEW.producto_id, 'VENTA', -NEW.cantidad, stock_anterior, stock_anterior - NEW.cantidad, NEW.id, 'Venta automatica');
END//

CREATE TRIGGER after_compra_insert 
AFTER INSERT ON compras
FOR EACH ROW
BEGIN
    DECLARE stock_anterior INT;
    
    -- Obtener stock anterior
    SELECT stock_actual INTO stock_anterior 
    FROM productos 
    WHERE id = NEW.producto_id;
    
    -- Actualizar stock del producto
    UPDATE productos 
    SET stock_actual = stock_actual + NEW.cantidad,
        precio_compra = NEW.precio_unitario
    WHERE id = NEW.producto_id;
    
    -- Registrar movimiento
    INSERT INTO movimientos_stock (empresa_id, producto_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, referencia_id, notas)
    VALUES (NEW.empresa_id, NEW.producto_id, 'COMPRA', NEW.cantidad, stock_anterior, stock_anterior + NEW.cantidad, NEW.id, CONCAT('Compra de proveedor: ', IFNULL(NEW.proveedor, 'Sin especificar')));
END//

DELIMITER ;
