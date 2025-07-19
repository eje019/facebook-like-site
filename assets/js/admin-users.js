// JS de gestion dynamique des utilisateurs du back-office

document.addEventListener("DOMContentLoaded", function () {
  // --- Vérification de la session admin côté client ---
  const admin = JSON.parse(sessionStorage.getItem("admin"));
  if (!admin) {
    window.location.href = "login.html";
    return;
  }

  // --- Afficher le bouton 'Ajouter un utilisateur' si admin ---
  if (admin.role === "admin" && document.getElementById("add-user-btn")) {
    document.getElementById("add-user-btn").style.display = "inline-flex";
  }

  // Variables globales pour la pagination et la recherche
  let currentPage = 1;
  let currentSearch = "";
  let totalPages = 1;
  loadUsers();
  setupEventListeners();

  // --- Gestion de la recherche en temps réel ---
  function setupEventListeners() {
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

  // --- Chargement des utilisateurs via l'API ---
  async function loadUsers() {
    const loader = document.getElementById("users-loader");
    if (loader) loader.style.display = "block";
    try {
      const params = new URLSearchParams({
        page: currentPage,
        search: currentSearch,
      });
      const response = await fetch(`../../api/admin/get_users.php?${params}`, {
        credentials: "include",
      });
      const data = await response.json();
      if (data.success) {
        displayUsers(data.users);
        displayPagination(data.page, data.total_pages, data.total);
        totalPages = data.total_pages;
      } else {
        showMessage("Erreur lors du chargement des utilisateurs", "error");
      }
    } catch (error) {
      showMessage("Erreur de connexion au serveur", "error");
    } finally {
      if (loader) loader.style.display = "none";
    }
  }

  // --- Affichage du tableau des utilisateurs ---
  function displayUsers(users) {
    const tbody = document.querySelector("#users-table tbody");
    if (!tbody) return;
    if (users.length === 0) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding:40px; color: #888;">
                        <i class="fa-solid fa-users fa-2x" style="margin-bottom: 10px; display: block;"></i>
                        Aucun utilisateur trouvé
                    </td>
                </tr>
            `;
      return;
    }
    // Génère chaque ligne du tableau avec les actions possibles selon le rôle
    tbody.innerHTML = users
      .map((user) => {
        // Détermination des actions possibles (voir, posts, promotion, ban, suppression...)
        let actions = `
            <button class="admin-btn admin-btn-view" onclick="viewUser(${user.id})" title="Voir le profil">
              <i class="fa-solid fa-eye"></i>
            </button>
            <button class="admin-btn admin-btn-posts" onclick="viewUserPosts(${user.id})" title="Voir les posts">
              <i class="fa-solid fa-file-alt"></i>
            </button>
          `;
        const isSelf =
          admin && (user.id == admin.id || user.email === admin.email);
        // Gestion des rôles et actions
        if (!isSelf && admin.role === "admin") {
          if (user.role === "user") {
            actions += `<button class="admin-btn admin-btn-promote" onclick="promoteUser(${user.id}, 'moderator')" title="Promouvoir en modérateur"><i class="fa-solid fa-user-shield"></i></button>`;
            actions += `<button class="admin-btn admin-btn-promote" onclick="promoteUser(${user.id}, 'admin')" title="Promouvoir en administrateur"><i class="fa-solid fa-crown"></i></button>`;
          } else if (user.role === "moderator") {
            actions += `<button class="admin-btn admin-btn-demote" onclick="demoteUser(${user.id}, 'user')" title="Rétrograder en utilisateur"><i class="fa-solid fa-user"></i></button>`;
            actions += `<button class="admin-btn admin-btn-promote" onclick="promoteUser(${user.id}, 'admin')" title="Promouvoir en administrateur"><i class="fa-solid fa-crown"></i></button>`;
          } else if (user.role === "admin") {
            actions += `<button class="admin-btn admin-btn-demote" onclick="demoteUser(${user.id}, 'moderator')" title="Rétrograder en modérateur"><i class="fa-solid fa-user-shield"></i></button>`;
          }
          if (user.statut === "Banni") {
            actions += `<button class="admin-btn admin-btn-unban" onclick="unbanUser(${user.id})" title="Débannir"><i class="fa-solid fa-check"></i></button>`;
          } else {
            actions += `<button class="admin-btn admin-btn-ban" onclick="banUser(${user.id})" title="Bannir"><i class="fa-solid fa-ban"></i></button>`;
          }
          actions += `<button class="admin-btn admin-btn-delete" onclick="deleteUser(${user.id})" title="Supprimer"><i class="fa-solid fa-trash"></i></button>`;
        } else if (!isSelf && admin.role === "moderator") {
          if (user.role === "user") {
            if (user.statut === "Banni") {
              actions += `<button class="admin-btn admin-btn-unban" onclick="unbanUser(${user.id})" title="Débannir"><i class="fa-solid fa-check"></i></button>`;
            } else {
              actions += `<button class="admin-btn admin-btn-ban" onclick="banUser(${user.id})" title="Bannir"><i class="fa-solid fa-ban"></i></button>`;
            }
            actions += `<button class="admin-btn admin-btn-delete" onclick="deleteUser(${user.id})" title="Supprimer"><i class="fa-solid fa-trash"></i></button>`;
          }
        }
        return `
            <tr data-user-id="${user.id}">
              <td>${user.id}</td>
              <td>
                <div style="display: flex; align-items: center; gap: 8px;">
                  <div style="width: 32px; height: 32px; border-radius: 50%; background: #1877f2; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                    ${getInitials(
                      user.name || user.prenom || user.nom || user.email
                    )}
                  </div>
                  ${user.name || user.prenom + " " + user.nom || user.email}
                </div>
              </td>
              <td>${user.email}</td>
              <td>${
                user.role
                  ? user.role.charAt(0).toUpperCase() + user.role.slice(1)
                  : ""
              }</td>
              <td>
                <span class="user-status ${getStatusClass(user.statut)}">
                  ${user.statut}
                </span>
              </td>
              <td>${formatDate(user.created_at)}</td>
              <td>
                <div class="admin-table-actions">
                  ${actions}
                </div>
              </td>
            </tr>
          `;
      })
      .join("");
  }

  // --- Affichage de la pagination ---
  function displayPagination(currentPage, totalPages, total) {
    const pagination = document.getElementById("users-pagination");
    if (!pagination) return;
    if (totalPages <= 1) {
      pagination.innerHTML = "";
      return;
    }
    let html = "";
    html += `<button ${
      currentPage === 1 ? "disabled" : ""
    } onclick="changePage(${
      currentPage - 1
    })"><i class="fa-solid fa-chevron-left"></i></button>`;
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
    html += `<button ${
      currentPage === totalPages ? "disabled" : ""
    } onclick="changePage(${
      currentPage + 1
    })"><i class="fa-solid fa-chevron-right"></i></button>`;
    pagination.innerHTML = html;
  }

  // --- Fonctions utilitaires pour l'affichage ---
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

  // --- Fonctions globales pour les actions (pagination, voir profil, ban, promotion, suppression, etc.) ---
  window.changePage = function (page) {
    if (page >= 1 && page <= totalPages) {
      currentPage = page;
      loadUsers();
    }
  };

  window.viewUser = function (userId) {
    window.open(`../clients/profile.html?id=${userId}`, "_blank");
  };

  window.banUser = async function (userId) {
    if (confirm("Êtes-vous sûr de vouloir bannir cet utilisateur ?")) {
      try {
        const response = await fetch("../../api/admin/ban_user.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ user_id: userId }),
        });
        const data = await response.json();
        if (data.success) {
          showMessage(data.message || "Utilisateur banni !", "success");
          loadUsers();
        } else {
          showMessage(data.error || "Erreur lors du bannissement", "error");
        }
      } catch (e) {
        showMessage("Erreur serveur", "error");
      }
    }
  };

  window.unbanUser = async function (userId) {
    if (confirm("Êtes-vous sûr de vouloir débannir cet utilisateur ?")) {
      try {
        const response = await fetch("../../api/admin/unban_user.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ user_id: userId }),
        });
        const data = await response.json();
        if (data.success) {
          showMessage(data.message || "Utilisateur débanni !", "success");
          loadUsers();
        } else {
          showMessage(data.error || "Erreur lors du débannissement", "error");
        }
      } catch (e) {
        showMessage("Erreur serveur", "error");
      }
    }
  };

  window.deleteUser = async function (userId) {
    if (
      confirm(
        "Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible."
      )
    ) {
      try {
        const response = await fetch("../../api/admin/delete_user.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ user_id: userId }),
        });
        const data = await response.json();
        if (data.success) {
          showMessage(data.message || "Utilisateur supprimé !", "success");
          loadUsers();
        } else {
          showMessage(data.error || "Erreur lors de la suppression", "error");
        }
      } catch (e) {
        showMessage("Erreur serveur", "error");
      }
    }
  };

  window.promoteUser = async function (userId, role) {
    if (confirm(`Promouvoir cet utilisateur en ${role} ?`)) {
      try {
        const response = await fetch("../../api/admin/promote_user.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ user_id: userId, role: role }),
        });
        const data = await response.json();
        if (data.success) {
          showMessage(data.message || "Utilisateur promu !", "success");
          loadUsers();
        } else {
          showMessage(data.error || "Erreur lors de la promotion", "error");
        }
      } catch (e) {
        showMessage("Erreur serveur", "error");
      }
    }
  };

  window.demoteUser = async function (userId, role) {
    if (confirm(`Rétrograder cet utilisateur en ${role} ?`)) {
      try {
        const response = await fetch("../../api/admin/demote_user.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ user_id: userId, role: role }),
        });
        const data = await response.json();
        if (data.success) {
          showMessage(data.message || "Utilisateur rétrogradé !", "success");
          loadUsers();
        } else {
          showMessage(
            data.error || "Erreur lors de la rétrogradation",
            "error"
          );
        }
      } catch (e) {
        showMessage("Erreur serveur", "error");
      }
    }
  };

  window.viewUserPosts = function (userId) {
    window.open(`../admin/user_posts.html?id=${userId}`, "_blank");
  };
});
