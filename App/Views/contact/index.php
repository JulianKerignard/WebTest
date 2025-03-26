<?php
// Définir le titre et la page courante
$title = 'Contact';
$current_page = 'contact';
?>

<section class="contact-hero">
    <div class="container">
        <div class="contact-hero-content">
            <h1>Comment pouvons-nous vous aider ?</h1>
            <p>Notre équipe est à votre disposition pour répondre à toutes vos questions</p>
        </div>
    </div>
</section>

<section class="contact-options">
    <div class="container">
        <div class="contact-cards">
            <div class="contact-card">
                <div class="card-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>Support technique</h3>
                <p>Besoin d'aide avec votre compte ou notre plateforme ?</p>
                <a href="#contact-form" class="contact-link">Contacter le support <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="contact-card">
                <div class="card-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Entreprises</h3>
                <p>Questions sur la publication d'offres ou le recrutement ?</p>
                <a href="#contact-form" class="contact-link">Service entreprises <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="contact-card">
                <div class="card-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Étudiants</h3>
                <p>Besoin d'aide pour trouver un stage ou optimiser votre profil ?</p>
                <a href="#contact-form" class="contact-link">Aide aux étudiants <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="contact-card">
                <div class="card-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Partenariats</h3>
                <p>Intéressé par un partenariat ou une collaboration ?</p>
                <a href="#contact-form" class="contact-link">Proposer un partenariat <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="contact-main" id="contact-form">
    <div class="container">
        <div class="contact-container">
            <div class="contact-form-container">
                <div class="section-header">
                    <h2>Envoyez-nous un message</h2>
                    <p>Remplissez le formulaire ci-dessous et nous vous répondrons dans les meilleurs délais</p>
                </div>
                <form class="contact-form" action="/contact/submit" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom complet</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" placeholder="Votre nom et prénom" required>
                            </div>
                            <?php if (isset($errors['name'])): ?>
                                <div class="error-message"><?= $errors['name'][0] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" placeholder="Votre adresse email" required>
                            </div>
                            <?php if (isset($errors['email'])): ?>
                                <div class="error-message"><?= $errors['email'][0] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Sujet</label>
                        <div class="input-with-icon">
                            <i class="fas fa-tag"></i>
                            <select id="subject" name="subject" required>
                                <option value="" disabled selected>Sélectionnez le sujet de votre message</option>
                                <option value="support">Support technique</option>
                                <option value="entreprise">Question entreprise</option>
                                <option value="etudiant">Question étudiant</option>
                                <option value="partenariat">Proposition de partenariat</option>
                                <option value="autre">Autre sujet</option>
                            </select>
                        </div>
                        <?php if (isset($errors['subject'])): ?>
                            <div class="error-message"><?= $errors['subject'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <div class="input-with-icon textarea-container">
                            <i class="fas fa-comment-alt"></i>
                            <textarea id="message" name="message" rows="6" placeholder="Détaillez votre demande ici..." required></textarea>
                        </div>
                        <?php if (isset($errors['message'])): ?>
                            <div class="error-message"><?= $errors['message'][0] ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <div class="checkbox-container">
                            <input type="checkbox" id="privacy" name="privacy" required>
                            <label for="privacy">J'accepte que mes données soient utilisées pour traiter ma demande conformément à la <a href="/terms#privacy">politique de confidentialité</a></label>
                            <?php if (isset($errors['privacy'])): ?>
                                <div class="error-message"><?= $errors['privacy'][0] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Envoyer le message</button>
                </form>
            </div>
            <div class="contact-info-container">
                <div class="info-box">
                    <h3>Nos coordonnées</h3>
                    <ul class="contact-info-list">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span>Adresse</span>
                                <p>123 Avenue de l'Innovation<br>75008 Paris, France</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <span>Téléphone</span>
                                <p>+33 1 23 45 67 89</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <div>
                                <span>Email</span>
                                <p>contact@LeBonPlan.fr</p>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <div>
                                <span>Horaires d'ouverture</span>
                                <p>Lundi - Vendredi: 9h00 - 18h00</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="social-box">
                    <h3>Suivez-nous</h3>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="map-container">
                    <img src="/api/placeholder/450/250" alt="Plan d'accès LeBonPlan" class="location-map">
                    <a href="#" class="get-directions">
                        <i class="fas fa-directions"></i> Obtenir l'itinéraire
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="faq-section">
    <div class="container">
        <div class="section-header center">
            <h2>Questions fréquentes</h2>
            <p>Trouvez rapidement des réponses aux questions les plus courantes</p>
        </div>
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Comment créer un compte sur LeBonPlan ?</h3>
                    <button class="faq-toggle"><i class="fas fa-chevron-down"></i></button>
                </div>
                <div class="faq-answer">
                    <p>Pour créer un compte, cliquez sur le bouton "Inscription" en haut à droite de la page. Vous pourrez choisir entre un compte étudiant ou entreprise, puis remplir le formulaire avec vos informations personnelles.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Comment publier une offre de stage ?</h3>
                    <button class="faq-toggle"><i class="fas fa-chevron-down"></i></button>
                </div>
                <div class="faq-answer">
                    <p>Les entreprises peuvent publier des offres de stage en se connectant à leur compte, puis en cliquant sur "Publier une offre" dans le tableau de bord. Remplissez ensuite le formulaire détaillé pour créer votre annonce.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Comment postuler à un stage ?</h3>
                    <button class="faq-toggle"><i class="fas fa-chevron-down"></i></button>
                </div>
                <div class="faq-answer">
                    <p>Pour postuler à un stage, connectez-vous à votre compte étudiant, naviguez vers l'offre qui vous intéresse et cliquez sur le bouton "Postuler". Vous pourrez alors envoyer votre CV, lettre de motivation et répondre aux éventuelles questions du recruteur.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Les services de LeBonPlan sont-ils gratuits ?</h3>
                    <button class="faq-toggle"><i class="fas fa-chevron-down"></i></button>
                </div>
                <div class="faq-answer">
                    <p>LeBonPlan est entièrement gratuit pour les étudiants. Pour les entreprises, la publication d'offres de stage est gratuite, mais nous proposons des services premium payants pour améliorer la visibilité et accéder à des fonctionnalités avancées.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <h3>Comment modifier mon profil ou mon CV ?</h3>
                    <button class="faq-toggle"><i class="fas fa-chevron-down"></i></button>
                </div>
                <div class="faq-answer">
                    <p>Connectez-vous à votre compte, accédez à votre tableau de bord et cliquez sur "Mon profil" ou "Gérer mon CV". Vous pourrez alors modifier toutes vos informations et télécharger une nouvelle version de votre CV si nécessaire.</p>
                </div>
            </div>
        </div>
        <div class="faq-more">
            <a href="/faq" class="btn btn-outline">Voir toutes les FAQ <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<section class="newsletter contact-newsletter">
    <div class="container">
        <div class="newsletter-content">
            <h2 class="section-title">Restez informé</h2>
            <p>Inscrivez-vous à notre newsletter pour recevoir nos actualités et nos conseils pour votre recherche de stage.</p>
            <form class="newsletter-form" action="/newsletter/subscribe" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                <input type="email" name="email" placeholder="Votre adresse email" required>
                <button type="submit" class="btn btn-primary">S'abonner</button>
            </form>
        </div>
    </div>
</section>