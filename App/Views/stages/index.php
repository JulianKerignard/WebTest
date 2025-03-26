<?php
// Définir le titre et la page courante
$title = 'Recherche de Stages';
$current_page = 'stages';
?>

<section class="search-section">
    <div class="container">
        <div class="search-container">
            <form class="search-bar advanced" action="/stages" method="GET">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="keyword" placeholder="Titre, mot-clé ou entreprise" value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
                <div class="search-filters">
                    <div class="filter-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <select name="location">
                            <option value="">Toutes les villes</option>
                            <?php foreach ($locations ?? [] as $loc): ?>
                                <option value="<?= $loc['value'] ?>" <?= isset($location) && $location === $loc['value'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc['label']) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (empty($locations)): ?>
                                <option value="paris">Paris</option>
                                <option value="lyon">Lyon</option>
                                <option value="marseille">Marseille</option>
                                <option value="bordeaux">Bordeaux</option>
                                <option value="toulouse">Toulouse</option>
                                <option value="nantes">Nantes</option>
                                <option value="strasbourg">Strasbourg</option>
                                <option value="lille">Lille</option>
                                <option value="montpellier">Montpellier</option>
                                <option value="remote">Télétravail</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <i class="fas fa-th-large"></i>
                        <select name="domain">
                            <option value="">Tous les domaines</option>
                            <?php foreach ($domains ?? [] as $dom): ?>
                                <option value="<?= $dom['value'] ?>" <?= isset($domain) && $domain === $dom['value'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dom['label']) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (empty($domains)): ?>
                                <option value="tech">Informatique & Tech</option>
                                <option value="marketing">Marketing & Communication</option>
                                <option value="finance">Finance & Comptabilité</option>
                                <option value="sante">Santé</option>
                                <option value="education">Éducation</option>
                                <option value="rh">Ressources Humaines</option>
                                <option value="design">Design & Créativité</option>
                                <option value="ingenierie">Ingénierie</option>
                                <option value="vente">Vente & Commerce</option>
                                <option value="juridique">Juridique</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <i class="fas fa-euro-sign"></i>
                        <select name="remuneration">
                            <option value="">Rémunération</option>
                            <option value="600" <?= isset($remuneration) && $remuneration == 600 ? 'selected' : '' ?>>600€ min.</option>
                            <option value="700" <?= isset($remuneration) && $remuneration == 700 ? 'selected' : '' ?>>700€ min.</option>
                            <option value="800" <?= isset($remuneration) && $remuneration == 800 ? 'selected' : '' ?>>800€ min.</option>
                            <option value="900" <?= isset($remuneration) && $remuneration == 900 ? 'selected' : '' ?>>900€ min.</option>
                            <option value="1000" <?= isset($remuneration) && $remuneration == 1000 ? 'selected' : '' ?>>1000€ min.</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <i class="fas fa-clock"></i>
                        <select name="duration">
                            <option value="">Durée</option>
                            <option value="2" <?= isset($duration) && $duration == 2 ? 'selected' : '' ?>>2 mois</option>
                            <option value="3" <?= isset($duration) && $duration == 3 ? 'selected' : '' ?>>3 mois</option>
                            <option value="4" <?= isset($duration) && $duration == 4 ? 'selected' : '' ?>>4 mois</option>
                            <option value="5" <?= isset($duration) && $duration == 5 ? 'selected' : '' ?>>5 mois</option>
                            <option value="6" <?= isset($duration) && $duration == 6 ? 'selected' : '' ?>>6 mois et +</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </form>
            <div class="advanced-filters">
                <button class="btn-filter-toggle" id="filterToggle">
                    <i class="fas fa-sliders-h"></i> Filtres avancés
                </button>
                <div class="sort-options">
                    <span>Trier par :</span>
                    <select id="sortSelect" name="sort">
                        <option value="recent" <?= isset($sort) && $sort === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                        <option value="salary" <?= isset($sort) && $sort === 'salary' ? 'selected' : '' ?>>Mieux rémunérés</option>
                        <option value="relevant" <?= isset($sort) && $sort === 'relevant' ? 'selected' : '' ?>>Plus pertinents</option>
                        <option value="duration" <?= isset($sort) && $sort === 'duration' ? 'selected' : '' ?>>Durée</option>
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
            <?php if (!empty($active_filters)): ?>
                <div class="filter-tags-container">
                    <div class="filter-tags">
                        <?php foreach ($active_filters as $filter): ?>
                            <span class="filter-tag">
                        <?= htmlspecialchars($filter['label']) ?>
                        <button class="remove-tag" data-filter="<?= $filter['name'] ?>"><i class="fas fa-times"></i></button>
                    </span>
                        <?php endforeach; ?>
                        <button class="clear-filters">Effacer tous les filtres</button>
                    </div>
                </div>
            <?php endif; ?>
            <div class="search-results-info">
                <p><strong><?= number_format($total_results ?? 248) ?> stages</strong> correspondent à votre recherche</p>
            </div>
        </div>
    </div>
