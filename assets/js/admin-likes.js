// JS de gestion dynamique des likes du back-office

document.addEventListener("DOMContentLoaded", function () {
  // Variables globales pour la pagination et la recherche
  let currentPage = 1;
  let currentSearch = "";
  let totalPages = 1;
  loadLikes();
  setupEventListeners();

  // --- Gestion de la recherche en temps réel ---
  function setupEventListeners() {
    const searchInput = document.getElementById("like-search");
    if (searchInput) {
      let searchTimeout;
      searchInput.addEventListener("input", function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          currentSearch = this.value.trim();
          currentPage = 1;
          loadLikes();
        }, 300);
      });
    }
  }

  // --- Chargement des likes via l'API ---
  async function loadLikes() {
    const loader = document.getElementById("likes-loader");
    if (loader) loader.style.display = "block";
    try {
      const params = new URLSearchParams({
        page: currentPage,
        search: currentSearch,
      });
      const response = await fetch(`../../api/admin/get_likes.php?${params}`, {
        credentials: "include",
      });
      const data = await response.json();
      if (data.success) {
        displayLikes(data.likes);
        displayPagination(data.page, data.total_pages, data.total);
        totalPages = data.total_pages;
        displayLikeStats(data.top_posts, data.top_users);
      } else {
        showMessage(
          data.error || "Erreur lors du chargement des likes",
          "error"
        );
      }
    } catch (error) {
      showMessage("Erreur de connexion au serveur", "error");
    } finally {
      if (loader) loader.style.display = "none";
    }
  }

  // --- Affichage du tableau des likes ---
  function displayLikes(likes) {
    const tbody = document.querySelector("#likes-table tbody");
    if (!tbody) return;
    if (likes.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="4" style="text-align: center; padding:40px; color: #888;">
            <i class="fa-solid fa-thumbs-up fa-2x" style="margin-bottom: 10px; display: block;"></i>
            Aucun like trouvé
          </td>
        </tr>
      `;
      return;
    }
    // Génère chaque ligne du tableau
    tbody.innerHTML = likes
      .map(
        (like) => `
          <tr>
            <td>${like.id}</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #42b72a; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                  ${getInitials(like.prenom + " " + like.nom)}
                </div>
                ${like.prenom} ${
          like.nom
        } <br><span style="color:var(--color-text-muted); font-size:0.95em;">${
          like.email
        }</span>
              </div>
            </td>
            <td>
              <span title="ID: ${like.post_id}">${
          like.content.length > 40
            ? like.content.substring(0, 40) + "..."
            : like.content
        }</span>
            </td>
            <td>${formatDate(like.created_at)}</td>
          </tr>
        `
      )
      .join("");
  }

  // --- Affichage de la pagination ---
  function displayPagination(currentPage, totalPages, total) {
    const pagination = document.getElementById("likes-pagination");
    if (!pagination) return;
    if (totalPages <= 1) {
      pagination.innerHTML = "";
      return;
    }
    let html = "";
    html += `<button ${
      currentPage === 1 ? "disabled" : ""
    } onclick="changeLikePage(${
      currentPage - 1
    })"><i class="fa-solid fa-chevron-left"></i></button>`;
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    if (startPage > 1) {
      html += `<button onclick="changeLikePage(1)">1</button>`;
      if (startPage > 2) {
        html += `<span>...</span>`;
      }
    }
    for (let i = startPage; i <= endPage; i++) {
      html += `<button class="${
        i === currentPage ? "current" : ""
      }" onclick="changeLikePage(${i})">${i}</button>`;
    }
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        html += `<span>...</span>`;
      }
      html += `<button onclick="changeLikePage(${totalPages})">${totalPages}</button>`;
    }
    html += `<button ${
      currentPage === totalPages ? "disabled" : ""
    } onclick="changeLikePage(${
      currentPage + 1
    })"><i class="fa-solid fa-chevron-right"></i></button>`;
    pagination.innerHTML = html;
  }

  // --- Fonction globale pour changer de page ---
  window.changeLikePage = function (page) {
    if (page >= 1 && page <= totalPages) {
      currentPage = page;
      loadLikes();
    }
  };

  // --- Utilitaire pour obtenir les initiales d'un nom ---
  function getInitials(name) {
    return name
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .substring(0, 2);
  }

  // --- Formatage de la date ---
  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("fr-FR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  // --- Affichage d'un message temporaire (succès/erreur) ---
  function showMessage(message, type) {
    const messageDiv = document.createElement("div");
    messageDiv.className = `admin-message ${type}`;
    messageDiv.textContent = message;
    messageDiv.style.position = "fixed";
    messageDiv.style.top = "20px";
    messageDiv.style.right = "20px";
    messageDiv.style.zIndex = 1000;
    document.body.appendChild(messageDiv);
    setTimeout(() => {
      messageDiv.remove();
    }, 3000);
  }

  // --- Affichage des statistiques (top posts, top utilisateurs) ---
  function displayLikeStats(topPosts, topUsers) {
    const statsDiv = document.getElementById("likes-stats");
    if (!statsDiv) return;
    let html = "<div style='display:flex; gap:2em; flex-wrap:wrap;'>";
    // Top posts
    html += `<div><h3>Top posts les plus likés</h3><ol style='padding-left:1.2em;'>`;
    if (topPosts && topPosts.length > 0) {
      topPosts.forEach((post) => {
        html += `<li><b>ID #${post.id}</b> (${
          post.total_likes
        } likes)<br><span style='color:var(--color-text-muted);font-size:0.97em;'>${
          post.content.length > 60
            ? post.content.substring(0, 60) + "..."
            : post.content
        }</span></li>`;
      });
    } else {
      html += `<li>Aucun post</li>`;
    }
    html += `</ol></div>`;
    // Top utilisateurs
    html += `<div><h3>Top utilisateurs les plus actifs</h3><ol style='padding-left:1.2em;'>`;
    if (topUsers && topUsers.length > 0) {
      topUsers.forEach((user) => {
        html += `<li><b>${user.prenom} ${user.nom}</b> (${user.total_likes} likes)<br><span style='color:var(--color-text-muted);font-size:0.97em;'>${user.email}</span></li>`;
      });
    } else {
      html += `<li>Aucun utilisateur</li>`;
    }
    html += `</ol></div>`;
    html += "</div>";
    statsDiv.innerHTML = html;
  }
});
