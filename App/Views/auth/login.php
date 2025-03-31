<?php
// Définir le titre et la page courante
$title = 'Connexion';
$current_page = '';
?>

<link rel="stylesheet" href="/Asset/Css/main.css">
<link rel="stylesheet" href="/Asset/Css/Style.css">
<script src="/Asset/Js/scripts.js"></script>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Connexion</h2>
                    <p>Accédez à votre compte pour gérer vos candidatures</p>
                </div>
                <form class="auth-form" action="/login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

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

                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember"
                                <?= isset($old['remember']) ? 'checked' : '' ?>>
                            <label for="remember">Se souvenir de moi</label>
                        </div>
                        <a href="/forgot-password" class="forgot-password">Mot de passe oublié?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>

                    <div class="social-login">
                        <p>Ou connectez-vous avec</p>
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
                    <p>Vous n'avez pas de compte? <a href="/register">Inscrivez-vous</a></p>
                </div>
            </div>
            <div class="auth-image">
                <img src="/img/login_img.jpg" alt="Connexion LeBonPlan" onerror="this.src='/api/placeholder/600/800'">
                <div class="auth-overlay">
                    <h3>Trouvez le stage qui lance votre carrière</h3>
                    <p>Des milliers d'opportunités de stage dans des entreprises innovantes à travers la France.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function redirectToSocialLogin(provider) {
        window.location.href = `/auth/${provider}`;
    }
</script>