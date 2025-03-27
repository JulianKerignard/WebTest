<?php
// Définir le titre et la page courante
$title = 'Mes candidatures';
$current_page = 'applications';
?>

<div class="page-title">
    <h1>Mes candidatures</h1>
    <p>Suivez l'évolution de vos candidatures aux stages</p>
</div>

<div class="applications-dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-paper-plane"></i>
            </div>
            <div class="stat-info">
                <h3><?= $stats['total'] ?? 0 ?></h3>
                <p>Candidatures envoyées</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon yellow">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <h3><?= $stats['pending'] ?? 0 ?></h3>
                <p>En attente</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-search"></i>
            </div>
            <div class="stat-info">
                <h3><?= $stats['in-review'] ?? 0 ?></h3>
                <p>En cours d'examen</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3><?= $stats['interview'] ?? 0 ?></h3>
                <p>Entretiens</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?= $stats['accepted'] ?? 0 ?></h3>
                <p>Acceptées</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?= $stats['rejected'] ?? 0 ?></h3>
                <p>Non retenues</p>
            </div>
        </div>
    </div>

    <div class="applications-container">
        <div class="applications-header">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">Toutes</button>
                <button class="filter-tab" data-filter="pending">En attente</button>
                <button class="filter-tab" data-filter="in-review">En cours</button>
                <button class="filter-tab" data-filter="interview">Entretiens</button>
                <button class="filter-tab" data-filter="accepted">Acceptées</button>
                <button class="filter-tab" data-filter="rejected">Non retenues</button>
            </div>
            <div class="applications-search">
                <div class="input-with-icon">
                    <i class="fas fa-search"></i>
                    <input type="text" id="applicationSearch" placeholder="Rechercher une candidature...">
                </div>
            </div>
        </div>

        <div class="applications-list">
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Aucune candidature trouvée</h3>
                    <p>Vous n'avez pas encore postulé à des offres de stage. Commencez dès maintenant !</p>
                    <a href="/stages" class="btn btn-primary">Découvrir les stages</a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $application): ?>
                    <div class="application-item" data-status="<?= $application['status'] ?>">
                        <div class="application-company">
                            <div class="company-logo">
                                <?php if (!empty($application['company_logo'])): ?>
                                    <img src="/uploads/company_logos/<?= htmlspecialchars($application['company_logo']) ?>" alt="<?= htmlspecialchars($application['company_name']) ?> Logo">
                                <?php else: ?>
                                    <?= htmlspecialchars($application['company_initials']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="application-info">
                            <h3><?= htmlspecialchars($application['Offer_title']) ?></h3>
                            <p class="company-name"><?= htmlspecialchars($application['company_name']) ?></p>
                            <div class="application-details-sm">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($application['location']) ?></span>
                                <span><i class="fas fa-euro-sign"></i> <?= htmlspecialchars($application['monthly_remuneration']) ?></span>
                                <span><i class="fas fa-clock"></i> <?= htmlspecialchars($application['internship_duration']) ?></span>
                            </div>
                        </div>
                        <div class="application-status">
                            <span class="status-badge <?= $application['status'] ?>"><?= htmlspecialchars($application['status_label']) ?></span>
                            <?php if ($application['status'] === 'interview'): ?>
                                <span class="interview-date"><i class="far fa-calendar-check"></i> <?= htmlspecialchars($application['interview_date_formatted']) ?></span>
                            <?php endif; ?>
                            <span class="application-date"><i class="far fa-clock"></i> <?= htmlspecialchars($application['time_ago']) ?></span>
                        </div>
                        <div class="application-actions">
                            <a href="/applications/<?= $application['id'] ?>" class="btn btn-outline btn-sm">Voir détails</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
            <div class="pagination">
                <a href="<?= $pagination['page'] > 1 ? '/applications?page=' . ($pagination['page'] - 1) : '#' ?>"
                   class="pagination-prev <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i === 1 || $i === $pagination['total_pages'] || abs($i - $pagination['page']) <= 2): ?>
                        <a href="/applications?page=<?= $i ?>"
                           class="pagination-item <?= $pagination['page'] === $i ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php elseif (abs($i - $pagination['page']) === 3): ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <a href="<?= $pagination['page'] < $pagination['total_pages'] ? '/applications?page=' . ($pagination['page'] + 1) : '#' ?>"
                   class="pagination-next <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtrage par statut
        const filterTabs = document.querySelectorAll('.filter-tab');
        const applicationItems = document.querySelectorAll('.application-item');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Mettre à jour les classes actives
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;

                // Filtrer les candidatures
                applicationItems.forEach(item => {
                    if (filter === 'all' || item.dataset.status === filter) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Mettre à jour l'état vide si nécessaire
                updateEmptyState();
            });
        });

        // Recherche
        const searchInput = document.getElementById('applicationSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                applicationItems.forEach(item => {
                    const title = item.querySelector('h3').textContent.toLowerCase();
                    const company = item.querySelector('.company-name').textContent.toLowerCase();

                    if (title.includes(searchTerm) || company.includes(searchTerm)) {
                        // Vérifier également le filtre actif
                        const activeFilter = document.querySelector('.filter-tab.active').dataset.filter;
                        if (activeFilter === 'all' || item.dataset.status === activeFilter) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Mettre à jour l'état vide si nécessaire
                updateEmptyState();
            });
        }

        // Fonction pour mettre à jour l'état vide
        function updateEmptyState() {
            const visibleItems = Array.from(applicationItems).filter(item => item.style.display !== 'none');
            const applicationsContainer = document.querySelector('.applications-list');
            const existingEmptyState = document.querySelector('.empty-state');

            if (visibleItems.length === 0) {
                if (!existingEmptyState) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'empty-state';
                    emptyState.innerHTML = `
                        <div class="empty-icon">
                            <i class="fas fa-filter"></i>
                        </div>
                        <h3>Aucun résultat</h3>
                        <p>Aucune candidature ne correspond à vos critères de recherche.</p>
                        <button class="btn btn-outline reset-filters">Réinitialiser les filtres</button>
                    `;
                    applicationsContainer.appendChild(emptyState);

                    // Ajouter l'événement au bouton de réinitialisation
                    emptyState.querySelector('.reset-filters').addEventListener('click', function() {
                        searchInput.value = '';
                        document.querySelector('.filter-tab[data-filter="all"]').click();
                    });
                }
            } else if (existingEmptyState) {
                applicationsContainer.removeChild(existingEmptyState);
            }
        }
    });
</script>