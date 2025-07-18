// Chat basique - Recherche, conversation, messages

class ChatApp {
  constructor() {
    this.user = null;
    this.currentConversation = null;
    this.init();
  }

  init() {
    // Récupérer l'utilisateur connecté
    this.user = JSON.parse(sessionStorage.getItem("user"));
    if (!this.user) {
      window.location.href = "login.html";
      return;
    }

    this.cacheDom();
    this.bindEvents();
    this.loadUsers();
  }

  cacheDom() {
    this.$search = document.getElementById("chat-search");
    this.$list = document.getElementById("chat-list");
    this.$header = document.getElementById("chat-header");
    this.$messages = document.getElementById("chat-messages");
    this.$form = document.getElementById("chat-form");
    this.$input = document.getElementById("chat-input");
  }

  bindEvents() {
    // Recherche utilisateur
    this.$search.addEventListener("input", (e) => {
      const query = e.target.value.trim().toLowerCase();
      this.filterUsers(query);
    });

    // Envoi message
    this.$form.addEventListener("submit", (e) => {
      e.preventDefault();
      this.sendMessage();
    });
  }

  // --- Recherche et affichage des utilisateurs ---
  async loadUsers() {
    try {
      const res = await fetch(
        `../../api/chat/users.php?user_id=${this.user.id}`,
        {
          credentials: "same-origin",
          headers: {
            "X-User-Id": this.user.id,
          },
        }
      );
      const data = await res.json();

      if (data.success) {
        this.users = data.users;
        this.renderUsers(this.users);
      } else {
        this.$list.innerHTML = `<div style="color:red;padding:1em;">Erreur: ${data.error}</div>`;
      }
    } catch (err) {
      this.$list.innerHTML =
        '<div style="color:red;padding:1em;">Erreur chargement utilisateurs</div>';
    }
  }

  filterUsers(query) {
    if (query.length === 0) {
      this.renderUsers(this.users);
      return;
    }

    const filtered = this.users.filter((user) =>
      `${user.prenom} ${user.nom}`.toLowerCase().includes(query)
    );
    this.renderUsers(filtered);
  }

  renderUsers(users) {
    if (users.length === 0) {
      this.$list.innerHTML =
        '<div style="color:#666;padding:1em;">Aucun utilisateur trouvé</div>';
      return;
    }

    this.$list.innerHTML = users
      .map(
        (user) => `
      <div class="chat-user-item" data-user-id="${user.id}">
        <img src="../../assets/images/${
          user.avatar || "default-avatar.png"
        }" class="chat-avatar" alt="avatar">
        <div class="chat-user-info">
          <div class="chat-user-name">${user.prenom} ${user.nom}</div>
          <div class="chat-user-status">${
            user.is_online ? "En ligne" : "Hors ligne"
          }</div>
        </div>
      </div>
    `
      )
      .join("");

    // Event listeners pour cliquer sur un utilisateur
    this.$list.querySelectorAll(".chat-user-item").forEach((item) => {
      item.addEventListener("click", () => {
        const userId = item.getAttribute("data-user-id");
        this.createConversation(userId);
      });
    });
  }

  // --- Création de conversation ---
  async createConversation(userId) {
    try {
      const res = await fetch("../../api/chat/conversations.php", {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-User-Id": this.user.id,
        },
        body: JSON.stringify({ user_id: userId }),
      });

      const data = await res.json();
      if (data.success) {
        this.currentConversation = data.conversation;
        this.renderHeader(data.conversation.user);
        this.loadMessages();
        this.$form.style.display = "flex";
      } else {
        alert(data.error || "Erreur création conversation");
      }
    } catch (err) {
      alert("Erreur création conversation");
    }
  }

  renderHeader(user) {
    this.$header.innerHTML = `
      <img src="../../assets/images/${
        user.avatar || "default-avatar.png"
      }" class="chat-avatar" alt="avatar">
      <div class="chat-header-info">
        <div class="chat-header-name">${user.prenom} ${user.nom}</div>
        <div class="chat-header-status">${
          user.is_online ? "En ligne" : "Hors ligne"
        }</div>
      </div>
    `;
  }

  // --- Messages ---
  async loadMessages() {
    if (!this.currentConversation) return;

    try {
      const res = await fetch(
        `../../api/chat/messages.php?conversation_id=${this.currentConversation.id}&user_id=${this.user.id}`,
        {
          credentials: "same-origin",
          headers: {
            "X-User-Id": this.user.id,
          },
        }
      );

      const data = await res.json();
      if (data.success) {
        this.renderMessages(data.messages);
      } else {
        this.$messages.innerHTML = `<div style="color:red;text-align:center;">Erreur: ${data.error}</div>`;
      }
    } catch (err) {
      this.$messages.innerHTML =
        '<div style="color:red;text-align:center;">Erreur chargement messages</div>';
    }
  }

  renderMessages(messages) {
    if (messages.length === 0) {
      this.$messages.innerHTML =
        '<div style="color:#666;text-align:center;">Aucun message</div>';
      return;
    }

    this.$messages.innerHTML = messages
      .map(
        (msg) => `
      <div class="chat-message${msg.sender_id == this.user.id ? " me" : ""}">
        <div class="message-content">${this.escapeHtml(msg.content)}</div>
        <div class="message-time">${this.formatTime(msg.created_at)}</div>
      </div>
    `
      )
      .join("");

    this.$messages.scrollTop = this.$messages.scrollHeight;
  }

  async sendMessage() {
    const content = this.$input.value.trim();
    if (!content || !this.currentConversation) return;

    try {
      const res = await fetch(
        `../../api/chat/messages.php?conversation_id=${this.currentConversation.id}&user_id=${this.user.id}`,
        {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-User-Id": this.user.id,
          },
          body: JSON.stringify({ content: content }),
        }
      );

      const data = await res.json();
      if (data.success) {
        this.$input.value = "";
        this.loadMessages(); // Recharger tous les messages
      } else {
        alert(data.error || "Erreur envoi message");
      }
    } catch (err) {
      alert("Erreur envoi message");
    }
  }

  // --- Utilitaires ---
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString("fr-FR", {
      hour: "2-digit",
      minute: "2-digit",
      day: "2-digit",
      month: "2-digit",
    });
  }
}

// Initialisation
window.addEventListener("DOMContentLoaded", () => {
  new ChatApp();
});
