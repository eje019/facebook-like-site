document.addEventListener("DOMContentLoaded", function () {
  // Sécurité : redirection si non connecté
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (!user) {
    window.location.href = "login.html";
    return;
  }

  // Déconnexion
  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.onclick = function () {
      sessionStorage.removeItem("user");
      window.location.href = "login.html";
    };
  }

  // Navigation sidebar
  document.querySelectorAll(".fb-friends-sidebar-link").forEach((link) => {
    link.onclick = function (e) {
      e.preventDefault();
      document
        .querySelectorAll(".fb-friends-sidebar-link")
        .forEach((l) => l.classList.remove("active"));
      this.classList.add("active");
      const section = this.getAttribute("data-section");
      if (section === "invitations") loadInvitations();
      else if (section === "tous") loadFriends();
      else if (section === "suggestions") loadSuggestions();
      else showEmpty();
    };
  });

  // Affichage par défaut : invitations
  loadInvitations();
  document
    .querySelector('.fb-friends-sidebar-link[data-section="invitations"]')
    .classList.add("active");

  function showEmpty() {
    document.getElementById("friends-content").innerHTML = `
            <div class="fb-friends-empty">
                <i class="fa-solid fa-users fa-3x" style="color:#b0b8c1;"></i>
                <p style="color:#555;font-size:1.15em;margin-top:18px;">Quand vous recevrez des invitations et des suggestions d’ami(e)s, vous les verrez ici.</p>
            </div>
        `;
  }

  // Chargement des invitations reçues
  function loadInvitations() {
    fetch("../../api/users/get_users.php?user_id=" + user.id)
      .then((r) => r.json())
      .then((data) => {
        if (!data.success) {
          showEmpty();
          return;
        }
        const invitations = data.users.filter(
          (u) => u.friend_status === "pending_received"
        );
        let html = `<h2 style='margin-bottom:18px;'><i class='fa-solid fa-user-plus'></i> Invitations reçues</h2>`;
        if (invitations.length === 0) {
          html += `<div class='fb-friends-empty'><p>Aucune invitation reçue pour le moment.</p></div>`;
        } else {
          html += `<div class='fb-friends-list'>`;
          invitations.forEach((u) => {
            html += `
                        <div class='fb-friend-item'>
                            <img src='../../assets/images/${
                              u.avatar || "default-avatar.png"
                            }' class='fb-avatar fb-avatar-lg' alt='avatar'>
                            <span class='fb-friend-name'>${u.prenom} ${
              u.nom
            }</span>
                            <button class='fb-btn-accept' data-id='${
                              u.id
                            }'>Accepter</button>
                            <button class='fb-btn-refuse' data-id='${
                              u.id
                            }'>Refuser</button>
                        </div>
                        `;
          });
          html += `</div>`;
        }
        document.getElementById("friends-content").innerHTML = html;
        // Listeners accepter/refuser
        document.querySelectorAll(".fb-btn-accept").forEach((btn) => {
          btn.onclick = function () {
            respondInvitation(this.getAttribute("data-id"), "accept");
          };
        });
        document.querySelectorAll(".fb-btn-refuse").forEach((btn) => {
          btn.onclick = function () {
            respondInvitation(this.getAttribute("data-id"), "refuse");
          };
        });
      });
  }

  function respondInvitation(friend_id, action) {
    fetch("../../api/users/respond_friend_request.php", {
      method: "POST",
      body: new URLSearchParams({
        user_id: user.id,
        friend_id: friend_id,
        action: action,
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) loadInvitations();
        else alert(data.error || "Erreur");
      });
  }

  // Chargement de la liste d'amis
  function loadFriends() {
    fetch("../../api/users/get_friends.php?user_id=" + user.id)
      .then((r) => r.json())
      .then((data) => {
        let html = `<h2 style='margin-bottom:18px;'><i class='fa-solid fa-users'></i> Mes ami(e)s</h2>`;
        if (!data.success || data.friends.length === 0) {
          html += `<div class='fb-friends-empty'><p>Vous n'avez pas encore d'ami(e)s.</p></div>`;
        } else {
          html += `<div class='fb-friends-list'>`;
          data.friends.forEach((u) => {
            html += `
                        <div class='fb-friend-item'>
                            <img src='../../assets/images/${
                              u.avatar || "default-avatar.png"
                            }' class='fb-avatar fb-avatar-lg' alt='avatar'>
                            <span class='fb-friend-name'>${u.prenom} ${
              u.nom
            }</span>
                            <button class='fb-btn-message' data-id='${
                              u.id
                            }' data-name='${u.prenom} ${u.nom}'>
                                <i class='fa-solid fa-comment'></i> Message
                            </button>
                        </div>
                        `;
          });
          html += `</div>`;
        }
        document.getElementById("friends-content").innerHTML = html;

        // Listeners pour les boutons message
        document.querySelectorAll(".fb-btn-message").forEach((btn) => {
          btn.onclick = function () {
            const friendId = this.getAttribute("data-id");
            const friendName = this.getAttribute("data-name");
            startConversation(friendId, friendName);
          };
        });
      });
  }

  // Suggestions d'amis
  function loadSuggestions() {
    fetch("../../api/users/get_users.php?user_id=" + user.id)
      .then((r) => r.json())
      .then((data) => {
        if (!data.success) {
          showEmpty();
          return;
        }
        const suggestions = data.users.filter(
          (u) => u.friend_status === "none"
        );
        let html = `<h2 style='margin-bottom:18px;'><i class='fa-solid fa-user-plus'></i> Suggestions d'ami(e)s</h2>`;
        if (suggestions.length === 0) {
          html += `<div class='fb-friends-empty'><p>Aucune suggestion pour le moment.</p></div>`;
        } else {
          html += `<div class='fb-friends-list'>`;
          suggestions.forEach((u) => {
            html += `
            <div class='fb-friend-item'>
              <img src='../../assets/images/${
                u.avatar || "default-avatar.png"
              }' class='fb-avatar fb-avatar-lg' alt='avatar'>
              <span class='fb-friend-name'>${u.prenom} ${u.nom}</span>
              <button class='fb-btn-add' data-id='${u.id}'>Ajouter</button>
            </div>
            `;
          });
          html += `</div>`;
        }
        document.getElementById("friends-content").innerHTML = html;
        // Listener bouton Ajouter
        document.querySelectorAll(".fb-btn-add").forEach((btn) => {
          btn.onclick = function () {
            sendInvitation(this.getAttribute("data-id"));
          };
        });
      });
  }

  function sendInvitation(friend_id) {
    fetch("../../api/users/send_friend_request.php", {
      method: "POST",
      body: new URLSearchParams({
        user_id: user.id,
        friend_id: friend_id,
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) loadSuggestions();
        else alert(data.error || "Erreur");
      });
  }

  // Fonction pour démarrer une conversation avec un ami
  function startConversation(friendId, friendName) {
    fetch("../../api/chat/create_conversation.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        friend_id: friendId,
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          // Rediriger vers la page de chat avec l'ID de la conversation
          window.location.href = `chat.html?conversation=${data.conversation.id}`;
        } else {
          alert(data.error || "Erreur lors de la création de la conversation");
        }
      })
      .catch((error) => {
        console.error("Erreur:", error);
        alert("Erreur lors de la création de la conversation");
      });
  }
});
