<?php
require_once '../includes/config.php';

// Verificar que esté logueado y tenga empresa seleccionada
if (!isLoggedIn()) {
    redirect('../index.php');
}

if (!hasSelectedCompany()) {
    showAlert('Debe seleccionar una empresa primero', 'warning');
    redirect('companies.php');
}

$db = new Database();
$conn = $db->getConnection();

// API endpoint para obtener datos
if (isset($_GET['api']) && $_GET['api'] == 'data') {
    header('Content-Type: application/json');
    
    $data = [
        'ventas_por_mes' => [],
        'compras_por_mes' => [],
        'productos_mas_vendidos' => [],
        'ventas_por_categoria' => [],
        'resumen' => []
    ];
    
    try {
        // Ventas por mes (últimos 12 meses)
        $stmt = $conn->prepare("
            SELECT 
                DATE_FORMAT(fecha_venta, '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(total) as total
            FROM ventas 
            WHERE empresa_id = ? 
            AND fecha_venta >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(fecha_venta, '%Y-%m')
            ORDER BY mes
        ");
        $stmt->execute([$_SESSION['empresa_id']]);
        $data['ventas_por_mes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Compras por mes (últimos 12 meses)
        $stmt = $conn->prepare("
            SELECT 
                DATE_FORMAT(fecha_compra, '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(total) as total
            FROM compras 
            WHERE empresa_id = ? 
            AND fecha_compra >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(fecha_compra, '%Y-%m')
            ORDER BY mes
        ");
        $stmt->execute([$_SESSION['empresa_id']]);
        $data['compras_por_mes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Productos más vendidos
        $stmt = $conn->prepare("
            SELECT 
                p.nombre,
                p.codigo,
                SUM(v.cantidad) as total_vendido,
                SUM(v.total) as ingresos_generados
            FROM ventas v
            INNER JOIN productos p ON v.producto_id = p.id
            WHERE v.empresa_id = ?
            GROUP BY v.producto_id
            ORDER BY total_vendido DESC
            LIMIT 10
        ");
        $stmt->execute([$_SESSION['empresa_id']]);
        $data['productos_mas_vendidos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ventas por categoría
        $stmt = $conn->prepare("
            SELECT 
                c.nombre as categoria,
                COUNT(v.id) as cantidad_ventas,
                SUM(v.total) as total_ventas
            FROM ventas v
            INNER JOIN productos p ON v.producto_id = p.id
            INNER JOIN categorias c ON p.categoria_id = c.id
            WHERE v.empresa_id = ?
            GROUP BY c.id
            ORDER BY total_ventas DESC
        ");
        $stmt->execute([$_SESSION['empresa_id']]);
        $data['ventas_por_categoria'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Resumen general
        $stmt = $conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM productos WHERE empresa_id = ? AND activo = 1) as total_productos,
                (SELECT COUNT(*) FROM ventas WHERE empresa_id = ?) as total_ventas,
                (SELECT COUNT(*) FROM compras WHERE empresa_id = ?) as total_compras,
                (SELECT COALESCE(SUM(total), 0) FROM ventas WHERE empresa_id = ?) as ingresos_totales,
                (SELECT COALESCE(SUM(total), 0) FROM compras WHERE empresa_id = ?) as gastos_totales
        ");
        $stmt->execute([
            $_SESSION['empresa_id'], $_SESSION['empresa_id'], $_SESSION['empresa_id'], 
            $_SESSION['empresa_id'], $_SESSION['empresa_id']
        ]);
        $data['resumen'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $data['error'] = $e->getMessage();
    }
    
    echo json_encode($data);
    exit;
}

$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <!-- Pyodide CDN -->
    <script src="https://cdn.jsdelivr.net/pyodide/v0.24.1/full/pyodide.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">MGBStock</div>
            <div class="user-info">
                <span>Bienvenido, <?php echo $_SESSION['admin_nombre']; ?></span>
                <span>Empresa: <?php echo $_SESSION['empresa_nombre']; ?></span>
                <a href="../logout.php" class="btn btn-sm" style="background: rgba(255,255,255,0.2);">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-content">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../dashboard.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="companies.php" class="nav-link">Empresas</a>
                </li>
                <li class="nav-item">
                    <a href="categories.php" class="nav-link">Categorías</a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link">Productos</a>
                </li>
                <li class="nav-item">
                    <a href="sales.php" class="nav-link">Ventas</a>
                </li>
                <li class="nav-item">
                    <a href="purchases.php" class="nav-link">Compras</a>
                </li>
                <li class="nav-item">
                    <a href="statistics.php" class="nav-link active">Estadísticas</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <?php if ($alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?>">
                <?php echo $alert['message']; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Estadísticas y Reportes</h1>
                <div class="d-flex gap-2">
                    <button onclick="StatisticsManager.generateCharts()" class="btn btn-primary">
                        📊 Generar Gráficos
                    </button>
                    <button onclick="StatisticsManager.refreshData()" class="btn btn-success">
                        🔄 Actualizar Datos
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Loading indicator -->
                <div id="loading-indicator" class="text-center" style="display: none;">
                    <div class="loading"></div>
                    <p>Cargando Pyodide y generando gráficos...</p>
                </div>

                <!-- Charts container -->
                <div id="charts-container"></div>
                
                <!-- Error container -->
                <div id="error-container" style="display: none;"></div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Gestor de Estadísticas con Pyodide
        const StatisticsManager = {
            pyodide: null,
            data: null,

            async init() {
                try {
                    this.showLoading();
                    console.log('Inicializando Pyodide...');
                    
                    // Cargar Pyodide
                    this.pyodide = await loadPyodide();
                    
                    // Instalar paquetes necesarios
                    await this.pyodide.loadPackage(['matplotlib', 'numpy']);
                    
                    console.log('Pyodide inicializado correctamente');
                    
                    // Cargar datos y generar gráficos
                    await this.loadData();
                    await this.generateCharts();
                    
                } catch (error) {
                    console.error('Error inicializando Pyodide:', error);
                    this.showError('Error al cargar el sistema de gráficos: ' + error.message);
                } finally {
                    this.hideLoading();
                }
            },

            async loadData() {
                try {
                    const response = await fetch('statistics.php?api=data');
                    this.data = await response.json();
                    
                    if (this.data.error) {
                        throw new Error(this.data.error);
                    }
                    
                    console.log('Datos cargados:', this.data);
                } catch (error) {
                    console.error('Error cargando datos:', error);
                    throw error;
                }
            },

            async generateCharts() {
                if (!this.pyodide || !this.data) {
                    await this.init();
                    return;
                }

                try {
                    this.showLoading();
                    
                    // Limpiar contenedor
                    document.getElementById('charts-container').innerHTML = '';
                    
                    // Configurar datos en Python
                    this.pyodide.globals.set('chart_data', this.data);
                    
                    // Convertir datos a JSON para Python
                    const dataJson = JSON.stringify(this.data);
                    this.pyodide.globals.set('chart_data_json', dataJson);
                    
                    // Ejecutar código Python para generar gráficos
                    await this.pyodide.runPython(`
import matplotlib.pyplot as plt
import numpy as np
import json
from matplotlib.backends.backend_agg import FigureCanvasAgg
import base64
from io import BytesIO

# Configurar matplotlib para usar el backend apropiado
plt.style.use('default')

# Convertir datos JSON a diccionario Python
chart_data = json.loads(chart_data_json)

# Función para convertir figura a base64
def fig_to_base64(fig):
    buffer = BytesIO()
    fig.savefig(buffer, format='png', dpi=100, bbox_inches='tight')
    buffer.seek(0)
    image_png = buffer.getvalue()
    buffer.close()
    graphic = base64.b64encode(image_png)
    return graphic.decode('utf-8')

# Crear gráficos
charts_html = []

# 1. Gráfico de Ventas por Mes
if chart_data.get('ventas_por_mes') and len(chart_data['ventas_por_mes']) > 0:
    fig, ax = plt.subplots(figsize=(12, 6))
    meses = [item['mes'] for item in chart_data['ventas_por_mes']]
    totales = [float(item['total']) for item in chart_data['ventas_por_mes']]
    
    bars = ax.bar(meses, totales, color='#3498db', alpha=0.8)
    ax.set_title('Ventas por Mes', fontsize=16, fontweight='bold')
    ax.set_xlabel('Mes')
    ax.set_ylabel('Total de Ventas (S/)')
    ax.tick_params(axis='x', rotation=45)
    
    # Agregar valores en las barras
    for bar, total in zip(bars, totales):
        height = bar.get_height()
        ax.text(bar.get_x() + bar.get_width()/2., height,
                f'S/ {total:,.2f}',
                ha='center', va='bottom', fontsize=9)
    
    plt.tight_layout()
    chart_base64 = fig_to_base64(fig)
    charts_html.append(f'''
    <div class="chart-container">
        <h3 class="chart-title">Ventas por Mes</h3>
        <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
    </div>
    ''')
    plt.close(fig)

# 2. Gráfico de Productos Más Vendidos
if chart_data.get('productos_mas_vendidos') and len(chart_data['productos_mas_vendidos']) > 0:
    fig, ax = plt.subplots(figsize=(12, 8))
    productos = [item['nombre'][:20] + '...' if len(item['nombre']) > 20 else item['nombre'] 
                for item in chart_data['productos_mas_vendidos'][:8]]
    cantidades = [int(item['total_vendido']) for item in chart_data['productos_mas_vendidos'][:8]]
    
    bars = ax.barh(productos, cantidades, color='#2ecc71', alpha=0.8)
    ax.set_title('Productos Más Vendidos', fontsize=16, fontweight='bold')
    ax.set_xlabel('Cantidad Vendida')
    
    # Agregar valores en las barras
    for bar, cantidad in zip(bars, cantidades):
        width = bar.get_width()
        ax.text(width, bar.get_y() + bar.get_height()/2.,
                f'{cantidad}',
                ha='left', va='center', fontsize=10)
    
    plt.tight_layout()
    chart_base64 = fig_to_base64(fig)
    charts_html.append(f'''
    <div class="chart-container">
        <h3 class="chart-title">Productos Más Vendidos</h3>
        <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
    </div>
    ''')
    plt.close(fig)

# 3. Gráfico Circular de Ventas por Categoría
if chart_data.get('ventas_por_categoria') and len(chart_data['ventas_por_categoria']) > 0:
    fig, ax = plt.subplots(figsize=(10, 8))
    categorias = [item['categoria'] for item in chart_data['ventas_por_categoria']]
    totales = [float(item['total_ventas']) for item in chart_data['ventas_por_categoria']]
    
    colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#e67e22']
    
    wedges, texts, autotexts = ax.pie(totales, labels=categorias, autopct='%1.1f%%', 
                                     colors=colors[:len(categorias)], startangle=90)
    
    ax.set_title('Distribución de Ventas por Categoría', fontsize=16, fontweight='bold')
    
    # Mejorar la legibilidad
    for autotext in autotexts:
        autotext.set_color('white')
        autotext.set_fontweight('bold')
    
    plt.tight_layout()
    chart_base64 = fig_to_base64(fig)
    charts_html.append(f'''
    <div class="chart-container">
        <h3 class="chart-title">Ventas por Categoría</h3>
        <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
    </div>
    ''')
    plt.close(fig)

# 4. Comparación Ventas vs Compras
if (chart_data.get('ventas_por_mes') and len(chart_data['ventas_por_mes']) > 0 and 
    chart_data.get('compras_por_mes') and len(chart_data['compras_por_mes']) > 0):
    fig, ax = plt.subplots(figsize=(12, 8))
    
    # Preparar datos
    meses_ventas = {item['mes']: float(item['total']) for item in chart_data['ventas_por_mes']}
    meses_compras = {item['mes']: float(item['total']) for item in chart_data['compras_por_mes']}
    
    # Obtener todos los meses únicos
    todos_meses = sorted(set(list(meses_ventas.keys()) + list(meses_compras.keys())))
    
    ventas_valores = [meses_ventas.get(mes, 0) for mes in todos_meses]
    compras_valores = [meses_compras.get(mes, 0) for mes in todos_meses]
    
    x = np.arange(len(todos_meses))
    width = 0.35
    
    bars1 = ax.bar(x - width/2, ventas_valores, width, label='Ventas', color='#2ecc71', alpha=0.8)
    bars2 = ax.bar(x + width/2, compras_valores, width, label='Compras', color='#e74c3c', alpha=0.8)
    
    ax.set_title('Comparación: Ventas vs Compras por Mes', fontsize=16, fontweight='bold')
    ax.set_xlabel('Mes')
    ax.set_ylabel('Monto (S/)')
    ax.set_xticks(x)
    ax.set_xticklabels(todos_meses, rotation=45)
    ax.legend()
    
    plt.tight_layout()
    chart_base64 = fig_to_base64(fig)
    charts_html.append(f'''
    <div class="chart-container">
        <h3 class="chart-title">Ventas vs Compras</h3>
        <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
    </div>
    ''')
    plt.close(fig)

# Combinar todos los gráficos
if len(charts_html) == 0:
    final_html = '''
    <div class="alert alert-info">
        <h4>No hay datos suficientes para generar gráficos</h4>
        <p>Para visualizar estadísticas, necesita:</p>
        <ul>
            <li>Registrar al menos una venta</li>
            <li>Registrar al menos una compra</li>
            <li>Tener productos en diferentes categorías</li>
        </ul>
        <p>Una vez que tenga datos, podrá ver gráficos detallados de:</p>
        <ul>
            <li>📈 Ventas por mes</li>
            <li>🏆 Productos más vendidos</li>
            <li>🥧 Distribución por categorías</li>
            <li>📊 Comparativa ventas vs compras</li>
        </ul>
    </div>
    '''
else:
    final_html = '\\n'.join(charts_html)
                    `);
                    
                    // Obtener el HTML generado
                    const chartsHtml = this.pyodide.globals.get('final_html');
                    
                    // Agregar resumen de datos
                    const resumenHtml = this.generateSummaryHtml();
                    
                    // Mostrar en el contenedor
                    document.getElementById('charts-container').innerHTML = resumenHtml + chartsHtml;
                    
                } catch (error) {
                    console.error('Error generando gráficos:', error);
                    this.showError('Error al generar gráficos: ' + error.message);
                } finally {
                    this.hideLoading();
                }
            },

            generateSummaryHtml() {
                if (!this.data || !this.data.resumen) {
                    return `
                    <div class="alert alert-warning">
                        <h4>Cargando datos del resumen...</h4>
                        <p>Si este mensaje persiste, verifique que haya datos en el sistema.</p>
                    </div>
                    `;
                }
                
                const resumen = this.data.resumen;
                return `
                <div class="dashboard-grid">
                    <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #74b9ff);">
                        <div class="stat-number">${resumen.total_productos || 0}</div>
                        <div class="stat-label">Total Productos</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #2ecc71, #00b894);">
                        <div class="stat-number">${resumen.total_ventas || 0}</div>
                        <div class="stat-label">Total Ventas</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #ff6b6b);">
                        <div class="stat-number">${resumen.total_compras || 0}</div>
                        <div class="stat-label">Total Compras</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #fdcb6e);">
                        <div class="stat-number">S/ ${parseFloat(resumen.ingresos_totales || 0).toFixed(2)}</div>
                        <div class="stat-label">Ingresos Totales</div>
                    </div>
                </div>
                `;
            },

            async refreshData() {
                await this.loadData();
                await this.generateCharts();
            },

            showLoading() {
                document.getElementById('loading-indicator').style.display = 'block';
                document.getElementById('error-container').style.display = 'none';
            },

            hideLoading() {
                document.getElementById('loading-indicator').style.display = 'none';
            },

            showError(message) {
                const errorContainer = document.getElementById('error-container');
                errorContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${message}
                        <br><br>
                        <button onclick="StatisticsManager.init()" class="btn btn-primary">
                            Reintentar
                        </button>
                    </div>
                `;
                errorContainer.style.display = 'block';
            }
        };

        // Inicializar cuando la página esté lista
        document.addEventListener('DOMContentLoaded', function() {
            StatisticsManager.init();
        });
    </script>
</body>
</html>
