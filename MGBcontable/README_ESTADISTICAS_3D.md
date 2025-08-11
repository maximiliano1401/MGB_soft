# 📊 Implementación de Estadísticas 3D en MGBcontable

## 🎯 Descripción General

Se ha implementado un sistema completo de estadísticas en 3D para MGBcontable, basado en el análisis del archivo `statistics.php` de MGBstock. La implementación utiliza **Pyodide** para ejecutar código Python directamente en el navegador web, generando gráficos tridimensionales interactivos.

## 🛠️ Tecnologías Utilizadas

- **PHP** (Backend): Para consultas a la base de datos y API
- **MySQL/MySQLi**: Base de datos y conexión
- **JavaScript**: Frontend y orquestación
- **Pyodide**: Ejecución de Python en el navegador
- **Matplotlib**: Generación de gráficos 3D
- **NumPy**: Cálculos matemáticos
- **HTML5/CSS3**: Interfaz de usuario moderna

## 📁 Archivos Creados

### 1. `estadisticas_3d.php`
**Funcionalidad principal de estadísticas 3D**

#### Características:
- **API REST**: Endpoint `/estadisticas_3d.php?api=data` que devuelve datos en JSON
- **Consultas SQL optimizadas**: Análisis patrimonial, evolución temporal, distribución de cuentas
- **Interfaz moderna**: Design responsive con gradientes y efectos visuales
- **Gráficos 3D**:
  - 📈 Análisis patrimonial por empresa (Barras 3D)
  - ⏰ Evolución temporal de balances (Superficie 3D)
  - 💼 Distribución de cuentas por tipo (Barras agrupadas 3D)
  - 🎯 Análisis de posición financiera (Scatter 3D)

#### Estructura del código:
```php
// API para datos
if (isset($_GET['api']) && $_GET['api'] == 'data') {
    // Consultas SQL con MySQLi
    // Retorna JSON con datos procesados
}

// HTML con Pyodide integration
// JavaScript que carga Pyodide y ejecuta Python
```

### 2. `demo_estadisticas_3d.php`
**Versión demo con datos de ejemplo**

#### Características:
- **Datos simulados**: 4 empresas con información financiera completa
- **Mismos gráficos 3D**: Pero con datos demo para mostrar capacidades
- **Efectos visuales mejorados**: Colores, transparencias, etiquetas
- **Navegación**: Enlaces a la versión real y al menú principal

## 🔧 Implementación Técnica

### Diferencias clave respecto a `statistics.php` original:

| Aspecto | MGBstock (Original) | MGBcontable (Implementado) |
|---------|---------------------|----------------------------|
| **Base de datos** | PDO | MySQLi |
| **Dominio** | Inventarios/Ventas | Contabilidad/Balances |
| **Tablas principales** | `ventas`, `compras`, `productos` | `balance_inicial`, `cuentas` |
| **Gráficos** | 2D con algunos elementos 3D | Totalmente 3D |
| **Datos** | Transaccionales | Patrimoniales |

### Consultas SQL Adaptadas:

```sql
-- Balance por empresa
SELECT 
    bi.empresa,
    COUNT(bid.id) as total_cuentas,
    SUM(CASE WHEN c.tipo = 'Activo' THEN bid.saldo ELSE 0 END) as total_activos,
    SUM(CASE WHEN c.tipo = 'Pasivo' THEN bid.saldo ELSE 0 END) as total_pasivos,
    SUM(CASE WHEN c.tipo = 'Patrimonio' THEN bid.saldo ELSE 0 END) as total_patrimonio
FROM balance_inicial bi
LEFT JOIN balance_inicial_detalle bid ON bi.id = bid.balance_id
LEFT JOIN cuentas c ON bid.cuenta_codigo = c.codigo
GROUP BY bi.empresa
```

### Código Python 3D (Ejemplo):

```python
# Gráfico 3D de barras patrimoniales
fig = plt.figure(figsize=(14, 10))
ax = fig.add_subplot(111, projection='3d')

# Crear barras 3D
ax.bar(x - width, activos, width, label='Activos', color='#2ecc71', alpha=0.9, zdir='y', zs=0)
ax.bar(x, pasivos, width, label='Pasivos', color='#e74c3c', alpha=0.9, zdir='y', zs=1)
ax.bar(x + width, patrimonios, width, label='Patrimonio', color='#3498db', alpha=0.9, zdir='y', zs=2)

ax.view_init(elev=20, azim=45)  # Perspectiva 3D
```

