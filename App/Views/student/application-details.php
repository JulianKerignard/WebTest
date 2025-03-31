<?php
// Définir le titre et la page courante
$title = 'Gestion de candidature';
$current_page = 'applications';

// Fonctions utilitaires locales pour éviter la dépendance au ViewHelper
function safe_value($array, $key, $default = '') {
    if (!isset($array) || !is_array($array) || !isset($array[$key])) {
        return $default;
    }
    return $array[$key];
}

function escape_value($array, $key, $default = '') {
    return htmlspecialchars(safe_value($array, $key, $default), ENT_QUOTES, 'UTF-8');
}

function has_value($array, $key) {
    return isset($array) && is_array($array) && isset($array[$key]) && !empty($array[$key]);
}

function format_date($array, $key, $format = 'd/m/Y', $default = '') {
    $value = safe_value($array, $key);
    if (empty($value)) {
        return $default;
    }
    try {
        $date = new \DateTime($value);
        return $date->format($format);
    } catch (\Exception $e) {
        return $default;
    }
}

// Alias court pour les fonctions
function v($array, $key, $default = '') {
    return escape_value($array, $key, $default);
}

// Vérifier si les données de candidature sont présentes
if (!isset($application) || empty($application)) {
    echo '<div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 15px; border-radius: 5px;">
        <strong>Erreur:</strong> Les données de candidature sont manquantes ou la connexion à la base de données a échoué.
    </div>';
    echo '<div class="actions-container" style="margin: 20px 15px;">
        <a href="/applications" class="btn btn-primary">Retour à la liste des candidatures</a>
    </div>';
    return;
}
?>

<div class="page-title">
    <h1>Gestion de candidature</h1>
    <p>Candidature de <strong><?= escape_value($application, 'student_name', 'Étudiant') ?></strong> pour le stage <strong><?= escape_value($application, 'Offer_title', 'Non spécifié') ?></strong></p>
</div>

