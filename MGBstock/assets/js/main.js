/**
 * MGBStock - Sistema de Inventario
 * JavaScript Principal
 */

// Configuración global
const MGBStock = {
    apiUrl: window.location.origin + '/MGB_soft/MGBstock/',
    
    // Inicializar la aplicación
    init: function() {
        this.setupEventListeners();
        this.loadAlerts();
        this.setupModals();
    },

    // Configurar event listeners globales
    setupEventListeners: function() {
        // Confirmación para eliminar registros
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete')) {
                if (!confirm('¿Está seguro de que desea eliminar este registro?')) {
                    e.preventDefault();
                }
            }
        });

        // Auto-hide alerts después de 5 segundos
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    },

    // Cargar alertas desde sesión
    loadAlerts: function() {
        // Las alertas se manejan desde PHP/sesión
    },

    // Configurar modales
    setupModals: function() {
        // Cerrar modal al hacer clic en X o fuera del modal
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('close') || e.target.classList.contains('modal')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            }
        });

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal[style*="block"]');
                if (openModal) {
                    openModal.style.display = 'none';
                }
            }
        });
    },

    // Mostrar modal
    showModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
        }
    },

    // Ocultar modal
    hideModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    },

    // Mostrar loading en botón
    showLoading: function(button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="loading"></span> Procesando...';
        button.dataset.originalText = originalText;
    },

    // Ocultar loading en botón
    hideLoading: function(button) {
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
            button.disabled = false;
        }
    },

    // Formatear precio
    formatPrice: function(price) {
        return 'S/ ' + parseFloat(price).toFixed(2);
    },

    // Formatear fecha
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-PE') + ' ' + date.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'});
    },

    // Validar formulario
    validateForm: function(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;

        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#e74c3c';
                isValid = false;
            } else {
                field.style.borderColor = '#ddd';
            }
        });

        return isValid;
    },

    // Realizar petición AJAX
    ajax: function(options) {
        const defaultOptions = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        };

        const config = Object.assign(defaultOptions, options);

        return fetch(config.url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .catch(error => {
                console.error('Error:', error);
                throw error;
            });
    },

    // Mostrar notificación
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.innerHTML = message;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(notification, container.firstChild);
            
            // Auto-hide después de 5 segundos
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }
    }
};

// Funciones específicas para módulos

// Gestión de Empresas
const CompanyManager = {
    select: function(companyId) {
        if (confirm('¿Desea trabajar con esta empresa?')) {
            window.location.href = `select_company.php?id=${companyId}`;
        }
    },

    showAddForm: function() {
        MGBStock.showModal('addCompanyModal');
    },

    showEditForm: async function(companyId) {
        try {
            const response = await fetch(`companies.php?api=get&id=${companyId}`);
            const data = await response.json();
            
            if (data.success) {
                const empresa = data.empresa;
                
                // Llenar el formulario con los datos
                document.getElementById('edit_id').value = empresa.id;
                document.getElementById('edit_nombre').value = empresa.nombre || '';
                document.getElementById('edit_direccion').value = empresa.direccion || '';
                document.getElementById('edit_telefono').value = empresa.telefono || '';
                document.getElementById('edit_ruc').value = empresa.ruc || '';
                document.getElementById('edit_email').value = empresa.email || '';
                
                // Configurar moneda
                const monedaValue = `${empresa.moneda_codigo || 'PEN'}|${empresa.moneda_simbolo || 'S/'}|${empresa.moneda_nombre || 'Sol Peruano'}`;
                document.getElementById('edit_moneda').value = monedaValue;
                document.getElementById('edit_moneda_simbolo').value = empresa.moneda_simbolo || 'S/';
                document.getElementById('edit_moneda_codigo').value = empresa.moneda_codigo || 'PEN';
                document.getElementById('edit_moneda_nombre').value = empresa.moneda_nombre || 'Sol Peruano';
                
                MGBStock.showModal('editCompanyModal');
            } else {
                MGBStock.showNotification(data.message, 'danger');
            }
        } catch (error) {
            MGBStock.showNotification('Error al cargar datos de la empresa', 'danger');
        }
    },

    updateCurrencyFields: function() {
        const select = document.getElementById('moneda');
        const selectedValue = select.value;
        
        if (selectedValue) {
            const [codigo, simbolo, nombre] = selectedValue.split('|');
            document.getElementById('moneda_codigo').value = codigo;
            document.getElementById('moneda_simbolo').value = simbolo;
            document.getElementById('moneda_nombre').value = nombre;
        }
    },

    updateEditCurrencyFields: function() {
        const select = document.getElementById('edit_moneda');
        const selectedValue = select.value;
        
        if (selectedValue) {
            const [codigo, simbolo, nombre] = selectedValue.split('|');
            document.getElementById('edit_moneda_codigo').value = codigo;
            document.getElementById('edit_moneda_simbolo').value = simbolo;
            document.getElementById('edit_moneda_nombre').value = nombre;
        }
    },

    add: function() {
        if (!MGBStock.validateForm('addCompanyForm')) {
            MGBStock.showNotification('Por favor complete todos los campos requeridos', 'danger');
            return;
        }

        const form = document.getElementById('addCompanyForm');
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');

        MGBStock.showLoading(button);

        fetch('companies.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MGBStock.showNotification('Empresa agregada exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                MGBStock.showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            MGBStock.showNotification('Error al agregar empresa', 'danger');
        })
        .finally(() => {
            MGBStock.hideLoading(button);
        });
    },

    edit: function() {
        if (!MGBStock.validateForm('editCompanyForm')) {
            MGBStock.showNotification('Por favor complete todos los campos requeridos', 'danger');
            return;
        }

        const form = document.getElementById('editCompanyForm');
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');

        MGBStock.showLoading(button);

        fetch('companies.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MGBStock.showNotification('Empresa actualizada exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                MGBStock.showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            MGBStock.showNotification('Error al actualizar empresa', 'danger');
        })
        .finally(() => {
            MGBStock.hideLoading(button);
        });
    }
};

