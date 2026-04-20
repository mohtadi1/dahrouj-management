/**
 * Société Dahrouj Import Textile - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle with Overlay
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('show');
        if (sidebarOverlay) sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('show');
        if (sidebarOverlay) sidebarOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (sidebar && sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // User Dropdown
    const userBtn = document.getElementById('userBtn');
    const userMenu = document.getElementById('userMenu');
    
    if (userBtn && userMenu) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenu.classList.toggle('show');
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (userMenu) userMenu.classList.remove('show');
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-confirm]');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Êtes-vous sûr de vouloir supprimer cet élément?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Table row selection
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                const checkbox = this.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                }
            }
        });
    });
    
    // Select all checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected[]"]');
            checkboxes.forEach(function(cb) {
                cb.checked = selectAllCheckbox.checked;
            });
        });
    }
    
    // Tabs functionality
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            const target = this.dataset.target;
            
            // Remove active from all tabs
            tabs.forEach(function(t) {
                t.classList.remove('active');
            });
            
            // Add active to clicked tab
            this.classList.add('active');
            
            // Show target content
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(function(content) {
                content.classList.remove('active');
            });
            
            if (target) {
                const targetContent = document.getElementById(target);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            }
        });
    });
    
    // Modal functionality
    const modalTriggers = document.querySelectorAll('[data-modal]');
    modalTriggers.forEach(function(trigger) {
        trigger.addEventListener('click', function() {
            const modalId = this.dataset.modal;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
            }
        });
    });
    
    const modalCloseButtons = document.querySelectorAll('.modal-close, [data-close-modal]');
    modalCloseButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('show');
            }
        });
    });
    
    // Close modal when clicking outside
    const modalOverlays = document.querySelectorAll('.modal-overlay');
    modalOverlays.forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Add error message if not exists
                    let errorMsg = field.parentElement.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('span');
                        errorMsg.className = 'error-message';
                        errorMsg.style.color = '#dc3545';
                        errorMsg.style.fontSize = '12px';
                        errorMsg.style.marginTop = '5px';
                        errorMsg.style.display = 'block';
                        field.parentElement.appendChild(errorMsg);
                    }
                    errorMsg.textContent = 'Ce champ est obligatoire';
                } else {
                    field.classList.remove('is-invalid');
                    const errorMsg = field.parentElement.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // Remove invalid class on input
    const formInputs = document.querySelectorAll('.form-control');
    formInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    });
    
    // Search functionality for custom data-search attributes (existing)
    const searchInputs = document.querySelectorAll('[data-search]');
    searchInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const targetTable = document.querySelector(this.dataset.search);
            
            if (targetTable) {
                const rows = targetTable.querySelectorAll('tbody tr');
                rows.forEach(function(row) {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });
    });

    // ========== NOUVEAU : Recherche globale depuis l'en-tête ==========
    const globalSearchInput = document.getElementById('globalSearch');
    if (globalSearchInput) {
        globalSearchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            // 1. Filtrer tous les tableaux .data-table
            const tables = document.querySelectorAll('.data-table');
            tables.forEach(table => {
                const rows = table.querySelectorAll('tbody tr');
                let hasVisible = false;
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (searchTerm === '' || text.includes(searchTerm)) {
                        row.style.display = '';
                        hasVisible = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                // Afficher/masquer un message "aucun résultat"
                let noResultMsg = table.parentElement.querySelector('.no-search-result');
                if (!hasVisible && searchTerm !== '') {
                    if (!noResultMsg) {
                        noResultMsg = document.createElement('div');
                        noResultMsg.className = 'alert alert-info no-search-result';
                        noResultMsg.textContent = 'Aucun résultat trouvé pour "' + searchTerm + '"';
                        table.parentElement.appendChild(noResultMsg);
                    }
                } else if (noResultMsg) {
                    noResultMsg.remove();
                }
            });
            
            // 2. Filtrer les listes récentes (ex: dashboard)
            const recentLists = document.querySelectorAll('.recent-list');
            recentLists.forEach(list => {
                const items = list.querySelectorAll('.recent-item');
                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = (searchTerm === '' || text.includes(searchTerm)) ? '' : 'none';
                });
            });
        });
    }
    
    // Print functionality
    const printButtons = document.querySelectorAll('[data-print]');
    printButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Date picker enhancement
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function(input) {
        input.addEventListener('focus', function() {
            this.showPicker && this.showPicker();
        });
    });
    
    // Number formatting
    const numberInputs = document.querySelectorAll('input[data-number]');
    numberInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                const decimals = this.dataset.decimals || 3;
                this.value = value.toFixed(decimals);
            }
        });
    });
    
    // Dynamic table row addition
    const addRowButtons = document.querySelectorAll('[data-add-row]');
    addRowButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetTable = document.querySelector(this.dataset.addRow);
            if (targetTable) {
                const tbody = targetTable.querySelector('tbody');
                const template = targetTable.querySelector('template');
                
                if (template) {
                    const clone = template.content.cloneNode(true);
                    tbody.appendChild(clone);
                    
                    // Re-initialize delete buttons
                    initDeleteRowButtons();
                }
            }
        });
    });
    
    // Initialize delete row buttons
    function initDeleteRowButtons() {
        const deleteRowButtons = document.querySelectorAll('.delete-row');
        deleteRowButtons.forEach(function(btn) {
            btn.removeEventListener('click', deleteRow);
            btn.addEventListener('click', deleteRow);
        });
    }
    
    function deleteRow() {
        const row = this.closest('tr');
        if (row) {
            row.remove();
        }
    }
    
    initDeleteRowButtons();
    
    // Calculate totals in forms
    const quantityInputs = document.querySelectorAll('input[data-quantity]');
    const priceInputs = document.querySelectorAll('input[data-price]');
    
    function calculateTotal() {
        const rows = document.querySelectorAll('.calc-row');
        let grandTotal = 0;
        
        rows.forEach(function(row) {
            const qty = parseFloat(row.querySelector('[data-quantity]').value) || 0;
            const price = parseFloat(row.querySelector('[data-price]').value) || 0;
            const total = qty * price;
            
            const totalField = row.querySelector('[data-total]');
            if (totalField) {
                totalField.value = total.toFixed(3);
            }
            
            grandTotal += total;
        });
        
        const grandTotalField = document.querySelector('[data-grand-total]');
        if (grandTotalField) {
            grandTotalField.value = grandTotal.toFixed(3);
        }
    }
    
    quantityInputs.forEach(function(input) {
        input.addEventListener('input', calculateTotal);
    });
    
    priceInputs.forEach(function(input) {
        input.addEventListener('input', calculateTotal);
    });
    
    // Export to Excel
    const exportButtons = document.querySelectorAll('[data-export]');
    exportButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tableId = this.dataset.export;
            const table = document.getElementById(tableId);
            
            if (table) {
                let csv = [];
                const rows = table.querySelectorAll('tr');
                
                rows.forEach(function(row) {
                    const cols = row.querySelectorAll('td, th');
                    const rowData = [];
                    
                    cols.forEach(function(col) {
                        let data = col.textContent.replace(/"/g, '""');
                        rowData.push('"' + data + '"');
                    });
                    
                    csv.push(rowData.join(';'));
                });
                
                const csvContent = '\uFEFF' + csv.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                
                link.href = URL.createObjectURL(blob);
                link.download = 'export_' + new Date().toISOString().slice(0, 10) + '.csv';
                link.click();
            }
        });
    });
    
    // Tooltip initialization
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    tooltipTriggers.forEach(function(trigger) {
        trigger.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            tooltip.style.cssText = `
                position: fixed;
                background: var(--dark-color);
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 9999;
                pointer-events: none;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            
            this._tooltip = tooltip;
        });
        
        trigger.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
    
    // Loading spinner for form submissions
    const submitForms = document.querySelectorAll('form[data-loading]');
    submitForms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 8px;"></span> Chargement...';
            }
        });
    });
    
});

// Utility Functions
function formatMoney(amount, decimals = 3) {
    return parseFloat(amount).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' DT';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

function generateCode(prefix, id) {
    return prefix + '-' + new Date().getFullYear() + '-' + String(id).padStart(6, '0');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// AJAX Helper
function ajax(url, method = 'GET', data = null) {
    return new Promise(function(resolve, reject) {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        if (method === 'POST' && data) {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        }
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(xhr.response);
            } else {
                reject(xhr.statusText);
            }
        };
        
        xhr.onerror = () => reject(xhr.statusText);
        xhr.send(data);
    });
}

// Chart.js defaults if available
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Cairo', sans-serif";
    Chart.defaults.color = '#6c757d';
}