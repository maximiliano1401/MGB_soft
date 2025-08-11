<?php
session_start();

// Demo de datos para las estadísticas 3D
$demo_data = [
    'balance_por_empresa' => [
        ['empresa' => 'MGB Corp', 'total_cuentas' => '15', 'total_activos' => '150000', 'total_pasivos' => '80000', 'total_patrimonio' => '70000'],
        ['empresa' => 'Tech Solutions', 'total_cuentas' => '12', 'total_activos' => '95000', 'total_pasivos' => '45000', 'total_patrimonio' => '50000'],
        ['empresa' => 'Innovate SA', 'total_cuentas' => '18', 'total_activos' => '220000', 'total_pasivos' => '120000', 'total_patrimonio' => '100000'],
        ['empresa' => 'Global Trade', 'total_cuentas' => '10', 'total_activos' => '75000', 'total_pasivos' => '35000', 'total_patrimonio' => '40000'],
    ],
    'evolucion_temporal' => [
        ['fecha' => '2024-01-01', 'empresa' => 'MGB Corp', 'total_balance' => '120000', 'num_cuentas' => '12'],
        ['fecha' => '2024-02-01', 'empresa' => 'MGB Corp', 'total_balance' => '135000', 'num_cuentas' => '14'],
        ['fecha' => '2024-03-01', 'empresa' => 'MGB Corp', 'total_balance' => '150000', 'num_cuentas' => '15'],
        ['fecha' => '2024-01-01', 'empresa' => 'Tech Solutions', 'total_balance' => '80000', 'num_cuentas' => '10'],
        ['fecha' => '2024-02-01', 'empresa' => 'Tech Solutions', 'total_balance' => '88000', 'num_cuentas' => '11'],
        ['fecha' => '2024-03-01', 'empresa' => 'Tech Solutions', 'total_balance' => '95000', 'num_cuentas' => '12'],
        ['fecha' => '2024-01-01', 'empresa' => 'Innovate SA', 'total_balance' => '180000', 'num_cuentas' => '16'],
        ['fecha' => '2024-02-01', 'empresa' => 'Innovate SA', 'total_balance' => '200000', 'num_cuentas' => '17'],
        ['fecha' => '2024-03-01', 'empresa' => 'Innovate SA', 'total_balance' => '220000', 'num_cuentas' => '18'],
    ],
    'distribucion_cuentas' => [
        ['tipo' => 'Activo', 'cuenta_nombre' => 'Caja', 'total_saldo' => '50000', 'frecuencia' => '5'],
        ['tipo' => 'Activo', 'cuenta_nombre' => 'Bancos', 'total_saldo' => '120000', 'frecuencia' => '4'],
        ['tipo' => 'Activo', 'cuenta_nombre' => 'Inventarios', 'total_saldo' => '80000', 'frecuencia' => '3'],
        ['tipo' => 'Pasivo', 'cuenta_nombre' => 'Proveedores', 'total_saldo' => '60000', 'frecuencia' => '4'],
        ['tipo' => 'Pasivo', 'cuenta_nombre' => 'Préstamos Bancarios', 'total_saldo' => '100000', 'frecuencia' => '2'],
        ['tipo' => 'Patrimonio', 'cuenta_nombre' => 'Capital Social', 'total_saldo' => '150000', 'frecuencia' => '4'],
        ['tipo' => 'Patrimonio', 'cuenta_nombre' => 'Utilidades Retenidas', 'total_saldo' => '40000', 'frecuencia' => '3'],
    ],
    'analisis_patrimonial' => [
        ['empresa' => 'MGB Corp', 'fecha' => '2024-03-01', 'activos' => '150000', 'pasivos' => '80000', 'patrimonio' => '70000'],
        ['empresa' => 'Tech Solutions', 'fecha' => '2024-03-01', 'activos' => '95000', 'pasivos' => '45000', 'patrimonio' => '50000'],
        ['empresa' => 'Innovate SA', 'fecha' => '2024-03-01', 'activos' => '220000', 'pasivos' => '120000', 'patrimonio' => '100000'],
        ['empresa' => 'Global Trade', 'fecha' => '2024-03-01', 'activos' => '75000', 'pasivos' => '35000', 'patrimonio' => '40000'],
    ],
    'resumen' => [
        'total_empresas' => '4',
        'total_balances' => '12',
        'total_registros' => '48',
        'suma_total_saldos' => '540000',
        'promedio_saldos' => '11250',
        'tipos_cuenta' => '3'
    ],
    'empresas_activas' => [
        ['empresa' => 'MGB Corp', 'ultimo_balance' => '2024-03-01', 'num_balances' => '3'],
        ['empresa' => 'Tech Solutions', 'ultimo_balance' => '2024-03-01', 'num_balances' => '3'],
        ['empresa' => 'Innovate SA', 'ultimo_balance' => '2024-03-01', 'num_balances' => '3'],
        ['empresa' => 'Global Trade', 'ultimo_balance' => '2024-02-15', 'num_balances' => '2'],
    ]
];

