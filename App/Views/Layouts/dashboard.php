<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeBonPlan - <?= $title ?? 'Tableau de bord' ?></title>
    <link rel="stylesheet" href="/Asset/Css/Style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php if (isset($additionalCss)): ?>
        <?php foreach($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="dashboard-body">
<script src="/Asset/Js/scripts.js"></script>
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="dashboard-sidebar">
        <div class="sidebar-header">
            <a href="/" class="logo">
                <i class="fas fa-briefcase"></i>
                <?php if (!isset($compact_sidebar) || !$compact_sidebar): ?>
                    LeBonPlan
                <?php endif; ?>
            </a>
            <button class="close-sidebar-btn" id="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="user-profile">
            <div class="user-avatar">
                <img src="/api/placeholder/100/100" alt="Photo de profil">
            </div>
            <div class="user-info">
                <h3><?= htmlspecialchars($user['username'] ?? 'Utilisateur') ?></h3>
                <?php if ($user['role'] === 'student'): ?>
                    <p>Étudiant<?= isset($student['study_field']) ? ' en ' . htmlspecialchars($student['study_field']) : '' ?></p>
                    <span class="user-status">
                        <i class="fas fa-circle"></i> En recherche de stage
                    </span>
                <?php elseif ($user['role'] === 'pilot'): ?>
                    <p>Pilote de promotion</p>
                    <span class="user-status">
                        <i class="fas fa-user"></i> Pilote
                    </span>
                <?php elseif ($user['role'] === 'admin'): ?>
                    <p>Administrateur</p>
                    <span class="user-status admin-status">
                        <i class="fas fa-shield-alt"></i> Admin
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <?php if ($user['role'] === 'student'): ?>
                    <li<?= $current_page === 'dashboard' ? ' class="active"' : '' ?>>
                        <a href="/student/dashboard"><i class="fas fa-home"></i> Tableau de bord</a>
                    </li>
                    <li<?= $current_page === 'search' ? ' class="active"' : '' ?>>
                        <a href="/stages"><i class="fas fa-search"></i> Rechercher des stages</a>
                    </li>
                    <li<?= $current_page === 'applications' ? ' class="active"' : '' ?>>
                        <a href="/student/applications"><i class="fas fa-file-alt"></i> Mes candidatures</a>
                    </li>
                    <li<?= $current_page === 'wishlist' ? ' class="active"' : '' ?>>
                        <a href="/student/wishlist"><i class="fas fa-star"></i> Stages favoris</a>
                    </li>
                    <li<?= $current_page === 'notifications' ? ' class="active"' : '' ?>>
                        <a href="/student/notifications"><i class="fas fa-bell"></i> Notifications</a>
                    </li>
                    <li<?= $current_page === 'profile' ? ' class="active"' : '' ?>>
                        <a href="/student/profile"><i class="fas fa-user"></i> Mon profil</a>
                    </li>
                <?php elseif ($user['role'] === 'pilot'): ?>
                    <li<?= $current_page === 'dashboard' ? ' class="active"' : '' ?>>
                        <a href="/pilot/dashboard"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
                    </li>
                    <li<?= $current_page === 'students' ? ' class="active"' : '' ?>>
                        <a href="/pilot/students"><i class="fas fa-users"></i> Étudiants</a>
                    </li>
                    <li<?= $current_page === 'company' ? ' class="active"' : '' ?>>
                        <a href="/pilot/companies"><i class="fas fa-building"></i> Entreprises</a>
                    </li>
                    <li<?= $current_page === 'internships' ? ' class="active"' : '' ?>>
                        <a href="/pilot/internships"><i class="fas fa-briefcase"></i> Stages</a>
                    </li>
                    <li<?= $current_page === 'statistics' ? ' class="active"' : '' ?>>
                        <a href="/pilot/statistics"><i class="fas fa-chart-bar"></i> Statistiques</a>
                    </li>
                <?php elseif ($user['role'] === 'admin'): ?>
                    <li<?= $current_page === 'dashboard' ? ' class="active"' : '' ?>>
                        <a href="/admin/dashboard"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
                    </li>
                    <li<?= $current_page === 'internships' ? ' class="active"' : '' ?>>
                        <a href="/admin/internships"><i class="fas fa-briefcase"></i> Stages</a>
                    </li>
                    <li<?= $current_page === 'users' ? ' class="active"' : '' ?>>
                        <a href="/admin/users"><i class="fas fa-users"></i> Utilisateurs</a>
                    </li>
                    <li<?= $current_page === 'company' ? ' class="active"' : '' ?>>
                        <a href="/admin/companies"><i class="fas fa-building"></i> Entreprises</a>
                    </li>
                    <li<?= $current_page === 'pilots' ? ' class="active"' : '' ?>>
                        <a href="/admin/pilots"><i class="fas fa-user-tie"></i> Pilotes</a>
                    </li>
                    <li<?= $current_page === 'students' ? ' class="active"' : '' ?>>
                        <a href="/admin/students"><i class="fas fa-user-graduate"></i> Étudiants</a>
                    </li>
                    <li<?= $current_page === 'settings' ? ' class="active"' : '' ?>>
                        <a href="/admin/settings"><i class="fas fa-cog"></i> Paramètres</a>
                    </li>
                <?php endif; ?>
                <li class="sidebar-divider"></li>
                <li>
                    <a href="/logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-search">
                <div class="input-with-icon">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher...">
                </div>
            </div>
            <div class="header-actions">
                <div class="notification-badge">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                <div class="message-badge">
                    <i class="fas fa-envelope"></i>
                    <span class="badge">5</span>
                </div>
                <div class="user-dropdown">
                    <img src="/api/placeholder/40/40" alt="Photo de profil">
                    <span><?= htmlspecialchars($user['username'] ?? 'Utilisateur') ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <?php if (isset($flash_messages) && !empty($flash_messages)): ?>
            <div class="flash-container dashboard-flash">
                <?php foreach ($flash_messages as $type => $message): ?>
                    <div class="flash-message flash-<?= $type ?>">
                        <?= $message ?>
                        <button class="close-btn"><i class="fas fa-times"></i></button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-content">
            <?= $content ?>
        </div>
    </main>
</div>

<?php if (isset($additionalJs)): ?>
    <?php foreach($additionalJs as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>