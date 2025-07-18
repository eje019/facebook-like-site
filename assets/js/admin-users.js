document.addEventListener("DOMContentLoaded", function () {
  // Vérifier si ladmin est connecté
  const admin = JSON.parse(sessionStorage.getItem("admin"));
  if (!admin) {
    window.location.href = "login.html";
    return;
  }

  // Variables globales
  let currentPage = 1;
  let currentSearch = "";
  let totalPages = 1; // Initialisation
  loadUsers();
  setupEventListeners();

  function setupEventListeners() {
    // Recherche en temps réel
    const searchInput = document.getElementById("user-search");
    if (searchInput) {
      let searchTimeout;
      searchInput.addEventListener("input", function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          currentSearch = this.value.trim();
          currentPage = 1;
          loadUsers();
        }, 300);
      });
    }
  }

  async function loadUsers() {
    try {
      const params = new URLSearchParams({
        page: currentPage,
        search: currentSearch,
      });

      const response = await fetch(`../../api/admin/get_users.php?${params}`);
      const data = await response.json();

      if (data.success) {
        displayUsers(data.users);
        displayPagination(data.page, data.total_pages, data.total);
        totalPages = data.total_pages;
      } else {
        console.error(
          "Erreur lors du chargement des utilisateurs:",
          data.error
        );
        showMessage("Erreur lors du chargement des utilisateurs", "error");
      }
    } catch (error) {
      console.error("Erreur de connexion:", error);
      showMessage("Erreur de connexion au serveur", "error");
    }
  }

  function displayUsers(users) {
    const tbody = document.querySelector("#users-table tbody");
    if (!tbody) return;

    if (users.length === 0) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding:40px; color: #888;">
                        <i class="fa-solid fa-users fa-2x" style="margin-bottom: 10px; display: block;"></i>
                        Aucun utilisateur trouvé
                    </td>
                </tr>
            `;
      return;
    }

    tbody.innerHTML = users
      .map(
        (user) => `
            <tr data-user-id="${user.id}">
                <td>${user.id}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #1877f2; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                            ${getInitials(user.name)}
                        </div>
                        ${user.name}
                    </div>
                </td>
                <td>${user.email}</td>
                <td>${formatDate(user.created_at)}</td>
                <td>
                    <span class="user-status ${getStatusClass(user.statut)}">
                        ${user.statut}
                    </span>
                </td>
                <td>
                    <div class="admin-table-actions">
                        <button class="admin-btn admin-btn-view" onclick="viewUser(${
                          user.id
                        })" title="Voir le profil">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <button class="admin-btn admin-btn-posts" onclick="viewUserPosts(${
                          user.id
                        })" title="Voir les posts">
                            <i class="fa-solid fa-file-alt"></i>
                        </button>
                        ${
                          user.statut === "Actif"
                            ? `<button class="admin-btn admin-btn-ban" onclick="banUser(${user.id})" title="Bannir l'utilisateur">
                                <i class="fa-solid fa-ban"></i>
                            </button>`
                            : `<button class="admin-btn admin-btn-unban" onclick="unbanUser(${user.id})" title="Débannir l'utilisateur">
                                <i class="fa-solid fa-check"></i>
                            </button>`
                        }
                        <button class="admin-btn admin-btn-delete" onclick="deleteUser(${
                          user.id
                        })" title="Supprimer l'utilisateur">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `
      )
      .join("");
  }

  function displayPagination(currentPage, totalPages, total) {
    const pagination = document.getElementById("users-pagination");
    if (!pagination) return;

    if (totalPages <= 1) {
      pagination.innerHTML = "";
      return;
    }

    let html = "";

    // Bouton précédent
    html += `<button ${
      currentPage === 1 ? "disabled" : ""
    } onclick="changePage(${currentPage - 1})">
            <i class="fa-solid fa-chevron-left"></i>
        </button>`;

    // Pages
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    if (startPage > 1) {
      html += `<button onclick="changePage(1)">1</button>`;
      if (startPage > 2) {
        html += `<span>...</span>`;
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      html += `<button class="${
        i === currentPage ? "current" : ""
      }" onclick="changePage(${i})">${i}</button>`;
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        html += `<span>...</span>`;
      }
      html += `<button onclick="changePage(${totalPages})">${totalPages}</button>`;
    }

    // Bouton suivant
    html += `<button ${
      currentPage === totalPages ? "disabled" : ""
    } onclick="changePage(${currentPage + 1})">
            <i class="fa-solid fa-chevron-right"></i>
        </button>`;

    pagination.innerHTML = html;
  }

  // Fonctions utilitaires
  function getInitials(name) {
    return name
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .substring(0, 2);
  }

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

  function getStatusClass(statut) {
    switch (statut) {
      case "Admin":
        return "status-admin";
      case "Actif":
        return "status-active";
      case "Non confirmé":
        return "status-pending";
      default:
        return "status-banned";
    }
  }

  function showMessage(message, type) {
    // Créer un message temporaire
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

  // Fonctions globales pour les actions
  window.changePage = function (page) {
    if (page >= 1 && page <= totalPages) {
      currentPage = page;
      loadUsers();
    }
  };

  window.viewUser = function (userId) {
    // Ouvrir le profil utilisateur dans un nouvel onglet
    window.open(`../clients/profile.html?id=${userId}`, "_blank");
  };

  window.banUser = function (userId) {
    if (confirm("Êtes-vous sûr de vouloir bannir cet utilisateur ?")) {
      // TODO: Implémenter l'API de bannissement
      showMessage("Fonctionnalité de bannissement à implémenter", "info");
    }
  };

  window.unbanUser = function (userId) {
    if (confirm("Êtes-vous sûr de vouloir débannir cet utilisateur ?")) {
      // TODO: Implémenter l'API de débannissement
      showMessage("Fonctionnalité de débannissement à implémenter", "info");
    }
  };

  window.deleteUser = function (userId) {
    if (
      confirm(
        "Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible."
      )
    ) {
      // TODO: Implémenter l'API de suppression
      showMessage("Fonctionnalité de suppression à implémenter", "info");
    }
  };

  window.viewUserPosts = function (userId) {
    // Ouvrir la page des posts de l'utilisateur
    window.open(`../admin/user_posts.html?id=${userId}`, "_blank");
  };
});
