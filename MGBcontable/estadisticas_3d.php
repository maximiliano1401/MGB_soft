<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'php/conexion.php';

// API endpoint para obtener datos
if (isset($_GET['api']) && $_GET['api'] == 'data') {
    header('Content-Type: application/json');
    
    $data = [
        'balance_por_empresa' => [],
        'evolucion_temporal' => [],
        'distribucion_cuentas' => [],
        'analisis_patrimonial' => [],
        'resumen' => [],
        'empresas_activas' => []
    ];
    
    try {
        // Balance por empresa
        $sql = "
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
            ORDER BY bi.empresa
        ";
        $result = $conn->query($sql);
        $data['balance_por_empresa'] = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data['balance_por_empresa'][] = $row;
            }
        }
        
        // Evolución temporal de balances
        $sql = "
            SELECT 
                bi.fecha,
                bi.empresa,
                SUM(bid.saldo) as total_balance,
                COUNT(bid.id) as num_cuentas
            FROM balance_inicial bi
            LEFT JOIN balance_inicial_detalle bid ON bi.id = bid.balance_id
            GROUP BY bi.fecha, bi.empresa
            ORDER BY bi.fecha ASC
        ";
        $result = $conn->query($sql);
        $data['evolucion_temporal'] = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data['evolucion_temporal'][] = $row;
            }
        }
        
        // Distribución por tipo de cuenta
        $sql = "
            SELECT 
                c.tipo as tipo,
                c.nombre as cuenta_nombre,
                SUM(bid.saldo) as total_saldo,
                COUNT(bid.id) as frecuencia
            FROM balance_inicial_detalle bid
            JOIN cuentas c ON bid.cuenta_codigo = c.codigo
            GROUP BY c.tipo, c.nombre
            ORDER BY c.tipo, total_saldo DESC
        ";
        $result = $conn->query($sql);
        $data['distribucion_cuentas'] = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data['distribucion_cuentas'][] = $row;
            }
        }
        
        // Análisis patrimonial por empresa
        $sql = "
            SELECT 
                bi.empresa,
                bi.fecha,
                SUM(CASE WHEN c.tipo = 'Activo' THEN bid.saldo ELSE 0 END) as activos,
                SUM(CASE WHEN c.tipo = 'Pasivo' THEN bid.saldo ELSE 0 END) as pasivos,
                SUM(CASE WHEN c.tipo = 'Patrimonio' THEN bid.saldo ELSE 0 END) as patrimonio,
                (SUM(CASE WHEN c.tipo = 'Activo' THEN bid.saldo ELSE 0 END) - 
                 SUM(CASE WHEN c.tipo = 'Pasivo' THEN bid.saldo ELSE 0 END)) as patrimonio_calculado
            FROM balance_inicial bi
            LEFT JOIN balance_inicial_detalle bid ON bi.id = bid.balance_id
            LEFT JOIN cuentas c ON bid.cuenta_codigo = c.codigo
            GROUP BY bi.empresa, bi.fecha
            ORDER BY bi.fecha DESC
        ";
        $result = $conn->query($sql);
        $data['analisis_patrimonial'] = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data['analisis_patrimonial'][] = $row;
            }
        }
        
        // Resumen general
        $sql = "
            SELECT 
                COUNT(DISTINCT bi.empresa) as total_empresas,
                COUNT(DISTINCT bi.id) as total_balances,
                COUNT(bid.id) as total_registros,
                SUM(bid.saldo) as suma_total_saldos,
                AVG(bid.saldo) as promedio_saldos,
                COUNT(DISTINCT c.tipo) as tipos_cuenta
            FROM balance_inicial bi
            LEFT JOIN balance_inicial_detalle bid ON bi.id = bid.balance_id
            LEFT JOIN cuentas c ON bid.cuenta_codigo = c.codigo
        ";
        $result = $conn->query($sql);
        $data['resumen'] = [];
        if ($result && $result->num_rows > 0) {
            $data['resumen'] = $result->fetch_assoc();
        }
        
        // Empresas activas con último balance
        $sql = "
            SELECT 
                bi.empresa,
                MAX(bi.fecha) as ultimo_balance,
                COUNT(DISTINCT bi.id) as num_balances
            FROM balance_inicial bi
            GROUP BY bi.empresa
            ORDER BY ultimo_balance DESC
        ";
        $result = $conn->query($sql);
        $data['empresas_activas'] = [];
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data['empresas_activas'][] = $row;
            }
        }
        
    } catch (Exception $e) {
        $data['error'] = $e->getMessage();
    }
    
    echo json_encode($data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas 3D - MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Pyodide CDN -->
    <script src="https://cdn.jsdelivr.net/pyodide/v0.24.1/full/pyodide.js"></script>
    <style>
        .stats-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid #667eea;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .chart-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
            text-align: center;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
        }
        
        .loading {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .controls-panel {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-3d {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .alert-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            color: #1565c0;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
        
        .alert-danger {
            background: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="stats-header">
        <h1>📊 Estadísticas 3D - MGB Contabilidad</h1>
        <p>Análisis visual avanzado de datos contables con gráficos tridimensionales</p>
        <p><strong>Usuario:</strong> <?php echo $_SESSION['usuario_nombre']; ?></p>
    </div>

    <!-- Controls Panel -->
    <div class="controls-panel">
        <button onclick="Statistics3D.generateCharts()" class="btn-3d">
            🎯 Generar Gráficos 3D
        </button>
        <button onclick="Statistics3D.refreshData()" class="btn-3d">
            🔄 Actualizar Datos
        </button>
        <button onclick="window.location.href='index.php'" class="btn-3d" style="background: linear-gradient(45deg, #28a745, #20c997);">
            🏠 Volver al Menú
        </button>
    </div>

    <!-- Loading indicator -->
    <div id="loading-indicator" style="display: none; text-align: center; padding: 2rem;">
        <div class="loading"></div>
        <p style="margin-top: 1rem; color: #666;">Cargando Pyodide y generando gráficos 3D...</p>
    </div>

    <!-- Summary cards -->
    <div id="summary-container"></div>

    <!-- Charts container -->
    <div id="charts-container"></div>
    
    <!-- Error container -->
    <div id="error-container" style="display: none;"></div>

    <script>
        // Gestor de Estadísticas 3D con Pyodide
        const Statistics3D = {
            pyodide: null,
            data: null,

            async init() {
                try {
                    this.showLoading();
                    console.log('Inicializando Pyodide para gráficos 3D...');
                    
                    // Cargar Pyodide
                    this.pyodide = await loadPyodide();
                    
                    // Instalar paquetes necesarios para gráficos 3D
                    await this.pyodide.loadPackage(['matplotlib', 'numpy']);
                    
                    console.log('Pyodide inicializado correctamente');
                    
                    // Cargar datos y generar gráficos
                    await this.loadData();
                    await this.generateCharts();
                    
                } catch (error) {
                    console.error('Error inicializando Pyodide:', error);
                    this.showError('Error al cargar el sistema de gráficos 3D: ' + error.message);
                } finally {
                    this.hideLoading();
                }
            },

            async loadData() {
                try {
                    const response = await fetch('estadisticas_3d.php?api=data');
                    this.data = await response.json();
                    
                    if (this.data.error) {
                        throw new Error(this.data.error);
                    }
                    
                    console.log('Datos contables cargados:', this.data);
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
                    
                    // Limpiar contenedores
                    document.getElementById('charts-container').innerHTML = '';
                    
                    // Configurar datos en Python
                    this.pyodide.globals.set('chart_data', this.data);
                    
                    // Convertir datos a JSON para Python
                    const dataJson = JSON.stringify(this.data);
                    this.pyodide.globals.set('chart_data_json', dataJson);
                    
                    // Ejecutar código Python para generar gráficos 3D
                    await this.pyodide.runPython(`
import matplotlib.pyplot as plt
import numpy as np
import json
from mpl_toolkits.mplot3d import Axes3D
from matplotlib.backends.backend_agg import FigureCanvasAgg
import base64
from io import BytesIO

# Configurar matplotlib para 3D
plt.style.use('default')

# Convertir datos JSON a diccionario Python
chart_data = json.loads(chart_data_json)

# Función para convertir figura a base64
def fig_to_base64(fig):
    buffer = BytesIO()
    fig.savefig(buffer, format='png', dpi=120, bbox_inches='tight', facecolor='white')
    buffer.seek(0)
    image_png = buffer.getvalue()
    buffer.close()
    graphic = base64.b64encode(image_png)
    return graphic.decode('utf-8')

charts_html = []

# 1. Gráfico 3D: Balance por Empresa (Activos, Pasivos, Patrimonio)
if chart_data.get('balance_por_empresa') and len(chart_data['balance_por_empresa']) > 0:
    fig = plt.figure(figsize=(14, 10))
    ax = fig.add_subplot(111, projection='3d')
    
    empresas = []
    activos = []
    pasivos = []
    patrimonios = []
    
    for item in chart_data['balance_por_empresa']:
        if item['empresa']:
            empresas.append(item['empresa'][:15])  # Limitar nombre
            activos.append(float(item['total_activos'] or 0))
            pasivos.append(float(item['total_pasivos'] or 0))
            patrimonios.append(float(item['total_patrimonio'] or 0))
    
    if len(empresas) > 0:
        x = np.arange(len(empresas))
        width = 0.25
        
        # Crear barras 3D sin conflictos de parámetros
        ax.bar3d(x - width, [0]*len(x), [0]*len(x), width, [0.8]*len(x), activos, 
                 color='#2ecc71', alpha=0.8, label='Activos')
        ax.bar3d(x, [1]*len(x), [0]*len(x), width, [0.8]*len(x), pasivos, 
                 color='#e74c3c', alpha=0.8, label='Pasivos')
        ax.bar3d(x + width, [2]*len(x), [0]*len(x), width, [0.8]*len(x), patrimonios, 
                 color='#3498db', alpha=0.8, label='Patrimonio')
        
        ax.set_xlabel('Empresas', fontsize=12)
        ax.set_ylabel('Categorías', fontsize=12)
        ax.set_zlabel('Montos ($)', fontsize=12)
        ax.set_title('Análisis Patrimonial 3D por Empresa', fontsize=16, fontweight='bold', pad=20)
        
        # Configurar etiquetas
        ax.set_xticks(x)
        ax.set_xticklabels(empresas, rotation=45, ha='right')
        ax.set_yticks([0, 1, 2])
        ax.set_yticklabels(['Activos', 'Pasivos', 'Patrimonio'])
        
        ax.legend(loc='upper left')
        ax.view_init(elev=20, azim=45)
        
        plt.tight_layout()
        chart_base64 = fig_to_base64(fig)
        charts_html.append(f'''
        <div class="chart-container">
            <h3 class="chart-title">📈 Análisis Patrimonial 3D por Empresa</h3>
            <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
            <p style="text-align: center; color: #666; margin-top: 1rem;">
                Vista tridimensional del balance patrimonial mostrando Activos, Pasivos y Patrimonio por empresa
            </p>
        </div>
        ''')
        plt.close(fig)

# 2. Gráfico 3D: Evolución Temporal de Balances
if chart_data.get('evolucion_temporal') and len(chart_data['evolucion_temporal']) > 0:
    fig = plt.figure(figsize=(14, 10))
    ax = fig.add_subplot(111, projection='3d')
    
    # Procesar datos de evolución temporal
    fechas_dict = {}
    empresas_set = set()
    
    for item in chart_data['evolucion_temporal']:
        fecha = item['fecha']
        empresa = item['empresa']
        balance = float(item['total_balance'] or 0)
        
        if fecha not in fechas_dict:
            fechas_dict[fecha] = {}
        fechas_dict[fecha][empresa] = balance
        empresas_set.add(empresa)
    
    fechas = sorted(fechas_dict.keys())
    empresas_list = list(empresas_set)
    
    if len(fechas) > 0 and len(empresas_list) > 0:
        X, Y = np.meshgrid(range(len(fechas)), range(len(empresas_list)))
        Z = np.zeros((len(empresas_list), len(fechas)))
        
        for i, empresa in enumerate(empresas_list):
            for j, fecha in enumerate(fechas):
                Z[i, j] = fechas_dict[fecha].get(empresa, 0)
        
        # Crear superficie 3D
        surf = ax.plot_surface(X, Y, Z, cmap='viridis', alpha=0.8, linewidth=0.5, edgecolors='white')
        
        ax.set_xlabel('Períodos de Tiempo', fontsize=12)
        ax.set_ylabel('Empresas', fontsize=12)
        ax.set_zlabel('Balance Total ($)', fontsize=12)
        ax.set_title('Evolución Temporal de Balances 3D', fontsize=16, fontweight='bold', pad=20)
        
        # Configurar etiquetas
        ax.set_xticks(range(len(fechas)))
        ax.set_xticklabels([f.split('-')[1] + '/' + f.split('-')[0][-2:] for f in fechas], rotation=45)
        ax.set_yticks(range(len(empresas_list)))
        ax.set_yticklabels([emp[:10] for emp in empresas_list])
        
        # Barra de colores
        fig.colorbar(surf, ax=ax, shrink=0.5, aspect=20, label='Balance ($)')
        ax.view_init(elev=30, azim=45)
        
        plt.tight_layout()
        chart_base64 = fig_to_base64(fig)
        charts_html.append(f'''
        <div class="chart-container">
            <h3 class="chart-title">⏰ Evolución Temporal de Balances 3D</h3>
            <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
            <p style="text-align: center; color: #666; margin-top: 1rem;">
                Superficie 3D mostrando la evolución de los balances por empresa a través del tiempo
            </p>
        </div>
        ''')
        plt.close(fig)

# 3. Gráfico 3D: Distribución de Cuentas por Tipo
if chart_data.get('distribucion_cuentas') and len(chart_data['distribucion_cuentas']) > 0:
    fig = plt.figure(figsize=(14, 10))
    ax = fig.add_subplot(111, projection='3d')
    
    # Agrupar por tipo de cuenta
    tipos_dict = {}
    for item in chart_data['distribucion_cuentas']:
        tipo = item['tipo']
        if tipo not in tipos_dict:
            tipos_dict[tipo] = []
        tipos_dict[tipo].append({
            'cuenta': item['cuenta_nombre'][:20],
            'saldo': float(item['total_saldo'] or 0),
            'frecuencia': int(item['frecuencia'] or 0)
        })
    
    colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6']
    z_offset = 0
    
    for i, (tipo, cuentas) in enumerate(tipos_dict.items()):
        if len(cuentas) > 0:
            x = range(len(cuentas))
            saldos = [c['saldo'] for c in cuentas]
            frecuencias = [c['frecuencia'] for c in cuentas]
            
            # Crear barras 3D para cada tipo
            ax.bar(x, saldos, zs=z_offset, zdir='y', alpha=0.8, 
                   color=colors[i % len(colors)], label=tipo, width=0.8)
            z_offset += 1
    
    ax.set_xlabel('Cuentas', fontsize=12)
    ax.set_ylabel('Tipos de Cuenta', fontsize=12)
    ax.set_zlabel('Saldo Total ($)', fontsize=12)
    ax.set_title('Distribución 3D de Cuentas por Tipo', fontsize=16, fontweight='bold', pad=20)
    
    ax.legend(loc='upper right')
    ax.view_init(elev=25, azim=35)
    
    plt.tight_layout()
    chart_base64 = fig_to_base64(fig)
    charts_html.append(f'''
    <div class="chart-container">
        <h3 class="chart-title">💼 Distribución 3D de Cuentas por Tipo</h3>
        <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
        <p style="text-align: center; color: #666; margin-top: 1rem;">
            Visualización tridimensional de la distribución de saldos por tipo de cuenta contable
        </p>
    </div>
    ''')
    plt.close(fig)

# 4. Gráfico 3D: Análisis de Liquidez (Scatter 3D)
if chart_data.get('analisis_patrimonial') and len(chart_data['analisis_patrimonial']) > 0:
    fig = plt.figure(figsize=(14, 10))
    ax = fig.add_subplot(111, projection='3d')
    
    activos = []
    pasivos = []
    patrimonios = []
    empresas = []
    
    for item in chart_data['analisis_patrimonial']:
        activos.append(float(item['activos'] or 0))
        pasivos.append(float(item['pasivos'] or 0))
        patrimonios.append(float(item['patrimonio'] or 0))
        empresas.append(item['empresa'])
    
    if len(activos) > 0:
        # Crear scatter 3D
        scatter = ax.scatter(activos, pasivos, patrimonios, 
                           c=range(len(activos)), cmap='rainbow', 
                           s=100, alpha=0.8, edgecolors='black', linewidth=0.5)
        
        ax.set_xlabel('Activos ($)', fontsize=12)
        ax.set_ylabel('Pasivos ($)', fontsize=12)
        ax.set_zlabel('Patrimonio ($)', fontsize=12)
        ax.set_title('Análisis de Posición Financiera 3D', fontsize=16, fontweight='bold', pad=20)
        
        # Agregar etiquetas para cada punto
        for i, empresa in enumerate(empresas):
            ax.text(activos[i], pasivos[i], patrimonios[i], 
                   f'  {empresa}', fontsize=8)
        
        # Barra de colores
        fig.colorbar(scatter, ax=ax, shrink=0.5, aspect=20, label='Empresa ID')
        ax.view_init(elev=20, azim=45)
        
        plt.tight_layout()
        chart_base64 = fig_to_base64(fig)
        charts_html.append(f'''
        <div class="chart-container">
            <h3 class="chart-title">🎯 Análisis de Posición Financiera 3D</h3>
            <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
            <p style="text-align: center; color: #666; margin-top: 1rem;">
                Diagrama de dispersión 3D mostrando la relación entre Activos, Pasivos y Patrimonio
            </p>
        </div>
        ''')
        plt.close(fig)

# Combinar todos los gráficos
if len(charts_html) == 0:
    final_html = '''
    <div class="alert-info">
        <h4>📊 No hay datos suficientes para generar gráficos 3D</h4>
        <p>Para visualizar estadísticas en 3D, necesita:</p>
        <ul>
            <li>✅ Registrar al menos una empresa</li>
            <li>✅ Crear un balance inicial con cuentas</li>
            <li>✅ Tener múltiples registros para comparación temporal</li>
        </ul>
        <p>Una vez que tenga datos, podrá ver gráficos 3D de:</p>
        <ul>
            <li>📈 Análisis patrimonial por empresa</li>
            <li>⏰ Evolución temporal de balances</li>
            <li>💼 Distribución de cuentas por tipo</li>
            <li>🎯 Análisis de posición financiera</li>
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
                    
                    // Mostrar en los contenedores
                    document.getElementById('summary-container').innerHTML = resumenHtml;
                    document.getElementById('charts-container').innerHTML = chartsHtml;
                    
                } catch (error) {
                    console.error('Error generando gráficos 3D:', error);
                    this.showError('Error al generar gráficos 3D: ' + error.message);
                } finally {
                    this.hideLoading();
                }
            },

            generateSummaryHtml() {
                if (!this.data || !this.data.resumen) {
                    return `
                    <div class="alert-info">
                        <h4>Cargando resumen de datos contables...</h4>
                        <p>Si este mensaje persiste, verifique que haya datos en el sistema.</p>
                    </div>
                    `;
                }
                
                const resumen = this.data.resumen;
                return `
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">${resumen.total_empresas || 0}</div>
                        <div class="stat-label">Empresas Registradas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${resumen.total_balances || 0}</div>
                        <div class="stat-label">Balances Creados</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${resumen.total_registros || 0}</div>
                        <div class="stat-label">Registros Contables</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$${parseFloat(resumen.suma_total_saldos || 0).toFixed(2)}</div>
                        <div class="stat-label">Suma Total Saldos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$${parseFloat(resumen.promedio_saldos || 0).toFixed(2)}</div>
                        <div class="stat-label">Promedio por Cuenta</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${resumen.tipos_cuenta || 0}</div>
                        <div class="stat-label">Tipos de Cuenta</div>
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
                    <div class="alert-danger">
                        <strong>❌ Error:</strong> ${message}
                        <br><br>
                        <button onclick="Statistics3D.init()" class="btn-3d">
                            🔄 Reintentar
                        </button>
                    </div>
                `;
                errorContainer.style.display = 'block';
            }
        };

        // Inicializar cuando la página esté lista
        document.addEventListener('DOMContentLoaded', function() {
            Statistics3D.init();
        });
    </script>
</body>
</html>
