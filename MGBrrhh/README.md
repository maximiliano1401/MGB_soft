# Sistema de Recursos Humanos MGB

Sistema completo de gestión de recursos humanos desarrollado en PHP y MySQL, que permite administrar empleados, departamentos, empresas, nóminas, ausencias y altas/bajas de personal.

## Estructura de la Base de Datos

La base de datos `mgb_rrhh` incluye las siguientes tablas:

### Tablas Principales
- **empresas**: Información de las empresas del grupo
- **departamentos**: Departamentos organizacionales (relacionados con empresas)
- **puestos**: Puestos de trabajo (relacionados con departamentos)
- **empleados**: Información personal y laboral de empleados
- **usuarios**: Sistema de usuarios para el acceso al sistema

### Tablas de Gestión
- **nomina**: Información salarial y periodicidad de pago
- **ausencias**: Registro de vacaciones, incapacidades y faltas
- **altas_bajas**: Control de altas y bajas de empleados

## Funcionalidades

### 1. Gestión de Empresas
- Registro de empresas con información fiscal y dirección completa
- Visualización de empresas registradas
- Archivo: `registro_empresas.php`, `Ver_Registrados/Ver_Registro_Empresas.php`

### 2. Gestión de Departamentos y Puestos
- Registro de departamentos (opcionalmente asociados a empresas)
- Registro de puestos (asociados a departamentos)
- Filtrado dinámico de puestos por departamento
- Archivo: `registro_departamentos_puestos.php`, `Ver_Registrados/Ver_Registro_puestos.php`

### 3. Gestión de Empleados
- Registro completo de empleados con datos personales y laborales
- Asignación a departamentos y puestos
- Validación de campos obligatorios
- Archivo: `registro_empleados.php`, `Ver_Registrados/ver_Registro_Empleados.php`

### 4. Gestión de Nómina
- Registro de información salarial
- Configuración de periodicidad de pago (semanal, quincenal, mensual)
- Control de nóminas activas/inactivas
- Archivo: `registro_nomina.php`, `Ver_Registrados/Ver_Registro_Nomina.php`

### 5. Gestión de Ausencias
- Registro de vacaciones, incapacidades y faltas
- Control de fechas y cálculo automático de días
- Archivo: `registro_ausencias.php`, `Ver_Registrados/Ver_Registro_Ausencias.php`

### 6. Altas y Bajas
- Registro de altas de empleados
- Gestión de bajas con causa justificada
- Control de estados activo/inactivo
- Archivo: `registro_altas_bajas.php`, `Ver_Registrados/Ver_Registro_Altas_Bajas.php`

### 7. Gestión de Usuarios
- Sistema simplificado de usuarios con contraseñas encriptadas
- Archivo: `registro_admin.php`, `Ver_Registrados/Ver_Registro_Admin.php`

## Estructura de Archivos

```
MGBrrhh/
├── conexion.php              # Configuración de base de datos
├── index.html                # Página principal
├── registro_*.php            # Archivos de registro
├── Ver_Registrados/          # Archivos de visualización
│   ├── ver_Registro_Empleados.php
│   ├── Ver_Registro_*.php
├── css/                      # Estilos CSS
├── IMG/                      # Imágenes y logos
├── bd_mgb_rrhh/             # Base de datos
│   └── mgb_rrhh.sql
└── editar/                   # Funcionalidades de edición
    └── empleados/
```

## Configuración

### Base de Datos
1. Importar el archivo `bd_mgb_rrhh/mgb_rrhh.sql` en MySQL
2. Verificar la configuración en `conexion.php`:
   - Host: localhost
   - Usuario: root
   - Contraseña: (vacía)
   - Base de datos: mgb_rrhh

### Servidor Web
- Requiere Apache con PHP habilitado
- Recomendado: XAMPP, WAMP o similar
- Colocar el proyecto en la carpeta `htdocs`

## Características Técnicas

- **Backend**: PHP con MySQLi
- **Frontend**: HTML5, CSS3, JavaScript
- **Base de Datos**: MySQL/MariaDB
- **Seguridad**: Contraseñas encriptadas con `password_hash()`
- **Validación**: Validación tanto del lado cliente como servidor
- **Diseño**: Responsivo y moderno
- **Estilo**: Paleta de colores corporativa (azul #005baa, cian #00bcd4)

## Flujo de Trabajo Recomendado

1. **Configurar empresas** (si aplica)
2. **Crear departamentos** y asociarlos a empresas
3. **Definir puestos** para cada departamento
4. **Registrar empleados** asignándolos a departamentos y puestos
5. **Configurar nóminas** para empleados activos
6. **Gestionar altas** de empleados nuevos
7. **Registrar ausencias** cuando sea necesario
8. **Procesar bajas** cuando corresponda

## Navegación

La interfaz principal (`index.html`) proporciona acceso directo a:
- Formularios de registro para cada entidad
- Vistas de consulta para revisar información registrada
- Navegación intuitiva entre módulos

## Características de Seguridad

- Validación de entrada de datos
- Uso de prepared statements para prevenir SQL injection
- Encriptación de contraseñas
- Validación de tipos de datos

Este sistema proporciona una solución completa para la gestión de recursos humanos, manteniendo la coherencia con la estructura de base de datos definida y siguiendo buenas prácticas de desarrollo web.