</section>

<section class="filters-panel" id="filtersPanel">
    <div class="container">
        <div class="filters-content">
            <div class="filters-column">
                <div class="filter-group-title">
                    <h3>Type de stage</h3>
                </div>
                <div class="filter-options">
                    <div class="filter-checkbox">
                        <input type="checkbox" id="conventionne" name="type[]" value="conventionne" <?= isset($types) && in_array('conventionne', $types) ? 'checked' : '' ?>>
                        <label for="conventionne">Stage conventionné</label>
                    </div>
                    <div class="filter-checkbox">
                        <input type="checkbox" id="alternance" name="type[]" value="alternance" <?= isset($types) && in_array('alternance', $types) ? 'checked' : '' ?>>
                        <label for="alternance">Alternance</label>
                    </div>
                    <div class="filter-checkbox">
                        <input type="checkbox" id="international" name="type[]" value="international" <?= isset($types) && in_array('international', $types) ? 'checked' : '' ?>>
                        <label for="international">International</label>
                    </div>
                    <div class="filter-checkbox">
                        <input type="checkbox" id="teletravail" name="type[]" value="teletravail" <?= isset($types) && in_array('teletravail', $types) ? 'checked' : '' ?>>
                        <label for="teletravail">Télétravail</label>
                    </div>
                </div>
            </div>
            <div class="filters-column">
                <div class="filter-group-title">
                    <h3>Compétences</h3>
                </div>
                <div class="filter-options skills-filter">
                    <?php foreach ($skills ?? [] as $skill): ?>
                        <div class="skill-tag <?= isset($selected_skills) && in_array($skill['id'], $selected_skills) ? 'active' : '' ?>"
                             data-skill-id="<?= $skill['id'] ?>"><?= htmlspecialchars($skill['name']) ?></div>
                    <?php endforeach; ?>
                    <?php if (empty($skills)): ?>
                        <div class="skill-tag">JavaScript</div>
                        <div class="skill-tag">Python</div>
                        <div class="skill-tag">Marketing Digital</div>
                        <div class="skill-tag">Excel</div>
                        <div class="skill-tag">Design UX/UI</div>
                        <div class="skill-tag">React</div>
                        <div class="skill-tag">Communication</div>
                        <div class="skill-tag">Photoshop</div>
                        <div class="skill-tag">SEO</div>
                        <div class="skill-tag">NodeJS</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="filters-column">
                <div class="filter-group-title">
                    <h3>Niveau d'études</h3>
                </div>
                <div class="filter-options">
                    <?php foreach ($education_levels ?? [] as $level): ?>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="<?= $level['id'] ?>" name="education[]" value="<?= $level['id'] ?>"
                                <?= isset($selected_levels) && in_array($level['id'], $selected_levels) ? 'checked' : '' ?>>
                            <label for="<?= $level['id'] ?>"><?= htmlspecialchars($level['name']) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($education_levels)): ?>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="bac" name="education[]" value="bac">
                            <label for="bac">Bac</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="bac2" name="education[]" value="bac2">
                            <label for="bac2">Bac+2</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="bac3" name="education[]" value="bac3">
                            <label for="bac3">Bac+3</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="bac4" name="education[]" value="bac4">
                            <label for="bac4">Bac+4</label>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="bac5" name="education[]" value="bac5">
                            <label for="bac5">Bac+5 et plus</label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="filters-column">
                <div class="filter-group-title">
                    <h3>Date de publication</h3>
                </div>
                <div class="filter-options">
                    <div class="filter-checkbox">
                        <input type="radio" name="date" id="aujourdhui" value="today" <?= isset($date) && $date === 'today' ? 'checked' : '' ?>>
                        <label for="aujourdhui">Aujourd'hui</label>
                    </div>
                    <div class="filter-checkbox">
                        <input type="radio" name="date" id="3jours" value="3days" <?= isset($date) && $date === '3days' ? 'checked' : '' ?>>
                        <label for="3jours">3 derniers jours</label>
                    </div>
                    <div class="filter-checkbox">
                        <input type="radio" name="date" id="semaine" value="week" <?= isset($date) && $date === 'week' ? 'checked' : '' ?>>
                        <label for="semaine">7 derniers jours</label>
                    </div>
                    <div class="filter-checkbox">
                        <input type="radio" name="date" id="mois" value="month" <?= isset($date) && $date === 'month' ? 'checked' : '' ?>>
                        <label for="mois">30 derniers jours</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="filters-actions">
            <button class="btn btn-outline" id="resetFilters">Réinitialiser</button>
            <button class="btn btn-primary" id="applyFilters">Appliquer les filtres</button>
        </div>
    </div>
