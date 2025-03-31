<?php
// Définir le titre et la page courante
$title = 'Trouvez votre stage idéal';
$current_page = 'home';

// Initialisation des variables manquantes pour éviter les erreurs
$popularInternships = $popularInternships ?? [];
$user = $user ?? null;
$csrf_token = $csrf_token ?? '';

// Génération d'un token CSRF si nécessaire
if (empty($csrf_token) && class_exists('\\App\\Helpers\\SecurityHelper')) {
    $csrf_token = \App\Helpers\SecurityHelper::generateCSRFToken();
}
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
        <h2 class="section-title">Stages populaires</h2>
        <p class="section-description">Découvrez les stages les plus recherchés du moment</p>
        <div class="internship-grid">
            <?php if (!empty($popularInternships)): ?>
                <?php foreach ($popularInternships as $internship): ?>
                    <div class="internship-card">
                        <div class="card-header">
                            <h3><?= htmlspecialchars($internship['Offer_title']) ?></h3>
                            <span class="company-name"><?= htmlspecialchars($internship['company_name']) ?></span>
                            <span class="location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($internship['location']) ?></span>
                        </div>
                        <div class="card-body">
                            <div class="internship-details">
                                <div class="detail">
                                    <span class="label">Durée</span>
                                    <span class="value"><?= htmlspecialchars($internship['internship_duration']) ?></span>
                                </div>
                                <div class="detail">
                                    <span class="label">Rémunération</span>
                                    <span class="value"><?= htmlspecialchars($internship['monthly_remuneration']) ?> €/mois</span>
                                </div>
                            </div>
                            <p><?= htmlspecialchars(substr($internship['Description'], 0, 150)) ?>...</p>
                            <div class="internship-tags">
                                <?php if (!empty($internship['skills'])): ?>
                                    <?php foreach ($internship['skills'] as $skill): ?>
                                        <span class="tag"><?= htmlspecialchars($skill) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="posted-date">
                                Publié le <?= date('d/m/Y', strtotime($internship['Date_of_publication'])) ?>
                            </span>
                            <a href="<?= isset($user) ? '/stages/' . $internship['ID_Offer'] : '/login' ?>" class="btn btn-primary">
                                <?= isset($user) ? 'Voir détails' : 'Postuler' ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Afficher message si aucun stage disponible -->
                <div class="no-internships">
                    <p>Aucun stage disponible pour le moment.</p>
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
                            'X-CSRF-Token': '<?= $csrf_token ?>'
                        },
                        body: JSON.stringify({
                            offer_id: internshipId
                        })
                    });
                } else {
                    icon.classList.replace('fas', 'far');

                    // Retirer des favoris via AJAX
                    fetch('/wishlist/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?= $csrf_token ?>'
                        },
                        body: JSON.stringify({
                            offer_id: internshipId
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