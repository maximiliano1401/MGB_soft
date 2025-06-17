-- Ejemplo de catálogo de cuentas contables para Balance General

-- Activos
INSERT INTO cuentas (codigo, nombre, tipo) VALUES
('1101', 'Caja', 'Activo'),
('1102', 'Bancos', 'Activo'),
('1103', 'Cuentas por Cobrar', 'Activo'),
('1104', 'Documentos por Cobrar', 'Activo'),
('1105', 'Anticipos a Proveedores', 'Activo'),
('1201', 'Clientes', 'Activo'),
('1202', 'Inventarios', 'Activo'),
('1203', 'Inventario de Mercancías', 'Activo'),
('1204', 'Inventario de Materia Prima', 'Activo'),
('1205', 'Inventario de Productos Terminados', 'Activo'),
('1301', 'Terrenos', 'Activo'),
('1302', 'Edificios', 'Activo'),
('1303', 'Mobiliario y Equipo', 'Activo'),
('1304', 'Equipo de Cómputo', 'Activo'),
('1305', 'Vehículos', 'Activo'),
('1401', 'Depreciación Acumulada', 'Activo'),
('1501', 'Gastos Pagados por Anticipado', 'Activo'),
('1502', 'Seguros Pagados por Anticipado', 'Activo'),
('1503', 'Rentas Pagadas por Anticipado', 'Activo');

-- Pasivos
INSERT INTO cuentas (codigo, nombre, tipo) VALUES
('2101', 'Proveedores', 'Pasivo'),
('2102', 'Acreedores', 'Pasivo'),
('2103', 'Documentos por Pagar', 'Pasivo'),
('2104', 'Acreedores Diversos', 'Pasivo'),
('2105', 'Anticipos de Clientes', 'Pasivo'),
('2201', 'Préstamos Bancarios', 'Pasivo'),
('2202', 'Impuestos por Pagar', 'Pasivo'),
('2203', 'Sueldos por Pagar', 'Pasivo'),
('2204', 'Intereses por Pagar', 'Pasivo'),
('2205', 'Provisiones para Impuestos', 'Pasivo'),
('2301', 'Hipotecas por Pagar', 'Pasivo'),
('2302', 'Arrendamientos por Pagar', 'Pasivo');

-- Patrimonio
INSERT INTO cuentas (codigo, nombre, tipo) VALUES
('3101', 'Capital Social', 'Patrimonio'),
('3201', 'Utilidades Retenidas', 'Patrimonio'),
('3202', 'Resultados del Ejercicio', 'Patrimonio'),
('3301', 'Aportaciones de Socios', 'Patrimonio'),
('3302', 'Reserva Legal', 'Patrimonio'),
('3303', 'Reserva Estatutaria', 'Patrimonio'),
('3304', 'Reserva Voluntaria', 'Patrimonio'),
('3401', 'Pérdidas Acumuladas', 'Patrimonio');