</section>

<section class="internships-results">
    <div class="container">
        <div class="internship-grid">
            <?php if (!empty($internships)): ?>
                <?php foreach ($internships as $internship): ?>
                    <div class="internship-card">
                        <div class="card-header">
                            <div class="company-logo"><?= htmlspecialchars($internship['company_logo'] ?? substr($internship['company_name'], 0, 2)) ?></div>
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
                                    <span class="detail-text"><?= htmlspecialchars($internship['monthly_remuneration']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span class="detail-text"><?= htmlspecialchars($internship['internship_duration']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span class="detail-text"><?= htmlspecialchars($internship['study_level'] ?? '') ?></span>
                                </div>
                            </div>
                            <p><?= htmlspecialchars(substr($internship['Description'], 0, 150)) ?><?= strlen($internship['Description']) > 150 ? '...' : '' ?></p>
                            <div class="internship-tags">
                                <?php foreach ($internship['skills'] ?? [] as $skill): ?>
                                    <span class="tag"><?= htmlspecialchars($skill) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span class="posted-date">Publié <?= htmlspecialchars($internship['published_date']) ?></span>
                            <a href="/stages/<?= $internship['ID_Offer'] ?>" class="btn btn-primary">
                                <?= isset($user) ? 'Voir détails' : 'Postuler' ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Affichage par défaut si pas de données -->
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
                        <a href="/login" class="btn btn-primary">Postuler</a>
                    </div>
                </div>
                <!-- Répétition des autres stages ici (supprimé pour brevité) -->
            <?php endif; ?>
        </div>

        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="pagination">
                <a href="<?= $pagination['current_page'] > 1 ? '/stages?' . http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) : '#' ?>"
                   class="pagination-prev <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i <= 3 || $i > $pagination['total_pages'] - 3 || abs($i - $pagination['current_page']) < 2): ?>
                        <a href="/stages?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                           class="pagination-item <?= $pagination['current_page'] == $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php elseif (abs($i - $pagination['current_page']) == 2): ?>
                        <span class="pagination-more">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <a href="<?= $pagination['current_page'] < $pagination['total_pages'] ? '/stages?' . http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) : '#' ?>"
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
                <a href="#" class="pagination-item">12</a>
                <a href="#" class="pagination-next"><i class="fas fa-chevron-right"></i></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="newsletter search-newsletter">
    <div class="container">
        <div class="newsletter-content">
            <h2 class="section-title">Recevez les offres qui vous correspondent</h2>
            <p>Créez des alertes personnalisées et recevez par email les dernières offres de stage selon vos critères.</p>
            <form class="newsletter-form" action="/alerts/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <input type="email" name="email" placeholder="Votre adresse email" required>
                <button type="submit" class="btn btn-primary">Créer une alerte</button>
            </form>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtres avancés
        const filterToggle = document.getElementById('filterToggle');
        const filtersPanel = document.getElementById('filtersPanel');

        if(filterToggle && filtersPanel) {
            filterToggle.addEventListener('click', function() {
                filtersPanel.classList.toggle('active');
                this.classList.toggle('active');
            });
        }

        // Options d'affichage (grille/liste)
        const viewButtons = document.querySelectorAll('.view-btn');
        const internshipGrid = document.querySelector('.internship-grid');

        if(viewButtons.length && internshipGrid) {
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const view = this.dataset.view;
                    internshipGrid.className = 'internship-' + view;
                });
            });
        }

        // Gestion des favoris
        const bookmarkButtons = document.querySelectorAll('.bookmark-btn');

        bookmarkButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                <?php if (isset($user)): ?>
                const internshipId = this.dataset.id;
                const icon = this.querySelector('i');

                if(icon.classList.contains('far')) {
                    icon.classList.replace('far', 'fas');

                    // Ajouter aux favoris
                    fetch('/wishlist/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            offer_id: internshipId,
                            csrf_token: '<?= $csrf_token ?? '' ?>'
                        })
                    });
                } else {
                    icon.classList.replace('fas', 'far');

                    // Retirer des favoris
                    fetch('/wishlist/remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            offer_id: internshipId,
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

        // Filtrage par compétences
        const skillTags = document.querySelectorAll('.skill-tag');
        const hiddenSkillsInput = document.createElement('input');
        hiddenSkillsInput.type = 'hidden';
        hiddenSkillsInput.name = 'skills';
        document.querySelector('form.search-bar').appendChild(hiddenSkillsInput);

        skillTags.forEach(tag => {
            tag.addEventListener('click', function() {
                this.classList.toggle('active');
                updateSelectedSkills();
            });
        });

        function updateSelectedSkills() {
            const selectedSkills = Array.from(document.querySelectorAll('.skill-tag.active')).map(tag => tag.dataset.skillId);
            hiddenSkillsInput.value = selectedSkills.join(',');
        }

        // Réinitialisation des filtres
        const resetButton = document.getElementById('resetFilters');
        if(resetButton) {
            resetButton.addEventListener('click', function() {
                // Réinitialiser tous les filtres
                document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => checkbox.checked = false);
                document.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
                document.querySelectorAll('.skill-tag').forEach(tag => tag.classList.remove('active'));
                updateSelectedSkills();
            });
        }

        // Application des filtres
        const applyButton = document.getElementById('applyFilters');
        if(applyButton) {
            applyButton.addEventListener('click', function() {
                document.querySelector('form.search-bar').submit();
            });
        }

        // Suppression de filtres individuels
        const removeTagButtons = document.querySelectorAll('.remove-tag');
        removeTagButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                const input = document.querySelector(`[name="${filter}"]`);
                if(input) {
                    if(input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                }
                document.querySelector('form.search-bar').submit();
            });
        });

        // Suppression de tous les filtres
        const clearFiltersButton = document.querySelector('.clear-filters');
        if(clearFiltersButton) {
            clearFiltersButton.addEventListener('click', function() {
                window.location.href = '/stages';
            });
        }
    });
</script>