import './bootstrap';
// Application School Management - Scripts principaux
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialisation des composants
    initializeNotifications();
    initializeForms();
    initializeDataTables();
    initializeSelect2();
    initializeFlatpickr();
    initializeTooltips();
    initializeAutoSave();
    initializeTheme();
}

// Gestion des notifications
function initializeNotifications() {
    // Mettre à jour le compteur de notifications
    updateNotificationCount();

    // Charger les notifications récentes
    loadRecentNotifications();

    // Polling pour les nouvelles notifications (toutes les 30 secondes)
    setInterval(updateNotificationCount, 30000);
    setInterval(loadRecentNotifications, 30000);
}

function updateNotificationCount() {
    fetch('/api/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notification-count');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error fetching notification count:', error));
}

function loadRecentNotifications() {
    fetch('/api/notifications/recent')
        .then(response => response.json())
        .then(notifications => {
            const container = document.getElementById('notifications-list');
            if (container && notifications.length > 0) {
                container.innerHTML = notifications.map(notification => `
                    <a class="dropdown-item notification-item" href="#" data-id="${notification.id}">
                        <div class="d-flex w-100 justify-content-between">
                            <small class="text-truncate">${notification.title}</small>
                            <small class="text-muted">${formatTimeAgo(notification.created_at)}</small>
                        </div>
                        <small class="text-muted text-truncate d-block">${notification.message}</small>
                    </a>
                `).join('');

                // Ajouter les écouteurs d'événements
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        markNotificationAsRead(this.dataset.id);
                    });
                });
            } else if (container) {
                container.innerHTML = `
                    <div class="dropdown-item-text text-center py-3">
                        <i class="bi bi-bell text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">Aucune notification</p>
                    </div>
                `;
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

function markNotificationAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationCount();
            loadRecentNotifications();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

// Initialisation des formulaires
function initializeForms() {
    // Validation automatique des formulaires
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showToast('Veuillez corriger les erreurs dans le formulaire.', 'error');
            }
        });
    });

    // Confirmation pour les actions destructrices
    const destructiveForms = document.querySelectorAll('form[data-confirm]');
    destructiveForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.dataset.confirm || 'Êtes-vous sûr de vouloir effectuer cette action ?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // Validation des emails
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
        }
    });

    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Initialisation des tables de données
function initializeDataTables() {
    const tables = document.querySelectorAll('table[data-datatable]');
    tables.forEach(table => {
        // Implémentation basique du tri et de la recherche
        // Pour une solution complète, intégrer DataTables.js
        addTableSearch(table);
        addTableSorting(table);
    });
}

function addTableSearch(table) {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Rechercher...';
    searchInput.className = 'form-control mb-3';
    searchInput.style.maxWidth = '300px';

    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    table.parentNode.insertBefore(searchInput, table);
}

function addTableSorting(table) {
    const headers = table.querySelectorAll('thead th[data-sortable]');

    headers.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            sortTable(table, this.cellIndex);
        });
    });
}

function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const isNumeric = table.querySelector('thead th').dataset.numeric;

    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();

        if (isNumeric) {
            return parseFloat(aValue) - parseFloat(bValue);
        } else {
            return aValue.localeCompare(bValue);
        }
    });

    // Inverser l'ordre si déjà trié
    if (tbody.dataset.sortedColumn === columnIndex.toString()) {
        rows.reverse();
        tbody.dataset.sortedOrder = tbody.dataset.sortedOrder === 'asc' ? 'desc' : 'asc';
    } else {
        tbody.dataset.sortedColumn = columnIndex.toString();
        tbody.dataset.sortedOrder = 'asc';
    }

    // Réinsérer les lignes triées
    rows.forEach(row => tbody.appendChild(row));
}

// Initialisation de Select2
function initializeSelect2() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('select[data-select2]').select2({
            theme: 'bootstrap-5',
            width: '100%',
            language: 'fr'
        });
    }
}

// Initialisation de Flatpickr
function initializeFlatpickr() {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('input[data-flatpickr]', {
            dateFormat: 'd/m/Y',
            locale: 'fr',
            allowInput: true
        });
    }
}

// Initialisation des tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Système d'auto-save
function initializeAutoSave() {
    const autoSaveForms = document.querySelectorAll('form[data-autosave]');

    autoSaveForms.forEach(form => {
        let timeoutId;

        form.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                autoSaveForm(form);
            }, 2000);
        });

        // Sauvegarder avant de quitter la page
        window.addEventListener('beforeunload', function(e) {
            if (formHasChanges(form)) {
                autoSaveForm(form, true);
            }
        });
    });
}

function autoSaveForm(form, isLeaving = false) {
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!isLeaving) {
                showToast('Modifications sauvegardées automatiquement', 'success');
            }
            form.dataset.lastSaved = new Date().toISOString();
        }
    })
    .catch(error => {
        console.error('Auto-save error:', error);
        if (!isLeaving) {
            showToast('Erreur lors de la sauvegarde automatique', 'error');
        }
    });
}

function formHasChanges(form) {
    const lastSaved = form.dataset.lastSaved;
    // Implémentation basique - à améliorer selon les besoins
    return true;
}

// Gestion du thème
function initializeTheme() {
    const themeToggle = document.getElementById('theme-toggle');
    const savedTheme = localStorage.getItem('theme') || 'light';

    applyTheme(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);

    // Mettre à jour l'icône du bouton de thème
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        const icon = themeToggle.querySelector('i');
        if (icon) {
            icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        }
    }
}

// Utilitaires
function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) {
        return 'À l\'instant';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `Il y a ${minutes} min`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `Il y a ${hours} h`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `Il y a ${days} j`;
    }
}

function showToast(message, type = 'info') {
    // Implémentation basique des toasts
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    toastContainer.appendChild(toast);

    // Auto-dismiss après 5 secondes
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Gestion des chargements
function showLoading() {
    document.getElementById('loading-spinner').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loading-spinner').classList.add('d-none');
}

// Intercepteur pour les requêtes AJAX
const originalFetch = window.fetch;
window.fetch = function(...args) {
    showLoading();
    return originalFetch.apply(this, args)
        .then(response => {
            hideLoading();
            return response;
        })
        .catch(error => {
            hideLoading();
            throw error;
        });
};

// Export global pour le débogage
window.SchoolManagement = {
    initializeApp,
    showToast,
    showLoading,
    hideLoading
};
