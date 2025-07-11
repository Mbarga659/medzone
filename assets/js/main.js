/**
 * MedZone - JavaScript principal
 * Fonctionnalités communes à toutes les pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialisation des popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Animation au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    // Observer les éléments avec la classe fade-in
    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });

    // Smooth scroll pour les ancres
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Gestion du panier
    updateCartCount();

    // --- FILTRAGE DES PRODUITS PAR CATÉGORIE ---
    function filterProductsByCategory(category) {
        document.querySelectorAll('.row.g-4 > .col-lg-3').forEach(card => {
            if (!category || card.getAttribute('data-categorie') === category) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Map pour lier les valeurs d'URL aux data-categorie
    const categoryMap = {
        '1': 'analgesiques',
        '2': 'antibiotiques',
        '3': 'vitamines',
        '4': 'soins',
        '5': 'premiers-soins'
    };
    // Map pour lier le nom affiché à la data-categorie
    const reverseCategoryMap = {
        'Analgésiques': 'analgesiques',
        'Antibiotiques': 'antibiotiques',
        'Vitamines': 'vitamines',
        'Soins de la peau': 'soins',
        'Premiers soins': 'premiers-soins'
    };

    // Menu déroulant de filtre catégories UNIQUEMENT
    const filterDropdown = document.querySelector('.dropdown-menu.filter-categories');
    if (filterDropdown) {
        filterDropdown.querySelectorAll('a.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                const match = href.match(/categorie=(\w+)/);
                const category = match ? match[1] : '';
                filterProductsByCategory(categoryMap[category] || '');
            });
        });
    }

    // Cartes catégories
    const categoryCards = document.querySelectorAll('.row.g-4 .card.text-center');
    categoryCards.forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const title = this.querySelector('.card-title').textContent.trim();
            const category = reverseCategoryMap[title] || '';
            filterProductsByCategory(category);
        });
    });

    // Afficher tous les produits au chargement
    filterProductsByCategory('');
});

/**
 * Mettre à jour le compteur du panier
 */
function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        // Récupérer le nombre d'articles depuis la session via AJAX
        fetch('includes/get-cart-count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cartCount.textContent = data.count;
                    cartCount.style.display = data.count > 0 ? 'inline' : 'none';
                }
            })
            .catch(error => {
                console.error('Erreur lors de la récupération du panier:', error);
            });
    }
}

/**
 * Afficher une notification toast
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de notification (success, error, warning, info)
 * @param {number} duration - Durée d'affichage en millisecondes
 */
function showNotification(message, type = 'info', duration = 5000) {
    // Créer le conteneur de notifications s'il n'existe pas
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        `;
        document.body.appendChild(notificationContainer);
    }

    // Créer la notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    notification.style.marginBottom = '10px';
    
    const iconMap = {
        success: 'check_circle',
        error: 'error',
        warning: 'warning',
        info: 'info'
    };

    notification.innerHTML = `
        <span class="material-symbols-outlined me-2">${iconMap[type] || 'info'}</span>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    notificationContainer.appendChild(notification);

    // Auto-suppression après la durée spécifiée
    setTimeout(() => {
        if (notification.parentNode) {
            const bsAlert = new bootstrap.Alert(notification);
            bsAlert.close();
        }
    }, duration);

    // Suppression du DOM après l'animation
    notification.addEventListener('closed.bs.alert', () => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    });
}

/**
 * Valider un formulaire
 * @param {HTMLFormElement} form - Le formulaire à valider
 * @returns {boolean} - True si le formulaire est valide
 */
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

/**
 * Formater un prix
 * @param {number} price - Le prix à formater
 * @param {string} currency - La devise (par défaut: €)
 * @returns {string} - Le prix formaté
 */
function formatPrice(price, currency = '€') {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

/**
 * Debounce function pour optimiser les performances
 * @param {Function} func - La fonction à debouncer
 * @param {number} wait - Le délai d'attente en millisecondes
 * @returns {Function} - La fonction debouncée
 */
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

/**
 * Throttle function pour limiter la fréquence d'exécution
 * @param {Function} func - La fonction à throttler
 * @param {number} limit - La limite de fréquence en millisecondes
 * @returns {Function} - La fonction throttlée
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Charger des données via AJAX
 * @param {string} url - L'URL à appeler
 * @param {Object} options - Les options de la requête
 * @returns {Promise} - La promesse de la requête
 */
function loadData(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    };

    const finalOptions = { ...defaultOptions, ...options };

    return fetch(url, finalOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

/**
 * Sauvegarder des données dans le localStorage
 * @param {string} key - La clé de sauvegarde
 * @param {any} data - Les données à sauvegarder
 */
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
    } catch (error) {
        console.error('Erreur lors de la sauvegarde dans localStorage:', error);
    }
}

/**
 * Récupérer des données du localStorage
 * @param {string} key - La clé de récupération
 * @param {any} defaultValue - La valeur par défaut si la clé n'existe pas
 * @returns {any} - Les données récupérées
 */
function getFromLocalStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.error('Erreur lors de la récupération depuis localStorage:', error);
        return defaultValue;
    }
}

// Export des fonctions pour utilisation globale
window.MedZone = {
    showNotification,
    validateForm,
    formatPrice,
    debounce,
    throttle,
    loadData,
    saveToLocalStorage,
    getFromLocalStorage,
    updateCartCount
};

// Appeler updateCartCount au chargement de la page
window.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
}); 