<?php
// Définir le titre et la page courante
$title = 'Tableau de bord étudiant';
$current_page = 'dashboard';
?>

<link rel="stylesheet" href="/Asset/Css/main.css">
<link rel="stylesheet" href="/Asset/Css/Style.css">
<script src="/Asset/Js/scripts.js"></script>

<div class="page-title">
    <h1>Tableau de bord</h1>
    <p>Bienvenue <?= htmlspecialchars($user['username']) ?>, voici l'état de vos candidatures</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-paper-plane"></i>
        </div>
        <div class="stat-info">
            <h3><?= $stats['applications_sent'] ?? 8 ?></h3>
            <p>Candidatures envoyées</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-info">
            <h3><?= $stats['applications_in_review'] ?? 3 ?></h3>
            <p>En cours d'examen</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3><?= $stats['interviews_scheduled'] ?? 2 ?></h3>
            <p>Entretiens prévus</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-info">
            <h3><?= $stats['wishlist_count'] ?? 5 ?></h3>
            <p>Stages favoris</p>
        </div>
    </div>
</div>

<div class="content-row">
    <div class="content-column">
        <div class="dashboard-card">
            <div class="card-header-alt">
                <h2>Candidatures récentes</h2>
                <a href="/student/applications" class="view-all">Voir tout <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="applications-list">
                <?php if (!empty($recentApplications)): ?>
                    <?php foreach ($recentApplications as $application): ?>
                        <div class="application-item">
                            <div class="company-logo-sm"><?= htmlspecialchars($application['company_initials']) ?></div>
                            <div class="application-details">
                                <h3><?= htmlspecialchars($application['Offer_title']) ?></h3>
                                <p><?= htmlspecialchars($application['company_name']) ?> - <?= htmlspecialchars($application['location']) ?></p>
                            </div>
                            <div class="application-status <?= htmlspecialchars($application['status']) ?>">
                                <span><?= htmlspecialchars($application['status_label']) ?></span>
                            </div>
                            <div class="application-date">
                                <?php if ($application['status'] === 'interview'): ?>
                                    <i class="fas fa-calendar"></i> <?= htmlspecialchars($application['interview_date']) ?>
                                <?php else: ?>
                                    <i class="far fa-clock"></i> <?= htmlspecialchars($application['time_ago']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Données statiques si pas d'applications -->
                    <div class="application-item">
                        <div class="company-logo-sm">TD</div>
                        <div class="application-details">
                            <h3>Stage Marketing Digital</h3>
                            <p>TechDream - Lyon</p>
                        </div>
                        <div class="application-status pending">
                            <span>En attente</span>
                        </div>
                        <div class="application-date">
                            <i class="far fa-clock"></i> Il y a 2 jours
                        </div>
                    </div>
                    <div class="application-item">
                        <div class="company-logo-sm">AB</div>
                        <div class="application-details">
                            <h3>Développeur Full Stack</h3>
                            <p>Acme Branding - Paris</p>
                        </div>
                        <div class="application-status in-review">
                            <span>En cours d'examen</span>
                        </div>
                        <div class="application-date">
                            <i class="far fa-clock"></i> Il y a 5 jours
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="content-column">
        <div class="dashboard-card">
            <div class="card-header-alt">
                <h2>Événements à venir</h2>
                <a href="/student/events" class="view-all">Voir tout <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="events-list">
                <?php if (!empty($upcomingEvents)): ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="day"><?= date('d', strtotime($event['date'])) ?></span>
                                <span class="month"><?= date('M', strtotime($event['date'])) ?></span>
                            </div>
                            <div class="event-details">
                                <h3><?= htmlspecialchars($event['title']) ?></h3>
                                <p><i class="fas fa-clock"></i> <?= htmlspecialchars($event['time']) ?></p>
                                <p><i class="fas fa-<?= $event['is_remote'] ? 'video' : 'map-marker-alt' ?>"></i> <?= htmlspecialchars($event['location']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Données statiques si pas d'événements -->
                    <div class="event-item">
                        <div class="event-date">
                            <span class="day">15</span>
                            <span class="month">Mars</span>
                        </div>
                        <div class="event-details">
                            <h3>Entretien - Green Solutions</h3>
                            <p><i class="fas fa-clock"></i> 14:00 - 15:00</p>
                            <p><i class="fas fa-video"></i> Visioconférence</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header-alt">
                <h2>Compléter votre profil</h2>
            </div>
            <div class="profile-progress">
                <div class="progress-bar">
                    <div class="progress" style="width: <?= $profileCompletion ?? 75 ?>%"></div>
                </div>
                <div class="progress-text">
                    <span><?= $profileCompletion ?? 75 ?>% complété</span>
                </div>
            </div>
            <div class="profile-tasks">
                <?php foreach ($profileTasks as $task): ?>
                    <div class="task-item <?= $task['completed'] ? 'completed' : '' ?>">
                        <i class="fas <?= $task['completed'] ? 'fa-check-circle' : 'fa-circle' ?>"></i>
                        <span><?= htmlspecialchars($task['name']) ?></span>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($profileTasks)): ?>
                    <!-- Données statiques si pas de tâches -->
                    <div class="task-item completed">
                        <i class="fas fa-check-circle"></i>
                        <span>Informations personnelles</span>
                    </div>
                    <div class="task-item completed">
                        <i class="fas fa-check-circle"></i>
                        <span>Formation académique</span>
                    </div>
                    <div class="task-item completed">
                        <i class="fas fa-check-circle"></i>
                        <span>Compétences</span>
                    </div>
                    <div class="task-item">
                        <i class="far fa-circle"></i>
                        <span>Ajouter des expériences professionnelles</span>
                    </div>
                    <div class="task-item">
                        <i class="far fa-circle"></i>
                        <span>Télécharger votre CV</span>
                    </div>
                <?php endif; ?>
            </div>
            <a href="/student/profile" class="btn btn-outline btn-sm">Compléter mon profil</a>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header-alt">
        <h2>Stages recommandés pour vous</h2>
        <a href="/stages" class="view-all">Voir tout <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="recommended-internships">
        <?php if (!empty($recommendedInternships)): ?>
            <?php foreach ($recommendedInternships as $internship): ?>
                <div class="internship-card-sm">
                    <div class="card-header">
                        <div class="company-logo-sm"><?= htmlspecialchars($internship['company_initials']) ?></div>
                        <div class="card-title">
                            <h3><?= htmlspecialchars($internship['Offer_title']) ?></h3>
                            <div class="company-name"><?= htmlspecialchars($internship['company_name']) ?></div>
                        </div>
                        <button class="bookmark-btn" data-id="<?= $internship['ID_Offer'] ?>">
                            <i class="<?= isset($internship['in_wishlist']) && $internship['in_wishlist'] ? 'fas' : 'far' ?> fa-bookmark"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="internship-details-sm">
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
                        </div>
                        <div class="internship-tags-sm">
                            <?php foreach (array_slice($internship['skills'], 0, 3) as $skill): ?>
                                <span class="tag"><?= htmlspecialchars($skill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="match-score">
                            <i class="fas fa-bolt"></i> <?= htmlspecialchars($internship['match_score']) ?>% de correspondance
                        </span>
                        <a href="/stages/<?= $internship['ID_Offer'] ?>" class="btn btn-primary btn-sm">Voir détails</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Données statiques si pas de stages recommandés -->
            <div class="internship-card-sm">
                <div class="card-header">
                    <div class="company-logo-sm">NX</div>
                    <div class="card-title">
                        <h3>Développeur Backend</h3>
                        <div class="company-name">NextCloud</div>
                    </div>
                    <button class="bookmark-btn">
                        <i class="far fa-bookmark"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="internship-details-sm">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="detail-text">Paris, France</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-euro-sign"></i>
                            <span class="detail-text">900€/mois</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span class="detail-text">6 mois</span>
                        </div>
                    </div>
                    <div class="internship-tags-sm">
                        <span class="tag">Python</span>
                        <span class="tag">Django</span>
                        <span class="tag">API</span>
                    </div>
                </div>
                <div class="card-footer">
                    <span class="match-score">
                        <i class="fas fa-bolt"></i> 92% de correspondance
                    </span>
                    <a href="/stages/1" class="btn btn-primary btn-sm">Voir détails</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des boutons de favoris
        const bookmarkButtons = document.querySelectorAll('.bookmark-btn');

        bookmarkButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

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
            });
        });
    });
</script>