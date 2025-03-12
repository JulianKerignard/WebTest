/**
 * LeBonPlan - Code JavaScript principal
 * Version optimisée avec corrections pour les favoris
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de l'interface
    initUI();

    // Configuration des écouteurs d'événements
    setupEventListeners();

    // Initialiser les components spécifiques à la page courante
    initPageSpecificComponents();
});

/**
 * Initialise les éléments d'interface utilisateur
 */
function initUI() {
    // Navigation mobile pour le dashboard
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.dashboard-sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => sidebar.classList.add('active'));
    }

    const closeSidebar = document.getElementById('closeSidebar');
    if (closeSidebar && sidebar) {
        closeSidebar.addEventListener('click', () => sidebar.classList.remove('active'));
    }

    // Menu hamburger pour navigation principale
    initMobileNavigation();

    // Animation des éléments statistiques
    animateStatCards();
}

/**
 * Configuration du menu mobile
 */
function initMobileNavigation() {
    const navbar = document.querySelector('.navbar');
    const navMenu = document.querySelector('.nav-menu');

    if (navbar && navMenu && !document.querySelector('.mobile-menu-toggle')) {
        const hamburgerButton = document.createElement('button');
        hamburgerButton.classList.add('mobile-menu-toggle');
        hamburgerButton.innerHTML = '<i class="fas fa-bars"></i>';
        navbar.insertBefore(hamburgerButton, navbar.firstChild);

        hamburgerButton.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });

        // Fermer le menu au clic en dehors
        document.addEventListener('click', function(e) {
            if (navMenu.classList.contains('active') &&
                !e.target.closest('.nav-menu') &&
                !e.target.closest('.mobile-menu-toggle')) {
                navMenu.classList.remove('active');
            }
        });
    }
}

/**
 * Animation des cartes statistiques
 */
function animateStatCards() {
    const statCards = document.querySelectorAll('.stat-card');

    if (statCards.length > 0) {
        statCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100 * index);
        });
    }
}

/**
 * Configure tous les écouteurs d'événement
 */
function setupEventListeners() {
    // Gestion des filtres
    setupFilters();

    // Gestion des formulaires
    setupForms();

    // Gestion des favoris
    setupFavorites();

    // Gestion de la pagination
    setupPagination();

    // Gestion des accordéons
    setupAccordions();
}

/**
 * Configuration des filtres et vues
 */
function setupFilters() {
    // Filtres par catégorie
    const filterButtons = document.querySelectorAll('.filter-button');
    const filterItems = document.querySelectorAll('.internship-card, .entreprise-card');

    if (filterButtons.length > 0 && filterItems.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Enlever la classe active de tous les boutons
                filterButtons.forEach(btn => btn.classList.remove('active'));

                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');

                // Récupérer la valeur du filtre
                const filterValue = this.textContent.trim().toLowerCase();

                // Filtrer les éléments
                if (filterValue === 'tous') {
                    // Afficher tous les éléments
                    filterItems.forEach(item => {
                        item.style.display = 'block';
                    });
                } else {
                    // Filtrer par catégorie
                    filterItems.forEach(item => {
                        const tags = Array.from(item.querySelectorAll('.tag')).map(tag => tag.textContent.trim().toLowerCase());
                        const title = item.querySelector('.card-title h3').textContent.toLowerCase();
                        const company = item.querySelector('.company-name')?.textContent.toLowerCase() || '';

                        if (tags.includes(filterValue) || title.includes(filterValue) || company.includes(filterValue)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }
            });
        });
    }

    // Sélecteurs de vue (grille/liste)
    const viewButtons = document.querySelectorAll('.view-btn');
    const resultsContainer = document.querySelector('.internship-grid, .entreprises-grid');

    if (viewButtons.length && resultsContainer) {
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                viewButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const viewType = this.getAttribute('data-view');
                if (viewType === 'grid') {
                    resultsContainer.classList.remove('list-view');
                } else if (viewType === 'list') {
                    resultsContainer.classList.add('list-view');
                }
            });
        });
    }

    // Filtres avancés (toggle)
    const filterToggle = document.getElementById('filterToggle');
    const filtersPanel = document.getElementById('filtersPanel');

    if (filterToggle && filtersPanel) {
        filterToggle.addEventListener('click', function() {
            filtersPanel.classList.toggle('active');
        });
    }
}

/**
 * Gestion des favoris avec animations - Compatible avec la structure HTML existante
 */