// Gestión de Categorías
const CategoryManager = {
    showAddForm: function() {
        MGBStock.showModal('addCategoryModal');
    },

    add: function() {
        if (!MGBStock.validateForm('addCategoryForm')) {
            MGBStock.showNotification('Por favor complete todos los campos requeridos', 'danger');
            return;
        }

        const form = document.getElementById('addCategoryForm');
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');

        MGBStock.showLoading(button);

        fetch('categories.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MGBStock.showNotification('Categoría agregada exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                MGBStock.showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            MGBStock.showNotification('Error al agregar categoría', 'danger');
        })
        .finally(() => {
            MGBStock.hideLoading(button);
        });
    },

    edit: function(id) {
        // Implementar edición de categorías
        window.location.href = `categories.php?edit=${id}`;
    },

    delete: function(id) {
        if (confirm('¿Está seguro de que desea eliminar esta categoría?')) {
            window.location.href = `categories.php?delete=${id}`;
        }
    }
};

// Gestión de Productos
const ProductManager = {
    showAddForm: function() {
        MGBStock.showModal('addProductModal');
    },

    generateCode: function() {
        const codeField = document.getElementById('producto_codigo');
        if (codeField) {
            const prefix = 'PROD';
            const randomNum = Math.floor(Math.random() * 99999).toString().padStart(5, '0');
            codeField.value = prefix + randomNum;
        }
    },

    add: function() {
        if (!MGBStock.validateForm('addProductForm')) {
            MGBStock.showNotification('Por favor complete todos los campos requeridos', 'danger');
            return;
        }

        const form = document.getElementById('addProductForm');
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');

        MGBStock.showLoading(button);

        fetch('products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MGBStock.showNotification('Producto agregado exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                MGBStock.showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            MGBStock.showNotification('Error al agregar producto', 'danger');
        })
        .finally(() => {
            MGBStock.hideLoading(button);
        });
    },

    edit: function(id) {
        window.location.href = `products.php?edit=${id}`;
    },

    delete: function(id) {
        if (confirm('¿Está seguro de que desea eliminar este producto?')) {
            window.location.href = `products.php?delete=${id}`;
        }
    }
};

// Gestión de Ventas
const SalesManager = {
    showAddForm: function() {
        MGBStock.showModal('addSaleModal');
    },

    updateTotal: function() {
        const quantityField = document.getElementById('venta_cantidad');
        const priceField = document.getElementById('venta_precio');
        const totalField = document.getElementById('venta_total');

        if (quantityField && priceField && totalField) {
            const quantity = parseFloat(quantityField.value) || 0;
            const price = parseFloat(priceField.value) || 0;
            const total = quantity * price;
            totalField.value = total.toFixed(2);
        }
    },

    checkStock: function() {
        const productSelect = document.getElementById('venta_producto');
        const quantityField = document.getElementById('venta_cantidad');
        const stockInfo = document.getElementById('stock_info');

        if (productSelect && quantityField && stockInfo) {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const availableStock = parseInt(selectedOption.dataset.stock) || 0;
            const requestedQuantity = parseInt(quantityField.value) || 0;

            stockInfo.innerHTML = `Stock disponible: ${availableStock}`;

            if (requestedQuantity > availableStock) {
                stockInfo.innerHTML += ' <span style="color: #e74c3c;">(Cantidad insuficiente)</span>';
                quantityField.style.borderColor = '#e74c3c';
                return false;
            } else {
                quantityField.style.borderColor = '#ddd';
                return true;
            }
        }
        return true;
    },

    add: function() {
        if (!this.checkStock()) {
            MGBStock.showNotification('Stock insuficiente para la venta', 'danger');
            return;
        }

        if (!MGBStock.validateForm('addSaleForm')) {
            MGBStock.showNotification('Por favor complete todos los campos requeridos', 'danger');
            return;
        }

        const form = document.getElementById('addSaleForm');
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');

        MGBStock.showLoading(button);

        fetch('sales.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MGBStock.showNotification('Venta registrada exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                MGBStock.showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            MGBStock.showNotification('Error al registrar venta', 'danger');
        })
        .finally(() => {
            MGBStock.hideLoading(button);
        });
    }
};

// Gestión de Compras
const PurchaseManager = {
    showAddForm: function() {
        MGBStock.showModal('addPurchaseModal');
    },

    updateTotal: function() {
        const quantityField = document.getElementById('compra_cantidad');
        const priceField = document.getElementById('compra_precio');
        const totalField = document.getElementById('compra_total');

        if (quantityField && priceField && totalField) {
            const quantity = parseFloat(quantityField.value) || 0;
            const price = parseFloat(priceField.value) || 0;
            const total = quantity * price;
            totalField.value = total.toFixed(2);
        }
    },

    add: function() {
        if (!MGBStock.validateForm('addPurchaseForm')) {
            MGBStock.showNotification('Por favor complete todos los campos requeridos', 'danger');
            return;
        }

        const form = document.getElementById('addPurchaseForm');
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');

        MGBStock.showLoading(button);

        fetch('purchases.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                MGBStock.showNotification('Compra registrada exitosamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                MGBStock.showNotification(data.message, 'danger');
            }
        })
        .catch(error => {
            MGBStock.showNotification('Error al registrar compra', 'danger');
        })
        .finally(() => {
            MGBStock.hideLoading(button);
        });
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    MGBStock.init();
});
