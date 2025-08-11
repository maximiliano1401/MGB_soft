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
        'distribucion_cuentas' => [],
        'resumen' => []
    ];
    
    try {
        // Balance por empresa - consulta simplificada
        $sql = "SELECT bi.empresa, COUNT(bid.id) as total_cuentas, SUM(bid.saldo) as total_saldo
                FROM balance_inicial bi
                LEFT JOIN balance_inicial_detalle bid ON bi.id = bid.balance_id
                GROUP BY bi.empresa
                ORDER BY bi.empresa";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data['balance_por_empresa'][] = $row;
            }
        }
        
        // Distribución por tipo de cuenta
        $sql = "SELECT c.tipo, COUNT(bid.id) as frecuencia, SUM(bid.saldo) as total_saldo
                FROM balance_inicial_detalle bid
                JOIN cuentas c ON bid.cuenta_codigo = c.codigo
                GROUP BY c.tipo
                ORDER BY total_saldo DESC";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data['distribucion_cuentas'][] = $row;
            }
        }
        
        // Resumen general
        $sql = "SELECT COUNT(DISTINCT bi.empresa) as total_empresas,
                       COUNT(DISTINCT bi.id) as total_balances,
                       COUNT(bid.id) as total_registros,
                       SUM(bid.saldo) as suma_total_saldos
                FROM balance_inicial bi
                LEFT JOIN balance_inicial_detalle bid ON bi.id = bid.balance_id";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $data['resumen'] = $result->fetch_assoc();
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
        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
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
        .btn-3d {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="stats-header">
        <h1>📊 Estadísticas 3D - MGB Contabilidad</h1>
        <p>Usuario: <?php echo $_SESSION['usuario_nombre']; ?></p>
    </div>

    <div style="text-align: center; margin-bottom: 2rem;">
        <button onclick="Statistics3D.generateCharts()" class="btn-3d">🎯 Generar Gráficos 3D</button>
        <button onclick="window.location.href='index.php'" class="btn-3d" style="background: #28a745;">🏠 Volver al Menú</button>
    </div>

    <div id="loading-indicator" style="display: none; text-align: center; padding: 2rem;">
        <div class="loading"></div>
        <p>Generando gráficos 3D...</p>
    </div>

    <div id="charts-container"></div>
    <div id="error-container" style="display: none;"></div>

    <script>
        const Statistics3D = {
            pyodide: null,
            data: null,

            async init() {
                try {
                    this.showLoading();
                    this.pyodide = await loadPyodide();
                    await this.pyodide.loadPackage(['matplotlib', 'numpy']);
                    await this.loadData();
                    await this.generateCharts();
                } catch (error) {
                    this.showError('Error: ' + error.message);
                } finally {
                    this.hideLoading();
                }
            },

            async loadData() {
                const response = await fetch('estadisticas_3d_simple.php?api=data');
                this.data = await response.json();
                if (this.data.error) throw new Error(this.data.error);
            },

            async generateCharts() {
                if (!this.pyodide || !this.data) {
                    await this.init();
                    return;
                }

                try {
                    this.showLoading();
                    document.getElementById('charts-container').innerHTML = '';
                    
                    const dataJson = JSON.stringify(this.data);
                    this.pyodide.globals.set('chart_data_json', dataJson);
                    
                    await this.pyodide.runPython(`
import matplotlib.pyplot as plt
import numpy as np
import json
from mpl_toolkits.mplot3d import Axes3D
import base64
from io import BytesIO

chart_data = json.loads(chart_data_json)

def fig_to_base64(fig):
    buffer = BytesIO()
    fig.savefig(buffer, format='png', dpi=100, bbox_inches='tight')
    buffer.seek(0)
    image_png = buffer.getvalue()
    buffer.close()
    return base64.b64encode(image_png).decode('utf-8')

charts_html = []

# Gráfico 1: Balance por empresa
if chart_data.get('balance_por_empresa'):
    fig = plt.figure(figsize=(12, 8))
    ax = fig.add_subplot(111, projection='3d')
    
    empresas = [item['empresa'] for item in chart_data['balance_por_empresa']]
    saldos = [float(item['total_saldo'] or 0) for item in chart_data['balance_por_empresa']]
    cuentas = [int(item['total_cuentas'] or 0) for item in chart_data['balance_por_empresa']]
    
    x = np.arange(len(empresas))
    y = np.array(cuentas)
    z = [0] * len(empresas)
    
    ax.bar3d(x, y, z, 0.8, 0.8, saldos, color='#3498db', alpha=0.7)
    
    ax.set_xlabel('Empresas')
    ax.set_ylabel('Número de Cuentas')
    ax.set_zlabel('Saldo Total')
    ax.set_title('Balance 3D por Empresa')
    ax.set_xticks(x)
    ax.set_xticklabels(empresas, rotation=45)
    
    chart_base64 = fig_to_base64(fig)
    charts_html.append(f'''
    <div class="chart-container">
        <h3>📈 Balance 3D por Empresa</h3>
        <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%;">
    </div>
    ''')
    plt.close(fig)

# Gráfico 2: Distribución por tipo
if chart_data.get('distribucion_cuentas'):
    fig = plt.figure(figsize=(10, 8))
    ax = fig.add_subplot(111, projection='3d')
    
    tipos = [item['tipo'] for item in chart_data['distribucion_cuentas']]
    saldos = [float(item['total_saldo'] or 0) for item in chart_data['distribucion_cuentas']]
    frecuencias = [int(item['frecuencia'] or 0) for item in chart_data['distribucion_cuentas']]
    
    x = np.arange(len(tipos))
    y = np.array(frecuencias)
    z = [0] * len(tipos)
    
    colors = ['#e74c3c', '#2ecc71', '#f39c12']
    for i in range(len(tipos)):
        ax.bar3d(x[i], 0, 0, 0.8, y[i], saldos[i], 
                color=colors[i % len(colors)], alpha=0.7)
    
    ax.set_xlabel('Tipos de Cuenta')
    ax.set_ylabel('Frecuencia')
    ax.set_zlabel('Saldo Total')
    ax.set_title('Distribución 3D por Tipo de Cuenta')
    ax.set_xticks(x)
    ax.set_xticklabels(tipos)
    
    chart_base64 = fig_to_base64(fig)
    charts_html.append(f'''
    <div class="chart-container">
        <h3>💼 Distribución 3D por Tipo</h3>
        <img src="data:image/png;base64,{chart_base64}" style="max-width: 100%;">
    </div>
    ''')
    plt.close(fig)

final_html = '\\n'.join(charts_html) if charts_html else '''
<div class="chart-container">
    <h3>📊 No hay datos disponibles</h3>
    <p>Registre balances para ver gráficos 3D</p>
</div>
'''
                    `);
                    
                    const chartsHtml = this.pyodide.globals.get('final_html');
                    document.getElementById('charts-container').innerHTML = chartsHtml;
                    
                } catch (error) {
                    this.showError('Error generando gráficos: ' + error.message);
                } finally {
                    this.hideLoading();
                }
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
                    <div style="background: #ffebee; border: 1px solid #f44336; color: #c62828; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                        <strong>❌ Error:</strong> ${message}
                        <br><br>
                        <button onclick="Statistics3D.init()" class="btn-3d">🔄 Reintentar</button>
                    </div>
                `;
                errorContainer.style.display = 'block';
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            Statistics3D.init();
        });
    </script>
</body>
</html>
