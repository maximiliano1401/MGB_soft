<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'php/conexion.php';

// API para datos
if (isset($_GET['api']) && $_GET['api'] == 'data') {
    header('Content-Type: application/json');
    
    $data = ['empresas' => [], 'resumen' => ['total_empresas' => 0, 'total_saldos' => 0]];
    
    try {
        $sql = "SELECT bi.empresa, SUM(bid.saldo) as total_saldo FROM balance_inicial bi 
                LEFT JOIN balance_inicial_detalle bid ON bi.id = bid.balance_id 
                GROUP BY bi.empresa ORDER BY bi.empresa";
        $result = $conn->query($sql);
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $data['empresas'][] = $row;
            }
        }
        $data['resumen']['total_empresas'] = count($data['empresas']);
        $data['resumen']['total_saldos'] = array_sum(array_column($data['empresas'], 'total_saldo'));
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
    <title>Estadísticas 3D - MGB</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/pyodide/v0.24.1/full/pyodide.js"></script>
    <style>
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 2rem; text-align: center; margin: 1rem 0; border-radius: 10px; }
        .chart { background: white; padding: 2rem; margin: 1rem 0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .loading { width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .btn { background: #667eea; color: white; border: none; padding: 15px 30px; border-radius: 25px; cursor: pointer; margin: 10px; font-size: 16px; }
        .btn:hover { background: #5a67d8; }
        .error { background: #fee; border: 1px solid #f00; color: #c00; padding: 1rem; border-radius: 5px; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Estadísticas 3D - MGB Contabilidad</h1>
        <p>Usuario: <?php echo $_SESSION['usuario_nombre']; ?></p>
    </div>

    <div style="text-align: center;">
        <button onclick="generateChart()" class="btn">🎯 Generar Gráfico 3D</button>
        <button onclick="window.location.href='index.php'" class="btn" style="background: #28a745;">🏠 Volver</button>
    </div>

    <div id="loading" style="display: none; text-align: center;">
        <div class="loading"></div>
        <p>Cargando Pyodide y generando gráfico...</p>
    </div>

    <div id="chart-container"></div>
    <div id="error-container"></div>

    <script>
        let pyodide = null;

        async function loadPyodide() {
            if (pyodide) return pyodide;
            
            document.getElementById('loading').style.display = 'block';
            try {
                pyodide = await window.loadPyodide();
                await pyodide.loadPackage(['matplotlib', 'numpy']);
                console.log('Pyodide cargado exitosamente');
                return pyodide;
            } catch (error) {
                showError('Error cargando Pyodide: ' + error.message);
                throw error;
            }
        }

        async function generateChart() {
            try {
                document.getElementById('loading').style.display = 'block';
                document.getElementById('error-container').innerHTML = '';
                
                if (!pyodide) {
                    await loadPyodide();
                }

                const response = await fetch('estadisticas_final.php?api=data');
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }

                pyodide.globals.set('chart_data_json', JSON.stringify(data));
                
                await pyodide.runPython(`
import matplotlib.pyplot as plt
import numpy as np
import json
import base64
from io import BytesIO

data = json.loads(chart_data_json)

def fig_to_base64(fig):
    buffer = BytesIO()
    fig.savefig(buffer, format='png', dpi=100, bbox_inches='tight', facecolor='white')
    buffer.seek(0)
    image_png = buffer.getvalue()
    buffer.close()
    return base64.b64encode(image_png).decode('utf-8')

fig, ax = plt.subplots(figsize=(10, 6))

if data.get('empresas') and len(data['empresas']) > 0:
    empresas = [item['empresa'] for item in data['empresas']]
    saldos = [float(item['total_saldo'] or 0) for item in data['empresas']]
    
    bars = ax.bar(empresas, saldos, color=['#3498db', '#e74c3c', '#2ecc71', '#f39c12'])
    
    ax.set_title('Saldos por Empresa', fontsize=16, fontweight='bold', pad=20)
    ax.set_xlabel('Empresas', fontsize=12)
    ax.set_ylabel('Saldo Total', fontsize=12)
    ax.tick_params(axis='x', rotation=45)
    
    for bar, saldo in zip(bars, saldos):
        height = bar.get_height()
        ax.text(bar.get_x() + bar.get_width()/2., height, str(int(saldo)), ha='center', va='bottom', fontsize=10)
    
    plt.grid(True, alpha=0.3)
    plt.tight_layout()
    
    chart_base64 = fig_to_base64(fig)
    
    chart_html = '<div class="chart"><h3>Análisis de Saldos</h3><img src="data:image/png;base64,' + chart_base64 + '" style="max-width: 100%;"><p>Total empresas: ' + str(len(empresas)) + '</p></div>'
else:
    chart_html = '<div class="chart"><h3>No hay datos</h3><p>Registre balances para ver gráficos.</p></div>'

plt.close(fig)
                `);
                
                const chartHtml = pyodide.globals.get('chart_html');
                document.getElementById('chart-container').innerHTML = chartHtml;
                
            } catch (error) {
                showError('Error: ' + error.message);
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }

        function showError(message) {
            document.getElementById('error-container').innerHTML = `
                <div class="error">
                    <strong>❌ Error:</strong> ${message}
                    <br><br>
                    <button onclick="generateChart()" class="btn">🔄 Reintentar</button>
                </div>
            `;
        }

        // Auto-generar al cargar
        document.addEventListener('DOMContentLoaded', function() {
            generateChart();
        });
    </script>
</body>
</html>
