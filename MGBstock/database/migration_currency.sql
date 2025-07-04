-- Migración para agregar soporte de monedas por empresa
-- Ejecutar este script si ya tiene la base de datos creada

USE mgbstock;

-- Agregar columnas de moneda a la tabla empresas
ALTER TABLE empresas ADD COLUMN moneda_simbolo VARCHAR(5) DEFAULT '$' AFTER ruc;
ALTER TABLE empresas ADD COLUMN moneda_codigo VARCHAR(3) DEFAULT 'MXN' AFTER moneda_simbolo;
ALTER TABLE empresas ADD COLUMN moneda_nombre VARCHAR(50) DEFAULT 'Peso Mexicano' AFTER moneda_codigo;

-- Actualizar empresa mexicana como principal (ID = 1)
UPDATE empresas SET 
    moneda_simbolo = '$', 
    moneda_codigo = 'MXN', 
    moneda_nombre = 'Peso Mexicano',
    direccion = 'Av. Revolución 456, Ciudad de México',
    ruc = 'ELD870614987'
WHERE id = 1;

-- Actualizar empresa peruana (ID = 2)  
UPDATE empresas SET 
    moneda_simbolo = 'S/', 
    moneda_codigo = 'PEN', 
    moneda_nombre = 'Sol Peruano'
WHERE id = 2;

-- Verificar cambios
SELECT id, nombre, moneda_simbolo, moneda_codigo, moneda_nombre FROM empresas;