function setupFavorites() {
    // Sélectionne tous les boutons de favoris (bookmark ou star)
    const bookmarkBtns = document.querySelectorAll('.bookmark-btn');

    if (!bookmarkBtns.length) return; // Sortir si aucun bouton n'existe

    bookmarkBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Éviter la propagation aux éléments parents

            // Vérifier si le bouton est déjà en cours de traitement
            if (this.classList.contains('processing')) {
                return; // Éviter les clics multiples pendant le traitement
            }

            // Marquer comme en cours de traitement
            this.classList.add('processing');

            const icon = this.querySelector('i');
            if (!icon) {
                console.warn('Icône non trouvée dans le bouton favori');
                this.classList.remove('processing');
                return;
            }

            // Extraire des informations pour l'AJAX
            const card = this.closest('.internship-card');
            const titleElement = card?.querySelector('.card-title h3');
            const title = titleElement ? titleElement.textContent.trim() : 'Stage';

            // Déterminer si on ajoute ou supprime des favoris
            // En vérifiant plusieurs conditions possibles (far/fas, bookmark/star)
            const isBookmark = icon.classList.contains('fa-bookmark');
            const isStar = icon.classList.contains('fa-star');
            const isEmpty = icon.classList.contains('far');
            const isFilled = icon.classList.contains('fas');

            console.log('Click détecté:', { isBookmark, isStar, isEmpty, isFilled });

            if ((isBookmark && isEmpty) || (isStar && isEmpty) || (!isFilled)) {
                // C'est vide, donc on ajoute aux favoris
                addToFavorites(title, this, icon);
            } else {
                // C'est déjà plein, donc on retire des favoris
                removeFromFavorites(title, this, icon);
            }
        });
    });

    // Gestion boutons de suppression dans la page favoris
    const removeFavBtns = document.querySelectorAll('.btn-remove');

    if (removeFavBtns.length > 0) {
        removeFavBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.internship-card');
                if (!card) {
                    console.warn('Carte de stage non trouvée');
                    return;
                }

                // Désactiver le bouton pour éviter les clics multiples
                this.disabled = true;

                // Animation de suppression
                card.classList.add('removing');

                // AJAX simulé (à remplacer par votre appel réel)
                setTimeout(() => {
                    try {
                        // Attendre que l'animation soit terminée avant de supprimer
                        setTimeout(() => {
                            card.remove();
                            updateFavoritesCounter();
                            showToast('Stage retiré des favoris', 'success');
                        }, 500);
                    } catch (error) {
                        console.error('Erreur lors de la suppression:', error);
                        card.classList.remove('removing');
                        this.disabled = false;
                        showToast('Erreur de connexion', 'error');
                    }
                }, 300);
            });
        });
    }
}

/**
 * Ajoute un stage aux favoris (compatible avec bookmark ou star)
 */
function addToFavorites(title, button, icon) {
    console.log('Ajout aux favoris:', title);

    // Déterminer le type d'icône (bookmark ou star)
    const isBookmark = icon.classList.contains('fa-bookmark');
    const isStar = icon.classList.contains('fa-star');

    // Animation visuelle immédiate mais plus douce
    icon.classList.add('favorite-icon-transition');

    // Changer l'icône et la couleur après un court délai
    setTimeout(() => {
        if (isBookmark) {
            // Pour les icônes bookmark
            icon.classList.remove('far');
            icon.classList.add('fas');
        } else if (isStar) {
            // Pour les icônes star
            icon.classList.remove('far');
            icon.classList.add('fas');
        } else {
            // Fallback pour d'autres types d'icônes
            if (icon.classList.contains('far')) {
                icon.classList.replace('far', 'fas');
            } else {
                icon.classList.add('fas');
            }
        }

        icon.style.color = '#f72585'; // Couleur secondaire

        // Ajouter l'animation de pulse APRÈS le changement d'icône
        requestAnimationFrame(() => {
            icon.classList.add('pulse-animation');
        });
    }, 50);

    // AJAX simulé
    setTimeout(() => {
        // Après la fin de l'animation (600ms)
        setTimeout(() => {
            // Nettoyer les classes d'animation
            icon.classList.remove('pulse-animation');

            // Enlever le statut de traitement
            button.classList.remove('processing');

            showToast(`${title} ajouté aux favoris`, 'success');
        }, 650);
    }, 300);
}

/**
 * Retire un stage des favoris (compatible avec bookmark ou star)
 */