## 🎨 Características de Diseño

### Interfaz de Usuario:
- **Gradientes modernos**: Colores corporativos con efectos visuales
- **Cards responsivas**: Resumen estadístico en tarjetas
- **Botones animados**: Efectos hover y transiciones
- **Loading indicators**: Feedback visual durante carga

### Gráficos 3D:
- **Perspectiva ajustable**: `view_init(elev, azim)`
- **Transparencias**: Para mejor visualización de datos superpuestos
- **Colores consistentes**: Paleta corporativa coherente
- **Etiquetas informativas**: Contexto y explicación de cada gráfico

## 🚀 Instrucciones de Uso

### 1. Acceso desde el menú principal:
```
Menú Principal → 📊 Estadísticas 3D  (datos reales)
Menú Principal → 🚀 Demo Estadísticas 3D  (datos demo)
```

### 2. Funcionalidades disponibles:
- **Generar Gráficos 3D**: Crea visualizaciones basadas en datos actuales
- **Actualizar Datos**: Refresca información desde la base de datos
- **Volver al Menú**: Navegación de regreso

### 3. Requisitos del sistema:
- Navegador moderno con soporte para WebAssembly
- Conexión a internet (para cargar Pyodide CDN)
- Datos en las tablas: `balance_inicial`, `balance_inicial_detalle`, `cuentas`

## 📊 Tipos de Gráficos Implementados

### 1. **Análisis Patrimonial 3D**
- **Tipo**: Barras 3D agrupadas
- **Datos**: Activos, Pasivos, Patrimonio por empresa
- **Perspectiva**: Vista lateral con rotación

### 2. **Evolución Temporal 3D**
- **Tipo**: Superficie 3D
- **Datos**: Balances por empresa a través del tiempo
- **Perspectiva**: Vista aérea con gradiente de colores

### 3. **Distribución de Cuentas 3D**
- **Tipo**: Barras 3D por categorías
- **Datos**: Saldos agrupados por tipo de cuenta
- **Perspectiva**: Vista escalonada por tipo

### 4. **Posición Financiera 3D**
- **Tipo**: Scatter 3D
- **Datos**: Relación Activos-Pasivos-Patrimonio
- **Perspectiva**: Nube de puntos tridimensional

## 🔧 Mantenimiento y Extensibilidad

### Para agregar nuevos gráficos:
1. Crear consulta SQL en la sección API
2. Procesar datos en el frontend JavaScript
3. Agregar código Python para el nuevo gráfico
4. Incluir en el array `charts_html`

### Para modificar estilos:
- Editar las clases CSS en la sección `<style>`
- Ajustar colores en el código Python con `colors = ['#hex1', '#hex2']`
- Cambiar perspectivas con `ax.view_init(elev, azim)`

## 🐛 Resolución de Problemas

### Errores comunes:
1. **Pyodide no carga**: Verificar conexión a internet
2. **Datos vacíos**: Confirmar registros en base de datos
3. **Gráficos no aparecen**: Revisar console del navegador
4. **Consultas SQL fallan**: Verificar estructura de tablas

### Logs y debugging:
- Console del navegador: `F12 → Console`
- PHP errors: Revisar logs del servidor web
- SQL errors: Verificar conexión en `php/conexion.php`

---

## 📝 Conclusión

La implementación exitosa de estadísticas 3D en MGBcontable demuestra la versatilidad del enfoque usado en `statistics.php`. Se adaptó completamente al dominio contable, manteniendo la arquitectura base pero optimizando para:

- ✅ Datos contables específicos
- ✅ Visualizaciones 3D avanzadas
- ✅ Interfaz moderna y responsive
- ✅ Navegación integrada al sistema existente

**Resultado**: Un sistema de análisis visual potente que permite a los usuarios comprender mejor la situación financiera de sus empresas a través de gráficos tridimensionales interactivos.
