<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    // Validation des données
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email et mot de passe requis']);
        exit;
    }
    
    // Vérifier si l'utilisateur existe et a un rôle admin ou modérateur
    $query = "SELECT id, email, password, name, role FROM users WHERE email = ? AND role IN ('admin', 'moderator')";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        http_response_code(401);
        echo json_encode(['error' => 'Identifiants invalides ou accès non autorisé']);
        exit;
    }
    
    // Vérifier le mot de passe
    if (!password_verify($password, $admin['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Identifiants invalides']);
        exit;
    }
    
    // Créer la session admin/moderateur
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['role'] = $admin['role'];
    $_SESSION['is_admin'] = ($admin['role'] === 'admin');
    
    // Retourner les informations admin (sans le mot de passe)
    unset($admin['password']);
    echo json_encode([
       'success' => true,
       'admin' => $admin,
       'message' => 'Connexion réussie'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?> 