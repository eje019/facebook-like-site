document.addEventListener("DOMContentLoaded", function () {
  // Vérifier si ladmin est connecté
  const admin = JSON.parse(sessionStorage.getItem("admin"));
  if (!admin) {
    window.location.href = "login.html";
    return;
  }

  // Afficher les informations de ladmin
  displayAdminInfo(admin);

  // Charger les statistiques
  loadStats();

  // Rafraîchissement automatique toutes les 10 secondes
  setInterval(loadStats, 10000);

  // Bouton "Rafraîchir"
  const refreshBtn = document.getElementById("refresh-stats-btn");
  if (refreshBtn) {
    refreshBtn.addEventListener("click", function () {
      loadStats();
    });
  }

  // Gérer la déconnexion
  setupLogout();
});

function displayAdminInfo(admin) {
  const adminName = document.getElementById("admin-name");
  const adminAvatar = document.getElementById("admin-avatar");

  if (adminName) {
    adminName.textContent = admin.name || admin.email;
  }

  if (adminAvatar) {
    // Utiliser les initiales de l'admin pour l'avatar
    const initials = getInitials(admin.name || admin.email);
    adminAvatar.textContent = initials;
  }
}

function getInitials(name) {
  return name
    .split(" ")
    .map((word) => word.charAt(0))
    .join("")
    .toUpperCase()
    .substring(0, 2);
}

async function loadStats() {
  const loader = document.getElementById("stats-loader");
  if (loader) loader.style.display = "inline-block";
  try {
    const response = await fetch("../../api/admin/get_stats.php", {
      credentials: "include",
    });
    const data = await response.json();

    if (data.success) {
      updateStats(data.stats);
    } else {
      console.error("Erreur lors du chargement des statistiques:", data.error);
      // Afficher des données par défaut en cas d'erreur
      updateStats({
        users: 0,
        posts: 0,
        comments: 0,
        likes: 0,
      });
    }
  } catch (error) {
    console.error("Erreur de connexion:", error);
    // Afficher des données par défaut en cas d'erreur
    updateStats({
      users: 0,
      posts: 0,
      comments: 0,
      likes: 0,
    });
  } finally {
    if (loader) loader.style.display = "none";
  }
}

function updateStats(stats) {
  // Mettre à jour les statistiques avec animation
  animateNumber("stat-users", stats.users);
  animateNumber("stat-posts", stats.posts);
  animateNumber("stat-comments", stats.comments);
  animateNumber("stat-likes", stats.likes);

  // Afficher les statistiques détaillées pour admin uniquement
  const admin = JSON.parse(sessionStorage.getItem("admin"));
  if (admin && admin.role === "admin" && stats.detailed) {
    // Afficher les cartes de stats détaillées
    document.querySelectorAll(".admin-only").forEach((card) => {
      card.style.display = "block";
    });

    // Mettre à jour les stats détaillées
    animateNumber("stat-recent-users-number", stats.recent_users || 0);
    animateNumber("stat-banned-users-number", stats.banned_users || 0);
    animateNumber("stat-admin-users-number", stats.admin_users || 0);
    animateNumber("stat-moderator-users-number", stats.moderator_users || 0);
  } else {
    // Masquer les cartes de stats détaillées pour les modérateurs
    document.querySelectorAll(".admin-only").forEach((card) => {
      card.style.display = "none";
    });
  }
}

function animateNumber(elementId, targetValue) {
  const element = document.getElementById(elementId);
  if (!element) return;

  const startValue = 0;
  const duration = 10; // 1 seconde
  const startTime = performance.now();

  function updateNumber(currentTime) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);

    // Fonction d'easing pour une animation fluide
    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
    const currentValue = Math.floor(
      startValue + (targetValue - startValue) * easeOutQuart
    );

    element.textContent = currentValue.toLocaleString();

    if (progress < 1) {
      requestAnimationFrame(updateNumber);
    }
  }

  requestAnimationFrame(updateNumber);
}

function setupLogout() {
  const logoutBtn = document.getElementById("admin-logout");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", async function () {
      try {
        // Appeler l'API de déconnexion
        const response = await fetch("../../api/admin/logout.php", {
          method: "POST",
          credentials: "include",
        });

        // Supprimer les données de session
        sessionStorage.removeItem("admin");

        // Rediriger vers la page de connexion
        window.location.href = "login.html";
      } catch (error) {
        console.error("Erreur lors de la déconnexion:", error);
        // Rediriger quand même
        sessionStorage.removeItem("admin");
        window.location.href = "login.html";
      }
    });
  }
}

// Rafraîchir les statistiques toutes les 5 minutes
setInterval(loadStats, 5 * 60 * 1000);
