// JS de gestion dynamique des commentaires du back-office

document.addEventListener("DOMContentLoaded", function () {
  // Variables globales pour la pagination et la recherche
  let currentPage = 1;
  let currentSearch = "";
  let totalPages = 1;
  loadComments();
  setupEventListeners();

  // --- Gestion de la recherche en temps réel ---
  function setupEventListeners() {
    const searchInput = document.getElementById("comment-search");
    if (searchInput) {
      let searchTimeout;
      searchInput.addEventListener("input", function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          currentSearch = this.value.trim();
          currentPage = 1;
          loadComments();
        }, 300);
      });
    }
  }

  // --- Chargement des commentaires via l'API ---
  async function loadComments() {
    const loader = document.getElementById("comments-loader");
    if (loader) loader.style.display = "block";
    try {
      const params = new URLSearchParams({
        page: currentPage,
        search: currentSearch,
      });
      const response = await fetch(
        `../../api/admin/get_comments.php?${params}`,
        {
          credentials: "include",
        }
      );
      const data = await response.json();
      if (data.success) {
        displayComments(data.comments);
        displayPagination(data.page, data.total_pages, data.total);
        totalPages = data.total_pages;
        displayCommentStats(data.top_posts, data.top_users);
      } else {
        showMessage(
          data.error || "Erreur lors du chargement des commentaires",
          "error"
        );
      }
    } catch (error) {
      showMessage("Erreur de connexion au serveur", "error");
    } finally {
      if (loader) loader.style.display = "none";
    }
  }

  // --- Affichage du tableau des commentaires ---
  function displayComments(comments) {
    const tbody = document.querySelector("#comments-table tbody");
    if (!tbody) return;
    if (comments.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" style="text-align: center; padding:40px; color: #888;">
            <i class="fa-solid fa-comments fa-2x" style="margin-bottom: 10px; display: block;"></i>
            Aucun commentaire trouvé
          </td>
        </tr>
      `;
      return;
    }
    // Génère chaque ligne du tableau
    tbody.innerHTML = comments
      .map(
        (c) => `
          <tr>
            <td>${c.id}</td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #1877f2; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                  ${getInitials(c.prenom + " " + c.nom)}
                </div>
                ${c.prenom} ${
          c.nom
        } <br><span style="color:var(--color-text-muted); font-size:0.95em;">${
          c.email
        }</span>
              </div>
            </td>
            <td>
              <span title="ID: ${c.post_id}">${
          c.post_content.length > 40
            ? c.post_content.substring(0, 40) + "..."
            : c.post_content
        }</span>
            </td>
            <td>${
              c.comment_content.length > 60
                ? c.comment_content.substring(0, 60) + "..."
                : c.comment_content
            }</td>
            <td>${formatDate(c.created_at)}</td>
            <td>
              <button class="admin-btn admin-btn-delete" onclick="deleteComment(${
                c.id
              })" title="Supprimer le commentaire"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>
        `
      )
      .join("");
  }

  // --- Affichage de la pagination ---
  function displayPagination(currentPage, totalPages, total) {
    const pagination = document.getElementById("comments-pagination");
    if (!pagination) return;
    if (totalPages <= 1) {
      pagination.innerHTML = "";
      return;
    }
    let html = "";
    html += `<button ${
      currentPage === 1 ? "disabled" : ""
    } onclick="changeCommentPage(${
      currentPage - 1
    })"><i class="fa-solid fa-chevron-left"></i></button>`;
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    if (startPage > 1) {
      html += `<button onclick="changeCommentPage(1)">1</button>`;
      if (startPage > 2) {
        html += `<span>...</span>`;
      }
    }
    for (let i = startPage; i <= endPage; i++) {
      html += `<button class="${
        i === currentPage ? "current" : ""
      }" onclick="changeCommentPage(${i})">${i}</button>`;
    }
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        html += `<span>...</span>`;
      }
      html += `<button onclick="changeCommentPage(${totalPages})">${totalPages}</button>`;
    }
    html += `<button ${
      currentPage === totalPages ? "disabled" : ""
    } onclick="changeCommentPage(${
      currentPage + 1
    })"><i class="fa-solid fa-chevron-right"></i></button>`;
    pagination.innerHTML = html;
  }

  // --- Fonction globale pour changer de page ---
  window.changeCommentPage = function (page) {
    if (page >= 1 && page <= totalPages) {
      currentPage = page;
      loadComments();
    }
  };

  // --- Suppression d'un commentaire avec confirmation ---
  window.deleteComment = async function (commentId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce commentaire ?")) {
      try {
        const response = await fetch(`../../api/admin/delete_comment.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ comment_id: commentId }),
        });
        const data = await response.json();
        if (data.success) {
          showMessage(data.message || "Commentaire supprimé !", "success");
          loadComments();
        } else {
          showMessage(data.error || "Erreur lors de la suppression", "error");
        }
      } catch (e) {
        showMessage("Erreur serveur", "error");
      }
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

  // --- Affichage des statistiques (top posts commentés, top commentateurs) ---
  function displayCommentStats(topPosts, topUsers) {
    const statsDiv = document.getElementById("comments-stats");
    if (!statsDiv) return;
    let html = "<div style='display:flex; gap:2em; flex-wrap:wrap;'>";
    // Top posts commentés
    html += `<div><h3>Top posts les plus commentés</h3><ol style='padding-left:1.2em;'>`;
    if (topPosts && topPosts.length > 0) {
      topPosts.forEach((post) => {
        html += `<li><b>ID #${post.id}</b> (${
          post.total_comments
        } commentaires)<br><span style='color:var(--color-text-muted);font-size:0.97em;'>${
          post.content.length > 60
            ? post.content.substring(0, 60) + "..."
            : post.content
        }</span></li>`;
      });
    } else {
      html += `<li>Aucun post</li>`;
    }
    html += `</ol></div>`;
    // Top commentateurs
    html += `<div><h3>Top commentateurs</h3><ol style='padding-left:1.2em;'>`;
    if (topUsers && topUsers.length > 0) {
      topUsers.forEach((user) => {
        html += `<li><b>${user.prenom} ${user.nom}</b> (${user.total_comments} commentaires)<br><span style='color:var(--color-text-muted);font-size:0.97em;'>${user.email}</span></li>`;
      });
    } else {
      html += `<li>Aucun utilisateur</li>`;
    }
    html += `</ol></div>`;
    html += "</div>";
    statsDiv.innerHTML = html;
  }
});
