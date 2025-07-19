<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email et mot de passe requis']);
        exit;
    }

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // On ne prend que les admins
    $stmt = $pdo->prepare("SELECT id, prenom, nom, email, password, role FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo json_encode(['success' => false, 'error' => 'Accès refusé ou identifiants invalides']);
        exit;
    }

    if (!password_verify($password, $admin['password'])) {
        echo json_encode(['success' => false, 'error' => 'Identifiants invalides']);
        exit;
    }

    // Créer la session admin
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_nom'] = $admin['nom'];
    $_SESSION['admin_prenom'] = $admin['prenom'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];

    // On ne retourne pas le mot de passe
    unset($admin['password']);

    echo json_encode([
        'success' => true,
        'admin' => $admin,
        'message' => 'Connexion réussie'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