function removeFromFavorites(title, button, icon) {
    console.log('Retrait des favoris:', title);

    // Déterminer le type d'icône (bookmark ou star)
    const isBookmark = icon.classList.contains('fa-bookmark');
    const isStar = icon.classList.contains('fa-star');

    // Animation visuelle immédiate
    icon.classList.add('shake-animation');

    // AJAX simulé
    setTimeout(() => {
        // Attendre la fin de l'animation
        setTimeout(() => {
            // Transition douce pour le changement d'icône
            icon.classList.remove('shake-animation');
            icon.classList.add('favorite-icon-transition');

            // Changer l'icône en fonction de son type
            setTimeout(() => {
                if (isBookmark) {
                    // Pour les icônes bookmark
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                } else if (isStar) {
                    // Pour les icônes star
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                } else {
                    // Fallback pour d'autres types d'icônes
                    if (icon.classList.contains('fas')) {
                        icon.classList.replace('fas', 'far');
                    } else {
                        icon.classList.add('far');
                    }
                }

                icon.style.color = ''; // Réinitialiser la couleur

                // Enlever le statut de traitement
                button.classList.remove('processing');

                showToast(`${title} retiré des favoris`, 'success');
            }, 100);
        }, 500);
    }, 300);
}

/**
 * Met à jour le compteur de favoris
 */
function updateFavoritesCounter() {
    const counter = document.querySelector('.stat-card:first-child .stat-info h3');
    if (counter) {
        const currentCount = parseInt(counter.textContent);
        counter.textContent = (currentCount - 1).toString();
    }

    // Vérifier s'il reste des favoris
    const favCards = document.querySelectorAll('.internship-card').length;
    if (favCards === 0) {
        const emptyState = document.querySelector('.no-favorites');
        const resultsContainer = document.querySelector('.favorites-container');
        const pagination = document.querySelector('.pagination');

        if (emptyState && resultsContainer && pagination) {
            emptyState.classList.remove('hidden');
            resultsContainer.classList.add('hidden');
            pagination.classList.add('hidden');
        }
    }
}

/**
 * Configuration des formulaires
 */
function setupForms() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        if (form.classList.contains('no-validation')) {
            return; // Ignorer les formulaires avec cette classe
        }

        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });

    // Newsletter
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;

            if (isValidEmail(email)) {
                // AJAX pour newsletter
                fetch('actions/newsletter.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Merci pour votre inscription à notre newsletter !', 'success');
                            this.reset();
                        } else {
                            showToast(data.message || 'Une erreur est survenue', 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Erreur de connexion', 'error');
                    });
            } else {
                showToast('Veuillez entrer une adresse email valide', 'error');
            }
        });
    }

    // Toggle type de compte
    const accountTypes = document.querySelectorAll('.account-type');
    if (accountTypes.length > 0) {
        accountTypes.forEach(type => {
            type.addEventListener('click', function() {
                accountTypes.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const accountType = this.getAttribute('data-type');
                document.getElementById('account_type').value = accountType;

                // Afficher/masquer les champs spécifiques
                toggleAccountTypeFields(accountType);
            });
        });
    }
}

/**
 * Affiche/masque les champs spécifiques au type de compte
 */
function toggleAccountTypeFields(accountType) {
    const studentFields = document.querySelectorAll('.student-field');
    const companyFields = document.querySelectorAll('.company-field');

    if (accountType === 'student') {
        studentFields.forEach(field => field.style.display = 'block');
        companyFields.forEach(field => field.style.display = 'none');
    } else if (accountType === 'company') {
        studentFields.forEach(field => field.style.display = 'none');
        companyFields.forEach(field => field.style.display = 'block');
    }
}

/**
 * Validation d'un formulaire
 */
function validateForm(form) {
    let isValid = true;
    const invalidFeedbacks = form.querySelectorAll('.invalid-feedback');

    // Nettoyer les messages d'erreur précédents
    invalidFeedbacks.forEach(feedback => {
        feedback.remove();
    });

    // Validation des champs requis
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            markFieldAsInvalid(field, 'Ce champ est obligatoire');
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // Validation des emails
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            isValid = false;
            markFieldAsInvalid(field, 'Adresse email non valide');
        }
    });

    // Validation des mots de passe
    const password = form.querySelector('input[id="password"]');
    const confirmPassword = form.querySelector('input[id="confirm-password"]');

    if (password && confirmPassword && password.value !== confirmPassword.value) {
        isValid = false;
        markFieldAsInvalid(confirmPassword, 'Les mots de passe ne correspondent pas');
    }

    return isValid;
}

/**
 * Marque un champ comme invalide avec un message
 */
function markFieldAsInvalid(field, message) {
    field.classList.add('is-invalid');

    const feedback = document.createElement('div');
    feedback.classList.add('invalid-feedback');
    feedback.textContent = message;

    const parent = field.parentElement;
    parent.appendChild(feedback);
}

/**
 * Vérifie si une adresse email est valide
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Configuration de la pagination
 */
