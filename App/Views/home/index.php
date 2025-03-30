<?php
// Définir le titre et la page courante
$title = 'Trouvez votre stage idéal';
$current_page = 'home';
?>
<link rel="stylesheet" href="/Asset/Css/main.css">
<link rel="stylesheet" href="/Asset/Css/Style.css">
<script src="/Asset/Js/scripts.js"></script>

<section class="hero">
    <div class="container">
        <h1>Trouvez le stage qui lance votre carrière</h1>
        <p>Des milliers d'opportunités de stage dans des entreprises innovantes à travers la France. Démarrez votre parcours professionnel dès aujourd'hui.</p>
        <form action="/stages" method="GET" class="search-bar">
            <input type="text" name="keyword" placeholder="Titre, mot-clé ou entreprise">
            <select name="domain">
                <option value="">Tous les domaines</option>
                <option value="tech">Technologie</option>
                <option value="marketing">Marketing</option>
                <option value="finance">Finance</option>
                <option value="sante">Santé</option>
                <option value="education">Éducation</option>
            </select>
            <select name="location">
                <option value="">Toutes les villes</option>
                <option value="paris">Paris</option>
                <option value="lyon">Lyon</option>
                <option value="marseille">Marseille</option>
                <option value="bordeaux">Bordeaux</option>
                <option value="toulouse">Toulouse</option>
            </select>
            <button type="submit" class="btn btn-accent">Rechercher</button>
        </form>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2 class="section-title">Pourquoi LeBonPlan ?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Recherche avancée</h3>
                <p>Trouvez rapidement le stage parfait grâce à nos filtres de recherche personnalisés par secteur, durée et localisation.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>Entreprises de qualité</h3>
                <p>Accédez à des offres de stage dans des entreprises réputées, startups innovantes et organisations qui valorisent leurs stagiaires.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3>CV optimisé</h3>
                <p>Créez un profil attractif et un CV moderne pour augmenter vos chances d'être remarqué par les recruteurs.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Alertes personnalisées</h3>
                <p>Recevez des notifications en temps réel pour les nouveaux stages correspondant à vos critères et préférences.</p>
            </div>
        </div>
    </div>
</section>

