# MGBStock - Ejemplos de Uso del Sistema de Monedas

Este documento muestra ejemplos visuales de cómo funciona el sistema de monedas configurable por empresa.

## Configuración de Empresas

### Empresa Mexicana - Comercial El Dorado (Principal)
```
Nombre: Comercial El Dorado
País: México
Moneda: Peso Mexicano ($ - MXN) - POR DEFECTO
```

### Empresa Peruana - TechnoSoft S.A.
```
Nombre: TechnoSoft S.A.
País: Perú
Moneda: Sol Peruano (S/ - PEN)
```

## Ejemplos de Visualización

### 1. Lista de Productos

**Empresa Mexicana (Comercial El Dorado)**:
```
Producto: Refrigerador LG
Precio Venta: $ 1,899.99
Stock: 15 unidades
```

**Empresa Peruana (TechnoSoft S.A.)**:
```
Producto: Microsoft Office 365
Precio Venta: S/ 299.99
Stock: 50 unidades
```

### 2. Registro de Ventas

**Empresa Mexicana**:
```
Producto: Smartphone Samsung
Cantidad: 3
Precio Unitario: $ 899.99
Total: $ 2,699.97
```

**Empresa Peruana**:
```
Producto: Laptop Dell Inspiron
Cantidad: 2
Precio Unitario: S/ 2,599.99
Total: S/ 5,199.98
```

### 3. Estadísticas y Gráficos

**Empresa Peruana - Resumen**:
```
📊 ESTADÍSTICAS - TECHNOSOFT S.A.
================================
Total Productos: 25
Total Ventas: 48
Ingresos Totales: S/ 125,847.50
```

**Empresa Mexicana - Resumen**:
```
📊 ESTADÍSTICAS - COMERCIAL EL DORADO
====================================
Total Productos: 18
Total Ventas: 35
Ingresos Totales: $ 89,450.00
```

### 4. Gráficos de Ventas por Mes

**Empresa Peruana**:
- Eje Y: "Total de Ventas (S/)"
- Etiquetas de barras: "S/ 15,450.00", "S/ 22,100.50", etc.

**Empresa Mexicana**:
- Eje Y: "Total de Ventas ($)"
- Etiquetas de barras: "$ 12,300.00", "$ 18,750.00", etc.

### 5. Comparativa Ventas vs Compras

**Empresa Peruana**:
```
Eje Y: Monto (S/)
Barras Verdes (Ventas): S/ 45,000
Barras Rojas (Compras): S/ 32,000
```

**Empresa Mexicana**:
```
Eje Y: Monto ($)
Barras Verdes (Ventas): $ 38,500
Barras Rojas (Compras): $ 28,000
```

## Flujo de Trabajo

### Escenario 1: Administrador trabajando con empresa mexicana (principal)
1. Selecciona "Comercial El Dorado"
2. Ve todos los precios en Pesos Mexicanos ($)
3. Registra una venta: $ 1,899.99
4. Ve estadísticas en Pesos
5. Los gráficos muestran montos en $

### Escenario 2: Mismo administrador cambia a empresa peruana
1. Selecciona "TechnoSoft S.A."
2. Ve todos los precios en Soles (S/)
3. Registra una venta: S/ 2,599.99
4. Ve estadísticas en Soles
5. Los gráficos muestran montos en S/

## Configuración de Nueva Empresa

### Paso a Paso: Crear Empresa Argentina

1. **Ir a Empresas → Agregar Empresa**
2. **Completar datos básicos**:
   ```
   Nombre: Distribuidora Buenos Aires
   Dirección: Av. Corrientes 1234, Buenos Aires
   Teléfono: +54-11-4567-8900
   Email: info@distba.com
   RUC/CUIT: 30-12345678-9
   ```

3. **Seleccionar Moneda**:
   ```
   Moneda: Peso Argentino ($)
   ```

4. **Resultado**:
   - Toda la empresa usará símbolo: $
   - Código interno: ARS
   - Nombre: Peso Argentino

## Beneficios del Sistema

### Para Empresas Multinacionales
- Cada sucursal puede operar en su moneda local
- Reportes coherentes por país
- Facilita contabilidad local

### Para Administradores
- Cambio automático de moneda al seleccionar empresa
- No necesidad de conversiones manuales
- Interfaz consistente en toda la aplicación

### Para Reportes
- Gráficos automáticamente en moneda correcta
- Estadísticas relevantes por región
- Comparaciones válidas dentro de cada empresa

## Monedas Más Utilizadas en América Latina

```
1. Peso Mexicano ($)    - México
2. Sol Peruano (S/)     - Perú
3. Peso Colombiano ($)  - Colombia
4. Peso Argentino ($)   - Argentina
5. Peso Chileno ($)     - Chile
6. Boliviano (Bs)       - Bolivia
7. Dólar ($ USD)        - Ecuador, El Salvador
8. Bolívar (Bs)         - Venezuela
```

## Notas Técnicas

### Funcionamiento Interno
1. Al seleccionar una empresa, se carga su configuración de moneda
2. La función `formatPrice()` usa automáticamente el símbolo correcto
3. Los gráficos reciben la información de moneda vía JSON
4. Python/Pyodide aplica el símbolo en las visualizaciones

### Extensibilidad
- Fácil agregar nuevas monedas editando el formulario
- Soporte para cualquier símbolo Unicode
- Compatible con códigos ISO 4217 estándar

---

Este sistema permite que empresas de diferentes países puedan usar MGBStock manteniendo sus monedas locales sin complicaciones técnicas ni conversiones manuales.
