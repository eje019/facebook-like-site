<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.html');
    exit;
}
$adminNom = $_SESSION['admin_nom'] ?? '';
$adminPrenom = $_SESSION['admin_prenom'] ?? '';
$adminEmail = $_SESSION['admin_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Utilisateurs Admin - Facebook-like</title>
    <link rel="stylesheet" href="../../assets/css/admin.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
  </head>
  <body>
    <div class="admin-layout">
      <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
          <div class="admin-sidebar-logo">
            <i class="fab fa-facebook fa-2x"></i>
          </div>
          <div class="admin-sidebar-title">Facebook-like Admin</div>
        </div>
        <nav class="admin-sidebar-nav">
          <a href="dashboard.php" class="admin-nav-item"
            ><i class="fa-solid fa-gauge"></i> Dashboard</a
          >
          <a href="users.php" class="admin-nav-item active"
            ><i class="fa-solid fa-users"></i> Utilisateurs</a
          >
          <a href="#" class="admin-nav-item"
            ><i class="fa-solid fa-newspaper"></i> Posts</a
          >
          <a href="#" class="admin-nav-item"
            ><i class="fa-solid fa-comments"></i> Commentaires</a
          >
          <a href="#" class="admin-nav-item"
            ><i class="fa-solid fa-thumbs-up"></i> Likes</a
          >
        </nav>
      </aside>
      <main class="admin-main">
        <header class="admin-header">
          <h1>Utilisateurs</h1>
          <div class="admin-user-info">
            <div class="admin-user-avatar" id="admin-avatar">
              <i class="fa-solid fa-user-shield"></i>
            </div>
            <span id="admin-name">
              <?php echo htmlspecialchars(trim($adminPrenom . ' ' . $adminNom)); ?><br />
              <small style="color: var(--color-text-muted); font-size: 0.95em">
                <?php echo htmlspecialchars($adminEmail); ?>
              </small>
            </span>
            <form
              method="post"
              action="../../api/admin/logout.php"
              style="display: inline"
            >
              <button class="admin-logout-btn" type="submit">
                <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
              </button>
            </form>
          </div>
        </header>
        <section>
          <h2>Gestion des utilisateurs</h2>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2em;">
            <input id="user-search" class="admin-search" type="text" placeholder="Rechercher un utilisateur..." style="max-width: 320px;" />
            <button id="add-user-btn" class="admin-btn" style="background: var(--color-primary); color: #fff; font-weight: 600; display: none;">
              <i class="fa-solid fa-user-plus"></i> Ajouter un utilisateur
            </button>
          </div>
          <div id="users-loader" style="display:none; text-align:center; margin-bottom:1em;">
            <i class="fa fa-spinner fa-spin" style="font-size:1.7em; color: var(--color-primary);"></i>
          </div>
          <div style="overflow-x:auto;">
            <table id="users-table" class="admin-table" style="width:100%; min-width:700px;">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Rôle</th>
                  <th>Statut</th>
                  <th>Date d'inscription</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Rempli dynamiquement par JS -->
              </tbody>
            </table>
          </div>
          <div id="users-pagination" class="admin-pagination"></div>
        </section>
      </main>
    </div>
    <script src="../../assets/js/admin-users.js"></script>
  </body>
</html>
