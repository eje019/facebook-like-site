// Gestion dynamique des amis Facebook-like
// Navigation, affichage, actions (AJAX)
document.addEventListener("DOMContentLoaded", function () {
  // Vérifie la session utilisateur
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (!user) {
    window.location.href = "login.html";
    return;
  }

  // Navigation sidebar
  const sidebarLinks = document.querySelectorAll(".fb-sidebar-link");
  const sections = document.querySelectorAll(".friends-section");
  const emptyMsg = document.getElementById("friends-empty");

  function showSection(section) {
    sections.forEach((s) => (s.style.display = "none"));
    sidebarLinks.forEach((l) => l.classList.remove("active"));
    document.getElementById(`section-${section}`).style.display = "block";
    document
      .querySelector(`.fb-sidebar-link[data-section='${section}']`)
      .classList.add("active");
    emptyMsg.style.display = "none";
  }

  sidebarLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      showSection(this.dataset.section);
      if (this.dataset.section === "invitations") loadInvitations();
      if (this.dataset.section === "suggestions") loadSuggestions();
      if (this.dataset.section === "all") loadAllUsers();
      if (this.dataset.section === "friends") loadFriends();
    });
  });

  // Affichage initial
  showSection("invitations");
  loadInvitations();

  // Chargement des invitations reçues
  function loadInvitations() {
    fetch("../../api/users/get_users.php?pending=1&user_id=" + user.id)
      .then((r) => r.json())
      .then((data) => {
        const list = document.getElementById("invitations-list");
        if (
          !data.success ||
          !data.invitations ||
          data.invitations.length === 0
        ) {
          list.innerHTML =
            '<div style="color:var(--color-text-muted);text-align:center;">Aucune invitation reçue.</div>';
          return;
        }
        list.innerHTML = data.invitations
          .map(
            (u) => `
          <div class="friend-item">
            <img src="../../assets/images/${
              u.avatar || "default-avatar.png"
            }" class="fb-avatar" style="width:44px;height:44px;">
            <span class="friend-name">${u.prenom} ${u.nom}</span>
            <button class="fb-btn-accept" data-id="${u.id}">Accepter</button>
            <button class="fb-btn-refuse" data-id="${u.id}">Refuser</button>
            <a href="profile.html?user_id=${
              u.id
            }" class="fb-btn-profile">Profil</a>
          </div>
        `
          )
          .join("");
        // Actions accepter/refuser
        list.querySelectorAll(".fb-btn-accept").forEach((btn) => {
          btn.onclick = function () {
            respondInvitation(btn.dataset.id, true);
          };
        });
        list.querySelectorAll(".fb-btn-refuse").forEach((btn) => {
          btn.onclick = function () {
            respondInvitation(btn.dataset.id, false);
          };
        });
      });
  }

  function respondInvitation(otherId, accept) {
    fetch("../../api/users/respond_friend_request.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        user_id: user.id,
        friend_id: otherId,
        action: accept ? "accept" : "refuse",
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) loadInvitations();
        else alert(data.error || "Erreur lors de la réponse à l'invitation.");
      });
  }

  // Chargement des suggestions d'amis (utilisateurs non amis, non invités)
  function loadSuggestions() {
    fetch("../../api/users/get_users.php?suggest=1&user_id=" + user.id)
      .then((r) => r.json())
      .then((data) => {
        const list = document.getElementById("suggestions-list");
        if (
          !data.success ||
          !data.suggestions ||
          data.suggestions.length === 0
        ) {
          list.innerHTML =
            '<div style="color:var(--color-text-muted);text-align:center;">Aucune suggestion pour le moment.</div>';
          return;
        }
        list.innerHTML = data.suggestions
          .map(
            (u) => `
          <div class="friend-item">
            <img src="../../assets/images/${
              u.avatar || "default-avatar.png"
            }" class="fb-avatar" style="width:44px;height:44px;">
            <span class="friend-name">${u.prenom} ${u.nom}</span>
            <button class="fb-btn-add" data-id="${u.id}">Ajouter</button>
            <a href="profile.html?user_id=${
              u.id
            }" class="fb-btn-profile">Profil</a>
          </div>
        `
          )
          .join("");
        // Action ajouter
        list.querySelectorAll(".fb-btn-add").forEach((btn) => {
          btn.onclick = function () {
            sendInvitation(btn.dataset.id);
          };
        });
      });
  }

  function sendInvitation(otherId) {
    fetch("../../api/users/send_friend_request.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ user_id: user.id, friend_id: otherId }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) loadSuggestions();
        else alert(data.error || "Erreur lors de l'envoi de l'invitation.");
      });
  }

  // Chargement de tous les utilisateurs
  function loadAllUsers() {
    fetch("../../api/users/get_users.php?user_id=" + user.id)
      .then((r) => r.json())
      .then((data) => {
        const list = document.getElementById("all-users-list");
        if (!data.success || !data.users || data.users.length === 0) {
          list.innerHTML =
            '<div style="color:var(--color-text-muted);text-align:center;">Aucun utilisateur trouvé.</div>';
          return;
        }
        list.innerHTML = data.users
          .filter((u) => u.id != user.id)
          .map(
            (u) => `
          <div class="friend-item">
            <img src="../../assets/images/${
              u.avatar || "default-avatar.png"
            }" class="fb-avatar" style="width:44px;height:44px;">
            <span class="friend-name">${u.prenom} ${u.nom}</span>
            <a href="profile.html?user_id=${
              u.id
            }" class="fb-btn-profile">Profil</a>
          </div>
        `
          )
          .join("");
      });
  }

  // Chargement des amis
  function loadFriends() {
    fetch("../../api/users/get_friends.php?user_id=" + user.id)
      .then((r) => r.json())
      .then((data) => {
        const list = document.getElementById("friends-list");
        if (!data.success || !data.friends || data.friends.length === 0) {
          list.innerHTML =
            '<div style="color:var(--color-text-muted);text-align:center;">Aucun ami pour le moment.</div>';
          return;
        }
        list.innerHTML = data.friends
          .map(
            (u) => `
          <div class="friend-item">
            <img src="../../assets/images/${
              u.avatar || "default-avatar.png"
            }" class="fb-avatar" style="width:44px;height:44px;">
            <span class="friend-name">${u.prenom} ${u.nom}</span>
            <button class="fb-btn-remove" data-id="${u.id}">Supprimer</button>
            <a href="profile.html?user_id=${
              u.id
            }" class="fb-btn-profile">Profil</a>
          </div>
        `
          )
          .join("");
        // Action supprimer ami (optionnel, à brancher si API dispo)
        list.querySelectorAll(".fb-btn-remove").forEach((btn) => {
          btn.onclick = function () {
            if (confirm("Supprimer cet ami ?")) {
              fetch("../../api/users/remove_friend.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                  user_id: user.id,
                  other_user_id: btn.dataset.id,
                }),
              })
                .then((r) => r.json())
                .then((data) => {
                  if (data.success) loadFriends();
                  else alert(data.error || "Erreur lors de la suppression.");
                });
            }
          };
        });
      });
  }

  // Déconnexion navbar
  const logoutBtnNav = document.getElementById("logout-btn-navbar");
  if (logoutBtnNav) {
    logoutBtnNav.onclick = function () {
      sessionStorage.removeItem("user");
      window.location.href = "login.html";
    };
  }

  // Lien profil dynamique
  const profileLink = document.getElementById("navbar-profile-link");
  if (profileLink) {
    profileLink.href = `profile.html?user_id=${user.id}`;
  }
});
