<?php
// Définir le titre et la page courante
$title = 'Détails de la candidature';
$current_page = 'applications';
?>

<div class="page-title">
    <h1>Détails de la candidature</h1>
    <p>Suivez l'évolution de votre candidature pour le stage <strong><?= htmlspecialchars($application['Offer_title']) ?></strong></p>
</div>

<div class="application-detail-container">
    <div class="application-header">
        <div class="company-logo">
            <?php if (!empty($application['company_logo'])): ?>
                <img src="/uploads/company_logos/<?= htmlspecialchars($application['company_logo']) ?>" alt="<?= htmlspecialchars($application['company_name']) ?> Logo">
            <?php else: ?>
                <?= htmlspecialchars(substr($application['company_name'], 0, 2)) ?>
            <?php endif; ?>
        </div>
        <div class="application-title">
            <h2><?= htmlspecialchars($application['Offer_title']) ?></h2>
            <p class="company-name"><?= htmlspecialchars($application['company_name']) ?></p>
            <p class="application-meta">
                <span class="application-date"><i class="far fa-calendar"></i> Postuler le <?= date('d/m/Y', strtotime($application['created_at'])) ?></span>
                <span class="application-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($application['location']) ?></span>
            </p>
        </div>
        <div class="application-status <?= $application['status'] ?>">
            <span class="status-badge"><?= htmlspecialchars($application['status_label']) ?></span>
            <?php if ($application['status'] === 'interview'): ?>
                <p class="interview-date"><i class="far fa-calendar-check"></i> Entretien le <?= htmlspecialchars($application['interview_date_formatted']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="application-content">
        <div class="application-column">
            <div class="application-card">
                <div class="card-header-alt">
                    <h3>Statut de la candidature</h3>
                </div>
                <div class="application-timeline">
                    <?php foreach ($application['status_history'] as $history): ?>
                        <div class="timeline-item">
                            <div class="timeline-indicator <?= $history['status'] ?>"></div>
                            <div class="timeline-content">
                                <h4><?= htmlspecialchars($history['status_label']) ?></h4>
                                <p class="timeline-date"><?= htmlspecialchars($history['time_ago']) ?></p>
                                <?php if (!empty($history['comment'])): ?>
                                    <p class="timeline-comment"><?= htmlspecialchars($history['comment']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="application-card">
                <div class="card-header-alt">
                    <h3>Lettre de motivation</h3>
                </div>
                <div class="motivation-letter">
                    <div class="letter-content">
                        <?= nl2br(htmlspecialchars($application['cover_letter'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="application-column">
            <div class="application-card">
                <div class="card-header-alt">
                    <h3>Détails de l'offre</h3>
                </div>
                <div class="internship-details">
                    <div class="detail-group">
                        <h4>Durée</h4>
                        <p><?= htmlspecialchars($application['internship_duration']) ?></p>
                    </div>
                    <div class="detail-group">
                        <h4>Rémunération</h4>
                        <p><?= htmlspecialchars($application['monthly_remuneration']) ?> €/mois</p>
                    </div>
                    <div class="detail-group">
                        <h4>Date de début</h4>
                        <p><?= !empty($application['Starting_internship_date']) ? date('d/m/Y', strtotime($application['Starting_internship_date'])) : 'Non spécifiée' ?></p>
                    </div>
                    <div class="detail-group">
                        <h4>Localisation</h4>
                        <p><?= htmlspecialchars($application['location']) ?></p>
                    </div>
                </div>
                <div class="internship-description">
                    <h4>Description du stage</h4>
                    <p><?= nl2br(htmlspecialchars($application['offer_description'])) ?></p>
                </div>
                <div class="card-actions">
                    <a href="/stages/<?= $application['offer_id'] ?>" class="btn btn-outline">Voir l'offre complète</a>
                </div>
            </div>

            <div class="application-card">
                <div class="card-header-alt">
                    <h3>Votre CV</h3>
                </div>
                <div class="cv-preview">
                    <?php if (!empty($application['cv_path'])): ?>
                        <div class="cv-embed">
                            <iframe src="<?= $application['cv_url'] ?>" frameborder="0"></iframe>
                        </div>
                        <div class="cv-actions">
                            <a href="/applications/download-cv/<?= $application['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-download"></i> Télécharger
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="no-cv-message">
                            <i class="fas fa-file-alt"></i>
                            <p>Aucun CV disponible pour cette candidature.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($application['status'] === 'interview'): ?>
                <div class="application-card">
                    <div class="card-header-alt">
                        <h3>Détails de l'entretien</h3>
                    </div>
                    <div class="interview-details">
                        <p><i class="far fa-calendar-check"></i> <strong>Date:</strong> <?= date('d/m/Y', strtotime($application['interview_date'])) ?></p>
                        <p><i class="far fa-clock"></i> <strong>Heure:</strong> <?= date('H:i', strtotime($application['interview_date'])) ?></p>
                        <?php if (!empty($application['feedback'])): ?>
                            <div class="interview-instructions">
                                <h4>Instructions</h4>
                                <p><?= nl2br(htmlspecialchars($application['feedback'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="interview-actions">
                            <a href="#" class="btn btn-outline btn-sm add-to-calendar">
                                <i class="fas fa-calendar-plus"></i> Ajouter au calendrier
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($application['status'] === 'accepted'): ?>
                <div class="application-card success-card">
                    <div class="card-header-alt">
                        <h3>Candidature acceptée !</h3>
                    </div>
                    <div class="success-message">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <p>Félicitations ! Votre candidature a été acceptée.</p>
                        <?php if (!empty($application['feedback'])): ?>
                            <div class="feedback-message">
                                <h4>Message de l'entreprise</h4>
                                <p><?= nl2br(htmlspecialchars($application['feedback'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="next-steps">
                            <h4>Prochaines étapes</h4>
                            <p>L'entreprise va vous contacter prochainement pour finaliser les détails administratifs.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($application['status'] === 'rejected'): ?>
                <div class="application-card reject-card">
                    <div class="card-header-alt">
                        <h3>Candidature non retenue</h3>
                    </div>
                    <div class="reject-message">
                        <p>Nous sommes désolés, mais votre candidature n'a pas été retenue pour ce stage.</p>
                        <?php if (!empty($application['feedback'])): ?>
                            <div class="feedback-message">
                                <h4>Feedback de l'entreprise</h4>
                                <p><?= nl2br(htmlspecialchars($application['feedback'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="suggestions">
                            <h4>Suggestions</h4>
                            <p>Ne vous découragez pas ! Voici d'autres stages qui pourraient vous intéresser :</p>
                            <div class="suggested-internships">
                                <a href="/stages" class="btn btn-primary">Voir d'autres stages</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour ajouter l'entretien au calendrier
        const addToCalendarBtn = document.querySelector('.add-to-calendar');
        if (addToCalendarBtn) {
            addToCalendarBtn.addEventListener('click', function(e) {
                e.preventDefault();

                <?php if (!empty($application['interview_date'])): ?>
                const interviewDate = new Date('<?= $application['interview_date'] ?>');
                const endTime = new Date(interviewDate.getTime() + 60*60*1000); // 1 heure après

                const title = 'Entretien - <?= addslashes($application['Offer_title']) ?>';
                const details = 'Entretien pour le stage "<?= addslashes($application['Offer_title']) ?>" chez <?= addslashes($application['company_name']) ?>';

                const calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&details=${encodeURIComponent(details)}&dates=${formatDate(interviewDate)}/${formatDate(endTime)}`;

                window.open(calendarUrl, '_blank');
                <?php endif; ?>
            });
        }

        // Fonction pour formater la date pour Google Calendar
        function formatDate(date) {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const seconds = date.getSeconds().toString().padStart(2, '0');

            return `${year}${month}${day}T${hours}${minutes}${seconds}`;
        }
    });
</script>