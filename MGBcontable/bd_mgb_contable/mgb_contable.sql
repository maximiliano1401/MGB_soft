-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-05-2025 a las 01:32:58
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mgb_contable`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asientos_contables`
--

CREATE TABLE `asientos_contables` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `balance_inicial`
--

CREATE TABLE `balance_inicial` (
  `id` int(11) NOT NULL,
  `empresa` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `notas` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `balance_inicial`
--

INSERT INTO `balance_inicial` (`id`, `empresa`, `fecha`, `notas`, `creado_por`, `creado_en`) VALUES
(1, 'MGB', '2025-05-25', 'Hola mundo', 2, '2025-05-27 22:06:28'),
(2, 'MGB', '2025-05-25', 'Hola Mundo 2', 2, '2025-05-27 22:10:10'),
(3, 'RGB', '2024-12-31', 'hOLA MUNDO 3', 2, '2025-05-27 22:15:02'),
(4, 'RGB', '2025-05-25', '', 2, '2025-05-27 22:18:45'),
(5, 'Amorchito', '2025-05-28', '', 2, '2025-05-28 20:18:31'),
(6, '1', '2025-05-25', '', 2, '2025-05-30 23:29:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `balance_inicial_detalle`
--

CREATE TABLE `balance_inicial_detalle` (
  `id` int(11) NOT NULL,
  `balance_id` int(11) NOT NULL,
  `cuenta_codigo` varchar(10) NOT NULL,
  `saldo` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `balance_inicial_detalle`
--

INSERT INTO `balance_inicial_detalle` (`id`, `balance_id`, `cuenta_codigo`, `saldo`) VALUES
(1, 2, '1101', 2000.00),
(3, 2, '3101', 500.00),
(4, 3, '1101', 800.00),
(5, 3, '2101', 400.00),
(6, 3, '3101', 400.00),
(7, 4, '1101', 800.00),
(8, 4, '2101', 400.00),
(9, 4, '3101', 400.00),
(11, 2, '1101', 2000.00),
(12, 2, '2101', 4000.00),
(13, 2, '3303', 500.00),
(14, 2, '1501', 1000.00),
(15, 5, '1303', 15000.25),
(16, 5, '2101', 10000.00),
(17, 5, '2201', 5000.25),
(18, 6, '1101', 100.00),
(19, 6, '2101', 200.00),
(20, 6, '3101', 100.00),
(21, 6, '1102', 200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `archivo_factura` varchar(255) DEFAULT NULL,
  `pagada` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `proveedor`, `fecha`, `monto`, `concepto`, `archivo_factura`, `pagada`) VALUES
(1, 'KFC', '2025-05-27', 5000.00, 'Pollos', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas`
--

CREATE TABLE `cuentas` (
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('Activo','Pasivo','Patrimonio') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cuentas`
--

INSERT INTO `cuentas` (`codigo`, `nombre`, `tipo`) VALUES
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
('1503', 'Rentas Pagadas por Anticipado', 'Activo'),
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
('2302', 'Arrendamientos por Pagar', 'Pasivo'),
('3101', 'Capital Social', 'Patrimonio'),
('3201', 'Utilidades Retenidas', 'Patrimonio'),
('3202', 'Resultados del Ejercicio', 'Patrimonio'),
('3301', 'Aportaciones de Socios', 'Patrimonio'),
('3302', 'Reserva Legal', 'Patrimonio'),
('3303', 'Reserva Estatutaria', 'Patrimonio'),
('3304', 'Reserva Voluntaria', 'Patrimonio'),
('3401', 'Pérdidas Acumuladas', 'Patrimonio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `puesto` varchar(100) DEFAULT NULL,
  `salario` decimal(12,2) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rfc` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id`, `nombre`, `rfc`, `direccion`, `telefono`) VALUES
(1, 'RGB', 'BSDFEW89', 'Calle falta', '9876543210');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `importaciones_exportaciones`
--

CREATE TABLE `importaciones_exportaciones` (
  `id` int(11) NOT NULL,
  `tipo` enum('importacion','exportacion') DEFAULT NULL,
  `modulo` varchar(50) DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `impuestos`
--

CREATE TABLE `impuestos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `periodo` varchar(20) DEFAULT NULL,
  `monto` decimal(15,2) DEFAULT NULL,
  `fecha_presentacion` date DEFAULT NULL,
  `pagado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventarios`
--

CREATE TABLE `inventarios` (
  `id` int(11) NOT NULL,
  `producto` varchar(100) NOT NULL,
  `metodo` enum('PEPS','UEPS','PROMEDIO') DEFAULT 'PEPS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventarios`
--

INSERT INTO `inventarios` (`id`, `producto`, `metodo`) VALUES
(1, 'Mochilas', 'PEPS'),
(2, 'Tus hijos', 'PEPS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kardex`
--

CREATE TABLE `kardex` (
  `id` int(11) NOT NULL,
  `inventario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('SALDO INICIAL','ENTRADA','SALIDA') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `kardex`
--

INSERT INTO `kardex` (`id`, `inventario_id`, `fecha`, `tipo`, `cantidad`, `costo_unitario`, `documento`, `responsable`, `descripcion`) VALUES
(1, 1, '2025-05-04', 'SALDO INICIAL', 500.00, 50.00, '', '', ''),
(2, 1, '2025-05-26', 'ENTRADA', 1500.00, 50.00, '', '', ''),
(3, 1, '2025-05-25', 'SALIDA', 12.00, 10.00, '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_diario`
--

CREATE TABLE `movimientos_diario` (
  `id` int(11) NOT NULL,
  `asiento_id` int(11) NOT NULL,
  `cuenta_codigo` varchar(10) NOT NULL,
  `debe` decimal(15,2) DEFAULT 0.00,
  `haber` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nomina`
--

CREATE TABLE `nomina` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `fecha_pago` date NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `concepto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','usuario') DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`) VALUES
(2, 'Administrador', 'admin@outlook.com', '$2y$10$IJFKiUfT7D8tXuFzWYWrRO.shTqbCT8TmmGZj2EL3/e7npO5QgMBG', 'admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cliente` varchar(100) DEFAULT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `archivo_factura` varchar(255) DEFAULT NULL,
  `cobrada` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asientos_contables`
--
ALTER TABLE `asientos_contables`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `balance_inicial`
--
ALTER TABLE `balance_inicial`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `balance_inicial_detalle`
--
ALTER TABLE `balance_inicial_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balance_id` (`balance_id`),
  ADD KEY `cuenta_codigo` (`cuenta_codigo`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inventarios`
--
ALTER TABLE `inventarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventario_id` (`inventario_id`);

--
-- Indices de la tabla `movimientos_diario`
--
ALTER TABLE `movimientos_diario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asiento_id` (`asiento_id`),
  ADD KEY `cuenta_codigo` (`cuenta_codigo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `balance_inicial`
--
ALTER TABLE `balance_inicial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `balance_inicial_detalle`
--
ALTER TABLE `balance_inicial_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inventarios`
--
ALTER TABLE `inventarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `kardex`
--
ALTER TABLE `kardex`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `movimientos_diario`
--
ALTER TABLE `movimientos_diario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `balance_inicial_detalle`
--
ALTER TABLE `balance_inicial_detalle`
  ADD CONSTRAINT `balance_inicial_detalle_ibfk_1` FOREIGN KEY (`balance_id`) REFERENCES `balance_inicial` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `balance_inicial_detalle_ibfk_2` FOREIGN KEY (`cuenta_codigo`) REFERENCES `cuentas` (`codigo`);

--
-- Filtros para la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `kardex_ibfk_1` FOREIGN KEY (`inventario_id`) REFERENCES `inventarios` (`id`);

--
-- Filtros para la tabla `movimientos_diario`
--
ALTER TABLE `movimientos_diario`
  ADD CONSTRAINT `movimientos_diario_ibfk_1` FOREIGN KEY (`asiento_id`) REFERENCES `asientos_contables` (`id`),
  ADD CONSTRAINT `movimientos_diario_ibfk_2` FOREIGN KEY (`cuenta_codigo`) REFERENCES `cuentas` (`codigo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
