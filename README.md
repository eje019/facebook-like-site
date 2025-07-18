# 🚀 Facebook-like-site - Réseau Social

Un réseau social complet inspiré de Facebook, développé en PHP, MySQL, JavaScript et HTML/CSS.

## 📋 **Fonctionnalités**

### ✅ **Modules Implémentés**

- **🔐 Authentification** : Inscription, connexion, réinitialisation de mot de passe
- **📰 Feed de Posts** : Création, likes/dislikes, commentaires, upload d'images
- **👥 Gestion d'Amis** : Invitations, acceptation, liste d'amis
- **👤 Profil Personnel** : Modification des informations, photo de profil
- **💬 Chat en Temps Réel** : Messagerie instantanée avec WebSocket
- **🛡️ Back-office** : Panel d'administration complet

### 🎨 **Interface Moderne**

- Design responsive et animations fluides
- CSS avec variables et transitions modernes
- Interface utilisateur intuitive
- Support mobile complet

## 🏗️ **Structure du Projet**

````
facebook-like-site/
├── 📄 index.html              # Page d'accueil avec navigation
├── 📄 config.php              # Configuration centralisée
├── 📄 .htaccess               # Configuration serveur
├── 📁 api/                    # APIs PHP
│   ├── auth_*.php            # Authentification
│   ├── get_*.php             # Récupération de données
│   ├── admin_*.php           # APIs back-office
│   └── db_connection.php     # Connexion base de données
├── 📁 vues/clients/           # Pages utilisateur
│   ├── login.html            # Connexion
│   ├── register.html         # Inscription
│   ├── home.html             # Feed principal
│   ├── profile.html          # Profil utilisateur
│   ├── friends.html          # Gestion d'amis
│   └── chat.html             # Messagerie
├── 📁 admin/                  # Back-office
│   ├── login.html            # Connexion admin
│   ├── dashboard.html        # Tableau de bord
│   ├── users.html            # Gestion utilisateurs
│   ├── user_posts.html       # Posts d'utilisateur
│   ├── css/                  # Styles admin
│   └── js/                   # JavaScript admin
├── 📁 assets/                 # Ressources statiques
│   ├── css/                  # Styles CSS
│   ├── js/                   # JavaScript
│   └── images/               # Images
├── 📁 uploads/                # Fichiers uploadés
│   ├── posts/                # Images des posts
│   ├── profiles/             # Photos de profil
│   └── chat/                 # Fichiers de chat
├── 📁 database/               # Scripts base de données

## 🚀 **Installation et Configuration**

### **1. Prérequis**

- PHP 7.4+ avec extensions : PDO, MySQL, cURL, JSON
- MySQL 5.7+ ou MariaDB 10.2+
- Serveur web (Apache/Nginx)
- Composer (optionnel)

### **2. Installation**

```bash
# 1. Cloner le projet
git clone [https://github.com/eje019/facebook-like-site]
cd facebook-like-site

# 2. Configurer la base de données
mysql -u root -p
CREATE DATABASE facebook_like;
USE facebook_like;

# 3. Importer la structure
source database/facebook-like-site.sql;


### **3. Configuration**

Modifiez `config.php` selon votre environnement :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'facebook-like-site');
define('DB_USER', 'root');
define('DB_PASS', '');
````

## 🧪 **Tests et Démonstration**

### **Tests Automatisés**

### **Comptes de Test**

- **Utilisateur** : `test@example.com` / `password`
- **Admin** : `admin@example.com` / `password`
- **Modérateur** : `mod@example.com` / `password`

### **Navigation**

- **Page d'accueil** : `http://localhost/facebook-like-site/`
- **Connexion** : `http://localhost/facebook-like-site/vues/clients/login.html`
- **Back-office** : `http://localhost/facebook-like-site/api/admin/login.html`

## 📊 **Fonctionnalités Techniques**

### **Sécurité**

- ✅ Validation des données côté serveur
- ✅ Protection CSRF
- ✅ Hachage des mots de passe (bcrypt)
- ✅ Sessions sécurisées
- ✅ Contrôle d'accès par rôles

### **Performance**

- ✅ Pagination des résultats
- ✅ Optimisation des requêtes SQL
- ✅ Compression GZIP
- ✅ Cache des fichiers statiques

### **UX/UI**

- ✅ Design responsive
- ✅ Petites Animations
- ✅ Interface intuitive
- ✅ Feedback utilisateur
- ✅ Gestion des erreurs

## 🛠️ **Technologies Utilisées**

### **Backend**

- **PHP 8.0+** : Langage principal
- **MySQL** : Base de données
- **PDO** : Accès aux données
- **WebSocket** : Chat temps réel

### **Frontend**

- **HTML** : Structure
- **CSS** : Styles et animations
- **JS** : Interactivité
- **Font Awesome** : Icônes

### **Outils**

- **Composer** : Gestion des dépendances
- **Git** : Versioning
- **Apache** : Serveur web

## 📈 **Statistiques du Projet**

- **Lignes de code** : ~17,000
- **Fichiers** : 50+
- **Fonctionnalités** : 20+
- **Pages** : 15+
- **APIs** : 25+

## 🎯 **Présentation**

## 🔧 **Développement**

### **Structure Respectée**

✅ **Frontend** : Pages dans `vues/clients/`
✅ **Backend** : APIs dans `api/`
✅ **Base de données** : Scripts dans `database/`
✅ **Assets** : CSS/JS dans `assets/`
✅ **Configuration** : Centralisée dans `config.php`
