# 🚀 Facebook-like-site - Réseau Social Complet

Un réseau social moderne inspiré de Facebook, développé en **PHP**, **MySQL**, **JavaScript** et **HTML/CSS** avec une interface utilisateur intuitive et des fonctionnalités avancées.

![Version](https://img.shields.io/badge/version-2.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-green)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![License](https://img.shields.io/badge/license-MIT-yellow)

## 📋 **Fonctionnalités Principales**

### 🔐 **Authentification & Sécurité**

- ✅ **Inscription/Connexion** avec validation email
- ✅ **Récupération de mot de passe** par email
- ✅ **Sessions sécurisées** avec gestion des rôles
- ✅ **Hachage bcrypt** des mots de passe
- ✅ **Protection CSRF** et validation des données

### 📰 **Système de Posts**

- ✅ **Création de posts** avec texte et images
- ✅ **Likes/Dislikes** avec compteurs en temps réel
- ✅ **Commentaires** dynamiques sans rechargement
- ✅ **Partage de posts** avec attribution
- ✅ **Feed personnalisé** avec tri chronologique
- ✅ **Upload d'images** avec prévisualisation

### 👥 **Gestion d'Amis**

- ✅ **Invitations d'amis** depuis les profils
- ✅ **Acceptation/Refus** d'invitations
- ✅ **Liste d'amis** avec statuts
- ✅ **Suggestions d'amis** intelligentes
- ✅ **Gestion des relations** (ami, en attente, refusé)

### 💬 **Chat en Temps Réel**

- ✅ **Messagerie instantanée** avec AJAX polling
- ✅ **Recherche d'utilisateurs** pour chat
- ✅ **Upload de fichiers** dans les conversations
- ✅ **Notifications** de nouveaux messages
- ✅ **Historique des conversations**

### 👤 **Profils Utilisateurs**

- ✅ **Profil personnalisable** avec avatar
- ✅ **Modification des informations** en temps réel
- ✅ **Historique des posts** par utilisateur
- ✅ **Navigation entre profils** avec liens dynamiques
- ✅ **Statuts d'amitié** affichés sur les profils

### 🛡️ **Back-office Administrateur**

- ✅ **Panel d'administration** sécurisé
- ✅ **Gestion des utilisateurs** (bannir/débannir)
- ✅ **Statistiques** du site
- ✅ **Modération** des contenus
- ✅ **Contrôle d'accès** par rôles

## 🎨 **Interface Utilisateur**

### **Design Moderne**

- 🎨 **Thème sombre** avec variables CSS
- 📱 **Design responsive** pour tous les appareils
- ✨ **Animations fluides** et transitions
- 🎯 **Interface intuitive** inspirée de Facebook
- 🔤 **Icônes Font Awesome** pour une meilleure UX

### **Expérience Utilisateur**

- ⚡ **Mises à jour en temps réel** sans rechargement
- 🎭 **Feedback visuel** immédiat sur les actions
- 📊 **Compteurs dynamiques** pour likes/commentaires
- 🔄 **Auto-refresh** du feed toutes les 15 secondes
- 🎪 **Modales interactives** pour les actions

## 🏗️ **Architecture du Projet**

```
facebook-like-site/
├── 📄 index.html                    # Page d'accueil
├── 📄 config.php                    # Configuration centralisée
├── 📄 test_posts_system.php         # Script de test du système
├── 📁 api/                          # APIs Backend
│   ├── 📁 auth/                     # Authentification
│   │   ├── login.php               # Connexion
│   │   ├── register.php            # Inscription
│   │   ├── forgot_password.php     # Mot de passe oublié
│   │   ├── reset_password.php      # Réinitialisation
│   │   └── confirm_email.php       # Confirmation email
│   ├── 📁 posts/                    # Gestion des posts
│   │   ├── get_feed.php            # Récupération du feed
│   │   ├── add_post.php            # Création de post
│   │   ├── like_post.php           # Likes/dislikes
│   │   ├── add_comment.php         # Ajout de commentaires
│   │   ├── get_comments.php        # Récupération commentaires
│   │   └── share_post.php          # Partage de posts
│   ├── 📁 users/                    # Gestion utilisateurs
│   │   ├── get_user.php            # Infos utilisateur
│   │   ├── get_users.php           # Liste utilisateurs
│   │   ├── get_friends.php         # Liste d'amis
│   │   ├── send_friend_request.php # Invitation d'amis
│   │   ├── respond_friend_request.php # Réponse invitation
│   │   ├── update_profile.php      # Modification profil
│   │   └── update_avatar.php       # Upload avatar
│   ├── 📁 chat/                     # Système de chat
│   │   ├── get_conversations.php   # Conversations
│   │   ├── get_messages.php        # Messages
│   │   ├── send_message.php        # Envoi message
│   │   ├── upload_file.php         # Upload fichiers
│   │   └── get_unread_count.php    # Messages non lus
│   └── 📁 admin/                    # Back-office
│       ├── login.php               # Connexion admin
│       ├── get_users.php           # Gestion utilisateurs
│       ├── ban_user.php            # Bannir utilisateur
│       ├── unban_user.php          # Débannir utilisateur
│       └── get_stats.php           # Statistiques
├── 📁 vues/                         # Pages Frontend
│   ├── 📁 clients/                  # Interface utilisateur
│   │   ├── login.html              # Connexion
│   │   ├── register.html           # Inscription
│   │   ├── home.html               # Feed principal
│   │   ├── profile.html            # Profil utilisateur
│   │   ├── edit_profile.html       # Édition profil
│   │   ├── friends.html            # Gestion d'amis
│   │   ├── chat.html               # Messagerie
│   │   ├── forgot.html             # Mot de passe oublié
│   │   └── reset.html              # Réinitialisation
│   └── 📁 back-office/              # Interface admin
│       ├── login.html              # Connexion admin
│       ├── dashboard.html          # Tableau de bord
│       └── users.html              # Gestion utilisateurs
├── 📁 assets/                       # Ressources statiques
│   ├── 📁 css/                      # Styles
│   │   ├── style.css               # Styles principaux
│   │   ├── style-enhanced.css      # Styles avancés
│   │   └── admin.css               # Styles admin
│   ├── 📁 js/                       # JavaScript
│   │   ├── auth.js                 # Authentification
│   │   ├── home.js                 # Page d'accueil
│   │   ├── profile.js              # Profil utilisateur
│   │   ├── friends.js              # Gestion d'amis
│   │   ├── chat.js                 # Messagerie
│   │   ├── admin-dashboard.js      # Dashboard admin
│   │   └── admin-users.js          # Gestion utilisateurs admin
│   └── 📁 images/                   # Images
│       ├── avatars/                # Avatars utilisateurs
│       └── default-avatar.png      # Avatar par défaut
├── 📁 database/                     # Base de données
│   ├── schema.sql                  # Structure principale
│   ├── chat_tables.sql             # Tables chat
│   └── admin_roles.sql             # Rôles administrateurs
├── 📁 vendor/                       # Dépendances (Composer)
└── 📄 composer.json                 # Configuration Composer
```

## 🚀 **Installation Rapide**

### **1. Prérequis**

- **PHP 8.0+** avec extensions : PDO, MySQL, cURL, JSON, GD
- **MySQL 5.7+** ou MariaDB 10.2+
- **Serveur web** (Apache/Nginx)
- **Composer** (pour les dépendances)

### **2. Installation**

```bash
# 1. Cloner le projet
git clone https://github.com/votre-username/facebook-like-site.git
cd facebook-like-site

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données
mysql -u root -p
CREATE DATABASE facebook_like;
USE facebook_like;
source database/schema.sql;
source database/chat_tables.sql;
source database/admin_roles.sql;

# 4. Configurer l'application
cp config.php.example config.php
# Éditer config.php avec vos paramètres
```

### **3. Configuration**

Modifiez `config.php` :

```php
<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'facebook_like');
define('DB_USER', 'votre_utilisateur');
define('DB_PASS', 'votre_mot_de_passe');

// Configuration email (pour récupération de mot de passe)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'votre_email@gmail.com');
define('SMTP_PASS', 'votre_mot_de_passe_app');

// Configuration du site
define('SITE_URL', 'http://localhost/facebook-like-site');
define('SITE_NAME', 'Facebook-like');
?>
```

### **4. Créer un administrateur**

```bash
# Exécuter le script de création d'admin
php create_admin.php
```

## 🧪 **Tests et Démonstration**

### **Script de Test**

Exécutez `test_posts_system.php` pour vérifier l'installation :

```bash
php test_posts_system.php
```

### **Comptes de Test**

- **Utilisateur** : `test@example.com` / `password123`
- **Administrateur** : `admin@example.com` / `admin123`

### **Navigation**

- **Accueil** : `http://localhost/facebook-like-site/`
- **Connexion** : `http://localhost/facebook-like-site/vues/clients/login.html`
- **Back-office** : `http://localhost/facebook-like-site/vues/back-office/login.html`

## 🔧 **Fonctionnalités Techniques**

### **Sécurité**

- 🔒 **Validation des données** côté serveur et client
- 🛡️ **Protection CSRF** sur tous les formulaires
- 🔐 **Hachage bcrypt** des mots de passe
- 🎭 **Sessions sécurisées** avec régénération d'ID
- 👮 **Contrôle d'accès** par rôles (user/admin)

### **Performance**

- ⚡ **Requêtes SQL optimisées** avec JOINs
- 📄 **Pagination** des résultats
- 🗜️ **Compression GZIP** activée
- 💾 **Cache navigateur** pour les assets statiques
- 🔄 **AJAX polling** pour les mises à jour

### **UX/UI**

- 📱 **Design responsive** mobile-first
- 🎨 **Thème sombre** avec variables CSS
- ✨ **Animations CSS** fluides
- 🎯 **Interface intuitive** inspirée de Facebook
- 🔔 **Feedback utilisateur** immédiat

## 🛠️ **Technologies Utilisées**

### **Backend**

- **PHP 8.0+** : Langage principal du serveur
- **MySQL** : Base de données relationnelle
- **PDO** : Accès sécurisé aux données
- **PHPMailer** : Envoi d'emails
- **Composer** : Gestion des dépendances

### **Frontend**

- **HTML5** : Structure sémantique
- **CSS3** : Styles avec variables et animations
- **JavaScript ES6+** : Interactivité moderne
- **Font Awesome 6** : Icônes vectorielles
- **AJAX** : Communication asynchrone

### **Outils de Développement**

- **Git** : Contrôle de version
- **Apache/Nginx** : Serveur web
- **Composer** : Gestion des dépendances PHP

## 📊 **Statistiques du Projet**

- **📁 Fichiers** : 80+
- **📝 Lignes de code** : 25,000+
- **🎯 Fonctionnalités** : 30+
- **🌐 Pages** : 20+
- **🔌 APIs** : 35+
- **🎨 Composants UI** : 50+

## 🎯 **Fonctionnalités Avancées**

### **Système de Posts**

- 📝 **Création riche** avec texte et images
- 👍👎 **Likes/Dislikes** avec toggle
- 💬 **Commentaires** en temps réel
- 📤 **Partage** avec attribution
- 🖼️ **Upload d'images** avec prévisualisation

### **Gestion d'Amis**

- 👥 **Invitations** depuis les profils
- ✅❌ **Acceptation/Refus** d'invitations
- 📋 **Liste d'amis** avec statuts
- 🧠 **Suggestions** intelligentes
- 🔄 **Gestion des relations** complète

### **Chat en Temps Réel**

- 💬 **Messagerie instantanée**
- 🔍 **Recherche d'utilisateurs**
- 📎 **Upload de fichiers**
- 🔔 **Notifications** de nouveaux messages
- 📚 **Historique** des conversations

## 🤝 **Contribution**

Les contributions sont les bienvenues ! Voici comment contribuer :

1. **Fork** le projet
2. **Créez** une branche pour votre fonctionnalité
3. **Commitez** vos changements
4. **Poussez** vers la branche
5. **Ouvrez** une Pull Request

## 📄 **Licence**

Ce projet est sous licence **MIT**. Voir le fichier `LICENSE` pour plus de détails.

## 🙏 **Remerciements**

- **Facebook** pour l'inspiration de l'interface
- **Font Awesome** pour les icônes
- **PHPMailer** pour l'envoi d'emails
- **Composer** pour la gestion des dépendances

## 📞 **Support**

Pour toute question ou problème :

- 📧 **Email** : support@example.com
- 🐛 **Issues** : [GitHub Issues](https://github.com/votre-username/facebook-like-site/issues)
- 📖 **Documentation** : [Wiki](https://github.com/votre-username/facebook-like-site/wiki)

---

**⭐ N'oubliez pas de donner une étoile au projet si vous l'aimez !**
