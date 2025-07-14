# MGBStock - Flujo de Trabajo del Sistema

Este documento describe el flujo de trabajo completo del sistema de inventario multiempresa MGBStock, desde el acceso inicial hasta la gestión avanzada de inventario y estadísticas.

## 📊 Diagrama de Flujo Principal

```mermaid
flowchart TD
    A[🔐 Inicio: Login] --> B{¿Usuario autenticado?}
    B -- No --> A1[❌ Redirige a Login]
    B -- Sí --> C[🏢 Seleccionar Empresa]
    
    C --> D{¿Empresa seleccionada?}
    D -- No --> C1[📋 Mostrar lista de empresas]
    C1 --> C2[➕ Crear nueva empresa]
    C1 --> C3[✏️ Editar empresa existente]
    C1 --> C4[🎯 Seleccionar empresa]
    C2 --> C5[💰 Configurar moneda]
    C3 --> C5
    C4 --> E
    C5 --> C
    
    D -- Sí --> E[🏠 Dashboard Principal]
    E --> F[📱 Menú de Módulos]
    
    F --> G1[🏢 Empresas]
    F --> G2[📂 Categorías]
    F --> G3[📦 Productos]
    F --> G4[💰 Ventas]
    F --> G5[🛒 Compras]
    F --> G6[📊 Estadísticas]
    
    G1 --> H1[Gestión de Empresas]
    G2 --> H2[Gestión de Categorías]
    G3 --> H3[Gestión de Productos]
    G4 --> H4[Procesamiento de Ventas]
    G5 --> H5[Procesamiento de Compras]
    G6 --> H6[Generación de Reportes]
    
    H1 --> I1[Configurar moneda por empresa]
    H2 --> I2[Organizar productos por categoría]
    H3 --> I3[Control de stock y precios]
    H4 --> I4[Validación y descuento de stock]
    H5 --> I5[Incremento de stock]
    H6 --> I6[Gráficos con moneda específica]
    
    I1 --> E
    I2 --> E
    I3 --> E
    I4 --> E
    I5 --> E
    I6 --> E
```

## 🔄 Flujo Detallado por Módulos

### 1. 🔐 Módulo de Autenticación

```mermaid
flowchart LR
    A[Usuario ingresa] --> B[Formulario de Login]
    B --> C[Validar credenciales]
    C --> D{¿Válidas?}
    D -- No --> E[Mensaje de error]
    D -- Sí --> F[Crear sesión]
    F --> G[Redirigir a Dashboard]
    E --> B
```

**Proceso:**
1. Usuario accede a `index.php`
2. Ingresa credenciales (por defecto: admin/admin123)
3. Sistema valida contra base de datos
4. Si es válido, crea sesión y redirige al dashboard

### 2. 🏢 Módulo de Empresas

```mermaid
flowchart TD
    A[Acceso a Empresas] --> B[Listar empresas activas]
    B --> C[Opciones disponibles]
    C --> D[➕ Agregar nueva]
    C --> E[✏️ Editar existente]
    C --> F[🎯 Seleccionar empresa]
    C --> G[🗑️ Eliminar empresa]
    
    D --> H[Formulario nueva empresa]
    H --> I[💰 Seleccionar moneda]
    I --> J[Guardar empresa]
    J --> B
    
    E --> K[Cargar datos empresa]
    K --> L[Formulario edición]
    L --> M[💰 Cambiar moneda]
    M --> N[Actualizar empresa]
    N --> B
    
    F --> O[Establecer empresa activa]
    O --> P[Cargar configuración moneda]
    P --> Q[Redirigir a Dashboard]
```

**Características clave:**
- **Configuración de moneda**: Cada empresa puede tener su propia moneda
- **Monedas disponibles**: Peso Mexicano (por defecto), Sol Peruano, Dólar, Euro, etc.
- **Efecto global**: Al seleccionar empresa, toda la aplicación usa su moneda

### 3. 📂 Módulo de Categorías

```mermaid
flowchart TD
    A[Acceso a Categorías] --> B{¿Empresa seleccionada?}
    B -- No --> C[Redirigir a Empresas]
    B -- Sí --> D[Listar categorías de empresa]
    D --> E[Opciones disponibles]
    E --> F[➕ Agregar categoría]
    E --> G[✏️ Editar categoría]
    E --> H[🗑️ Eliminar categoría]
    
    F --> I[Formulario nueva categoría]
    I --> J[Guardar categoría]
    J --> D
    
    G --> K[Formulario edición]
    K --> L[Actualizar categoría]
    L --> D
```

