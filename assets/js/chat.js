document.addEventListener("DOMContentLoaded", function () {
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (!user) {
    window.location.href = "login.html";
    return;
  }

  // Recherche utilisateurs
  const searchInput = document.getElementById("chat-search");
  const chatList = document.getElementById("chat-list");
  let allUsers = [];

  function loadUsers() {
    fetch(`../../api/chat/users.php?user_id=${user.id}`)
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          allUsers = data.users;
          renderUsers(allUsers);
        }
      });
  }

  function renderUsers(users) {
    chatList.innerHTML = users
      .map(
        (u) => `
      <div class="chat-user-item" data-id="${u.id}">
        <img src="../../assets/images/${
          u.avatar || "default-avatar.png"
        }" class="chat-avatar">
        <div class="chat-user-info">
          <div class="chat-user-name">${u.prenom} ${u.nom}</div>
        </div>
      </div>
    `
      )
      .join("");
    document.querySelectorAll(".chat-user-item").forEach((item) => {
      item.onclick = function () {
        startConversation(item.getAttribute("data-id"));
      };
    });
  }

  searchInput.addEventListener("input", function () {
    const q = this.value.trim().toLowerCase();
    renderUsers(
      allUsers.filter(
        (u) =>
          u.prenom.toLowerCase().includes(q) || u.nom.toLowerCase().includes(q)
      )
    );
  });

  // Conversation
  let currentConversationId = null;
  let currentContact = null; // Ajouté pour stocker l'utilisateur sélectionné

  function startConversation(otherUserId) {
    // Trouver l'objet utilisateur sélectionné
    currentContact = allUsers.find((u) => u.id == otherUserId);
    // Mettre à jour l'en-tête du chat
    updateChatHeader();
    fetch("../../api/chat/conversations.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ user_id: user.id, other_user_id: otherUserId }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          currentConversationId = data.conversation_id;
          document.getElementById("chat-form").style.display = "";
          loadMessages();
        }
      });
  }

  function updateChatHeader() {
    const header = document.getElementById("chat-header");
    if (!currentContact) {
      header.innerHTML =
        '<span style="color: var(--color-text-muted)">Sélectionnez une conversation</span>';
      return;
    }
    header.innerHTML = `
    <img src="../../assets/images/${
      currentContact.avatar || "default-avatar.png"
    }" class="chat-avatar" style="width:48px;height:48px;">
    <div class="chat-header-info">
      <div class="chat-header-name">${currentContact.prenom} ${
      currentContact.nom
    }</div>
      <div class="chat-header-status">Hors ligne</div>
    </div>
  `;
  }

  // Messages
  const chatMessages = document.getElementById("chat-messages");
  const chatForm = document.getElementById("chat-form");
  const chatInput = document.getElementById("chat-input");

  function loadMessages() {
    if (!currentConversationId) return;
    fetch(
      `../../api/chat/messages.php?conversation_id=${currentConversationId}`
    )
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          chatMessages.innerHTML = data.messages
            .map(
              (m) => `
            <div class="chat-message${m.sender_id == user.id ? " me" : ""}">
              <div>${m.content}</div>
              <div class="chat-message-time">${new Date(
                m.created_at
              ).toLocaleString("fr-FR")}</div>
            </div>
          `
            )
            .join("");
          chatMessages.scrollTop = chatMessages.scrollHeight;
        }
      });
  }

  // Gestion de l'envoi d'image (préparation, pas d'appel API encore)
  const chatImageInput = document.getElementById("chat-image-input");
  let imageToSend = null;
  chatImageInput.addEventListener("change", function () {
    if (this.files && this.files[0]) {
      imageToSend = this.files[0];
      // Optionnel : afficher un aperçu ou notifier l'utilisateur
    } else {
      imageToSend = null;
    }
  });

  chatForm.addEventListener("submit", function (e) {
    e.preventDefault();
    if (!currentConversationId) return;
    const content = chatInput.value.trim();
    if (!content && !imageToSend) return;
    if (imageToSend) {
      let formData = new FormData();
      formData.append("conversation_id", currentConversationId);
      formData.append("sender_id", user.id);
      formData.append("image", imageToSend);
      fetch("../../api/chat/upload_file.php", {
        method: "POST",
        body: formData,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            imageToSend = null;
            chatImageInput.value = "";
            chatInput.value = "";
            loadMessages();
          } else {
            alert(data.error || "Erreur lors de l'envoi de l'image");
          }
        })
        .catch(() => {
          alert("Erreur lors de l'envoi de l'image");
        });
      return;
    }
    if (!content) return;
    fetch("../../api/chat/messages.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        conversation_id: currentConversationId,
        sender_id: user.id,
        content: content,
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          chatInput.value = "";
          loadMessages();
        }
      });
  });

  // Polling pour rafraîchir les messages
  setInterval(() => {
    if (currentConversationId) loadMessages();
  }, 3000);

  // Initialisation
  loadUsers();
});
