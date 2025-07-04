# MGBStock - Sistema de Inventario Multiempresa

Un sistema completo de gestión de inventario con soporte para múltiples empresas y **monedas configurables por empresa**. Desarrollado con HTML, CSS, JavaScript, PHP, MySQL y Python (Pyodide) para estadísticas avanzadas.

## 🚀 Características Principales

### 🔐 Sistema de Autenticación
- Login seguro para administradores
- Verificación en base de datos con encriptación MD5
- Gestión de sesiones segura

### 🏢 Gestión Multi-Empresa
- Registro y administración de múltiples empresas
- **Configuración de moneda por empresa** (Símbolo, código ISO, nombre)
- Selección de empresa para trabajar
- Datos aislados por empresa

### � Sistema de Monedas Configurables
- **Sol Peruano (S/ - PEN)** - Por defecto
- **Peso Mexicano ($ - MXN)**
- **Dólar Estadounidense ($ - USD)**
- **Euro (€ - EUR)**
- **Peso Colombiano ($ - COP)**
- **Peso Argentino ($ - ARS)**
- **Peso Chileno ($ - CLP)**
- **Boliviano (Bs - BOB)**
- **Bolívar Venezolano (Bs - VES)**
- **Fácil extensión para nuevas monedas**

### �📦 Gestión de Inventario
- **Categorías**: Organización de productos por categorías
- **Productos**: Control completo de productos con códigos únicos
- **Stock**: Control automático de stock con alertas de stock bajo
- **Precios**: Gestión de precios en la moneda de cada empresa

### 🛒 Operaciones Comerciales
- **Ventas**: Registro de ventas con validación de stock y precios en moneda local
- **Compras**: Registro de compras con actualización automática de stock
- **Movimientos**: Historial completo de movimientos de stock

### 📊 Estadísticas Avanzadas
- Gráficos generados con Python y Pyodide
- **Todos los gráficos usan la moneda de la empresa seleccionada**
- Análisis de ventas por período
- Productos más vendidos
- Comparativa ventas vs compras
- Distribución por categorías

## 🛠️ Tecnologías Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+
- **Gráficos**: Python con Matplotlib (Pyodide)
- **Servidor**: Apache (XAMPP)

## 📋 Requisitos del Sistema

### Servidor Web
- Apache 2.4+
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones PHP: PDO, PDO_MySQL

### Cliente Web
- Navegador moderno compatible con ES6
- JavaScript habilitado
- Conexión a internet (para cargar Pyodide)

## 🚀 Instalación

### 🆕 Instalación Nueva

#### 1. Configurar XAMPP
```bash
# Descargar e instalar XAMPP
# Iniciar Apache y MySQL desde el panel de XAMPP
```

#### 2. Clonar/Copiar el Proyecto
```bash
# Copiar el proyecto en la carpeta htdocs de XAMPP
C:\xampp\htdocs\MGB_soft\MGBstock\
```

#### 3. Configurar la Base de Datos

##### Opción A: Usando phpMyAdmin
1. Abrir http://localhost/phpmyadmin
2. Crear nueva base de datos llamada `mgbstock`
3. Importar el archivo `database/mgbstock.sql`

##### Opción B: Usando línea de comandos
```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos
CREATE DATABASE mgbstock;

# Importar estructura
mysql -u root -p mgbstock < database/mgbstock.sql
```

### 🔄 Migración de Sistema Existente

Si ya tiene MGBStock instalado y quiere agregar soporte de monedas:

#### 1. Respaldar Base de Datos (Recomendado)
```sql
mysqldump -u root -p mgbstock > backup_mgbstock.sql
```

#### 2. Ejecutar Script de Migración
```sql
mysql -u root -p mgbstock < database/migration_currency.sql
```

#### 3. Verificar Migración
```sql
-- En phpMyAdmin o línea de comandos
DESCRIBE empresas;
-- Debe mostrar las nuevas columnas: moneda_simbolo, moneda_codigo, moneda_nombre
```

