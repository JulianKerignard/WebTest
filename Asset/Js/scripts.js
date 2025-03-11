    // StageConnect - Script principal
    document.addEventListener('DOMContentLoaded', function() {
        // Fonctions communes à toutes les pages
        initMobileMenu();
        initDropdowns();
    
        // Détection de la page actuelle et initialisation des fonctionnalités spécifiques
        const currentPage = getCurrentPage();
    
        switch(currentPage) {
            case 'index':
                initHomePage();
                break;
            case 'login':
                initLoginPage();
                break;
            case 'register':
                initRegisterPage();
                break;
            case 'dashboard':
                initDashboard();
                break;
            case 'entreprises':
                initEntreprisesPage();
                break;
            case 'contact':
                initContactPage();
                break;
            default:
                // Page générique
                break;
        }
    
        // Initialiser les fonctionnalités communes du footer
        initNewsletter();
    });
    
    // Détecter la page actuelle
    function getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('login')) return 'login';
        if (path.includes('register')) return 'register';
        if (path.includes('dashboard')) return 'dashboard';
        if (path.includes('entreprises')) return 'entreprises';
        if (path.includes('contact')) return 'contact';
        if (path === '/' || path.includes('index')) return 'index';
        return 'other';
    }
    
    // Fonctions utilitaires
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Fonction pour afficher un toast (notification)
    function showToast(message, type = 'info') {
        // Supprimer les toasts existants
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
    
        // Créer un nouveau toast
        const toast = document.createElement('div');
        toast.classList.add('toast', `toast-${type}`);
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close">&times;</button>
        `;
    
        document.body.appendChild(toast);
    
        // Afficher le toast
        setTimeout(() => {
            toast.classList.add('toast-visible');
        }, 10);
    
        // Fermer le toast au clic sur le bouton ou après un délai
        const closeButton = toast.querySelector('.toast-close');
        closeButton.addEventListener('click', () => {
            toast.classList.remove('toast-visible');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
    
        // Fermer automatiquement après 5 secondes
        setTimeout(() => {
            if (document.body.contains(toast)) {
                toast.classList.remove('toast-visible');
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        toast.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    // Simulation d'API
    function simulateLogin(email, password) {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Simuler une authentification réussie
                if (email === 'test@example.com' && password === 'password') {
                    resolve({ success: true, user: { name: 'Marie Dubois' } });
                } else {
                    resolve({ success: false, message: 'Email ou mot de passe incorrect' });
                }
            }, 1000);
        });
    }
    // Menu mobile et navigation
    function initMobileMenu() {
        const menuToggle = document.getElementById('menuToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.querySelector('.dashboard-sidebar');
    
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                if (sidebar) {
                    sidebar.classList.add('active');
                }
            });
        }
    
        if (closeSidebar) {
            closeSidebar.addEventListener('click', function() {
                if (sidebar) {
                    sidebar.classList.remove('active');
                }
            });
        }
    
        // Version responsive du menu principal
        const hamburgerButton = document.createElement('button');
        hamburgerButton.classList.add('mobile-menu-toggle');
        hamburgerButton.innerHTML = '<i class="fas fa-bars"></i>';
    
        const navbar = document.querySelector('.navbar');
        const navMenu = document.querySelector('.nav-menu');
    
        if (navbar && navMenu && !document.querySelector('.mobile-menu-toggle')) {
            navbar.insertBefore(hamburgerButton, navbar.firstChild);
    
            hamburgerButton.addEventListener('click', function() {
                navMenu.classList.toggle('active');
            });
        }
    }
    
    // Initialisation des dropdowns (menu utilisateur, notifications, etc.)
    function initDropdowns() {
        const userDropdown = document.querySelector('.user-dropdown');
    
        if (userDropdown) {
            userDropdown.addEventListener('click', function() {
                const dropdownMenu = document.createElement('div');
                dropdownMenu.classList.add('dropdown-menu');
    
                // Si le dropdown existe déjà, on le supprime
                const existingDropdown = document.querySelector('.dropdown-menu');
                if (existingDropdown) {
                    existingDropdown.remove();
                    return;
                }
    
                // Création du contenu du dropdown
                dropdownMenu.innerHTML = `
                    <ul>
                        <li><a href="#"><i class="fas fa-user"></i> Mon profil</a></li>
                        <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
                        <li><a href="../../Main/index.html"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                `;
    
                userDropdown.appendChild(dropdownMenu);
    
                // Fermer le dropdown en cliquant à l'extérieur
                document.addEventListener('click', function closeDropdown(e) {
                    if (!userDropdown.contains(e.target)) {
                        if (document.querySelector('.dropdown-menu')) {
                            document.querySelector('.dropdown-menu').remove();
                            document.removeEventListener('click', closeDropdown);
                        }
                    }
                });
            });
        }
    
        // Initialisation des notifications et messages
        initNotifications();
        initMessages();
    }
    
    // Gestion des notifications
    function initNotifications() {
        const notificationBadge = document.querySelector('.notification-badge');
        if (notificationBadge) {
            notificationBadge.addEventListener('click', function() {
                const dropdownMenu = document.createElement('div');
                dropdownMenu.classList.add('dropdown-menu', 'notification-dropdown');
    
                // Si le dropdown existe déjà, on le supprime
                const existingDropdown = document.querySelector('.notification-dropdown');
                if (existingDropdown) {
                    existingDropdown.remove();
                    return;
                }
    
                // Création du contenu du dropdown
                dropdownMenu.innerHTML = `
                    <div class="dropdown-header">
                        <h3>Notifications</h3>
                        <a href="#">Marquer tout comme lu</a>
                    </div>
                    <ul>
                        <li class="unread">
                            <div class="notification-icon"><i class="fas fa-briefcase"></i></div>
                            <div class="notification-content">
                                <p>Votre candidature chez <strong>TechDream</strong> a été vue</p>
                                <span class="notification-time">Il y a 1 heure</span>
                            </div>
                        </li>
                        <li class="unread">
                            <div class="notification-icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="notification-content">
                                <p>Rappel: Entretien avec <strong>Green Solutions</strong> demain à 14h</p>
                                <span class="notification-time">Il y a 3 heures</span>
                            </div>
                        </li>
                        <li>
                            <div class="notification-icon"><i class="fas fa-star"></i></div>
                            <div class="notification-content">
                                <p>5 nouvelles offres correspondent à votre profil</p>
                                <span class="notification-time">Hier</span>
                            </div>
                        </li>
                    </ul>
                    <div class="dropdown-footer">
                        <a href="#">Voir toutes les notifications</a>
                    </div>
                `;
    
                notificationBadge.appendChild(dropdownMenu);
    
                // Fermer le dropdown en cliquant à l'extérieur
                document.addEventListener('click', function closeDropdown(e) {
                    if (!notificationBadge.contains(e.target)) {
                        if (document.querySelector('.notification-dropdown')) {
                            document.querySelector('.notification-dropdown').remove();
                            document.removeEventListener('click', closeDropdown);
                        }
                    }
                });
            });
        }
    }
    
    // Gestion des messages
    function initMessages() {
        const messageBadge = document.querySelector('.message-badge');
        if (messageBadge) {
            messageBadge.addEventListener('click', function() {
                const dropdownMenu = document.createElement('div');
                dropdownMenu.classList.add('dropdown-menu', 'message-dropdown');
    
                // Si le dropdown existe déjà, on le supprime
                const existingDropdown = document.querySelector('.message-dropdown');
                if (existingDropdown) {
                    existingDropdown.remove();
                    return;
                }
    
                // Création du contenu du dropdown
                dropdownMenu.innerHTML = `
                    <div class="dropdown-header">
                        <h3>Messages</h3>
                        <a href="#">Voir tous</a>
                    </div>
                    <ul>
                        <li class="unread">
                            <div class="message-avatar">
                                <img src="/api/placeholder/40/40" alt="Avatar">
                            </div>
                            <div class="message-content">
                                <div class="message-info">
                                    <h4>Sarah Martin</h4>
                                    <span class="message-time">11:43</span>
                                </div>
                                <p>Bonjour Marie, merci pour votre candidature...</p>
                            </div>
                        </li>
                        <li class="unread">
                            <div class="message-avatar">
                                <img src="/api/placeholder/40/40" alt="Avatar">
                            </div>
                            <div class="message-content">
                                <div class="message-info">
                                    <h4>Thomas Dubois</h4>
                                    <span class="message-time">Hier</span>
                                </div>
                                <p>Suite à notre entretien, j'ai le plaisir de...</p>
                            </div>
                        </li>
                        <li>
                            <div class="message-avatar">
                                <img src="/api/placeholder/40/40" alt="Avatar">
                            </div>
                            <div class="message-content">
                                <div class="message-info">
                                    <h4>Julie Lambert</h4>
                                    <span class="message-time">Lun</span>
                                </div>
                                <p>Nous recherchons un stagiaire pour...</p>
                            </div>
                        </li>
                    </ul>
                    <div class="dropdown-footer">
                        <a href="#">Voir tous les messages</a>
                    </div>
                `;
    
                messageBadge.appendChild(dropdownMenu);
    
                // Fermer le dropdown en cliquant à l'extérieur
                document.addEventListener('click', function closeDropdown(e) {
                    if (!messageBadge.contains(e.target)) {
                        if (document.querySelector('.message-dropdown')) {
                            document.querySelector('.message-dropdown').remove();
                            document.removeEventListener('click', closeDropdown);
                        }
                    }
                });
            });
        }
    }
    
    // Page d'accueil
    function initHomePage() {
        // Gestion des filtres pour les stages
        const filterButtons = document.querySelectorAll('.filter-button');
        const internshipCards = document.querySelectorAll('.internship-card');
    
        if (filterButtons.length > 0) {
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Enlever la classe active de tous les boutons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
    
                    // Ajouter la classe active au bouton cliqué
                    this.classList.add('active');
    
                    // Filtrer les stages
                    const filterValue = this.textContent.trim().toLowerCase();
    
                    if (filterValue === 'tous') {
                        // Afficher tous les stages
                        internshipCards.forEach(card => {
                            card.style.display = 'block';
                        });
                    } else {
                        // Filtrer les stages selon la catégorie
                        internshipCards.forEach(card => {
                            const tags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.trim().toLowerCase());
    
                            if (tags.includes(filterValue) ||
                                card.querySelector('.card-title h3').textContent.toLowerCase().includes(filterValue) ||
                                card.querySelector('.company-name').textContent.toLowerCase().includes(filterValue)) {
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }
                });
            });
        }
    
        // Fonction de recherche
        const searchBar = document.querySelector('.search-bar');
        if (searchBar) {
            searchBar.addEventListener('submit', function(e) {
                e.preventDefault();
    
                const keyword = this.querySelector('input[type="text"]').value.trim().toLowerCase();
                const domain = this.querySelector('select:nth-of-type(1)').value;
                const location = this.querySelector('select:nth-of-type(2)').value;
    
                // Redirection vers la page de recherche avec paramètres
                window.location.href = `stages.html?keyword=${encodeURIComponent(keyword)}&domain=${encodeURIComponent(domain)}&location=${encodeURIComponent(location)}`;
            });
        }
    
        // Ajouter fonctionnalité de favoris
        const bookmarkBtns = document.querySelectorAll('.bookmark-btn');
        if (bookmarkBtns.length > 0) {
            bookmarkBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (icon.classList.contains('far')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        showToast('Stage ajouté aux favoris');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        showToast('Stage retiré des favoris');
                    }
                });
            });
        }
    }
    
    // Initialisation de la newsletter dans le footer
    function initNewsletter() {
        const newsletterForm = document.querySelector('.newsletter-form');
    
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
    
                const emailInput = this.querySelector('input[type="email"]');
                const email = emailInput.value.trim();
    
                if (email && isValidEmail(email)) {
                    showToast('Merci pour votre inscription à notre newsletter !', 'success');
                    emailInput.value = '';
                } else {
                    showToast('Veuillez entrer une adresse email valide', 'error');
                }
            });
        }
    }
    // Page de connexion
    function initLoginPage() {
        const loginForm = document.querySelector('.auth-form');
    
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
    
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
    
                // Vérification basique des données
                if (!email || !password) {
                    showToast('Veuillez remplir tous les champs', 'error');
                    return;
                }
    
                if (!isValidEmail(email)) {
                    showToast('Veuillez entrer une adresse email valide', 'error');
                    return;
                }
    
                // Simulation de connexion (À remplacer par votre API)
                simulateLogin(email, password)
                    .then(response => {
                        if (response.success) {
                            showToast('Connexion réussie, redirection...');
                            setTimeout(() => {
                                window.location.href = 'dashboard.html';
                            }, 1500);
                        } else {
                            showToast('Échec de la connexion: ' + response.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Erreur de connexion: ' + error.message, 'error');
                    });
            });
    
            // Gestion du lien "Mot de passe oublié"
            const forgotPassword = document.querySelector('.forgot-password');
            if (forgotPassword) {
                forgotPassword.addEventListener('click', function(e) {
                    e.preventDefault();
    
                    // Créer une boîte de dialogue pour réinitialiser le mot de passe
                    const modal = document.createElement('div');
                    modal.classList.add('modal');
                    modal.innerHTML = `
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Réinitialisation du mot de passe</h2>
                                <button class="close-modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p>Entrez votre adresse email pour recevoir un lien de réinitialisation de mot de passe.</p>
                                <div class="form-group">
                                    <div class="input-with-icon">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" id="reset-email" placeholder="Votre adresse email" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline" id="cancel-reset">Annuler</button>
                                <button class="btn btn-primary" id="send-reset">Envoyer</button>
                            </div>
                        </div>
                    `;
    
                    document.body.appendChild(modal);
    
                    // Gestion du modal
                    const closeModal = modal.querySelector('.close-modal');
                    const cancelReset = modal.querySelector('#cancel-reset');
                    const sendReset = modal.querySelector('#send-reset');
    
                    closeModal.addEventListener('click', function() {
                        modal.remove();
                    });
    
                    cancelReset.addEventListener('click', function() {
                        modal.remove();
                    });
    
                    sendReset.addEventListener('click', function() {
                        const email = document.getElementById('reset-email').value;
    
                        if (!email || !isValidEmail(email)) {
                            showToast('Veuillez entrer une adresse email valide', 'error');
                            return;
                        }
    
                        // Simuler l'envoi d'un email
                        showToast('Un email de réinitialisation a été envoyé à ' + email);
                        modal.remove();
                    });
                });
            }
        }
    
        // Connexion avec les réseaux sociaux
        const socialButtons = document.querySelectorAll('.btn-social');
        if (socialButtons.length > 0) {
            socialButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
    
                    const provider = this.textContent.trim();
                    showToast(`Connexion avec ${provider} en cours...`);
    
                    // Simuler la connexion avec réseau social
                    setTimeout(() => {
                        window.location.href = 'dashboard.html';
                    }, 1500);
                });
            });
        }
    }
    
    // Page d'inscription
    function initRegisterPage() {
        const registerForm = document.querySelector('.auth-form');
    
        if (registerForm) {
            // Gestion du changement de type de compte
            const accountTypeButtons = document.querySelectorAll('.account-type');
    
            if (accountTypeButtons.length > 0) {
                accountTypeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        // Enlever la classe active de tous les boutons
                        accountTypeButtons.forEach(btn => btn.classList.remove('active'));
    
                        // Ajouter la classe active au bouton cliqué
                        this.classList.add('active');
    
                        const accountType = this.getAttribute('data-type');
    
                        // Adapter le formulaire selon le type de compte
                        if (accountType === 'student') {
                            // Formulaire étudiant (déjà par défaut)
                            document.getElementById('education').parentElement.parentElement.style.display = 'block';
                            document.getElementById('field').parentElement.parentElement.style.display = 'block';
    
                            // Masquer les champs spécifiques aux entreprises s'ils existent
                            const companyFields = document.querySelectorAll('.company-field');
                            if (companyFields.length > 0) {
                                companyFields.forEach(field => field.style.display = 'none');
                            }
                        } else if (accountType === 'company') {
                            // Masquer les champs spécifiques aux étudiants
                            document.getElementById('education').parentElement.parentElement.style.display = 'none';
                            document.getElementById('field').parentElement.parentElement.style.display = 'none';
    
                            // Afficher ou créer les champs spécifiques aux entreprises
                            let companyFields = document.querySelectorAll('.company-field');
    
                            if (companyFields.length === 0) {
                                // Créer les champs entreprise s'ils n'existent pas
                                const companyNameField = document.createElement('div');
                                companyNameField.classList.add('form-group', 'company-field');
                                companyNameField.innerHTML = `
                                    <label for="company-name">Nom de l'entreprise</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-building"></i>
                                        <input type="text" id="company-name" name="company-name" placeholder="Nom de votre entreprise" required>
                                    </div>
                                `;
    
                                const companySectorField = document.createElement('div');
                                companySectorField.classList.add('form-group', 'company-field');
                                companySectorField.innerHTML = `
                                    <label for="company-sector">Secteur d'activité</label>
                                    <div class="input-with-icon">
                                        <i class="fas fa-briefcase"></i>
                                        <select id="company-sector" name="company-sector" required>
                                            <option value="" disabled selected>Sélectionnez votre secteur</option>
                                            <option value="tech">Technologie & IT</option>
                                            <option value="finance">Finance & Banque</option>
                                            <option value="sante">Santé & Pharma</option>
                                            <option value="media">Médias & Communication</option>
                                            <option value="conseil">Conseil</option>
                                            <option value="industrie">Industrie & Ingénierie</option>
                                            <option value="commerce">Commerce & Distribution</option>
                                            <option value="transport">Transport & Logistique</option>
                                            <option value="energie">Énergie & Environnement</option>
                                            <option value="public">Secteur Public</option>
                                        </select>
                                    </div>
                                `;
    
                                // Insérer avant les conditions d'utilisation
                                const termsField = document.querySelector('.terms').parentElement;
                                registerForm.insertBefore(companyNameField, termsField);
                                registerForm.insertBefore(companySectorField, termsField);
                            } else {
                                // Afficher les champs entreprise s'ils existent déjà
                                companyFields.forEach(field => field.style.display = 'block');
                            }
                        }
                    });
                });
            }
    
            // Soumission du formulaire
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();
    
                // Validation des champs
                const firstname = document.getElementById('firstname').value;
                const lastname = document.getElementById('lastname').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm-password').value;
                const terms = document.getElementById('terms').checked;
    
                if (!firstname || !lastname || !email || !password || !confirmPassword) {
                    showToast('Veuillez remplir tous les champs obligatoires', 'error');
                    return;
                }
    
                if (!isValidEmail(email)) {
                    showToast('Veuillez entrer une adresse email valide', 'error');
                    return;
                }
    
                if (password.length < 8) {
                    showToast('Le mot de passe doit contenir au moins 8 caractères', 'error');
                    return;
                }
    
                if (password !== confirmPassword) {
                    showToast('Les mots de passe ne correspondent pas', 'error');
                    return;
                }
    
                if (!terms) {
                    showToast('Vous devez accepter les conditions d\'utilisation', 'error');
                    return;
                }
    
                // Type de compte
                const accountType = document.querySelector('.account-type.active').getAttribute('data-type');
    
                if (accountType === 'student') {
                    const education = document.getElementById('education').value;
                    const field = document.getElementById('field').value;
    
                    if (!education || !field) {
                        showToast('Veuillez compléter votre profil étudiant', 'error');
                        return;
                    }
                } else if (accountType === 'company') {
                    const companyName = document.getElementById('company-name').value;
                    const companySector = document.getElementById('company-sector').value;
    
                    if (!companyName || !companySector) {
                        showToast('Veuillez compléter votre profil entreprise', 'error');
                        return;
                    }
                }
    
                // Simulation d'inscription
                showToast('Inscription en cours...');
    
                setTimeout(() => {
                    showToast('Inscription réussie ! Redirection vers la connexion...', 'success');
    
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 1500);
                }, 1500);
            });
        }
    }
    
    // Tableau de bord
    function initDashboard() {
        // Gestion des favoris
        const bookmarkBtns = document.querySelectorAll('.bookmark-btn');
    
        if (bookmarkBtns.length > 0) {
            bookmarkBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (icon.classList.contains('far')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        showToast('Stage ajouté aux favoris');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        showToast('Stage retiré des favoris');
                    }
                });
            });
        }
    
        // Animations pour les cartes de statistiques
        const statCards = document.querySelectorAll('.stat-card');
    
        if (statCards.length > 0) {
            // Animation simple d'apparition avec délai
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
    
        // Navigation entre les onglets du tableau de bord
        const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    
        if (sidebarLinks.length > 0) {
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Si ce n'est pas un lien externe (comme déconnexion)
                    if (!this.getAttribute('href').includes('.html')) {
                        e.preventDefault();
    
                        // Enlever la classe active de tous les liens
                        sidebarLinks.forEach(l => l.parentElement.classList.remove('active'));
    
                        // Ajouter la classe active au lien cliqué
                        this.parentElement.classList.add('active');
    
                        // Mise à jour du titre de la page
                        const pageTitle = document.querySelector('.page-title h1');
                        const pageTitleText = this.textContent.trim();
    
                        if (pageTitle) {
                            pageTitle.textContent = pageTitleText;
                        }
    
                        // Simuler un changement de contenu
                        showToast(`Navigation vers ${pageTitleText}`);
                    }
                });
            });
        }
    
        // Postuler aux stages recommandés
        const applyButtons = document.querySelectorAll('.recommended-internships .btn-primary');
    
        if (applyButtons.length > 0) {
            applyButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
    
                    const cardHeader = this.closest('.internship-card-sm').querySelector('.card-header');
                    const stageName = cardHeader.querySelector('h3').textContent;
                    const companyName = cardHeader.querySelector('.company-name').textContent;
    
                    // Créer une boîte de dialogue pour confirmer la candidature
                    const modal = document.createElement('div');
                    modal.classList.add('modal');
                    modal.innerHTML = `
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Postuler au stage</h2>
                                <button class="close-modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p>Vous êtes sur le point de postuler au stage <strong>${stageName}</strong> chez <strong>${companyName}</strong>.</p>
                                <div class="form-group">
                                    <label for="cover-letter">Lettre de motivation</label>
                                    <textarea id="cover-letter" rows="6" placeholder="Écrivez votre lettre de motivation ici..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label>CV</label>
                                    <div class="upload-cv">
                                        <input type="file" id="cv-file" accept=".pdf,.doc,.docx">
                                        <label for="cv-file" class="btn btn-outline">
                                            <i class="fas fa-upload"></i> Télécharger votre CV
                                        </label>
                                        <span class="file-name">Aucun fichier sélectionné</span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-outline" id="cancel-apply">Annuler</button>
                                <button class="btn btn-primary" id="confirm-apply">Postuler</button>
                            </div>
                        </div>
                    `;
    
                    document.body.appendChild(modal);
    
                    // Gestion du modal
                    const closeModal = modal.querySelector('.close-modal');
                    const cancelApply = modal.querySelector('#cancel-apply');
                    const confirmApply = modal.querySelector('#confirm-apply');
                    const cvFile = modal.querySelector('#cv-file');
                    const fileName = modal.querySelector('.file-name');
    
                    closeModal.addEventListener('click', function() {
                        modal.remove();
                    });
    
                    cancelApply.addEventListener('click', function() {
                        modal.remove();
                    });
    
                    cvFile.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            fileName.textContent = this.files[0].name;
                        } else {
                            fileName.textContent = 'Aucun fichier sélectionné';
                        }
                    });
    
                    confirmApply.addEventListener('click', function() {
                        const coverLetter = document.getElementById('cover-letter').value;
    
                        if (!coverLetter) {
                            showToast('Veuillez rédiger une lettre de motivation', 'error');
                            return;
                        }
    
                        if (cvFile.files.length === 0) {
                            showToast('Veuillez télécharger votre CV', 'error');
                            return;
                        }
    
                        // Simulation de l'envoi de la candidature
                        showToast('Envoi de votre candidature en cours...');
    
                        setTimeout(() => {
                            showToast('Votre candidature a été envoyée avec succès !', 'success');
                            modal.remove();
    
                            // Mise à jour du bouton
                            button.textContent = 'Candidature envoyée';
                            button.classList.remove('btn-primary');
                            button.classList.add('btn-success');
                            button.disabled = true;
                        }, 1500);
                    });
                });
            });
        }
    }
    
    // Page Entreprises
    function initEntreprisesPage() {
        // Gestion des filtres pour les entreprises
        const filterSelects = document.querySelectorAll('.filter-group select');
    
        if (filterSelects.length > 0) {
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    // Simuler un filtrage des entreprises
                    showToast('Filtrage des entreprises en cours...');
    
                    setTimeout(() => {
                        // On pourrait ajouter une vraie logique de filtrage ici
                        showToast('Filtres appliqués');
                    }, 500);
                });
            });
        }
    
        // Gestion des tris
        const sortSelect = document.getElementById('sortSelect');
    
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                const sortValue = this.value;
                showToast(`Tri par ${sortValue} en cours...`);
    
                // Simuler un tri des entreprises
                setTimeout(() => {
                    showToast('Entreprises triées');
                }, 500);
            });
        }
    
        // Gestion de la vue (grid/list)
        const viewButtons = document.querySelectorAll('.view-btn');
        const entreprisesGrid = document.querySelector('.entreprises-grid');
    
        if (viewButtons.length > 0 && entreprisesGrid) {
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Enlever la classe active de tous les boutons
                    viewButtons.forEach(btn => btn.classList.remove('active'));
    
                    // Ajouter la classe active au bouton cliqué
                    this.classList.add('active');
    
                    const viewType = this.getAttribute('data-view');
    
                    // Changer la vue
                    if (viewType === 'grid') {
                        entreprisesGrid.classList.remove('list-view');
                    } else if (viewType === 'list') {
                        entreprisesGrid.classList.add('list-view');
                    }
                });
            });
        }
    
        // Gestion des favoris
        const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
        if (favoriteButtons.length > 0) {
            favoriteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const icon = this.querySelector('i');
    
                    if (icon.classList.contains('far')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        icon.style.color = '#f72585';
                        showToast('Entreprise ajoutée aux favoris');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        icon.style.color = '';
                        showToast('Entreprise retirée des favoris');
                    }
                });
            });
        }
    
        // Recherche d'entreprises
        const searchBar = document.querySelector('.search-bar.simple');
    
        if (searchBar) {
            searchBar.addEventListener('submit', function(e) {
                e.preventDefault();
    
                const keyword = this.querySelector('input[type="text"]').value.trim();
    
                if (keyword) {
                    showToast(`Recherche pour "${keyword}" en cours...`);
    
                    // Simuler une recherche
                    setTimeout(() => {
                        showToast(`Résultats pour "${keyword}"`);
                    }, 500);
                } else {
                    showToast('Veuillez entrer un terme de recherche', 'error');
                }
            });
        }
    
        // Pagination
        const paginationItems = document.querySelectorAll('.pagination-item, .pagination-prev, .pagination-next');
    
        if (paginationItems.length > 0) {
            paginationItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
    
                    if (this.classList.contains('disabled')) return;
    
                    // Enlever la classe active de tous les items
                    document.querySelectorAll('.pagination-item').forEach(item => item.classList.remove('active'));
    
                    // Si c'est un numéro de page, ajouter la classe active
                    if (this.classList.contains('pagination-item')) {
                        this.classList.add('active');
                    }
    
                    // Simuler un changement de page
                    showToast('Chargement de la page...');
    
                    setTimeout(() => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }, 300);
                });
            });
        }
    }
    // Page Contact
    function initContactPage() {
        // Gestion du formulaire de contact
        const contactForm = document.querySelector('.contact-form');
    
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
    
                // Validation des champs
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const subject = document.getElementById('subject').value;
                const message = document.getElementById('message').value;
                const privacy = document.getElementById('privacy').checked;
    
                if (!name || !email || !subject || !message) {
                    showToast('Veuillez remplir tous les champs', 'error');
                    return;
                }
    
                if (!isValidEmail(email)) {
                    showToast('Veuillez entrer une adresse email valide', 'error');
                    return;
                }
    
                if (!privacy) {
                    showToast('Vous devez accepter la politique de confidentialité', 'error');
                    return;
                }
    
                // Simulation d'envoi du message
                showToast('Envoi de votre message en cours...');
    
                setTimeout(() => {
                    showToast('Votre message a été envoyé avec succès ! Nous vous répondrons dans les meilleurs délais.', 'success');
    
                    // Réinitialiser le formulaire
                    contactForm.reset();
                }, 1500);
            });
        }
    
        // Gestion des FAQ
        const faqItems = document.querySelectorAll('.faq-item');
    
        if (faqItems.length > 0) {
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const toggle = item.querySelector('.faq-toggle');
    
                question.addEventListener('click', function() {
                    // Fermer les autres items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });
    
                    // Ouvrir/fermer l'item actuel
                    item.classList.toggle('active');
                });
    
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation(); // Éviter que l'événement se propage au question
    
                    // Fermer les autres items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });
    
                    // Ouvrir/fermer l'item actuel
                    item.classList.toggle('active');
                });
            });
        }
    
        // Google Maps (simulé)
        const getDirections = document.querySelector('.get-directions');
    
        if (getDirections) {
            getDirections.addEventListener('click', function(e) {
                e.preventDefault();
    
                // Ouvrir Google Maps dans un nouvel onglet
                window.open('https://maps.google.com?q=123+Avenue+de+l%27Innovation+75008+Paris+France', '_blank');
            });
        }
    }
    
    // Ajout de CSS dynamique pour les notifications et modals
    function addDynamicStyles() {
        // Vérifier si les styles sont déjà ajoutés
        if (document.getElementById('dynamic-styles')) return;
    
        const styleElement = document.createElement('style');
        styleElement.id = 'dynamic-styles';
    
        styleElement.textContent = `
            /* Toast notifications */
            .toast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background-color: white;
                color: #333;
                border-radius: 8px;
                padding: 0;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
                z-index: 9999;
                font-size: 14px;
                min-width: 300px;
                max-width: 400px;
                transform: translateY(100px);
                opacity: 0;
                transition: transform 0.3s ease, opacity 0.3s ease;
                overflow: hidden;
            }
            
            .toast-visible {
                transform: translateY(0);
                opacity: 1;
            }
            
            .toast-content {
                display: flex;
                align-items: center;
                padding: 15px;
            }
            
            .toast i {
                font-size: 20px;
                margin-right: 10px;
            }
            
            .toast-info i {
                color: var(--primary-color);
            }
            
            .toast-success i {
                color: #28a745;
            }
            
            .toast-error i {
                color: #dc3545;
            }
            
            .toast-close {
                position: absolute;
                top: 10px;
                right: 10px;
                background: none;
                border: none;
                font-size: 18px;
                cursor: pointer;
                color: #999;
            }
            
            /* Progress bars for toasts */
            .toast::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                width: 100%;
                background-color: var(--primary-color);
                animation: toast-progress 5s linear forwards;
            }
            
            .toast-success::after {
                background-color: #28a745;
            }
            
            .toast-error::after {
                background-color: #dc3545;
            }
            
            @keyframes toast-progress {
                from {
                    width: 100%;
                }
                to {
                    width: 0%;
                }
            }
            
            /* Modal styles */
            .modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                opacity: 0;
                animation: modal-fade-in 0.3s forwards;
            }
            
            @keyframes modal-fade-in {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
            
            .modal-content {
                background-color: white;
                border-radius: 8px;
                width: 90%;
                max-width: 500px;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                animation: modal-slide-in 0.3s forwards;
            }
            
            @keyframes modal-slide-in {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            .modal-header {
                padding: 15px 20px;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .modal-header h2 {
                margin: 0;
                font-size: 1.3rem;
            }
            
            .close-modal {
                background: none;
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: #999;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .modal-footer {
                padding: 15px 20px;
                border-top: 1px solid #eee;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }
            
            /* Dropdown menus */
            .dropdown-menu {
                position: absolute;
                top: 100%;
                right: 0;
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
                z-index: 99;
                min-width: 200px;
                margin-top: 10px;
                overflow: hidden;
                animation: dropdown-slide-in 0.2s forwards;
            }
            
            @keyframes dropdown-slide-in {
                from {
                    transform: translateY(-10px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            
            .dropdown-header {
                padding: 15px;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .dropdown-header h3 {
                margin: 0;
                font-size: 1rem;
            }
            
            .dropdown-header a {
                font-size: 0.8rem;
                color: var(--primary-color);
                text-decoration: none;
            }
            
            .dropdown-menu ul {
                list-style: none;
                padding: 0;
                margin: 0;
                max-height: 300px;
                overflow-y: auto;
            }
            
            .dropdown-menu li {
                padding: 12px 15px;
                border-bottom: 1px solid #f5f5f5;
                transition: background-color 0.2s;
            }
            
            .dropdown-menu li:last-child {
                border-bottom: none;
            }
            
            .dropdown-menu li:hover {
                background-color: #f9f9f9;
            }
            
            .dropdown-menu li.unread {
                background-color: #f0f7ff;
            }
            
            .dropdown-footer {
                padding: 12px 15px;
                border-top: 1px solid #eee;
                text-align: center;
            }
            
            .dropdown-footer a {
                color: var(--primary-color);
                text-decoration: none;
                font-size: 0.9rem;
            }
            
            /* Notifications dropdown specifics */
            .notification-dropdown {
                min-width: 300px;
            }
            
            .notification-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background-color: #f0f7ff;
                color: var(--primary-color);
                margin-right: 10px;
            }
            
            .notification-content {
                display: inline-block;
                vertical-align: middle;
                width: calc(100% - 50px);
            }
            
            .notification-content p {
                margin: 0 0 5px;
                font-size: 0.9rem;
            }
            
            .notification-time {
                font-size: 0.8rem;
                color: #999;
            }
            
            /* Messages dropdown specifics */
            .message-dropdown {
                min-width: 300px;
            }
            
            .message-avatar {
                display: inline-block;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                overflow: hidden;
                margin-right: 10px;
            }
            
            .message-avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .message-content {
                display: inline-block;
                vertical-align: middle;
                width: calc(100% - 50px);
            }
            
            .message-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
            }
            
            .message-info h4 {
                margin: 0;
                font-size: 0.95rem;
            }
            
            .message-time {
                font-size: 0.8rem;
                color: #999;
            }
            
            .message-content p {
                margin: 0;
                font-size: 0.85rem;
                color: #666;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            /* File upload */
            .upload-cv {
                display: flex;
                align-items: center;
                margin-top: 10px;
            }
            
            .upload-cv input[type="file"] {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                border: 0;
            }
            
            .upload-cv label {
                cursor: pointer;
                margin-right: 10px;
            }
            
            .file-name {
                flex: 1;
                font-size: 0.9rem;
                color: #666;
            }
            
            /* Responsive (mobile) menu */
            .mobile-menu-toggle {
                display: none;
                background: none;
                border: none;
                font-size: 1.4rem;
                cursor: pointer;
            }
            
            @media screen and (max-width: 768px) {
                .mobile-menu-toggle {
                    display: block;
                }
                
                .nav-menu {
                    display: none;
                }
                
                .nav-menu.active {
                    display: flex;
                    position: absolute;
                    top: 80px;
                    left: 0;
                    right: 0;
                    background-color: white;
                    flex-direction: column;
                    align-items: center;
                    padding: 20px;
                    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
                    z-index: 100;
                    animation: mobile-menu-slide-in 0.3s forwards;
                }
                
                @keyframes mobile-menu-slide-in {
                    from {
                        opacity: 0;
                        transform: translateY(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            }
            
            /* List view for entreprises */
            .entreprises-grid.list-view {
                display: block;
            }
            
            .entreprises-grid.list-view .entreprise-card {
                display: flex;
                margin-bottom: 20px;
            }
            
            .entreprises-grid.list-view .card-header {
                width: 150px;
                border-bottom: none;
                border-right: 1px solid #e0e0e0;
            }
            
            .entreprises-grid.list-view .card-body {
                flex: 1;
            }
            
            .entreprises-grid.list-view .card-footer {
                width: 200px;
                border-top: none;
                border-left: 1px solid #e0e0e0;
                flex-direction: column;
                justify-content: center;
            }
            
            .entreprises-grid.list-view .card-footer .btn {
                margin-bottom: 10px;
            }
        `;
    
        document.head.appendChild(styleElement);
    }
    
    // Appeler cette fonction au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        addDynamicStyles();
    });
    
    // Animations et effets visuels avancés
    function initAnimations() {
        // Animations au scroll
        const elementsToAnimate = document.querySelectorAll('.feature-card, .internship-card, .entreprise-card, .contact-card');
    
        if (elementsToAnimate.length > 0) {
            // Créer un observateur d'intersection
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });
    
            // Ajouter du CSS pour l'animation
            const styleElement = document.createElement('style');
            styleElement.textContent = `
                .feature-card, .internship-card, .entreprise-card, .contact-card {
                    opacity: 0;
                    transform: translateY(30px);
                    transition: opacity 0.5s ease, transform 0.5s ease;
                }
                
                .animate-in {
                    opacity: 1 !important;
                    transform: translateY(0) !important;
                }
                
                /* Effet de survol amélioré pour les cartes */
                .internship-card:hover, .entreprise-card:hover {
                    transform: translateY(-10px) !important;
                    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
                }
                
                /* Animation des icônes dans les cartes de fonctionnalités */
                .feature-card:hover .feature-icon {
                    transform: scale(1.2);
                    transition: transform 0.3s ease;
                }
                
                /* Animation des boutons */
                .btn {
                    transition: all 0.3s ease;
                }
                
                .btn-primary:hover, .btn-accent:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                }
            `;
    
            document.head.appendChild(styleElement);
    
            // Observer chaque élément
            elementsToAnimate.forEach(element => {
                observer.observe(element);
            });
        }
    
        // Animation de la barre de recherche sur la page d'accueil
        const heroSearchBar = document.querySelector('.hero .search-bar');
    
        if (heroSearchBar) {
            heroSearchBar.style.opacity = '0';
            heroSearchBar.style.transform = 'translateY(20px)';
            heroSearchBar.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    
            setTimeout(() => {
                heroSearchBar.style.opacity = '1';
                heroSearchBar.style.transform = 'translateY(0)';
            }, 500);
        }
    }
    
    // StageConnect - Admin Dashboard Script
    // Ce script s'appuie sur les fonctionnalités existantes dans Scripts.js
    // et ajoute des fonctionnalités spécifiques à l'administration
    
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si on est sur la page d'administration
        if (document.querySelector('.admin-sidebar')) {
            console.log('Initialisation du dashboard administrateur');
            initAdminTabs();
            initAdminDataTables();
            initAdminCharts();
            initAdminActions();
        }
    });
    
    // Gestion des onglets du dashboard administrateur
    function initAdminTabs() {
        const tabItems = document.querySelectorAll('.sidebar-nav li[data-tab]');
        const tabContents = document.querySelectorAll('.tab-content');
    
        if (tabItems.length > 0) {
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
    
                        // Mettre à jour le titre de la page (comme dans le script original)
                        const pageTitle = document.querySelector('.page-title h1');
                        if (pageTitle) {
                            const tabName = this.querySelector('a').textContent.trim();
                            document.title = 'StageConnect - ' + tabName;
                        }
                    }
                });
            });
        }
    }
    
    // Initialisation des tableaux de données avec recherche et filtrage
    function initAdminDataTables() {
        // Gestion de la recherche dans les tableaux
        const searchBars = document.querySelectorAll('.search-bar.admin');
    
        if (searchBars.length > 0) {
            searchBars.forEach(searchBar => {
                searchBar.addEventListener('submit', function(e) {
                    e.preventDefault();
    
                    const searchInput = this.querySelector('input').value.toLowerCase();
                    const tableContainer = this.closest('.dashboard-card').querySelector('.data-table');
    
                    if (tableContainer) {
                        const rows = tableContainer.querySelectorAll('tbody tr');
    
                        rows.forEach(row => {
                            let match = false;
                            const cells = row.querySelectorAll('td');
    
                            cells.forEach(cell => {
                                if (cell.textContent.toLowerCase().includes(searchInput)) {
                                    match = true;
                                }
                            });
    
                            row.style.display = match ? '' : 'none';
                        });
    
                        // Afficher un message si aucun résultat
                        const noResults = tableContainer.parentNode.querySelector('.no-results');
                        const hasVisibleRows = Array.from(rows).some(row => row.style.display !== 'none');
    
                        if (!hasVisibleRows) {
                            if (!noResults) {
                                const noResultsDiv = document.createElement('div');
                                noResultsDiv.className = 'no-results';
                                noResultsDiv.innerHTML = '<p>Aucun résultat trouvé pour "' + searchInput + '"</p>';
                                tableContainer.parentNode.appendChild(noResultsDiv);
                            }
                        } else if (noResults) {
                            noResults.remove();
                        }
                    }
                });
            });
        }
    
        // Gestion des filtres par statut/rôle
        const statusFilters = document.querySelectorAll('#internshipStatusFilter, #userRoleFilter, #companySectorFilter');
    
        if (statusFilters.length > 0) {
            statusFilters.forEach(filter => {
                filter.addEventListener('change', function() {
                    const filterValue = this.value.toLowerCase();
                    const tableContainer = this.closest('.dashboard-content').querySelector('.data-table');
    
                    if (tableContainer && filterValue !== 'all') {
                        const rows = tableContainer.querySelectorAll('tbody tr');
    
                        rows.forEach(row => {
                            let statusCell;
    
                            // Identifier la colonne qui contient la valeur à filtrer
                            if (this.id === 'internshipStatusFilter') {
                                statusCell = row.querySelector('td:nth-child(6)');
                            } else if (this.id === 'userRoleFilter') {
                                statusCell = row.querySelector('td:nth-child(4)');
                            } else if (this.id === 'companySectorFilter') {
                                statusCell = row.querySelector('td:nth-child(3)');
                            }
    
                            if (statusCell) {
                                const statusText = statusCell.textContent.toLowerCase();
                                row.style.display = statusText.includes(filterValue) ? '' : 'none';
                            }
                        });
                    } else if (tableContainer) {
                        // Réinitialiser l'affichage si "Tous" est sélectionné
                        const rows = tableContainer.querySelectorAll('tbody tr');
                        rows.forEach(row => row.style.display = '');
                    }
                });
            });
        }
    
        // Pagination des tableaux
        const paginationItems = document.querySelectorAll('.pagination-item, .pagination-prev, .pagination-next');
    
        if (paginationItems.length > 0) {
            paginationItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
    
                    if (this.classList.contains('disabled')) return;
    
                    // Enlever la classe active de tous les items
                    document.querySelectorAll('.pagination-item').forEach(item => item.classList.remove('active'));
    
                    // Si c'est un numéro de page, ajouter la classe active
                    if (this.classList.contains('pagination-item')) {
                        this.classList.add('active');
                    }
    
                    // Simuler un changement de page
                    showToast('Chargement de la page...');
    
                    setTimeout(() => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }, 300);
                });
            });
        }
    }
    
    // Initialisation des graphiques
    function initAdminCharts() {
        // Simulation de graphique d'évolution des inscriptions
        const registrationsChart = document.getElementById('registrationsChart');
        const userTypeFilter = document.getElementById('userTypeFilter');
        const timeRangeFilter = document.getElementById('timeRangeFilter');
    
        if (registrationsChart && userTypeFilter && timeRangeFilter) {
            const updateChart = function() {
                // Simulation de chargement
                registrationsChart.innerHTML = '<i class="fas fa-spinner fa-spin fa-3x"></i>';
    
                setTimeout(() => {
                    // Simulation d'un graphique (à remplacer par une vraie bibliothèque comme Chart.js)
                    registrationsChart.innerHTML = `
                        <div style="height: 300px; position: relative;">
                            <div style="position: absolute; bottom: 0; left: 0; width: 5%; height: 40%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 7%; width: 5%; height: 35%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 14%; width: 5%; height: 45%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 21%; width: 5%; height: 60%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 28%; width: 5%; height: 55%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 35%; width: 5%; height: 65%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 42%; width: 5%; height: 75%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 49%; width: 5%; height: 85%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 56%; width: 5%; height: 80%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 63%; width: 5%; height: 90%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 70%; width: 5%; height: 70%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 77%; width: 5%; height: 75%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 84%; width: 5%; height: 92%; background-color: var(--primary-color);"></div>
                            <div style="position: absolute; bottom: 0; left: 91%; width: 5%; height: 88%; background-color: var(--primary-color);"></div>
                        </div>
                    `;
                }, 800);
            };
    
            // Mettre à jour le graphique au chargement de la page
            updateChart();
    
            // Mettre à jour le graphique lors du changement de filtre
            userTypeFilter.addEventListener('change', updateChart);
            timeRangeFilter.addEventListener('change', updateChart);
        }
    }
    
    // Initialisation des actions sur les données
    function initAdminActions() {
        // Boutons d'ajout
        const addButtons = document.querySelectorAll('#addInternshipBtn, #addUserBtn, #addCompanyBtn');
    
        if (addButtons.length > 0) {
            addButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Déterminer le type d'élément à ajouter
                    let entityType = '';
    
                    if (this.id === 'addInternshipBtn') {
                        entityType = 'stage';
                    } else if (this.id === 'addUserBtn') {
                        entityType = 'utilisateur';
                    } else if (this.id === 'addCompanyBtn') {
                        entityType = 'entreprise';
                    }
    
                    // Afficher une notification toast
                    showToast(`Formulaire d'ajout d'un nouveau ${entityType} en cours de développement...`);
                });
            });
        }
    
        // Boutons d'action (voir, éditer, supprimer)
        const actionButtons = document.querySelectorAll('.btn-view, .btn-edit, .btn-delete');
    
        if (actionButtons.length > 0) {
            actionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Récupérer l'ID et le titre de l'élément à partir de la ligne
                    const row = this.closest('tr');
                    const id = row.querySelector('td:first-child').textContent;
                    const title = row.querySelector('td:nth-child(2)').textContent;
    
                    let actionType = '';
    
                    if (this.classList.contains('btn-view')) {
                        actionType = 'voir';
                    } else if (this.classList.contains('btn-edit')) {
                        actionType = 'modifier';
                    } else if (this.classList.contains('btn-delete')) {
                        actionType = 'supprimer';
                    }
    
                    if (actionType === 'supprimer') {
                        // Créer une boîte de dialogue de confirmation pour la suppression
                        const modal = document.createElement('div');
                        modal.classList.add('modal');
                        modal.innerHTML = `
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2>Confirmation de suppression</h2>
                                    <button class="close-modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <p>Êtes-vous sûr de vouloir supprimer <strong>${title}</strong> (${id}) ?</p>
                                    <p>Cette action est irréversible.</p>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-outline" id="cancelDelete">Annuler</button>
                                    <button class="btn btn-primary" id="confirmDelete">Supprimer</button>
                                </div>
                            </div>
                        `;
    
                        document.body.appendChild(modal);
    
                        // Gestion du modal
                        const closeModal = modal.querySelector('.close-modal');
                        const cancelDelete = modal.querySelector('#cancelDelete');
                        const confirmDelete = modal.querySelector('#confirmDelete');
    
                        closeModal.addEventListener('click', function() {
                            modal.remove();
                        });
    
                        cancelDelete.addEventListener('click', function() {
                            modal.remove();
                        });
    
                        confirmDelete.addEventListener('click', function() {
                            // Simuler la suppression
                            showToast('Suppression en cours...');
    
                            setTimeout(() => {
                                // Simuler une suppression réussie
                                row.remove();
                                modal.remove();
                                showToast('Élément supprimé avec succès', 'success');
                            }, 1000);
                        });
                    } else {
                        // Afficher une notification toast pour les autres actions
                        showToast(`Action pour ${actionType} l'élément "${title}" (${id}) en cours de développement...`);
                    }
                });
            });
        }
    }
    
    // Appeler cette fonction au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        // Délai pour laisser la page se charger complètement
        setTimeout(initAnimations, 100);
    });
    
    // StageConnect - Tableau de bord étudiant
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si on est sur la page du tableau de bord étudiant
        if (document.querySelector('.dashboard-body') && !document.querySelector('.admin-sidebar')) {
            console.log('Initialisation du tableau de bord étudiant');
    
            initStudentDashboardFeatures();
            initRecommendationSystem();
            initProfileCompletion();
            initEventCalendar();
            initApplicationTracking();
        }
    });
    
    // Fonctionnalités générales du tableau de bord
    function initStudentDashboardFeatures() {
        // Animation des cartes de statistiques avec délai
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
    
        // Recherche améliorée
        const searchInput = document.querySelector('.header-search input');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    showToast(`Recherche pour "${this.value}" en cours...`);
    
                    setTimeout(() => {
                        showToast('Fonctionnalité en développement');
                    }, 1000);
                }
            });
        }
    
        // Notifications interactives
        const notificationBadge = document.querySelector('.notification-badge');
        const messageBadge = document.querySelector('.message-badge');
    
        if (notificationBadge) {
            notificationBadge.addEventListener('click', function() {
                // Simuler l'affichage des notifications
                if (!document.querySelector('.notification-dropdown')) {
                    showToast('Chargement des notifications...');
                }
            });
        }
    
        if (messageBadge) {
            messageBadge.addEventListener('click', function() {
                // Simuler l'affichage des messages
                if (!document.querySelector('.message-dropdown')) {
                    showToast('Chargement des messages...');
                }
            });
        }
    }
    
    // Système de recommandation personnalisé
    function initRecommendationSystem() {
        const applyButtons = document.querySelectorAll('.recommended-internships .btn-primary');
    
        if (applyButtons.length > 0) {
            applyButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
    
                    const card = this.closest('.internship-card-sm');
                    const stageName = card.querySelector('h3').textContent;
                    const companyName = card.querySelector('.company-name').textContent;
    
                    showToast(`Préparation de votre candidature pour "${stageName}" chez ${companyName}...`);
    
                    // Simulation d'une candidature en cours
                    setTimeout(() => {
                        this.textContent = 'Candidature envoyée';
                        this.disabled = true;
                        this.classList.remove('btn-primary');
                        this.classList.add('btn-success');
                        this.style.backgroundColor = '#4BB543';
    
                        showToast('Votre candidature a été envoyée avec succès !', 'success');
                    }, 1500);
                });
            });
        }
    
        // Ajouter des favoris
        const bookmarkBtns = document.querySelectorAll('.bookmark-btn');
    
        if (bookmarkBtns.length > 0) {
            bookmarkBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
    
                    const icon = this.querySelector('i');
    
                    if (icon.classList.contains('far')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        icon.style.color = '#f72585';
    
                        showToast('Stage ajouté aux favoris');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        icon.style.color = '';
    
                        showToast('Stage retiré des favoris');
                    }
                });
            });
        }
    }
    
    // Complétion du profil
    function initProfileCompletion() {
        const profileButton = document.querySelector('.btn-outline.btn-sm');
        const progressBar = document.querySelector('.progress');
    
        if (profileButton && progressBar) {
            profileButton.addEventListener('click', function(e) {
                e.preventDefault();
    
                showToast('Chargement du formulaire de profil...');
    
                // Simuler le chargement
                setTimeout(() => {
                    // Incrémenter la progression
                    const currentWidth = parseInt(progressBar.style.width) || 75;
                    const newWidth = Math.min(currentWidth + 5, 100);
    
                    progressBar.style.width = newWidth + '%';
    
                    const progressText = document.querySelector('.progress-text span');
                    if (progressText) {
                        progressText.textContent = newWidth + '% complété';
                    }
    
                    // Marquer une tâche comme complétée
                    const incompleteTasks = document.querySelectorAll('.task-item:not(.completed)');
                    if (incompleteTasks.length > 0) {
                        const firstTask = incompleteTasks[0];
                        const icon = firstTask.querySelector('i');
    
                        icon.classList.remove('far', 'fa-circle');
                        icon.classList.add('fas', 'fa-check-circle');
                        firstTask.classList.add('completed');
    
                        showToast('Votre profil a été mis à jour !', 'success');
                    }
                }, 1000);
            });
        }
    
        // Rendre les tâches cliquables
        const taskItems = document.querySelectorAll('.task-item');
    
        if (taskItems.length > 0) {
            taskItems.forEach(item => {
                item.style.cursor = 'pointer';
    
                item.addEventListener('click', function() {
                    const taskName = this.querySelector('span').textContent;
    
                    if (this.classList.contains('completed')) {
                        showToast(`Tâche "${taskName}" déjà complétée !`);
                    } else {
                        showToast(`Compléter la tâche "${taskName}"...`);
    
                        // Simuler une action
                        setTimeout(() => {
                            const icon = this.querySelector('i');
    
                            icon.classList.remove('far', 'fa-circle');
                            icon.classList.add('fas', 'fa-check-circle');
                            this.classList.add('completed');
    
                            // Mettre à jour la barre de progression
                            const completedTasks = document.querySelectorAll('.task-item.completed').length;
                            const totalTasks = taskItems.length;
                            const percentage = Math.round((completedTasks / totalTasks) * 100);
    
                            progressBar.style.width = percentage + '%';
    
                            const progressText = document.querySelector('.progress-text span');
                            if (progressText) {
                                progressText.textContent = percentage + '% complété';
                            }
    
                            showToast('Votre profil a été mis à jour !', 'success');
                        }, 1000);
                    }
                });
            });
        }
    }
    
    // Gestion du calendrier des événements
    function initEventCalendar() {
        const eventItems = document.querySelectorAll('.event-item');
    
        if (eventItems.length > 0) {
            eventItems.forEach(item => {
                item.style.cursor = 'pointer';
    
                item.addEventListener('click', function() {
                    const eventTitle = this.querySelector('h3').textContent;
                    const eventDate = this.querySelector('.event-date .day').textContent + ' ' +
                        this.querySelector('.event-date .month').textContent;
                    const eventTime = this.querySelector('p:nth-child(2)').textContent;
                    const eventLocation = this.querySelector('p:nth-child(3)').textContent;
    
                    showToast(`Événement: ${eventTitle} - ${eventDate} - ${eventTime}`);
    
                    // Ajouter un effet visuel au clic
                    this.style.backgroundColor = '#f5f7fa';
    
                    setTimeout(() => {
                        this.style.backgroundColor = '';
                    }, 300);
                });
            });
        }
    
        // Lien "Voir tout" pour les événements
        const viewAllEvents = document.querySelector('.events-list').closest('.dashboard-card').querySelector('.view-all');
    
        if (viewAllEvents) {
            viewAllEvents.addEventListener('click', function(e) {
                e.preventDefault();
    
                showToast('Chargement de tous les événements...');
    
                // Simuler un chargement
                setTimeout(() => {
                    showToast('Fonctionnalité en développement');
                }, 1000);
            });
        }
    }
    
    // Gestion des candidatures
    function initApplicationTracking() {
        const applicationItems = document.querySelectorAll('.applications-list .application-item');
    
        if (applicationItems.length > 0) {
            applicationItems.forEach(item => {
                item.style.cursor = 'pointer';
    
                item.addEventListener('click', function() {
                    const title = this.querySelector('h3').textContent;
                    const details = this.querySelector('p').textContent;
                    const status = this.querySelector('.application-status') ?
                        this.querySelector('.application-status').textContent.trim() :
                        'En cours';
    
                    showToast(`Candidature: ${title} - ${details} - Statut: ${status}`);
    
                    // Ajouter un effet visuel au clic
                    this.style.backgroundColor = '#f5f7fa';
    
                    setTimeout(() => {
                        this.style.backgroundColor = '';
                    }, 300);
                });
            });
        }
    
        // Lien "Voir tout" pour les candidatures
        const viewAllApplications = document.querySelector('.applications-list').closest('.dashboard-card').querySelector('.view-all');
    
        if (viewAllApplications) {
            viewAllApplications.addEventListener('click', function(e) {
                e.preventDefault();
    
                showToast('Chargement de toutes les candidatures...');
    
                // Simuler un chargement
                setTimeout(() => {
                    showToast('Fonctionnalité en développement');
                }, 1000);
            });
        }
    }
    
    // Fonction pour initialiser la page des candidatures
    function initApplicationsPage() {
        // Gestion des éléments dépliables
        const applicationHeaders = document.querySelectorAll('.application-header');
    
        if (applicationHeaders.length > 0) {
            applicationHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    // Récupérer l'élément parent (application-detailed-item)
                    const applicationItem = this.closest('.application-detailed-item');
    
                    // Vérifier si l'élément est déjà actif
                    const isActive = applicationItem.classList.contains('active');
    
                    // Fermer tous les éléments ouverts
                    document.querySelectorAll('.application-detailed-item.active').forEach(item => {
                        if (item !== applicationItem) {
                            item.classList.remove('active');
                        }
                    });
    
                    // Basculer l'état de l'élément actuel
                    if (isActive) {
                        applicationItem.classList.remove('active');
                    } else {
                        applicationItem.classList.add('active');
                    }
                });
            });
    
            // Ouvrir le premier élément par défaut
            applicationHeaders[0].click();
        }
    
        // Gestion des filtres
        const filterForm = document.querySelector('.filter-controls');
    
        if (filterForm) {
            const applyButton = filterForm.querySelector('.btn-primary');
            const resetButton = filterForm.querySelector('.btn-outline');
    
            applyButton.addEventListener('click', function() {
                const statusFilter = document.getElementById('statusFilter').value;
                const periodFilter = document.getElementById('periodFilter').value;
                const sortFilter = document.getElementById('sortFilter').value;
    
                // Simuler un filtrage en affichant une notification
                showToast(`Filtres appliqués : Statut=${statusFilter}, Période=${periodFilter}, Tri=${sortFilter}`);
    
                // Ici, on pourrait ajouter la logique réelle de filtrage
            });
    
            resetButton.addEventListener('click', function() {
                // Réinitialiser les sélecteurs
                document.getElementById('statusFilter').value = 'all';
                document.getElementById('periodFilter').value = 'all';
                document.getElementById('sortFilter').value = 'date-desc';
    
                showToast('Filtres réinitialisés');
            });
        }
    
        // Gestion des vues (détaillée/compacte)
        const viewButtons = document.querySelectorAll('.view-btn');
    
        if (viewButtons.length > 0) {
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Enlever la classe active de tous les boutons
                    viewButtons.forEach(btn => btn.classList.remove('active'));
    
                    // Ajouter la classe active au bouton cliqué
                    this.classList.add('active');
    
                    const viewType = this.getAttribute('data-view');
    
                    // Simuler un changement de vue
                    showToast(`Vue ${viewType} sélectionnée`);
                });
            });
        }
    
        // Gestion des boutons d'action
        const actionButtons = document.querySelectorAll('.application-actions .btn');
    
        if (actionButtons.length > 0) {
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
    
                    const action = this.textContent.trim();
                    const applicationItem = this.closest('.application-detailed-item');
                    const jobTitle = applicationItem.querySelector('.application-title h3').textContent;
    
                    if (this.classList.contains('btn-danger')) {
                        // Confirmation pour les actions dangereuses
                        if (confirm(`Êtes-vous sûr de vouloir annuler votre candidature pour "${jobTitle}" ?`)) {
                            showToast(`Candidature pour "${jobTitle}" annulée`, 'success');
    
                            // Simuler la suppression visuelle
                            setTimeout(() => {
                                applicationItem.style.opacity = '0';
                                setTimeout(() => {
                                    applicationItem.style.display = 'none';
                                }, 300);
                            }, 500);
                        }
                    } else {
                        // Actions standard
                        showToast(`Action "${action}" pour "${jobTitle}" en cours...`);
                    }
                });
            });
        }
    }
    
    // Appel de l'initialisation quand le document est chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si on est sur la page des candidatures
        if (document.querySelector('.applications-detailed')) {
            initApplicationsPage();
        }
    });

    // Fonction pour initialiser la page des stages favoris
    function initFavoritesPage() {
        // Gestion des filtres
        const filterForm = document.querySelector('.filter-controls');

        if (filterForm) {
            const applyButton = filterForm.querySelector('.btn-primary');
            const resetButton = filterForm.querySelector('.btn-outline');

            applyButton.addEventListener('click', function() {
                const domainFilter = document.getElementById('domainFilter').value;
                const locationFilter = document.getElementById('locationFilter').value;
                const sortFilter = document.getElementById('sortFilter').value;

                // Simuler un filtrage en affichant une notification
                showToast(`Filtres appliqués : Domaine=${domainFilter}, Localisation=${locationFilter}, Tri=${sortFilter}`);

                // Ici, on pourrait ajouter la logique réelle de filtrage
                filterFavorites(domainFilter, locationFilter, sortFilter);
            });

            resetButton.addEventListener('click', function() {
                // Réinitialiser les sélecteurs
                document.getElementById('domainFilter').value = 'all';
                document.getElementById('locationFilter').value = 'all';
                document.getElementById('sortFilter').value = 'date-desc';

                showToast('Filtres réinitialisés');

                // Réinitialiser l'affichage
                resetFilters();
            });
        }

        // Simulation du filtrage
        function filterFavorites(domain, location, sort) {
            const cards = document.querySelectorAll('.internship-card');
            let visibleCount = 0;

            cards.forEach(card => {
                let visible = true;

                // Simuler le filtrage par domaine
                if (domain !== 'all') {
                    const tags = Array.from(card.querySelectorAll('.tag')).map(tag => tag.textContent.toLowerCase());
                    if (!tags.some(tag => tag.includes(getDomainKeyword(domain)))) {
                        visible = false;
                    }
                }

                // Simuler le filtrage par localisation
                if (location !== 'all' && visible) {
                    const locationText = card.querySelector('.detail-item:first-child .detail-text').textContent.toLowerCase();
                    if (!locationText.includes(location.toLowerCase())) {
                        visible = false;
                    }
                }

                // Appliquer la visibilité
                if (visible) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Mettre à jour le compteur
            updateCounter(visibleCount);

            // Afficher l'état vide si nécessaire
            toggleEmptyState(visibleCount === 0);
        }

        // Fonction pour obtenir le mot-clé correspondant au domaine
        function getDomainKeyword(domain) {
            const keywords = {
                'tech': 'python|react|node|api|javascript',
                'marketing': 'seo|ads|media|marketing',
                'finance': 'finance|comptabilité|banking',
                'design': 'figma|ui|ux|sketch|design',
                'data': 'data|analyst|tableau|sql'
            };

            return keywords[domain] || '';
        }

        // Réinitialiser les filtres
        function resetFilters() {
            const cards = document.querySelectorAll('.internship-card');
            cards.forEach(card => {
                card.style.display = 'block';
            });

            updateCounter(cards.length);
            toggleEmptyState(false);
        }

        // Mettre à jour le compteur de stages
        function updateCounter(count) {
            const counterElement = document.querySelector('.card-header-alt h2');
            if (counterElement) {
                counterElement.textContent = `Vos stages sauvegardés (${count})`;
            }
        }

        // Afficher/masquer l'état vide
        function toggleEmptyState(isEmpty) {
            const emptyState = document.querySelector('.no-favorites');
            const resultsContainer = document.querySelector('.favorites-container');
            const pagination = document.querySelector('.pagination');

            if (emptyState && resultsContainer && pagination) {
                if (isEmpty) {
                    emptyState.classList.remove('hidden');
                    resultsContainer.classList.add('hidden');
                    pagination.classList.add('hidden');
                } else {
                    emptyState.classList.add('hidden');
                    resultsContainer.classList.remove('hidden');
                    pagination.classList.remove('hidden');
                }
            }
        }

        // Gestion des vues (grille/liste)
        const viewButtons = document.querySelectorAll('.view-btn');
        const internshipGrid = document.querySelector('.internship-grid');

        if (viewButtons.length > 0 && internshipGrid) {
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Enlever la classe active de tous les boutons
                    viewButtons.forEach(btn => btn.classList.remove('active'));

                    // Ajouter la classe active au bouton cliqué
                    this.classList.add('active');

                    const viewType = this.getAttribute('data-view');

                    // Changer la vue
                    if (viewType === 'grid') {
                        internshipGrid.classList.remove('list-view');
                    } else if (viewType === 'list') {
                        internshipGrid.classList.add('list-view');
                    }
                });
            });
        }

        // Gestion des favoris (suppression)
        const removeButtons = document.querySelectorAll('.btn-remove');

        if (removeButtons.length > 0) {
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.internship-card');
                    const title = card.querySelector('.card-title h3').textContent;

                    if (confirm(`Êtes-vous sûr de vouloir retirer "${title}" de vos favoris ?`)) {
                        // Ajouter une animation de suppression
                        card.classList.add('removing');

                        // Supprimer après l'animation
                        setTimeout(() => {
                            card.remove();

                            // Mettre à jour le compteur
                            const remainingCards = document.querySelectorAll('.internship-card').length;
                            updateCounter(remainingCards);

                            // Vérifier s'il reste des stages
                            toggleEmptyState(remainingCards === 0);

                            // Mettre à jour les statistiques
                            updateStats();

                            showToast('Stage retiré des favoris', 'success');
                        }, 500);
                    }
                });
            });
        }

        // Gestion des étoiles (retirer des favoris)
        const bookmarkButtons = document.querySelectorAll('.bookmark-btn.active');

        if (bookmarkButtons.length > 0) {
            bookmarkButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.internship-card');
                    const title = card.querySelector('.card-title h3').textContent;

                    if (confirm(`Êtes-vous sûr de vouloir retirer "${title}" de vos favoris ?`)) {
                        // Ajouter une animation de suppression
                        card.classList.add('removing');

                        // Supprimer après l'animation
                        setTimeout(() => {
                            card.remove();

                            // Mettre à jour le compteur
                            const remainingCards = document.querySelectorAll('.internship-card').length;
                            updateCounter(remainingCards);

                            // Vérifier s'il reste des stages
                            toggleEmptyState(remainingCards === 0);

                            // Mettre à jour les statistiques
                            updateStats();

                            showToast('Stage retiré des favoris', 'success');
                        }, 500);
                    }
                });
            });
        }

        // Mise à jour des statistiques
        function updateStats() {
            const totalFavs = document.querySelectorAll('.internship-card').length;
            const statCards = document.querySelectorAll('.stat-card .stat-info h3');

            if (statCards.length >= 1) {
                statCards[0].textContent = totalFavs;
            }
        }

        // Gestion des boutons de candidature
        const applyButtons = document.querySelectorAll('.btn-primary:not(.filter-actions .btn-primary)');

        if (applyButtons.length > 0) {
            applyButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (button.textContent.includes('Postuler')) {
                        e.preventDefault();

                        const card = this.closest('.internship-card');
                        const jobTitle = card.querySelector('.card-title h3').textContent;
                        const companyName = card.querySelector('.company-name').textContent;

                        showToast(`Préparation de votre candidature pour "${jobTitle}" chez ${companyName}...`);

                        // Simuler une redirection vers la page de candidature
                        setTimeout(() => {
                            window.location.href = '#' + jobTitle.toLowerCase().replace(/ /g, '-');
                        }, 1500);
                    }
                });
            });
        }
    }

    // Appel de l'initialisation quand le document est chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Vérifier si on est sur la page des favoris
        if (document.querySelector('.favorites-container')) {
            initFavoritesPage();
        }
    });