function setupPagination() {
    const paginationItems = document.querySelectorAll('.pagination-item, .pagination-prev, .pagination-next');

    if (paginationItems.length > 0) {
        paginationItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                if (this.classList.contains('disabled')) return;

                // Récupérer la page
                let page = 1;

                if (this.classList.contains('pagination-item')) {
                    page = parseInt(this.textContent);

                    // Mettre à jour la classe active
                    document.querySelectorAll('.pagination-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.classList.add('active');
                } else if (this.classList.contains('pagination-next')) {
                    const activePage = document.querySelector('.pagination-item.active');
                    page = parseInt(activePage.textContent) + 1;
                } else if (this.classList.contains('pagination-prev')) {
                    const activePage = document.querySelector('.pagination-item.active');
                    page = parseInt(activePage.textContent) - 1;
                }

                // Rediriger avec le paramètre de page
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);
                window.location.href = url.toString();
            });
        });
    }
}

/**
 * Configuration des éléments accordéon
 */
function setupAccordions() {
    const accordionHeaders = document.querySelectorAll('.application-header, .faq-question');

    if (accordionHeaders.length > 0) {
        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const item = this.closest('.application-detailed-item, .faq-item');

                if (item.classList.contains('active')) {
                    item.classList.remove('active');
                } else {
                    // Fermer les autres éléments ouverts
                    document.querySelectorAll('.application-detailed-item.active, .faq-item.active').forEach(openItem => {
                        if (openItem !== item) {
                            openItem.classList.remove('active');
                        }
                    });

                    item.classList.add('active');
                }
            });
        });
    }
}

/**
 * Initialisation de composants spécifiques à certaines pages
 */
function initPageSpecificComponents() {
    // Détecter la page actuelle
    const path = window.location.pathname;

    if (path.includes('dashboard')) {
        initDashboardPage();
    } else if (path.includes('favorites') || path.includes('StagesFav')) {
        initFavoritesPage();
    } else if (path.includes('candidature')) {
        initApplicationsPage();
    }
}

/**
 * Initialisation spécifique pour le tableau de bord
 */
function initDashboardPage() {
    // Gestion des onglets admin
    const tabItems = document.querySelectorAll('.sidebar-nav li[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabItems.length > 0 && tabContents.length > 0) {
        tabItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                // Enlever la classe active de tous les onglets
                tabItems.forEach(tab => tab.classList.remove('active'));

                // Ajouter la classe active à l'onglet cliqué
                this.classList.add('active');

                // Récupérer l'id du tab à afficher
                const tabId = this.getAttribute('data-tab');

                // Masquer tous les contenus d'onglets
                tabContents.forEach(content => content.classList.remove('active'));

                // Afficher le contenu de l'onglet sélectionné
                const activeTab = document.getElementById(tabId + '-tab');
                if (activeTab) {
                    activeTab.classList.add('active');
                }
            });
        });
    }
}

/**
 * Initialisation spécifique pour la page des favoris
 */
function initFavoritesPage() {
    // Initialisation déjà couverte par les fonctions générales
}

/**
 * Initialisation spécifique pour la page des candidatures
 */
function initApplicationsPage() {
    // Initialisation déjà couverte par les fonctions générales
}

/**
 * Affiche une notification Toast améliorée
 */
function showToast(message, type = 'info') {
    console.log('Toast:', message, type);

    // Créer le conteneur de toast s'il n'existe pas
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.classList.add('toast-container');
        document.body.appendChild(toastContainer);
    }

    // Créer un nouveau toast
    const toast = document.createElement('div');
    toast.classList.add('toast', `toast-${type}`);

    // Icône selon le type
    let iconClass = 'fa-info-circle';
    if (type === 'success') iconClass = 'fa-check-circle';
    if (type === 'error') iconClass = 'fa-exclamation-circle';
    if (type === 'warning') iconClass = 'fa-exclamation-triangle';

    toast.innerHTML = `
        <div class="toast-header">
            <div class="toast-icon"><i class="fas ${iconClass}"></i></div>
            <div class="toast-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
            <button class="toast-close">&times;</button>
        </div>
        <div class="toast-body">${message}</div>
    `;

    // Ajouter au conteneur
    toastContainer.appendChild(toast);

    // Afficher le toast avec une courte animation
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    // Gérer la fermeture
    const closeButton = toast.querySelector('.toast-close');
    closeButton.addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toastContainer.contains(toast)) {
                toastContainer.removeChild(toast);
            }
        }, 300);
    });

    // Fermeture automatique après 5 secondes
    setTimeout(() => {
        if (toast.classList.contains('show')) {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toastContainer.contains(toast)) {
                    toastContainer.removeChild(toast);
                }
            }, 300);
        }
    }, 5000);
}