**Características:**
- Categorías específicas por empresa
- Organización de productos por tipo/familia
- Base para reportes por categoría

### 4. 📦 Módulo de Productos

```mermaid
flowchart TD
    A[Acceso a Productos] --> B{¿Empresa seleccionada?}
    B -- No --> C[Redirigir a Empresas]
    B -- Sí --> D[Listar productos de empresa]
    D --> E[Mostrar precios en moneda empresa]
    E --> F[Opciones disponibles]
    
    F --> G[➕ Agregar producto]
    F --> H[✏️ Editar producto]
    F --> I[🗑️ Eliminar producto]
    F --> J[🔍 Ver detalles]
    
    G --> K[Formulario nuevo producto]
    K --> L[Asignar categoría]
    L --> M[Establecer precios en moneda]
    M --> N[Definir stock inicial]
    N --> O[Guardar producto]
    O --> D
    
    H --> P[Cargar datos producto]
    P --> Q[Formulario edición]
    Q --> R[Actualizar precios/stock]
    R --> S[Guardar cambios]
    S --> D
```

**Características:**
- Control de stock en tiempo real
- Precios en moneda de la empresa
- Códigos únicos por empresa
- Alertas de stock bajo

### 5. 💰 Módulo de Ventas

```mermaid
flowchart TD
    A[Acceso a Ventas] --> B{¿Empresa seleccionada?}
    B -- No --> C[Redirigir a Empresas]
    B -- Sí --> D[Dashboard de Ventas]
    D --> E[Estadísticas rápidas en moneda empresa]
    E --> F[Opciones disponibles]
    
    F --> G[➕ Registrar venta]
    F --> H[📋 Ver historial]
    
    G --> I[Seleccionar producto]
    I --> J[Verificar stock disponible]
    J --> K{¿Stock suficiente?}
    K -- No --> L[Mensaje de error]
    K -- Sí --> M[Ingresarcantidad]
    M --> N[Calcular total en moneda]
    N --> O[Confirmar venta]
    O --> P[Descontar stock]
    P --> Q[Registrar movimiento]
    Q --> R[Actualizar estadísticas]
    R --> D
    
    H --> S[Listar ventas]
    S --> T[Mostrar totales en moneda]
```

**Características:**
- Validación automática de stock
- Cálculos en moneda de la empresa
- Descuento automático de inventario
- Registro de movimientos para auditoría

### 6. 🛒 Módulo de Compras

```mermaid
flowchart TD
    A[Acceso a Compras] --> B{¿Empresa seleccionada?}
    B -- No --> C[Redirigir a Empresas]
    B -- Sí --> D[Dashboard de Compras]
    D --> E[Estadísticas rápidas en moneda empresa]
    E --> F[Opciones disponibles]
    
    F --> G[➕ Registrar compra]
    F --> H[📋 Ver historial]
    
    G --> I[Seleccionar producto]
    I --> J[Ingresar cantidad]
    J --> K[Establecer precio unitario]
    K --> L[Calcular total en moneda]
    L --> M[Ingresar datos proveedor]
    M --> N[Confirmar compra]
    N --> O[Incrementar stock]
    O --> P[Actualizar precio compra]
    P --> Q[Registrar movimiento]
    Q --> R[Actualizar estadísticas]
    R --> D
    
    H --> S[Listar compras]
    S --> T[Mostrar totales en moneda]
```

**Características:**
- Incremento automático de stock
- Actualización de precios de compra
- Registro de proveedores
- Cálculos en moneda de la empresa

### 7. 📊 Módulo de Estadísticas

```mermaid
flowchart TD
    A[Acceso a Estadísticas] --> B{¿Empresa seleccionada?}
    B -- No --> C[Redirigir a Empresas]
    B -- Sí --> D[Cargar Pyodide]
    D --> E[Obtener datos empresa]
    E --> F[Cargar info moneda]
    F --> G[Generar gráficos]
    
    G --> H[Ventas por mes]
    G --> I[Productos más vendidos]
    G --> J[Ventas por categoría]
    G --> K[Comparativa ventas vs compras]
    
    H --> L[Aplicar símbolo moneda en ejes]
    I --> L
    J --> L
    K --> L
    
    L --> M[Mostrar resumen estadístico]
    M --> N[Todos los valores en moneda empresa]
```