<section class="internships">
    <div class="container">
        <div class="internships-header">
            <h2 class="section-title">Stages populaires</h2>
            <div class="filter-options">
                <button class="filter-button active" data-filter="all">Tous</button>
                <button class="filter-button" data-filter="tech">Tech</button>
                <button class="filter-button" data-filter="marketing">Marketing</button>
                <button class="filter-button" data-filter="finance">Finance</button>
                <button class="filter-button" data-filter="design">Design</button>
            </div>
        </div>
        <div class="internship-grid">
            <?php foreach ($popularInternships as $internship): ?>
                <div class="internship-card" data-category="<?= htmlspecialchars($internship['category']) ?>">
                    <div class="card-header">
                        <div class="company-logo"><?= htmlspecialchars($internship['company_initials']) ?></div>
                        <div class="card-title">
                            <h3><?= htmlspecialchars($internship['Offer_title']) ?></h3>
                            <div class="company-name"><?= htmlspecialchars($internship['company_name']) ?></div>
                        </div>
                        <button class="bookmark-btn" data-id="<?= $internship['ID_Offer'] ?>">
                            <i class="<?= isset($internship['in_wishlist']) && $internship['in_wishlist'] ? 'fas' : 'far' ?> fa-bookmark"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="internship-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="detail-text"><?= htmlspecialchars($internship['location']) ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-euro-sign"></i>
                                <span class="detail-text"><?= htmlspecialchars($internship['monthly_remuneration']) ?>€/mois</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span class="detail-text"><?= htmlspecialchars($internship['internship_duration']) ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span class="detail-text"><?= htmlspecialchars($internship['study_level']) ?></span>
                            </div>
                        </div>
                        <p><?= htmlspecialchars(substr($internship['Description'], 0, 150)) . (strlen($internship['Description']) > 150 ? '...' : '') ?></p>
                        <div class="internship-tags">
                            <?php foreach ($internship['skills'] as $skill): ?>
                                <span class="tag"><?= htmlspecialchars($skill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="posted-date">Publié <?= htmlspecialchars($internship['posted_time']) ?></span>
                        <a href="<?= $user ? '/stages/' . $internship['ID_Offer'] : '/login' ?>" class="btn btn-primary">
                            <?= $user ? 'Voir détails' : 'Postuler' ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($popularInternships)): ?>
                <!-- Afficher des données statiques si aucune donnée n'est disponible -->
                <div class="internship-card">
                    <div class="card-header">
                        <div class="company-logo">AB</div>
                        <div class="card-title">
                            <h3>Développeur Full Stack</h3>
                            <div class="company-name">Acme Branding</div>
                        </div>
                        <button class="bookmark-btn">
                            <i class="far fa-bookmark"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="internship-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="detail-text">Paris, France</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-euro-sign"></i>
                                <span class="detail-text">800-1000€/mois</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span class="detail-text">6 mois</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span class="detail-text">Bac+4/5</span>
                            </div>
                        </div>
                        <p>Stage au sein d'une équipe de développeurs pour participer à la création de nouvelles fonctionnalités sur notre plateforme e-commerce.</p>
                        <div class="internship-tags">
                            <span class="tag">React</span>
                            <span class="tag">Node.js</span>
                            <span class="tag">MongoDB</span>
                            <span class="tag">Agile</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="posted-date">Publié il y a 2 jours</span>
                        <a href="<?= isset($user) ? '/stages/1' : '/login' ?>" class="btn btn-primary">
                            <?= isset($user) ? 'Voir détails' : 'Postuler' ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="view-all-container">
            <a href="/stages" class="btn btn-outline btn-lg">Voir tous les stages <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Vous êtes une entreprise ?</h2>
            <p class="cta-text">Publiez vos offres de stage et trouvez les talents de demain pour renforcer vos équipes et développer votre activité.</p>
            <a href="/contact" class="btn btn-white">Publier une offre de stage</a>
        </div>
    </div>
</section>

<section class="newsletter">
    <div class="container">
        <div class="newsletter-content">
            <h2 class="section-title">Restez informé</h2>
            <p>Inscrivez-vous à notre newsletter pour recevoir les dernières offres de stage et conseils pour votre recherche.</p>
            <form class="newsletter-form" action="/subscribe" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="email" name="email" placeholder="Votre adresse email" required>
                <button type="submit" class="btn btn-primary">S'abonner</button>
            </form>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtrage des offres de stage
        const filterButtons = document.querySelectorAll('.filter-button');
        const internshipCards = document.querySelectorAll('.internship-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Retirer la classe active de tous les boutons
                filterButtons.forEach(btn => btn.classList.remove('active'));

                // Ajouter la classe active au bouton cliqué
                button.classList.add('active');

                // Filtrer les offres
                const filter = button.dataset.filter;

                internshipCards.forEach(card => {
                    if (filter === 'all' || card.dataset.category === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Gestion des boutons de favoris
        const bookmarkButtons = document.querySelectorAll('.bookmark-btn');

        bookmarkButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                <?php if (isset($user)): ?>
                const internshipId = this.dataset.id;
                const icon = this.querySelector('i');

                // Toggle de l'icône visuellement
                if (icon.classList.contains('far')) {
                    icon.classList.replace('far', 'fas');

                    // Ajouter aux favoris via AJAX
                    fetch('/wishlist/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            offer_id: internshipId,
                            csrf_token: '<?= $csrf_token ?>'
                        })
                    });
                } else {
                    icon.classList.replace('fas', 'far');

                    // Retirer des favoris via AJAX
                    fetch('/wishlist/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            offer_id: internshipId,
                            csrf_token: '<?= $csrf_token ?>'
                        })
                    });
                }
                <?php else: ?>
                // Rediriger vers la page de connexion
                window.location.href = '/login';
                <?php endif; ?>
            });
        });
    });
</script>