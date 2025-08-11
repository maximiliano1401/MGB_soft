<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'php/conexion.php';

// API simple para datos
if (isset($_GET['api']) && $_GET['api'] == 'data') {
    header('Content-Type: application/json');
    
    $data = array(
        'empresas' => array(),
        'success' => true
    );
    
    try {
        $sql = "SELECT bi.empresa, COALESCE(SUM(bid.saldo), 0) as total_saldo 
                FROM balance_inicial bi 
                LEFT JOIN balance_inicial_detalle bid ON bi.id = bid.balance_id 
                GROUP BY bi.empresa 
                ORDER BY bi.empresa 
                LIMIT 10";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data['empresas'][] = array(
                    'empresa' => $row['empresa'],
                    'total_saldo' => floatval($row['total_saldo'])
                );
            }
        }
        
        $data['total_empresas'] = count($data['empresas']);
        
    } catch (Exception $e) {
        $data['success'] = false;
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
    <title>Estadísticas - MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; border-radius: 10px; margin-bottom: 20px; }
        .controls { text-align: center; margin: 20px 0; }
        .btn { background: #667eea; color: white; border: none; padding: 15px 25px; border-radius: 8px; cursor: pointer; margin: 5px; font-size: 14px; }
        .btn:hover { background: #5a67d8; transform: translateY(-1px); }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .chart-area { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 20px 0; }
        .loading-container { text-align: center; padding: 40px; }
        .spinner { width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .error-box { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .success-box { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .data-info { background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Estadísticas Contables - MGB</h1>
            <p>Sistema de visualización de datos</p>
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
        </div>

        <div class="controls">
            <button onclick="cargarDatos()" class="btn">📈 Cargar Datos</button>
            <button onclick="generarGrafico()" class="btn" id="btnGrafico" disabled>🎯 Generar Gráfico</button>
            <button onclick="window.location.href='index.php'" class="btn btn-success">🏠 Volver al Menú</button>
        </div>

        <div id="mensaje-area"></div>
        <div id="datos-area"></div>
        <div id="loading-area" style="display: none;">
            <div class="loading-container">
                <div class="spinner"></div>
                <p>Procesando...</p>
            </div>
        </div>
        <div id="grafico-area"></div>
    </div>

    <script>
        let datosEmpresa = null;
        let pyodideReady = false;
        let pyodide = null;

        // Función para mostrar mensajes
        function mostrarMensaje(mensaje, tipo) {
            const area = document.getElementById('mensaje-area');
            const clase = tipo === 'error' ? 'error-box' : 'success-box';
            area.innerHTML = `<div class="${clase}">${mensaje}</div>`;
            setTimeout(() => { area.innerHTML = ''; }, 5000);
        }

        // Función para cargar datos del servidor
        async function cargarDatos() {
            try {
                document.getElementById('loading-area').style.display = 'block';
                mostrarMensaje('Cargando datos...', 'info');
                
                const response = await fetch('estadisticas_ok.php?api=data');
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Error al cargar datos');
                }
                
                datosEmpresa = data;
                mostrarDatos(data);
                document.getElementById('btnGrafico').disabled = false;
                mostrarMensaje('Datos cargados correctamente', 'success');
                
            } catch (error) {
                mostrarMensaje('Error: ' + error.message, 'error');
                console.error('Error cargando datos:', error);
            } finally {
                document.getElementById('loading-area').style.display = 'none';
            }
        }

        // Función para mostrar los datos cargados
        function mostrarDatos(data) {
            let html = '<div class="data-info">';
            html += '<h3>📋 Datos Cargados</h3>';
            html += `<p><strong>Total de empresas:</strong> ${data.total_empresas}</p>`;
            
            if (data.empresas && data.empresas.length > 0) {
                html += '<h4>Empresas encontradas:</h4><ul>';
                data.empresas.forEach(emp => {
                    html += `<li>${emp.empresa}: $${emp.total_saldo.toFixed(2)}</li>`;
                });
                html += '</ul>';
            } else {
                html += '<p>No se encontraron empresas con datos.</p>';
            }
            
            html += '</div>';
            document.getElementById('datos-area').innerHTML = html;
        }

        // Función para inicializar Pyodide
        async function inicializarPyodide() {
            if (pyodideReady) return true;
            
            try {
                document.getElementById('loading-area').style.display = 'block';
                mostrarMensaje('Cargando sistema de gráficos...', 'info');
                
                pyodide = await loadPyodide();
                await pyodide.loadPackage(['matplotlib', 'numpy']);
                
                pyodideReady = true;
                mostrarMensaje('Sistema de gráficos listo', 'success');
                return true;
                
            } catch (error) {
                mostrarMensaje('Error inicializando gráficos: ' + error.message, 'error');
                return false;
            } finally {
                document.getElementById('loading-area').style.display = 'none';
            }
        }

        // Función para generar gráfico
        async function generarGrafico() {
            if (!datosEmpresa || !datosEmpresa.empresas || datosEmpresa.empresas.length === 0) {
                mostrarMensaje('Primero debe cargar los datos', 'error');
                return;
            }

            try {
                document.getElementById('loading-area').style.display = 'block';
                
                // Inicializar Pyodide si no está listo
                const pyodideOk = await inicializarPyodide();
                if (!pyodideOk) return;

                // Preparar datos para Python
                const empresas = datosEmpresa.empresas.map(e => e.empresa);
                const saldos = datosEmpresa.empresas.map(e => e.total_saldo);
                
                pyodide.globals.set('empresas_list', empresas);
                pyodide.globals.set('saldos_list', saldos);
                
                // Ejecutar código Python 3D
                await pyodide.runPython(`
import matplotlib.pyplot as plt
from mpl_toolkits.mplot3d import Axes3D
import numpy as np
import base64
from io import BytesIO

# Crear figura 3D
fig = plt.figure(figsize=(12, 8))
ax = fig.add_subplot(111, projection='3d')

# Datos
empresas = empresas_list
saldos = saldos_list

# Preparar datos para barras 3D
xpos = np.arange(len(empresas))
ypos = np.zeros(len(empresas))
zpos = np.zeros(len(empresas))
dx = np.ones(len(empresas)) * 0.8
dy = np.ones(len(empresas)) * 0.8
dz = saldos

# Colores 3D
colores = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#34495e']
colors = [colores[i % len(colores)] for i in range(len(empresas))]

# Crear gráfico de barras 3D
bars = ax.bar3d(xpos, ypos, zpos, dx, dy, dz, color=colors, alpha=0.8)

# Personalizar
ax.set_title('Saldos por Empresa (3D)', fontsize=16, fontweight='bold', pad=20)
ax.set_xlabel('Empresas')
ax.set_ylabel('Profundidad')
ax.set_zlabel('Saldo Total')

# Configurar etiquetas del eje X
ax.set_xticks(xpos)
ax.set_xticklabels(empresas, rotation=45, ha='right')

# Ajustar vista 3D
ax.view_init(elev=30, azim=45)

# Agregar valores en las barras
for i, (x, z) in enumerate(zip(xpos, saldos)):
    ax.text(x, 0, z + max(saldos)*0.02, f'{z:.0f}', 
            ha='center', va='bottom', fontsize=9)

plt.tight_layout()

# Convertir a base64
buffer = BytesIO()
fig.savefig(buffer, format='png', dpi=100, bbox_inches='tight')
buffer.seek(0)
image_png = buffer.getvalue()
buffer.close()
chart_base64 = base64.b64encode(image_png).decode('utf-8')

plt.close(fig)
                `);
                
                // Obtener imagen generada
                const chartBase64 = pyodide.globals.get('chart_base64');
                
                // Mostrar gráfico 3D
                const graficoHtml = `
                    <div class="chart-area">
                        <h3>📊 Gráfico 3D de Saldos por Empresa</h3>
                        <img src="data:image/png;base64,${chartBase64}" style="max-width: 100%; height: auto;">
                        <p style="text-align: center; margin-top: 15px; color: #666;">
                            Gráfico 3D generado con ${datosEmpresa.empresas.length} empresas
                        </p>
                    </div>
                `;
                
                document.getElementById('grafico-area').innerHTML = graficoHtml;
                mostrarMensaje('Gráfico 3D generado exitosamente', 'success');
                
            } catch (error) {
                mostrarMensaje('Error generando gráfico: ' + error.message, 'error');
                console.error('Error:', error);
            } finally {
                document.getElementById('loading-area').style.display = 'none';
            }
        }

        // Cargar datos automáticamente al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarDatos();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/pyodide/v0.24.1/full/pyodide.js"></script>
</body>
</html>