<div class="application-detail-container">
    <div class="application-header">
        <div class="student-avatar">
            <img src="/api/placeholder/100/100" alt="Photo de profil">
        </div>
        <div class="application-title">
            <h2><?= escape_value($application, 'student_name', 'Étudiant') ?></h2>
            <p class="student-info"><?= escape_value($application, 'study_field', 'Non spécifié') ?> - <?= escape_value($application, 'school_name', 'Non spécifié') ?></p>
            <p class="application-meta">
                <span class="application-date"><i class="far fa-calendar"></i> Candidature reçue le <?= format_date($application, 'created_at', 'd/m/Y', 'Date inconnue') ?></span>
                <span class="application-email"><i class="far fa-envelope"></i> <?= escape_value($application, 'student_email', 'Email non disponible') ?></span>
            </p>
        </div>
        <div class="application-actions">
            <button class="btn btn-primary" id="updateStatusBtn">Mettre à jour le statut</button>
            <button class="btn btn-outline" id="addNoteBtn">Ajouter une note</button>
        </div>
    </div>

    <div class="application-content">
        <div class="application-column">
            <div class="application-card status-management">
                <div class="card-header-alt">
                    <h3>Statut de la candidature</h3>
                    <span class="status-badge <?= safe_value($application, 'status', '') ?>"><?= escape_value($application, 'status_label', 'Non défini') ?></span>
                </div>
                <div class="status-form" id="statusForm" style="display: none;">
                    <form id="updateStatusForm">
                        <input type="hidden" name="application_id" value="<?= safe_value($application, 'id', 0) ?>">
                        <input type="hidden" name="csrf_token" value="<?= isset($csrf_token) ? $csrf_token : '' ?>">

                        <div class="form-group">
                            <label for="status">Nouveau statut</label>
                            <select name="status" id="status" class="form-control" required>
                                <?php if (isset($status_options) && is_array($status_options)): ?>
                                    <?php foreach ($status_options as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= safe_value($application, 'status') === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="pending" <?= safe_value($application, 'status') === 'pending' ? 'selected' : '' ?>>En attente</option>
                                    <option value="in-review" <?= safe_value($application, 'status') === 'in-review' ? 'selected' : '' ?>>En cours d'examen</option>
                                    <option value="interview" <?= safe_value($application, 'status') === 'interview' ? 'selected' : '' ?>>Entretien</option>
                                    <option value="accepted" <?= safe_value($application, 'status') === 'accepted' ? 'selected' : '' ?>>Acceptée</option>
                                    <option value="rejected" <?= safe_value($application, 'status') === 'rejected' ? 'selected' : '' ?>>Refusée</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group interview-date-group" style="display: none;">
                            <label for="interview_date">Date et heure de l'entretien</label>
                            <input type="datetime-local" name="interview_date" id="interview_date" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="feedback">Commentaire (optionnel)</label>
                            <textarea name="feedback" id="feedback" rows="4" class="form-control" placeholder="Ajoutez un commentaire ou des instructions..."></textarea>
                            <p class="form-help">Ce commentaire sera visible par le candidat</p>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="cancelStatusBtn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
                <div class="application-timeline">
                    <?php if (isset($application['status_history']) && is_array($application['status_history']) && !empty($application['status_history'])): ?>
                        <?php foreach ($application['status_history'] as $history): ?>
                            <div class="timeline-item">
                                <div class="timeline-indicator <?= safe_value($history, 'status', '') ?>"></div>
                                <div class="timeline-content">
                                    <h4><?= escape_value($history, 'status_label', 'Statut inconnu') ?></h4>
                                    <p class="timeline-date"><?= escape_value($history, 'time_ago', '') ?></p>
                                    <?php if (has_value($history, 'comment')): ?>
                                        <p class="timeline-comment"><?= nl2br(htmlspecialchars($history['comment'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-history">Aucun historique de statut disponible</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="application-card">
                <div class="card-header-alt">
                    <h3>Notes internes</h3>
                    <span class="note-count"><?= isset($application['notes']) && is_array($application['notes']) ? count($application['notes']) : 0 ?> notes</span>
                </div>
                <div class="note-form" id="noteForm" style="display: none;">
                    <form id="addNoteForm">
                        <input type="hidden" name="application_id" value="<?= safe_value($application, 'id', 0) ?>">
                        <input type="hidden" name="csrf_token" value="<?= isset($csrf_token) ? $csrf_token : '' ?>">

                        <div class="form-group">
                            <label for="note">Nouvelle note</label>
                            <textarea name="note" id="note" rows="4" class="form-control" placeholder="Ajoutez une note interne..." required></textarea>
                            <p class="form-help">Cette note est interne et ne sera pas visible par le candidat</p>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="cancelNoteBtn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Ajouter la note</button>
                        </div>
                    </form>
                </div>
                <div class="notes-list" id="notesList">
                    <?php if (isset($application['notes']) && is_array($application['notes']) && !empty($application['notes'])): ?>
                        <?php foreach ($application['notes'] as $note): ?>
                            <div class="note-item">
                                <div class="note-header">
                                    <span class="note-author"><?= escape_value($note, 'author_name', 'Utilisateur') ?></span>
                                    <span class="note-date"><?= isset($note['created_at']) ? format_date($note, 'created_at', 'd/m/Y à H:i') : '' ?></span>
                                </div>
                                <div class="note-content">
                                    <?= has_value($note, 'content') ? nl2br(htmlspecialchars($note['content'])) : '' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-notes">Aucune note pour le moment. Ajoutez des notes pour partager des informations avec votre équipe.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="application-column">
            <div class="application-card">
                <div class="card-header-alt">
                    <h3>CV du candidat</h3>
                </div>
                <div class="cv-preview">
                    <?php if (has_value($application, 'cv_path') && isset($application['cv_url'])): ?>
                        <div class="cv-embed">
                            <iframe src="<?= htmlspecialchars($application['cv_url']) ?>" frameborder="0"></iframe>
                        </div>
                        <div class="cv-actions">
                            <a href="/applications/download-cv/<?= safe_value($application, 'id', 0) ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-download"></i> Télécharger le CV
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

            <div class="application-card">
                <div class="card-header-alt">
                    <h3>Lettre de motivation</h3>
                </div>
                <div class="motivation-letter">
                    <div class="letter-content">
                        <?= has_value($application, 'cover_letter') ? nl2br(htmlspecialchars($application['cover_letter'])) : 'Aucune lettre de motivation disponible.' ?>
                    </div>
                </div>
            </div>

            <div class="application-card">
                <div class="card-header-alt">
                    <h3>Informations sur le stage</h3>
                </div>
                <div class="internship-details">
                    <div class="detail-group">
                        <h4>Titre du stage</h4>
                        <p><?= escape_value($application, 'Offer_title', 'Non spécifié') ?></p>
                    </div>
                    <div class="detail-group">
                        <h4>Durée</h4>
                        <p><?= escape_value($application, 'internship_duration', 'Non spécifié') ?></p>
                    </div>
                    <div class="detail-group">
                        <h4>Rémunération</h4>
                        <p><?= escape_value($application, 'monthly_remuneration', 'Non spécifié') ?> €/mois</p>
                    </div>
                    <div class="detail-group">
                        <h4>Date de début</h4>
                        <p><?= has_value($application, 'Starting_internship_date') ? date('d/m/Y', strtotime($application['Starting_internship_date'])) : 'Non spécifiée' ?></p>
                    </div>
                    <div class="detail-group">
                        <h4>Localisation</h4>
                        <p><?= escape_value($application, 'location', 'Non spécifié') ?></p>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="/stages/<?= safe_value($application, 'offer_id', 0) ?>" class="btn btn-outline">Voir l'offre complète</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du formulaire de statut
        const updateStatusBtn = document.getElementById('updateStatusBtn');
        const cancelStatusBtn = document.getElementById('cancelStatusBtn');
        const statusForm = document.getElementById('statusForm');
        const statusSelect = document.getElementById('status');
        const interviewDateGroup = document.querySelector('.interview-date-group');

        if(updateStatusBtn && cancelStatusBtn && statusForm && statusSelect) {
            updateStatusBtn.addEventListener('click', function() {
                statusForm.style.display = 'block';
                this.style.display = 'none';

                // Afficher le champ de date d'entretien si nécessaire
                if (statusSelect.value === 'interview') {
                    interviewDateGroup.style.display = 'block';
                } else {
                    interviewDateGroup.style.display = 'none';
                }
            });

            cancelStatusBtn.addEventListener('click', function() {
                statusForm.style.display = 'none';
                updateStatusBtn.style.display = 'inline-block';
            });

            statusSelect.addEventListener('change', function() {
                if (this.value === 'interview') {
                    interviewDateGroup.style.display = 'block';
                } else {
                    interviewDateGroup.style.display = 'none';
                }
            });
        }

        // Gestion du formulaire de note
        const addNoteBtn = document.getElementById('addNoteBtn');
        const cancelNoteBtn = document.getElementById('cancelNoteBtn');
        const noteForm = document.getElementById('noteForm');

        if(addNoteBtn && cancelNoteBtn && noteForm) {
            addNoteBtn.addEventListener('click', function() {
                noteForm.style.display = 'block';
                this.style.display = 'none';
            });

            cancelNoteBtn.addEventListener('click', function() {
                noteForm.style.display = 'none';
                addNoteBtn.style.display = 'inline-block';
            });
        }

        // Soumission du formulaire de statut
        const updateStatusForm = document.getElementById('updateStatusForm');
        if(updateStatusForm) {
            updateStatusForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });

                fetch('/applications/update-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Masquer le formulaire
                            statusForm.style.display = 'none';
                            updateStatusBtn.style.display = 'inline-block';

                            // Recharger la page pour afficher les changements
                            location.reload();
                        } else {
                            alert(result.message || 'Une erreur est survenue');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue lors de la mise à jour du statut');
                    });
            });
        }

        // Soumission du formulaire de note
        const addNoteForm = document.getElementById('addNoteForm');
        const notesList = document.getElementById('notesList');

        if(addNoteForm && notesList) {
            addNoteForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const data = {};
                formData.forEach((value, key) => {
                    data[key] = value;
                });

                fetch('/applications/add-note', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Masquer le formulaire et réinitialiser
                            noteForm.style.display = 'none';
                            addNoteBtn.style.display = 'inline-block';
                            document.getElementById('note').value = '';

                            // Mettre à jour la liste des notes
                            if (result.notes && result.notes.length > 0) {
                                // Effacer le message "aucune note"
                                const noNotes = notesList.querySelector('.no-notes');
                                if (noNotes) {
                                    notesList.removeChild(noNotes);
                                }

                                // Ajouter la nouvelle note au début
                                const noteItem = document.createElement('div');
                                noteItem.className = 'note-item';
                                noteItem.innerHTML = `
                                <div class="note-header">
                                    <span class="note-author">${result.notes[0].author_name}</span>
                                    <span class="note-date">à l'instant</span>
                                </div>
                                <div class="note-content">${result.notes[0].content.replace(/\n/g, '<br>')}</div>
                            `;

                                if (notesList.firstChild) {
                                    notesList.insertBefore(noteItem, notesList.firstChild);
                                } else {
                                    notesList.appendChild(noteItem);
                                }
                            }
                        } else {
                            alert(result.message || 'Une erreur est survenue');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Une erreur est survenue lors de l\'ajout de la note');
                    });
            });
        }
    });
</script>