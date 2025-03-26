<?php
// Définir le titre et la page courante
$title = 'Conditions d\'utilisation';
$current_page = 'terms';
?>

<section class="terms-hero">
    <div class="container">
        <h1>Conditions Générales d'Utilisation</h1>
        <p>Ces conditions d'utilisation définissent les règles et régulations pour l'utilisation du site web LeBonPlan</p>
    </div>
</section>

<section class="terms-content">
    <div class="container">
        <div class="terms-container">
            <!-- Navigation fixe -->
            <div class="terms-nav">
                <h3>Table des matières</h3>
                <ul>
                    <li><a href="#introduction" class="active">1. Introduction</a></li>
                    <li><a href="#definitions">2. Définitions</a></li>
                    <li><a href="#accounts">3. Comptes et inscriptions</a></li>
                    <li><a href="#content">4. Contenu et propriété intellectuelle</a></li>
                    <li><a href="#services">5. Services et fonctionnalités</a></li>
                    <li><a href="#privacy">6. Confidentialité et données personnelles</a></li>
                    <li><a href="#limitations">7. Limitations de responsabilité</a></li>
                    <li><a href="#termination">8. Résiliation</a></li>
                    <li><a href="#modifications">9. Modifications des conditions</a></li>
                    <li><a href="#contact">10. Contact</a></li>
                </ul>
            </div>

            <!-- Contenu principal -->
            <div class="terms-content-main">
                <div class="terms-section" id="introduction">
                    <h2>1. Introduction</h2>
                    <p>Bienvenue sur LeBonPlan. En accédant à ce site web, vous acceptez d'être lié par ces conditions d'utilisation, toutes les lois et réglementations applicables, et acceptez que vous êtes responsable du respect des lois locales applicables. Si vous n'êtes pas d'accord avec l'une de ces conditions, vous êtes interdit d'utiliser ou d'accéder à ce site. Les documents contenus dans ce site web sont protégés par les lois applicables en matière de droit d'auteur et de marque.</p>

                    <p>LeBonPlan est une plateforme en ligne qui vise à faciliter la mise en relation entre les étudiants à la recherche de stages et les entreprises proposant des offres de stage. Ces Conditions Générales d'Utilisation (CGU) définissent les termes et conditions régissant l'utilisation de notre plateforme, que ce soit en tant qu'étudiant ou entreprise.</p>

                    <p>En utilisant notre plateforme, vous reconnaissez avoir lu, compris et accepté ces CGU dans leur intégralité. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre site web et nos services.</p>
                </div>

                <!-- Suite des sections (omises pour brièveté) -->

                <div class="terms-section" id="definitions">
                    <h2>2. Définitions</h2>
                    <p>Dans les présentes conditions générales d'utilisation, les termes suivants ont la signification indiquée ci-dessous :</p>

                    <ul>
                        <li><strong>"LeBonPlan"</strong>, <strong>"nous"</strong>, <strong>"notre"</strong> ou <strong>"nos"</strong> désignent la société LeBonPlan, exploitant le site web lebonplan.fr.</li>
                        <li><strong>"Plateforme"</strong> désigne le site web lebonplan.fr et l'ensemble des services associés.</li>
                        <li><strong>"Utilisateur"</strong>, <strong>"vous"</strong>, <strong>"votre"</strong> ou <strong>"vos"</strong> désignent toute personne qui accède à la Plateforme, qu'elle soit inscrite ou non.</li>
                        <li><strong>"Contenu"</strong> désigne l'ensemble des informations, textes, logos, marques, animations, dessins, photos, données, sons, graphiques, vidéos ou tout autre élément publié sur la Plateforme.</li>
                        <li><strong>"Étudiant"</strong> désigne un Utilisateur inscrit sur la Plateforme en tant que personne cherchant un stage.</li>
                        <li><strong>"Entreprise"</strong> désigne un Utilisateur inscrit sur la Plateforme en tant qu'entité proposant des offres de stage.</li>
                        <li><strong>"Stage"</strong> désigne une période de formation pratique en entreprise proposée par une Entreprise sur la Plateforme.</li>
                        <li><strong>"Compte"</strong> désigne l'espace personnel d'un Utilisateur inscrit sur la Plateforme.</li>
                    </ul>
                </div>

                <!-- Autres sections (omises pour brièveté) -->

                <div class="terms-section" id="contact">
                    <h2>10. Contact</h2>
                    <p>Si vous avez des questions ou des préoccupations concernant ces CGU ou la Plateforme en général, veuillez nous contacter :</p>

                    <ul>
                        <li>Par email : <a href="mailto:contact@lebonplan.fr" class="contact-link">contact@lebonplan.fr</a></li>
                        <li>Par courrier : LeBonPlan, 123 Avenue de l'Innovation, 75008 Paris, France</li>
                        <li>Par téléphone : +33 1 23 45 67 89 (du lundi au vendredi, de 9h à 18h)</li>
                    </ul>

                    <div class="terms-date">
                        <p>Dernière mise à jour : 5 mars 2025</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="terms-footer">
    <div class="container">
        <p>En cas de litige, la version française des présentes CGU prévaut.</p>
        <p>Pour toute question relative à ces conditions, veuillez <a href="/contact" class="contact-link">nous contacter</a>.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sélectionner toutes les sections et liens de navigation
        const sections = document.querySelectorAll('.terms-section');
        const navLinks = document.querySelectorAll('.terms-nav a');

        // Fonction pour mettre à jour les liens actifs lors du défilement
        function updateActiveLink() {
            let foundActive = false;
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const scrollPosition = window.scrollY + 100; // 100px d'offset pour compenser la navigation fixe

                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight && !foundActive) {
                    // Retirer la classe active de tous les liens
                    navLinks.forEach(link => link.classList.remove('active'));

                    // Trouver et activer le lien correspondant à cette section
                    const id = section.getAttribute('id');
                    const correspondingLink = document.querySelector(`.terms-nav a[href="#${id}"]`);
                    if (correspondingLink) {
                        correspondingLink.classList.add('active');
                        foundActive = true;
                    }
                }
            });
        }

        window.addEventListener('scroll', updateActiveLink);

        updateActiveLink();
    });
</script>