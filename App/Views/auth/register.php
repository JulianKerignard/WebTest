<?php
// Définir le titre et la page courante
$title = 'Inscription';
$current_page = '';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card register-card">
                <div class="auth-header">
                    <h2>Créer un compte</h2>
                    <p>Rejoignez LeBonPlan pour découvrir des opportunités de stage adaptées à votre profil</p>
                </div>
                <div class="account-type-selector">
                    <button class="account-type <?= ($account_type ?? 'student') === 'student' ? 'active' : '' ?>" data-type="student">
                        <i class="fas fa-user-graduate"></i>
                        <span>Étudiant</span>
                    </button>
                    <button class="account-type <?= ($account_type ?? '') === 'company' ? 'active' : '' ?>" data-type="company">
                        <i class="fas fa-building"></i>
                        <span>Entreprise</span>
                    </button>
                </div>
                <form class="auth-form" action="/register" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="account_type" id="account_type" value="<?= $account_type ?? 'student' ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">Prénom</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="firstname" name="firstname" placeholder="Votre prénom"
                                       value="<?= htmlspecialchars($old['firstname'] ?? '') ?>" required>
                            </div>
                            <?php if (isset($errors['firstname'])): ?>
                                <div class="error-message"><?= $errors['firstname'][0] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Nom</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="lastname" name="lastname" placeholder="Votre nom"
                                       value="<?= htmlspecialchars($old['lastname'] ?? '') ?>" required>
                            </div>
                            <?php if (isset($errors['lastname'])): ?>
                                <div class="error-message"><?= $errors['lastname'][0] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="Votre adresse email"
                                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div class="error-message"><?= $errors['email'][0] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="error-message"><?= $errors['password'][0] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirmer</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm-password" name="password_confirmation" placeholder="Confirmer mot de passe" required>
                            </div>
                        </div>
                    </div>

                    <!-- Champs spécifiques aux étudiants -->
                    <div id="student-fields" class="<?= ($account_type ?? 'student') !== 'student' ? 'hidden' : '' ?>">
                        <div class="form-group">
                            <label for="education">Niveau d'études</label>
                            <div class="input-with-icon">
                                <i class="fas fa-graduation-cap"></i>
                                <select id="education" name="education">
                                    <option value="" disabled <?= !isset($old['education']) ? 'selected' : '' ?>>Sélectionnez votre niveau</option>
                                    <option value="bac" <?= isset($old['education']) && $old['education'] === 'bac' ? 'selected' : '' ?>>Bac</option>
                                    <option value="bac+1" <?= isset($old['education']) && $old['education'] === 'bac+1' ? 'selected' : '' ?>>Bac+1</option>
                                    <option value="bac+2" <?= isset($old['education']) && $old['education'] === 'bac+2' ? 'selected' : '' ?>>Bac+2</option>
                                    <option value="bac+3" <?= isset($old['education']) && $old['education'] === 'bac+3' ? 'selected' : '' ?>>Bac+3</option>
                                    <option value="bac+4" <?= isset($old['education']) && $old['education'] === 'bac+4' ? 'selected' : '' ?>>Bac+4</option>
                                    <option value="bac+5" <?= isset($old['education']) && $old['education'] === 'bac+5' ? 'selected' : '' ?>>Bac+5</option>
                                    <option value="bac+6" <?= isset($old['education']) && $old['education'] === 'bac+6' ? 'selected' : '' ?>>Bac+6 et plus</option>
                                </select>
                            </div>
                            <?php if (isset($errors['education'])): ?>
                                <div class="error-message"><?= $errors['education'][0] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="field">Domaine d'études</label>
                            <div class="input-with-icon">
                                <i class="fas fa-book"></i>
                                <select id="field" name="field">
                                    <option value="" disabled <?= !isset($old['field']) ? 'selected' : '' ?>>Sélectionnez votre domaine</option>
                                    <option value="informatique" <?= isset($old['field']) && $old['field'] === 'informatique' ? 'selected' : '' ?>>Informatique</option>
                                    <option value="marketing" <?= isset($old['field']) && $old['field'] === 'marketing' ? 'selected' : '' ?>>Marketing</option>
                                    <option value="finance" <?= isset($old['field']) && $old['field'] === 'finance' ? 'selected' : '' ?>>Finance</option>
                                    <option value="rh" <?= isset($old['field']) && $old['field'] === 'rh' ? 'selected' : '' ?>>Ressources Humaines</option>
                                    <option value="communication" <?= isset($old['field']) && $old['field'] === 'communication' ? 'selected' : '' ?>>Communication</option>
                                    <option value="ingenierie" <?= isset($old['field']) && $old['field'] === 'ingenierie' ? 'selected' : '' ?>>Ingénierie</option>
                                    <option value="science" <?= isset($old['field']) && $old['field'] === 'science' ? 'selected' : '' ?>>Sciences</option>
                                    <option value="design" <?= isset($old['field']) && $old['field'] === 'design' ? 'selected' : '' ?>>Design</option>
                                    <option value="autre" <?= isset($old['field']) && $old['field'] === 'autre' ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>
                            <?php if (isset($errors['field'])): ?>
                                <div class="error-message"><?= $errors['field'][0] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Champs spécifiques aux entreprises -->
                    <div id="company-fields" class="<?= ($account_type ?? 'student') !== 'company' ? 'hidden' : '' ?>">
                        <div class="form-group">
                            <label for="company_name">Nom de l'entreprise</label>
                            <div class="input-with-icon">
                                <i class="fas fa-building"></i>
                                <input type="text" id="company_name" name="company_name" placeholder="Nom de votre entreprise"
                                       value="<?= htmlspecialchars($old['company_name'] ?? '') ?>">
                            </div>
                            <?php if (isset($errors['company_name'])): ?>
                                <div class="error-message"><?= $errors['company_name'][0] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="sector">Secteur d'activité</label>
                            <div class="input-with-icon">
                                <i class="fas fa-industry"></i>
                                <select id="sector" name="sector">
                                    <option value="" disabled <?= !isset($old['sector']) ? 'selected' : '' ?>>Sélectionnez votre secteur</option>
                                    <option value="tech" <?= isset($old['sector']) && $old['sector'] === 'tech' ? 'selected' : '' ?>>Informatique & Tech</option>
                                    <option value="finance" <?= isset($old['sector']) && $old['sector'] === 'finance' ? 'selected' : '' ?>>Finance & Banque</option>
                                    <option value="marketing" <?= isset($old['sector']) && $old['sector'] === 'marketing' ? 'selected' : '' ?>>Marketing & Communication</option>
                                    <option value="sante" <?= isset($old['sector']) && $old['sector'] === 'sante' ? 'selected' : '' ?>>Santé</option>
                                    <option value="commerce" <?= isset($old['sector']) && $old['sector'] === 'commerce' ? 'selected' : '' ?>>Commerce & Distribution</option>
                                    <option value="industrie" <?= isset($old['sector']) && $old['sector'] === 'industrie' ? 'selected' : '' ?>>Industrie & Ingénierie</option>
                                    <option value="autre" <?= isset($old['sector']) && $old['sector'] === 'autre' ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>
                            <?php if (isset($errors['sector'])): ?>
                                <div class="error-message"><?= $errors['sector'][0] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group terms">
                        <input type="checkbox" id="terms" name="terms" <?= isset($old['terms']) ? 'checked' : '' ?> required>
                        <label for="terms">J'accepte les <a href="/terms" target="_blank">conditions d'utilisation</a> et la <a href="/privacy" target="_blank">politique de confidentialité</a></label>
                        <?php if (isset($errors['terms'])): ?>
                            <div class="error-message"><?= $errors['terms'][0] ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>

                    <div class="social-login">
                        <p>Ou inscrivez-vous avec</p>
                        <div class="social-buttons">
                            <button type="button" class="btn btn-social btn-google" onclick="redirectToSocialLogin('google')">
                                <i class="fab fa-google"></i>
                                Google
                            </button>
                            <button type="button" class="btn btn-social btn-linkedin" onclick="redirectToSocialLogin('linkedin')">
                                <i class="fab fa-linkedin-in"></i>
                                LinkedIn
                            </button>
                        </div>
                    </div>
                </form>
                <div class="auth-footer">
                    <p>Vous avez déjà un compte? <a href="/login">Connectez-vous</a></p>
                </div>
            </div>
            <div class="auth-image">
                <img src="/api/placeholder/600/800" alt="Inscription LeBonPlan">
                <div class="auth-overlay">
                    <h3>Lancez votre carrière professionnelle</h3>
                    <p>Créez votre profil pour accéder à des milliers d'opportunités de stage dans toute la France.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du changement de type de compte
        const accountTypeButtons = document.querySelectorAll('.account-type');
        const accountTypeInput = document.getElementById('account_type');
        const studentFields = document.getElementById('student-fields');
        const companyFields = document.getElementById('company-fields');

        accountTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;

                // Mettre à jour le bouton actif
                accountTypeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Mettre à jour le champ caché
                accountTypeInput.value = type;

                // Afficher/masquer les champs spécifiques
                if (type === 'student') {
                    studentFields.classList.remove('hidden');
                    companyFields.classList.add('hidden');
                } else {
                    studentFields.classList.add('hidden');
                    companyFields.classList.remove('hidden');
                }
            });
        });
    });

    function redirectToSocialLogin(provider) {
        window.location.href = `/auth/${provider}?type=${document.getElementById('account_type').value}`;
    }
</script>