**Características:**
- Gráficos generados con Python/Matplotlib
- Todos los valores en moneda de la empresa
- Análisis temporal y por categorías
- Comparativas financieras

## 🔄 Flujo de Datos del Sistema

### Control de Stock

```mermaid
flowchart LR
    A[Stock Inicial] --> B[Compras +]
    B --> C[Stock Actualizado]
    C --> D[Ventas -]
    D --> E[Stock Final]
    E --> F[Validación Stock Mínimo]
    F --> G{¿Stock bajo?}
    G -- Sí --> H[Alerta Stock Bajo]
    G -- No --> I[Stock OK]
```

### Flujo de Monedas

```mermaid
flowchart LR
    A[Empresa seleccionada] --> B[Cargar config moneda]
    B --> C[Aplicar en formatPrice()]
    C --> D[Mostrar en UI]
    D --> E[Usar en gráficos]
    E --> F[Mostrar en reportes]
```

## 🎯 Casos de Uso Típicos

### Caso 1: Empresa Mexicana
1. **Login** → Credenciales admin/admin123
2. **Seleccionar** → Comercial El Dorado
3. **Resultado** → Todo el sistema muestra precios en $ (Peso Mexicano)
4. **Ventas** → Registra venta: $ 1,899.99
5. **Estadísticas** → Gráficos muestran "Total de Ventas ($)"

### Caso 2: Empresa Peruana
1. **Login** → Credenciales admin/admin123
2. **Seleccionar** → TechnoSoft S.A.
3. **Resultado** → Todo el sistema muestra precios en S/ (Sol Peruano)
4. **Ventas** → Registra venta: S/ 2,599.99
5. **Estadísticas** → Gráficos muestran "Total de Ventas (S/)"

### Caso 3: Crear Nueva Empresa
1. **Empresas** → Agregar Empresa
2. **Datos** → Nombre, dirección, contacto
3. **Moneda** → Seleccionar de lista (Peso Mexicano por defecto)
4. **Guardar** → Empresa creada con su moneda
5. **Usar** → Seleccionar empresa y trabajar con su moneda

## 📋 Checklist de Operaciones

### ✅ Operaciones Diarias
- [ ] Seleccionar empresa activa
- [ ] Registrar ventas del día
- [ ] Registrar compras recibidas
- [ ] Verificar stock bajo
- [ ] Revisar estadísticas

### ✅ Operaciones Semanales
- [ ] Generar reportes de ventas
- [ ] Analizar productos más vendidos
- [ ] Revisar movimientos de stock
- [ ] Actualizar precios si es necesario

### ✅ Operaciones Mensuales
- [ ] Generar gráficos estadísticos
- [ ] Comparar ventas vs compras
- [ ] Evaluar rendimiento por categoría
- [ ] Planificar compras futuras

## 🔧 Configuración Técnica

### Requisitos Previos
- Servidor web con PHP 7.4+
- Base de datos MySQL 5.7+
- Navegador moderno con JavaScript

### Instalación
1. Copiar archivos al servidor
2. Crear base de datos con `mgbstock.sql`
3. Configurar conexión en `config.php`
4. Acceder vía navegador

### Migración (si ya existe)
1. Respaldar base de datos
2. Ejecutar `migration_currency.sql`
3. Verificar funcionalidad

---

## 🎯 Beneficios del Flujo de Trabajo

1. **Simplicidad**: Flujo intuitivo y fácil de seguir
2. **Flexibilidad**: Soporte para múltiples empresas y monedas
3. **Automatización**: Stock y movimientos automáticos
4. **Trazabilidad**: Historial completo de operaciones
5. **Reportes**: Estadísticas en tiempo real con gráficos
6. **Escalabilidad**: Fácil agregar nuevas empresas y monedas

El sistema MGBStock está diseñado para ser intuitivo y eficiente, permitiendo a los usuarios gestionar inventarios multiempresa de forma sencilla y con total control sobre las monedas utilizadas en cada operación.