// API endpoint para obtener datos demo
if (isset($_GET['api']) && $_GET['api'] == 'demo') {
    header('Content-Type: application/json');
    echo json_encode($demo_data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Estadísticas 3D - MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Pyodide CDN -->
    <script src="https://cdn.jsdelivr.net/pyodide/v0.24.1/full/pyodide.js"></script>
    <style>
        .demo-header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
            border-left: 4px solid #ff6b6b;
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
            border-bottom: 2px solid #ff6b6b;
        }
        
        .loading {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #ff6b6b;
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
        
        .btn-demo {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }
        
        .btn-demo:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }
        
        .alert-info {
            background: #e8f4fd;
            border: 1px solid #3498db;
            color: #2980b9;
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
    <div class="demo-header">
        <h1>🚀 Demo Estadísticas 3D - MGB Contabilidad</h1>
        <p>Demostración interactiva de gráficos tridimensionales con datos de ejemplo</p>
        <p><strong>MODO DEMO:</strong> Este es un ejemplo con datos simulados para mostrar las capacidades del sistema</p>
    </div>

    <!-- Controls Panel -->
    <div class="controls-panel">
        <button onclick="DemoStatistics3D.generateCharts()" class="btn-demo">
            🎯 Generar Gráficos Demo 3D
        </button>
        <button onclick="DemoStatistics3D.refreshData()" class="btn-demo">
            🔄 Actualizar Datos Demo
        </button>
        <button onclick="window.location.href='estadisticas_3d.php'" class="btn-demo" style="background: linear-gradient(45deg, #667eea, #764ba2);">
            📊 Ver Estadísticas Reales
        </button>
        <button onclick="window.location.href='index.php'" class="btn-demo" style="background: linear-gradient(45deg, #28a745, #20c997);">
            🏠 Volver al Menú
        </button>
    </div>

    <!-- Loading indicator -->
    <div id="loading-indicator" style="display: none; text-align: center; padding: 2rem;">
        <div class="loading"></div>
        <p style="margin-top: 1rem; color: #666;">Cargando Pyodide y generando gráficos 3D demo...</p>
    </div>

    <!-- Summary cards -->
    <div id="summary-container"></div>

    <!-- Charts container -->
    <div id="charts-container"></div>
    
    <!-- Error container -->
    <div id="error-container" style="display: none;"></div>

    <script>
        // Gestor de Demo Estadísticas 3D con Pyodide
        const DemoStatistics3D = {
            pyodide: null,
            data: null,

            async init() {
                try {
                    this.showLoading();
                    console.log('Inicializando Pyodide para demo de gráficos 3D...');
                    
                    // Cargar Pyodide
                    this.pyodide = await loadPyodide();
                    
                    // Instalar paquetes necesarios para gráficos 3D
                    await this.pyodide.loadPackage(['matplotlib', 'numpy']);
                    
                    console.log('Pyodide inicializado correctamente');
                    
                    // Cargar datos demo y generar gráficos
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
                    const response = await fetch('demo_estadisticas_3d.php?api=demo');
                    this.data = await response.json();
                    
                    console.log('Datos demo cargados:', this.data);
                } catch (error) {
                    console.error('Error cargando datos demo:', error);
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
                    
                    // Ejecutar el mismo código Python que en statistics.php pero con datos demo
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
        
        # Crear barras 3D con efectos visuales mejorados
        ax.bar(x - width, activos, width, label='Activos', color='#2ecc71', alpha=0.9, zdir='y', zs=0)
        ax.bar(x, pasivos, width, label='Pasivos', color='#e74c3c', alpha=0.9, zdir='y', zs=1)
        ax.bar(x + width, patrimonios, width, label='Patrimonio', color='#3498db', alpha=0.9, zdir='y', zs=2)
        
        ax.set_xlabel('Empresas', fontsize=12, fontweight='bold')
        ax.set_ylabel('Categorías', fontsize=12, fontweight='bold')
        ax.set_zlabel('Montos ($)', fontsize=12, fontweight='bold')
        ax.set_title('🏢 Análisis Patrimonial 3D por Empresa\\n(Datos Demo)', fontsize=16, fontweight='bold', pad=20)
        
        # Configurar etiquetas
        ax.set_xticks(x)
        ax.set_xticklabels(empresas, rotation=45, ha='right', fontsize=10)
        ax.set_yticks([0, 1, 2])
        ax.set_yticklabels(['Activos', 'Pasivos', 'Patrimonio'])
        
        ax.legend(loc='upper left', fontsize=10)
        ax.view_init(elev=20, azim=45)
        
        # Mejorar el aspecto visual
        ax.grid(True, alpha=0.3)
        ax.set_facecolor('#f8f9fa')
        
        plt.tight_layout()
        chart_base64 = fig_to_base64(fig)
        charts_html.append(f'''
        <div class="chart-container">
            <h3 class="chart-title">🏢 Análisis Patrimonial 3D por Empresa</h3>
            <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
            <p style="text-align: center; color: #666; margin-top: 1rem;">
                <strong>Vista tridimensional del balance patrimonial</strong><br>
                Comparación de Activos, Pasivos y Patrimonio por empresa (datos demo)
            </p>
        </div>
        ''')
        plt.close(fig)

# 2. Gráfico 3D: Evolución Temporal de Balances con superficie mejorada
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
        
        # Crear superficie 3D con gradiente de colores
        surf = ax.plot_surface(X, Y, Z, cmap='plasma', alpha=0.8, linewidth=0.2, edgecolors='white')
        
        ax.set_xlabel('Períodos de Tiempo', fontsize=12, fontweight='bold')
        ax.set_ylabel('Empresas', fontsize=12, fontweight='bold')
        ax.set_zlabel('Balance Total ($)', fontsize=12, fontweight='bold')
        ax.set_title('📈 Evolución Temporal de Balances 3D\\n(Datos Demo)', fontsize=16, fontweight='bold', pad=20)
        
        # Configurar etiquetas
        ax.set_xticks(range(len(fechas)))
        ax.set_xticklabels([f.split('-')[1] + '/' + f.split('-')[0][-2:] for f in fechas], rotation=45)
        ax.set_yticks(range(len(empresas_list)))
        ax.set_yticklabels([emp[:12] for emp in empresas_list])
        
        # Barra de colores
        fig.colorbar(surf, ax=ax, shrink=0.6, aspect=25, label='Balance ($)')
        ax.view_init(elev=30, azim=45)
        
        # Efectos visuales
        ax.grid(True, alpha=0.3)
        ax.set_facecolor('#f8f9fa')
        
        plt.tight_layout()
        chart_base64 = fig_to_base64(fig)
        charts_html.append(f'''
        <div class="chart-container">
            <h3 class="chart-title">📈 Evolución Temporal de Balances 3D</h3>
            <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
            <p style="text-align: center; color: #666; margin-top: 1rem;">
                <strong>Superficie 3D de evolución temporal</strong><br>
                Muestra el crecimiento de los balances por empresa a través del tiempo (datos demo)
            </p>
        </div>
        ''')
        plt.close(fig)

# 3. Gráfico 3D: Distribución de Cuentas por Tipo con efectos visuales
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
            
            # Crear barras 3D para cada tipo con efectos mejorados
            bars = ax.bar(x, saldos, zs=z_offset, zdir='y', alpha=0.8, 
                         color=colors[i % len(colors)], label=tipo, width=0.6)
            
            # Agregar valores en las barras
            for j, (bar, saldo) in enumerate(zip(bars, saldos)):
                ax.text(j, z_offset, saldo + max(saldos) * 0.05, 
                       f'${saldo:,.0f}', ha='center', va='bottom', fontsize=8)
            
            z_offset += 1
    
    ax.set_xlabel('Cuentas Contables', fontsize=12, fontweight='bold')
    ax.set_ylabel('Tipos de Cuenta', fontsize=12, fontweight='bold')
    ax.set_zlabel('Saldo Total ($)', fontsize=12, fontweight='bold')
    ax.set_title('💼 Distribución 3D de Cuentas por Tipo\\n(Datos Demo)', fontsize=16, fontweight='bold', pad=20)
    
    ax.legend(loc='upper right', fontsize=10)
    ax.view_init(elev=25, azim=35)
    
    # Efectos visuales
    ax.grid(True, alpha=0.3)
    ax.set_facecolor('#f8f9fa')
    
    plt.tight_layout()
    chart_base64 = fig_to_base64(fig)
    charts_html.append(f'''
    <div class="chart-container">
        <h3 class="chart-title">💼 Distribución 3D de Cuentas por Tipo</h3>
        <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
        <p style="text-align: center; color: #666; margin-top: 1rem;">
            <strong>Visualización tridimensional por categorías</strong><br>
            Distribución de saldos por tipo de cuenta contable (datos demo)
        </p>
    </div>
    ''')
    plt.close(fig)

# 4. Gráfico 3D: Análisis de Liquidez mejorado (Scatter 3D)
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
        # Crear scatter 3D con tamaños variables
        sizes = [max(50, a/1000) for a in activos]  # Tamaño basado en activos
        
        scatter = ax.scatter(activos, pasivos, patrimonios, 
                           c=range(len(activos)), cmap='viridis', 
                           s=sizes, alpha=0.8, edgecolors='black', linewidth=1)
        
        ax.set_xlabel('Activos ($)', fontsize=12, fontweight='bold')
        ax.set_ylabel('Pasivos ($)', fontsize=12, fontweight='bold')
        ax.set_zlabel('Patrimonio ($)', fontsize=12, fontweight='bold')
        ax.set_title('🎯 Análisis de Posición Financiera 3D\\n(Datos Demo)', fontsize=16, fontweight='bold', pad=20)
        
        # Agregar etiquetas para cada punto con mejor posicionamiento
        for i, empresa in enumerate(empresas):
            ax.text(activos[i], pasivos[i], patrimonios[i] + max(patrimonios) * 0.05, 
                   f'{empresa}', fontsize=9, ha='center')
        
        # Líneas de referencia para mejor interpretación
        max_val = max(max(activos), max(pasivos), max(patrimonios))
        ax.plot([0, max_val], [0, max_val], [0, 0], 'r--', alpha=0.5, label='Línea Activos=Pasivos')
        
        # Barra de colores
        fig.colorbar(scatter, ax=ax, shrink=0.6, aspect=25, label='Empresa ID')
        ax.view_init(elev=20, azim=45)
        
        # Efectos visuales
        ax.grid(True, alpha=0.3)
        ax.set_facecolor('#f8f9fa')
        ax.legend()
        
        plt.tight_layout()
        chart_base64 = fig_to_base64(fig)
        charts_html.append(f'''
        <div class="chart-container">
            <h3 class="chart-title">🎯 Análisis de Posición Financiera 3D</h3>
            <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%; height: auto;">
            <p style="text-align: center; color: #666; margin-top: 1rem;">
                <strong>Diagrama de dispersión 3D con análisis financiero</strong><br>
                Relación entre Activos, Pasivos y Patrimonio por empresa (datos demo)
            </p>
        </div>
        ''')
        plt.close(fig)

# Combinar todos los gráficos
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
                    console.error('Error generando gráficos 3D demo:', error);
                    this.showError('Error al generar gráficos 3D demo: ' + error.message);
                } finally {
                    this.hideLoading();
                }
            },

            generateSummaryHtml() {
                if (!this.data || !this.data.resumen) {
                    return '';
                }
                
                const resumen = this.data.resumen;
                return `
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">${resumen.total_empresas || 0}</div>
                        <div class="stat-label">Empresas Demo</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${resumen.total_balances || 0}</div>
                        <div class="stat-label">Balances Demo</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">${resumen.total_registros || 0}</div>
                        <div class="stat-label">Registros Demo</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$${parseFloat(resumen.suma_total_saldos || 0).toFixed(2)}</div>
                        <div class="stat-label">Suma Total Demo</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$${parseFloat(resumen.promedio_saldos || 0).toFixed(2)}</div>
                        <div class="stat-label">Promedio Demo</div>
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
                        <button onclick="DemoStatistics3D.init()" class="btn-demo">
                            🔄 Reintentar Demo
                        </button>
                    </div>
                `;
                errorContainer.style.display = 'block';
            }
        };

        // Inicializar cuando la página esté lista
        document.addEventListener('DOMContentLoaded', function() {
            DemoStatistics3D.init();
        });
    </script>
</body>
</html>
