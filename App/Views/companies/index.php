<?php
// Définir le titre et la page courante
$title = 'Découvrir les entreprises';
$current_page = 'entreprises';
?>

<section class="entreprises-hero">
    <div class="container">
        <div class="hero-content">
            <h1>Découvrez les entreprises qui recrutent</h1>
            <p>Explorez les profils des entreprises proposant des stages dans votre domaine et trouvez votre futur employeur.</p>
            <form class="search-bar simple" action="/companies" method="GET">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="keyword" placeholder="Rechercher une entreprise par nom, secteur ou localisation" value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </form>
        </div>
    </div>
</section>

<section class="entreprises-filter">
    <div class="container">
        <div class="filter-container">
            <form class="filter-options" action="/companies" method="GET">
                <input type="hidden" name="keyword" value="<?= htmlspecialchars($keyword ?? '') ?>">
                <div class="filter-group">
                    <label>Secteur d'activité</label>
                    <select name="sector">
                        <option value="">Tous les secteurs</option>
                        <?php foreach ($sectors ?? [] as $sector): ?>
                            <option value="<?= $sector['value'] ?>" <?= isset($selected_sector) && $selected_sector === $sector['value'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sector['label']) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (empty($sectors)): ?>
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
                        <?php endif; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Localisation</label>
                    <select name="location">
                        <option value="">Toutes les localisations</option>
                        <?php foreach ($locations ?? [] as $location): ?>
                            <option value="<?= $location['value'] ?>" <?= isset($selected_location) && $selected_location === $location['value'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($location['label']) ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (empty($locations)): ?>
                            <option value="paris">Paris</option>
                            <option value="lyon">Lyon</option>
                            <option value="marseille">Marseille</option>
                            <option value="bordeaux">Bordeaux</option>
                            <option value="lille">Lille</option>
                            <option value="toulouse">Toulouse</option>
                            <option value="nantes">Nantes</option>
                            <option value="strasbourg">Strasbourg</option>
                            <option value="montpellier">Montpellier</option>
                            <option value="multiple">Plusieurs localisations</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Taille de l'entreprise</label>
                    <select name="size">
                        <option value="">Toutes tailles</option>
                        <option value="startup" <?= isset($selected_size) && $selected_size === 'startup' ? 'selected' : '' ?>>Startup (1-50)</option>
                        <option value="pme" <?= isset($selected_size) && $selected_size === 'pme' ? 'selected' : '' ?>>PME (51-250)</option>
                        <option value="eti" <?= isset($selected_size) && $selected_size === 'eti' ? 'selected' : '' ?>>ETI (251-5000)</option>
                        <option value="grande" <?= isset($selected_size) && $selected_size === 'grande' ? 'selected' : '' ?>>Grande entreprise (5000+)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Note des stagiaires</label>
                    <select name="rating">
                        <option value="">Toutes les notes</option>
                        <option value="4.5" <?= isset($selected_rating) && $selected_rating === '4.5' ? 'selected' : '' ?>>4.5★ et plus</option>
                        <option value="4" <?= isset($selected_rating) && $selected_rating === '4' ? 'selected' : '' ?>>4★ et plus</option>
                        <option value="3.5" <?= isset($selected_rating) && $selected_rating === '3.5' ? 'selected' : '' ?>>3.5★ et plus</option>
                        <option value="3" <?= isset($selected_rating) && $selected_rating === '3' ? 'selected' : '' ?>>3★ et plus</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Appliquer les filtres</button>
            </form>
            <div class="filter-actions">
                <div class="sort-options">
                    <span>Trier par :</span>
                    <select id="sortSelect" name="sort">
                        <option value="pertinence" <?= isset($sort) && $sort === 'pertinence' ? 'selected' : '' ?>>Pertinence</option>
                        <option value="note" <?= isset($sort) && $sort === 'note' ? 'selected' : '' ?>>Meilleures notes</option>
                        <option value="offres" <?= isset($sort) && $sort === 'offres' ? 'selected' : '' ?>>Nombre d'offres</option>
                        <option value="az" <?= isset($sort) && $sort === 'az' ? 'selected' : '' ?>>Ordre alphabétique</option>
                    </select>
                </div>
                <div class="view-options">
                    <button class="view-btn active" data-view="grid">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button class="view-btn" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="entreprises-results">
    <div class="container">
        <div class="results-info">
            <p><strong><?= number_format($total_companies ?? 128) ?> entreprises</strong> correspondent à votre recherche</p>
        </div>

        <div class="entreprises-grid">
            <?php if (!empty($companies)): ?>
                <?php foreach ($companies as $company): ?>
                    <div class="entreprise-card">
                        <div class="card-header">
                            <div class="entreprise-logo">
                                <?php if (!empty($company['Logo'])): ?>
                                    <img src="/uploads/company_logos/<?= htmlspecialchars($company['Logo']) ?>" alt="<?= htmlspecialchars($company['Name']) ?> Logo">
                                <?php else: ?>
                                    <?= htmlspecialchars(substr($company['Name'], 0, 2)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="favorite-btn" data-id="<?= $company['ID_Company'] ?>">
                                <i class="<?= isset($company['is_favorite']) && $company['is_favorite'] ? 'fas' : 'far' ?> fa-heart"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <h3 class="entreprise-name"><?= htmlspecialchars($company['Name']) ?></h3>
                            <div class="entreprise-rating">
                                <div class="stars">
                                    <?php
                                    $rating = $company['avg_rating'] ?? 0;
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= floor($rating)) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i - 0.5 <= $rating) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="rating-score"><?= number_format($company['avg_rating'] ?? 0, 1) ?></span>
                                <span class="rating-count">(<?= $company['reviews_count'] ?? 0 ?> avis)</span>
                            </div>
                            <div class="entreprise-info">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($company['Adresse'] ?? 'Non spécifié') ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span><?= htmlspecialchars($company['sector_name'] ?? 'Non spécifié') ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-users"></i>
                                    <span><?= htmlspecialchars($company['Size'] ?? 'Non spécifié') ?></span>
                                </div>
                            </div>
                            <p class="entreprise-description">
                                <?= htmlspecialchars(substr($company['Description'] ?? '', 0, 150)) ?>
                                <?= strlen($company['Description'] ?? '') > 150 ? '...' : '' ?>
                            </p>
                            <div class="entreprise-tags">
                                <?php if (!empty($company['tags'])): ?>
                                    <?php foreach ($company['tags'] as $tag): ?>
                                        <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="stage-count">
                                <span><strong><?= $company['offers_count'] ?? 0 ?> stages</strong> disponibles</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="/companies/<?= $company['ID_Company'] ?>" class="btn btn-outline btn-sm">Voir le profil</a>
                            <a href="/stages?company_id=<?= $company['ID_Company'] ?>" class="btn btn-primary btn-sm">Voir les offres</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Affichage par défaut si aucune entreprise -->
                <div class="entreprise-card">
                    <div class="card-header">
                        <div class="entreprise-logo">
                            <img src="/img/entreprises/TechDream.png" alt="TechDream Logo" onerror="this.innerText='TD'">
                        </div>
                        <div class="favorite-btn">
                            <i class="far fa-heart"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <h3 class="entreprise-name">TechDream</h3>
                        <div class="entreprise-rating">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="rating-score">4.5</span>
                            <span class="rating-count">(42 avis)</span>
                        </div>
                        <div class="entreprise-info">
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Lyon, France</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-briefcase"></i>
                                <span>Technologie & IT</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>50-200 employés</span>
                            </div>
                        </div>
                        <p class="entreprise-description">Startup innovante spécialisée dans le développement d'applications mobiles et de solutions cloud pour les entreprises.</p>
                        <div class="entreprise-tags">
                            <span class="tag">Applications mobiles</span>
                            <span class="tag">Cloud</span>
                            <span class="tag">IA</span>
                            <span class="tag">UX/UI</span>
                        </div>
                        <div class="stage-count">
                            <span><strong>8 stages</strong> disponibles</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-outline btn-sm">Voir le profil</a>
                        <a href="#" class="btn btn-primary btn-sm">Voir les offres</a>
                    </div>
                </div>
                <!-- Autres entreprises d'exemple (omises pour brièveté) -->
            <?php endif; ?>
        </div>

        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="pagination">
                <a href="<?= $pagination['current_page'] > 1 ? '/companies?' . http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) : '#' ?>"
                   class="pagination-prev <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i <= 3 || $i > $pagination['total_pages'] - 3 || abs($i - $pagination['current_page']) < 2): ?>
                        <a href="/companies?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                           class="pagination-item <?= $pagination['current_page'] == $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php elseif (abs($i - $pagination['current_page']) == 2): ?>
                        <span class="pagination-more">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <a href="<?= $pagination['current_page'] < $pagination['total_pages'] ? '/companies?' . http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) : '#' ?>"
                   class="pagination-next <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="pagination">
                <a href="#" class="pagination-prev disabled"><i class="fas fa-chevron-left"></i></a>
                <a href="#" class="pagination-item active">1</a>
                <a href="#" class="pagination-item">2</a>
                <a href="#" class="pagination-item">3</a>
                <a href="#" class="pagination-item">4</a>
                <span class="pagination-more">...</span>
                <a href="#" class="pagination-item">16</a>
                <a href="#" class="pagination-next"><i class="fas fa-chevron-right"></i></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="entreprise-bottom-cta">
    <div class="container">
        <div class="cta-cards">
            <div class="cta-card">
                <div class="cta-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>Vous êtes une entreprise ?</h3>
                <p>Publiez gratuitement vos offres de stage et trouvez les talents de demain pour rejoindre vos équipes.</p>
                <a href="/register?type=company" class="btn btn-primary">Publier une offre</a>
            </div>
            <div class="cta-card">
                <div class="cta-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Restez informé</h3>
                <p>Créez des alertes pour recevoir les nouvelles offres de stage des entreprises qui vous intéressent.</p>
                <a href="/alerts" class="btn btn-outline">Créer une alerte</a>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Options d'affichage (grille/liste)
        const viewButtons = document.querySelectorAll('.view-btn');
        const companiesGrid = document.querySelector('.entreprises-grid');

        if(viewButtons.length && companiesGrid) {
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const view = this.dataset.view;
                    companiesGrid.className = 'entreprises-' + view;
                });
            });
        }

        // Gestion du tri
        const sortSelect = document.getElementById('sortSelect');
        if(sortSelect) {
            sortSelect.addEventListener('change', function() {
                const url = new URL(window.location);
                url.searchParams.set('sort', this.value);
                window.location = url;
            });
        }

        // Gestion des favoris
        const favoriteButtons = document.querySelectorAll('.favorite-btn');

        favoriteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                <?php if (isset($user)): ?>
                const companyId = this.dataset.id;
                const icon = this.querySelector('i');

                if(icon.classList.contains('far')) {
                    icon.classList.replace('far', 'fas');

                    // Ajouter aux favoris
                    fetch('/companies/favorite/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            company_id: companyId,
                            csrf_token: '<?= $csrf_token ?? '' ?>'
                        })
                    });
                } else {
                    icon.classList.replace('fas', 'far');

                    // Retirer des favoris
                    fetch('/companies/favorite/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            company_id: companyId,
                            csrf_token: '<?= $csrf_token ?? '' ?>'
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