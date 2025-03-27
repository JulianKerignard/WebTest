# LeBonPlan - Plateforme de Stages pour Étudiants

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/license-MIT-green)

## 📝 Description

**LeBonPlan** est une plateforme web moderne qui facilite la mise en relation entre les étudiants à la recherche de stages et les entreprises proposant des offres. Cette plateforme permet aux étudiants de trouver facilement des stages correspondant à leur profil et aux entreprises de recruter les talents de demain.

## 🖼️ Aperçu

![Aperçu de LeBonPlan](https://via.placeholder.com/800x400?text=LeBonPlan+Screenshot)

## 🌟 Fonctionnalités principales

### Pour les étudiants
- Création et gestion de profil (CV, compétences, etc.)
- Recherche avancée de stages avec filtres personnalisés
- Candidature en ligne avec lettre de motivation
- Liste de favoris pour sauvegarder les offres intéressantes
- Suivi des candidatures
- Évaluation des entreprises et des stages

### Pour les entreprises
- Création et gestion de profil d'entreprise
- Publication d'offres de stage
- Gestion des candidatures reçues
- Visualisation des statistiques

### Pour les pilotes de promotion
- Suivi des étudiants
- Gestion des entreprises partenaires
- Accès aux statistiques et rapports

### Pour les administrateurs
- Gestion complète des utilisateurs
- Administration des contenus
- Statistiques globales de la plateforme

## 🛠️ Technologies utilisées

- **Backend**: PHP 7.4+ avec architecture MVC custom
- **Frontend**: HTML5, CSS3, JavaScript, Font Awesome
- **Base de données**: MySQL
- **Dépendances**: Composer pour l'autoloading

## 📂 Structure du projet

```
lebonplan/
│
├── App/                        # Code source principal
│   ├── Config/                 # Fichiers de configuration
│   ├── Controllers/            # Contrôleurs MVC
│   ├── Core/                   # Noyau de l'application (Router, Database, etc.)
│   ├── Helpers/                # Classes utilitaires
│   ├── Middleware/             # Middlewares pour la sécurité et l'authentification
│   ├── Models/                 # Modèles pour l'accès aux données
│   ├── Services/               # Services métier
│   └── Views/                  # Templates et vues
│       ├── admin/              # Vues pour l'administration
│       ├── auth/               # Vues d'authentification
│       ├── companies/          # Vues pour les entreprises
│       ├── contact/            # Vues pour les contacts
│       ├── error/              # Pages d'erreur
│       ├── home/               # Page d'accueil
│       ├── layouts/            # Layouts principaux
│       ├── legal/              # Pages légales
│       ├── stages/             # Vues pour les stages
│       └── student/            # Dashboard étudiant
│
├── Asset/                      # Ressources statiques
│   ├── Css/                    # Feuilles de style CSS
│   ├── Js/                     # Scripts JavaScript
│   └── img/                    # Images
│
├── logs/                       # Logs applicatifs
│
├── public/                     # Point d'entrée de l'application
│   ├── index.php               # Script principal
│   └── .htaccess               # Configuration Apache
│
├── storage/                    # Stockage des fichiers uploadés
│   └── uploads/                # Fichiers uploadés (CV, logos, etc.)
│
├── vendor/                     # Dépendances (généré par Composer)
│
├── .env                        # Variables d'environnement
├── .env.example                # Exemple de configuration
├── .gitignore                  # Fichiers ignorés par Git
├── composer.json               # Configuration Composer
└── README.md                   # Documentation du projet
```

## ⚙️ Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache recommandé)
- Composer

## 🚀 Installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/votre-username/lebonplan.git
   cd lebonplan
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   ```
   Modifiez le fichier `.env` avec vos paramètres de base de données et autres configurations.

4. **Créer la base de données**
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE lebonplan;
   ```

5. **Importer la structure de la base de données**
   ```bash
   mysql -u root -p lebonplan < database/schema.sql
   ```

6. **Configurer le serveur web**
   Pour Apache, assurez-vous que le module mod_rewrite est activé. Le fichier `.htaccess` est déjà configuré dans le dossier public.

7. **Définir les permissions**
   ```bash
   chmod -R 777 storage/
   chmod -R 777 logs/
   ```

## 💻 Utilisation

1. Accédez à votre site via le navigateur
2. Créez un compte administrateur à l'aide des identifiants par défaut :
   - Email : `admin@lebonplan.fr`
   - Mot de passe : `Admin123!`
3. Commencez à configurer votre plateforme

## 🔐 Sécurité

Le projet intègre plusieurs couches de sécurité :
- Protection contre les injections SQL
- Validation CSRF
- Hachage des mots de passe avec bcrypt
- Filtrage des entrées utilisateur
- Gestion des sessions sécurisées
- Limitation des tentatives de connexion

## 📐 Architecture technique

LeBonPlan utilise une architecture MVC (Modèle-Vue-Contrôleur) personnalisée :

- **Modèles**: Gestion des données et logique métier
- **Vues**: Présentation et templates
- **Contrôleurs**: Gestion des requêtes et coordination

Le routage est géré par un système personnalisé qui supporte les routes dynamiques et les middlewares.

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/amazing-feature`)
3. Commitez vos changements (`git commit -m 'Add some amazing feature'`)
4. Pushez vers la branche (`git push origin feature/amazing-feature`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier LICENSE pour plus de détails.

## 👥 Équipe

- [Votre Nom] - Développeur principal
- [Membre de l'équipe 2] - Frontend Developer
- [Membre de l'équipe 3] - Backend Developer
- [Membre de l'équipe 4] - UX/UI Designer

## 📞 Contact

Pour toute question ou suggestion, n'hésitez pas à nous contacter à l'adresse suivante : contact@lebonplan.fr

---

Fait avec ❤️ par l'équipe LeBonPlan
