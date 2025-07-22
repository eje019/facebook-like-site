<?php
/**
 * Configuration principale du projet Facebook-like
 * Centralise tous les paramètres importants
 */

// Configuration de la base de données
define('DB_HOST', 'nue.domcloud.co');
define('DB_NAME', 'facebook_db');
define('DB_USER', 'facebook');
define('DB_PASS', 't6wOpC276(+m8D(EfG');
// Configuration de l'application
define('APP_NAME', 'Facebook-like');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/facebook-like-site');
define('APP_DEBUG', true);

// Configuration des emails
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'j20426048@gmail.com');
define('SMTP_PASS', 'faaz gmof ripm knes');
define('SMTP_FROM', 'noreply@facebook-like.com');
define('SMTP_FROM_NAME', 'Facebook-like');

// Configuration des uploads
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', 'uploads/');

// Configuration de sécurité
define('SESSION_LIFETIME', 3600); // 1 heure
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Configuration du chat
define('CHAT_WEBSOCKET_HOST', 'localhost');
define('CHAT_WEBSOCKET_PORT', 8080);

// Configuration des rôles
define('ROLE_USER', 'user');
define('ROLE_MODERATOR', 'moderator');
define('ROLE_ADMIN', 'admin');

// Messages d'erreur
define('ERROR_MESSAGES', [
    'db_connection' => 'Erreur de connexion à la base de données',
    'invalid_credentials' => 'Email ou mot de passe incorrect',
    'user_not_found' => 'Utilisateur non trouvé',
    'email_exists' => 'Cette adresse email est déjà utilisée',
    'invalid_email' => 'Adresse email invalide',
    'password_mismatch' => 'Les mots de passe ne correspondent pas',
    'file_too_large' => 'Le fichier est trop volumineux',
    'invalid_file_type' => 'Type de fichier non autorisé',
    'upload_failed' => 'Échec de l\'upload du fichier',
    'unauthorized' => 'Accès non autorisé',
    'session_expired' => 'Session expirée, veuillez vous reconnecter'
]);

// Messages de succès
define('SUCCESS_MESSAGES', [
    'registration' => 'Inscription réussie ! Vérifiez votre email.',
    'login' => 'Connexion réussie !',
    'logout' => 'Déconnexion réussie !',
    'profile_updated' => 'Profil mis à jour avec succès !',
    'password_changed' => 'Mot de passe modifié avec succès !',
    'post_created' => 'Post publié avec succès !',
    'post_deleted' => 'Post supprimé avec succès !',
    'friend_request_sent' => 'Demande d\'ami envoyée !',
    'friend_request_accepted' => 'Demande d\'ami acceptée !',
    'message_sent' => 'Message envoyé !'
]);

// Configuration des paginations
define('POSTS_PER_PAGE', 10);
define('USERS_PER_PAGE', 20);
define('COMMENTS_PER_PAGE', 15);
define('MESSAGES_PER_PAGE', 50);

// Configuration des notifications
define('NOTIFICATION_TYPES', [
    'friend_request' => 'Demande d\'ami',
    'friend_accepted' => 'Ami accepté',
    'new_comment' => 'Nouveau commentaire',
    'new_like' => 'Nouveau like',
    'new_message' => 'Nouveau message'
]);

// Configuration des timeouts
define('API_TIMEOUT', 30);
define('WEBSOCKET_TIMEOUT', 60);

// Configuration des logs
define('LOG_ENABLED', true);
define('LOG_PATH', 'logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Configuration du cache
define('CACHE_ENABLED', false);
define('CACHE_PATH', 'cache/');
define('CACHE_DURATION', 3600); // 1 heure

// Configuration des tests
define('TEST_MODE', false);
define('TEST_EMAIL', 'test@example.com');
define('TEST_PASSWORD', 'password');

// Fonction pour obtenir la configuration
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

// Fonction pour logger
function logMessage($level, $message, $context = []) {
    if (!getConfig('LOG_ENABLED')) {
        return;
    }
    
    $logFile = getConfig('LOG_PATH') . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message " . json_encode($context) . PHP_EOL;
    
    if (!is_dir(getConfig('LOG_PATH'))) {
        mkdir(getConfig('LOG_PATH'), 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Fonction pour nettoyer les logs anciens
function cleanOldLogs($days = 7) {
    $logPath = getConfig('LOG_PATH');
    if (!is_dir($logPath)) {
        return;
    }
    
    $files = glob($logPath . '*.log');
    $cutoff = time() - ($days * 24 * 60 * 60);
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoff) {
            unlink($file);
        }
    }
}

// Nettoyage automatique des logs (une fois par jour)
if (rand(1, 100) === 1) {
    cleanOldLogs();
}
?> 