### 4. Configurar Conexión
Editar el archivo `includes/config.php` si es necesario:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'mgbstock');
define('DB_USER', 'root');
define('DB_PASS', ''); // Cambiar si tienes contraseña
```

### 5. Acceder al Sistema
Abrir en el navegador: http://localhost/MGB_soft/MGBstock/

## 🔑 Credenciales por Defecto

**Usuario**: `admin`  
**Contraseña**: `admin123`

## 📁 Estructura del Proyecto

```
MGBstock/
├── assets/
│   ├── css/
│   │   └── main.css          # Estilos principales
│   └── js/
│       └── main.js           # JavaScript principal
├── database/
│   └── mgbstock.sql          # Script de base de datos
├── includes/
│   └── config.php            # Configuración y funciones
├── modules/
│   ├── categories.php        # Gestión de categorías
│   ├── companies.php         # Gestión de empresas
│   ├── products.php          # Gestión de productos
│   ├── purchases.php         # Gestión de compras
│   ├── sales.php             # Gestión de ventas
│   ├── select_company.php    # Selección de empresa
│   └── statistics.php        # Estadísticas con Pyodide
├── dashboard.php             # Panel principal
├── index.php                 # Página de login
├── logout.php                # Cerrar sesión
└── README.md                 # Esta documentación
```

## 🎯 Guía de Uso

### 💰 Configuración de Monedas por Empresa

#### Crear Nueva Empresa con Moneda
1. Ir a **"Empresas"** → **"Agregar Empresa"**
2. Completar datos básicos (nombre, dirección, teléfono, etc.)
3. **Seleccionar Moneda** del dropdown:
   - Sol Peruano (S/)
   - Peso Mexicano ($)
   - Dólar Estadounidense ($)
   - Euro (€)
   - Y otras monedas disponibles
4. Guardar empresa

#### Editar Moneda de Empresa Existente
1. En la lista de empresas, hacer clic en **"Editar"**
2. Modificar la moneda seleccionada
3. Los cambios se aplican inmediatamente a todos los precios y reportes

#### Funcionamiento Automático
- Una vez configurada la moneda, **toda la empresa** usa ese símbolo:
  - Precios de productos (venta/compra)
  - Totales de ventas y compras
  - Estadísticas y gráficos
  - Reportes de ingresos

### 1. Primer Acceso
1. Acceder con las credenciales por defecto
2. Registrar la primera empresa
3. Seleccionar la empresa para trabajar

### 2. Configuración Inicial
1. **Categorías**: Crear categorías de productos
2. **Productos**: Agregar productos con stock inicial
3. **Precios**: Configurar precios de venta y compra

### 3. Operaciones Diarias
1. **Ventas**: Registrar ventas (reduce stock automáticamente)
2. **Compras**: Registrar compras (aumenta stock automáticamente)
3. **Control**: Monitorear alertas de stock bajo

### 4. Análisis y Reportes
1. **Dashboard**: Ver resumen general
2. **Estadísticas**: Generar gráficos con Python
3. **Tendencias**: Analizar ventas y compras por período

## 🔧 Funcionalidades Técnicas

### Control de Stock Automático
- Triggers de MySQL para control de stock
- Validación de stock en ventas
- Historial de movimientos de stock

### Seguridad
- Sanitización de entradas
- Sesiones seguras
- Validación de permisos por empresa

### Responsive Design
- Diseño adaptable a móviles
- Interfaz moderna con CSS Grid y Flexbox
- Experiencia de usuario optimizada

### API REST
- Endpoints para obtener datos de estadísticas
- Formato JSON para intercambio de datos
- Integración con Pyodide para gráficos

## 📊 Módulo de Estadísticas

El sistema incluye un módulo avanzado de estadísticas que utiliza **Pyodide** para ejecutar Python en el navegador y generar gráficos con **Matplotlib**.

### Gráficos Disponibles
1. **Ventas por Mes**: Evolución temporal de ventas
2. **Productos Más Vendidos**: Ranking de productos
3. **Ventas por Categoría**: Distribución en gráfico circular
4. **Ventas vs Compras**: Comparativa mensual

### Carga de Pyodide
```javascript
// El sistema carga automáticamente:
// - Pyodide runtime
// - Matplotlib para gráficos
// - NumPy para cálculos
```

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
```
Error: Connection failed: Access denied
```
**Solución**: Verificar credenciales en `includes/config.php`

### Error de Pyodide
```
Error al cargar el sistema de gráficos
```
**Solución**: Verificar conexión a internet y navegador compatible

### Stock Negativo
```
El sistema no permite ventas que excedan el stock
```
**Solución**: Registrar compras para aumentar stock

### Empresa No Seleccionada
```
Debe seleccionar una empresa primero
```
**Solución**: Ir a módulo de empresas y seleccionar una

## 📝 Datos de Ejemplo

El sistema incluye datos de ejemplo:
- 2 empresas de muestra
- 4 categorías básicas
- 4 productos con stock inicial
- Usuario administrador por defecto

## 🔄 Actualizaciones Futuras

### Funcionalidades Planeadas
- [ ] Reportes en PDF
- [ ] Códigos de barras
- [ ] Múltiples monedas
- [ ] API REST completa
- [ ] Aplicación móvil
- [ ] Notificaciones push
- [ ] Backup automático

### Mejoras Técnicas
- [ ] Encriptación avanzada
- [ ] Cache de consultas
- [ ] Optimización de rendimiento
- [ ] Tests automatizados

## 👥 Soporte

Para soporte técnico o consultas:
- **Desarrollador**: MGBSoft
- **Email**: Configurar según necesidades
- **Fecha**: Julio 2025

## 📄 Licencia

Este proyecto es de uso educativo y comercial. Desarrollado como sistema completo de inventario con tecnologías modernas.

---

**MGBStock v1.0** - Sistema Completo de Inventario  
Desarrollado con ❤️ por MGBSoft
