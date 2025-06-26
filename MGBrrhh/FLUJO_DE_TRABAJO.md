# Flujo de Trabajo del Sistema de Recursos Humanos MGB

## 🔐 1. Autenticación
- **Inicio**: Acceder a `login.php`
- **Credenciales**: Usuario y contraseña
- **Primera vez**: Registrar usuario en `registro_admin.php`

## 🏢 2. Configuración Inicial (Administrador)

### A. Registrar Empresa
1. Ir a **Dashboard** → `Registrar Empresa`
2. Completar información:
   - Nombre de empresa *
   - Razón social *
   - RFC *
   - Régimen fiscal
   - Dirección completa

### B. Crear Departamentos
1. Ir a **Dashboard** → `Departamentos/Puestos`
2. Seleccionar "Departamento"
3. Asignar a empresa (opcional)
4. Ejemplos: Contabilidad, Recursos Humanos, Ventas

### C. Definir Puestos de Trabajo
1. En el mismo formulario, seleccionar "Puesto"
2. Asociar al departamento correspondiente
3. Ejemplos: Contador, Gerente de RRHH, Vendedor

## 👥 3. Gestión de Personal

### A. Registrar Empleados
1. Ir a **Dashboard** → `Registrar Empleados`
2. Completar información personal:
   - Datos personales completos
   - RFC, CURP, IMSS
   - Asignar departamento y puesto
3. **Automático**: Se da de alta automáticamente al empleado

### B. Ver y Gestionar Empleados
1. Ir a **Dashboard** → `Ver Empleados`
2. **Acciones disponibles por empleado**:
   - ✏️ **Editar**: Modificar información
   - ❌ **Dar de baja**: Inactivar empleado
   - 🏖️ **Vacaciones**: Registrar período vacacional
   - 🏥 **Incapacidad**: Registrar incapacidad médica
   - ⚠️ **Falta**: Registrar ausencia injustificada

## 📋 4. Gestión de Ausencias

### Tipos de Ausencia:
- **Vacaciones**: Descanso programado
- **Incapacidad**: Por motivos médicos
- **Falta**: Ausencia injustificada

### Proceso:
1. Desde la vista de empleados, click en el botón correspondiente
2. Completar formulario modal:
   - Fecha de inicio
   - Fecha de fin
   - Motivo (opcional)
3. El sistema calcula automáticamente los días

## 💰 5. Gestión de Nómina

### A. Configurar Nómina
1. Ir a **Dashboard** → `Gestionar Nómina`
2. Seleccionar empleado
3. Configurar:
   - Sueldo base
   - Periodicidad (semanal, quincenal, mensual)
   - Fecha de inicio

### B. Consultar Nóminas
- Ver todas las nóminas activas/inactivas
- Información detallada por empleado

## 🔄 6. Altas y Bajas Manuales

### Para casos especiales:
1. Ir a **Dashboard** → `Gestionar Altas/Bajas`
2. **Alta manual**: Para empleados que regresan
3. **Baja manual**: Para casos especiales con motivo detallado

## 📊 7. Consultas y Reportes

### Dashboards Disponibles:
- **Estadísticas generales**: Total empleados, activos, ausencias
- **Vista de empresas**: Todas las empresas registradas
- **Vista de departamentos**: Estructura organizacional
- **Vista de ausencias**: Historial completo de ausencias
- **Vista de altas/bajas**: Movimientos de personal

## 🎯 Flujo Típico Diario:

1. **Login** → Dashboard
2. **Revisar estadísticas** del día
3. **Registrar ausencias** del día (si las hay)
4. **Consultar empleados** si es necesario
5. **Gestionar nuevos empleados** (si los hay)

## 🔧 Características Técnicas:

- **Autenticación**: Obligatoria en todas las páginas
- **Sesiones**: Manejo automático de sesiones
- **Validaciones**: Cliente y servidor
- **Base de datos**: Relaciones consistentes
- **Interfaz**: Responsiva y moderna
- **Acciones rápidas**: Modales para operaciones frecuentes

## ⚡ Acciones Rápidas desde Vista de Empleados:

- **Vacaciones**: Modal rápido con fechas
- **Incapacidades**: Registro inmediato
- **Faltas**: Un clic y listo
- **Bajas**: Confirmación con motivo
- **Edición**: Acceso directo al formulario

Este flujo garantiza una gestión eficiente y completa del personal, desde la estructura organizacional hasta el día a día operativo.
