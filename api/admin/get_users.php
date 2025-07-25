<?php
// --- Initialisation de la session et des entêtes ---
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// --- Vérification des droits d'accès (admin ou modérateur) ---
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], ['admin', 'moderator'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

// --- Connexion à la base de données ---
require_once '../../config.php';
$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// --- Gestion de la pagination et de la recherche ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$per_page = 10; // Nombre d'utilisateurs par page
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $per_page;

// --- Construction de la clause WHERE pour la recherche ---
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE name LIKE :search OR email LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// --- Compter le nombre total d'utilisateurs (pour la pagination) ---
$count_sql = "SELECT COUNT(*) as total FROM users $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total = (int)($count_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

// --- Récupérer les utilisateurs paginés ---
$sql = "SELECT id, name, email, created_at, confirmed, role, is_banned FROM users $where ORDER BY created_at DESC LIMIT :offset, :per_page";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Formater le statut selon le rôle et l'état de l'utilisateur ---
foreach ($users as &$user) {
    if ($user['is_banned']) {
        $user['statut'] = 'Banni';
    } elseif ($user['role'] === 'admin') {
        $user['statut'] = 'Administrateur';
    } elseif ($user['role'] === 'moderator') {
        $user['statut'] = 'Modérateur';
    } else {
        $user['statut'] = $user['confirmed'] ? 'Actif' : 'Non confirmé';
    }
    // Masquer certaines informations pour les modérateurs
    if ($_SESSION['admin_role'] === 'moderator') {
        unset($user['role']);
    }
    unset($user['confirmed']);
}

// --- Calcul du nombre total de pages ---
$total_pages = max(1, ceil($total / $per_page));

// --- Réponse JSON structurée ---
echo json_encode([
    'success' => true,
    'users' => $users,
    'total' => $total,
    'page' => $page,
    'per_page' => $per_page,
    'total_pages' => $total_pages
]); 