<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - <?= $title ?? 'Trouvez votre stage idéal' ?></title>
    <link rel="stylesheet" href="/Asset/Css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php if (isset($additionalCss)): ?>
        <?php foreach($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<script src="/Asset/Js/scripts.js"></script>
<header>
    <div class="container">
        <nav class="navbar">
            <a href="/" class="logo">
                <i class="fas fa-briefcase"></i>
                LeBonPlan
            </a>
            <ul class="nav-menu">
                <li<?= $current_page === 'home' ? ' class="active"' : '' ?>><a href="/">Accueil</a></li>
                <li<?= $current_page === 'stages' ? ' class="active"' : '' ?>><a href="/stages">Stages</a></li>
                <li<?= $current_page === 'entreprises' ? ' class="active"' : '' ?>><a href="/companies">Entreprises</a></li>
                <li<?= $current_page === 'contact' ? ' class="active"' : '' ?>><a href="/contact">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if (isset($user)): ?>
                    <div class="dropdown">
                        <a href="#" class="btn btn-outline dropdown-toggle">
                            <?= htmlspecialchars($user['username']) ?>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <div class="dropdown-menu">
                            <?php if ($user['role'] === 'student'): ?>
                                <a href="/student/dashboard">Tableau de bord</a>
                            <?php elseif ($user['role'] === 'pilot'): ?>
                                <a href="/pilot/dashboard">Tableau de bord</a>
                            <?php elseif ($user['role'] === 'admin'): ?>
                                <a href="/admin/dashboard">Tableau de bord</a>
                            <?php endif; ?>
                            <a href="/profile">Mon profil</a>
                            <a href="/logout">Déconnexion</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline">Connexion</a>
                    <a href="/register" class="btn btn-primary">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<!-- Flash messages -->
<?php if (isset($flash_messages) && !empty($flash_messages)): ?>
    <div class="flash-container">
        <?php foreach ($flash_messages as $type => $message): ?>
            <div class="flash-message flash-<?= $type ?>">
                <?= $message ?>
                <button class="close-btn"><i class="fas fa-times"></i></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Main content -->
<?= $content ?>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>LeBonPlan</h3>
                <p>La plateforme qui facilite la recherche de stages pour les étudiants et la recherche de talents pour les entreprises.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://x.com/" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com/in/julian-kerignard-b9ab6a2bb/" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Liens utiles</h3>
                <ul class="footer-links">
                    <li><a href="#">À propos</a></li>
                    <li><a href="/legal">CGU</a></li>
                    <li><a href="/terms">Mentions Légales</a></li>
                    <li><a href="/contact">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Candidats</h3>
                <ul class="footer-links">
                    <li><a href="/stages">Parcourir les stages</a></li>
                    <li><a href="/companies">Parcourir les entreprises</a></li>
                    <li><a href="/login">Se Connecter</a></li>
                    <?php if (isset($user) && $user['role'] === 'student'): ?>
                        <li><a href="/student/dashboard">Dashboard Étudiant</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?= date('Y') ?> LeBonPlan. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<?php if (isset($additionalJs)): ?>
    <?php foreach($additionalJs as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>