document.addEventListener("DOMContentLoaded", function () {
  // Vérifie si l'utilisateur est connecté
  const user = sessionStorage.getItem("user");
  if (!user) {
    window.location.href = "login.html";
    return;
  }

  const userData = JSON.parse(user);

  // Déconnexion
  const logoutBtn = document.getElementById("logout-btn-navbar");
  if (logoutBtn) {
    logoutBtn.onclick = function () {
      sessionStorage.removeItem("user");
      window.location.href = "login.html";
    };
  }

  // Publication d'un post
  const postForm = document.getElementById("post-form");
  if (postForm) {
    const imageInput = document.getElementById("image-input");
    const imagePreview = document.getElementById("image-preview");
    const imageFilename = document.getElementById("image-filename");

    if (imageInput && imagePreview && imageFilename) {
      imageInput.addEventListener("change", function () {
        if (this.files && this.files[0]) {
          imageFilename.textContent = this.files[0].name;
          const reader = new FileReader();
          reader.onload = function (e) {
            imagePreview.innerHTML = `<img src='${e.target.result}' style='max-width:120px;max-height:120px;border-radius:8px;margin:8px 0;'>`;
          };
          reader.readAsDataURL(this.files[0]);
        } else {
          imageFilename.textContent = "";
          imagePreview.innerHTML = "";
        }
      });
    }

    postForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(postForm);
      formData.append("user_id", userData.id);
      fetch("../../api/posts/add_post.php", {
        method: "POST",
        body: formData,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            postForm.reset();
            if (imagePreview) imagePreview.innerHTML = "";
            if (imageFilename) imageFilename.textContent = "";
            loadFeed();
            // Ferme la modale après publication
            const modal = document.getElementById("fb-post-modal");
            if (modal) modal.style.display = "none";
          } else {
            alert(data.error || "Erreur lors de la publication.");
          }
        })
        .catch(() => alert("Erreur réseau."));
    });
  }

  // Fonction pour charger les commentaires
  function loadComments(postId) {
    fetch(`../../api/posts/get_comments.php?post_id=${postId}`)
      .then((r) => r.json())
      .then((data) => {
        const container = document.getElementById(`comments-${postId}`);
        if (!container) {
          console.error(`Container comments-${postId} not found`);
          return;
        }
        if (!data.success) {
          container.innerHTML =
            '<div style="color:red;padding:10px;">Erreur lors du chargement des commentaires.</div>';
          return;
        }

        let commentsHtml = `
          <div class="fb-comments-form">
            <textarea id="comment-input-${postId}" placeholder="Écrivez un commentaire..." class="fb-comment-textarea"></textarea>
            <button onclick="addComment(${postId})" class="fb-comment-btn">
              <i class="fas fa-paper-plane"></i> Commenter
            </button>
          </div>
          <div class="fb-comments-list">
        `;

        if (data.comments.length === 0) {
          commentsHtml +=
            '<div style="text-align:center;padding:20px;color:var(--color-text-muted);"><i class="fas fa-comment-slash"></i> Aucun commentaire pour le moment.</div>';
        } else {
          data.comments.forEach((comment) => {
            commentsHtml += `
              <div class="fb-comment">
                <div class="fb-comment-header">
                  <img src="../../assets/images/${
                    comment.avatar || "default-avatar.png"
                  }" alt="avatar">
                  <span class="fb-comment-author">${comment.prenom} ${
              comment.nom
            }</span>
                  <span class="fb-comment-date"><i class="fas fa-clock"></i> ${new Date(
                    comment.created_at
                  ).toLocaleString("fr-FR")}</span>
                </div>
                <div class="fb-comment-content">
                  ${comment.content.replace(/\n/g, "<br>")}
                </div>
              </div>
            `;
          });
        }

        commentsHtml += "</div>";
        container.innerHTML = commentsHtml;
      })
      .catch(() => {
        const container = document.getElementById(`comments-${postId}`);
        if (container) {
          container.innerHTML =
            '<div style="color:red;padding:10px;">Erreur réseau.</div>';
        }
      });
  }

  // Fonction pour ajouter un commentaire (globale pour être accessible depuis onclick)
  window.addComment = function (postId) {
    const input = document.getElementById(`comment-input-${postId}`);
    if (!input) {
      console.error(`Comment input for post ${postId} not found`);
      return;
    }

    const content = input.value.trim();

    if (!content) {
      alert("Veuillez écrire un commentaire.");
      return;
    }

    fetch("../../api/posts/add_comment.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        user_id: userData.id,
        post_id: postId,
        content: content,
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          input.value = "";

          // Ajoute le nouveau commentaire directement dans l'interface
          const commentsList = document.querySelector(
            `#comments-${postId} .fb-comments-list`
          );
          if (commentsList && data.comment) {
            const newCommentHtml = `
              <div class="fb-comment">
                <div class="fb-comment-header">
                  <img src="../../assets/images/${
                    data.comment.avatar || "default-avatar.png"
                  }" alt="avatar">
                  <span class="fb-comment-author">${data.comment.prenom} ${
              data.comment.nom
            }</span>
                  <span class="fb-comment-date"><i class="fas fa-clock"></i> ${new Date(
                    data.comment.created_at
                  ).toLocaleString("fr-FR")}</span>
                </div>
                <div class="fb-comment-content">
                  ${data.comment.content.replace(/\n/g, "<br>")}
                </div>
              </div>
            `;
            commentsList.insertAdjacentHTML("beforeend", newCommentHtml);

            // Met à jour le compteur de commentaires
            const toggleBtn = document.querySelector(
              `[data-id="${postId}"].toggle-comments-btn`
            );
            if (toggleBtn) {
              const currentText = toggleBtn.innerHTML;
              const match = currentText.match(/(\d+)/);
              if (match) {
                const currentCount = parseInt(match[1]);
                toggleBtn.innerHTML = `<i class="fas fa-comment"></i> ${
                  currentCount + 1
                } commentaire(s)`;
              }
            }

            // Scroll vers le bas pour voir le nouveau commentaire
            setTimeout(() => {
              commentsList.scrollTop = commentsList.scrollHeight;
            }, 100);
          }
        } else {
          alert(data.error || "Erreur lors de l'ajout du commentaire.");
        }
      })
      .catch(() => alert("Erreur réseau."));
  };

  // Fonction pour partager un post
  window.sharePost = function (postId) {
    if (!confirm("Voulez-vous partager ce post ?")) return;

    fetch("../../api/posts/share_post.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        user_id: userData.id,
        post_id: postId,
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          alert("Post partagé avec succès !");
          loadFeed();
        } else {
          alert(data.error || "Erreur lors du partage.");
        }
      })
      .catch(() => alert("Erreur réseau."));
  };

  // Fonction pour charger le flux (factorisée)
  function loadFeed() {
    fetch(`../../api/posts/get_feed.php?user_id=${userData.id}`)
      .then((r) => r.json())
      .then((data) => {
        const feed = document.getElementById("feed-container");
        if (!feed) {
          console.error("Feed container not found");
          return;
        }

        if (!data.success) {
          feed.innerHTML =
            '<div style="color:red;text-align:center;">Erreur lors du chargement du flux.</div>';
          return;
        }
        if (data.posts.length === 0) {
          feed.innerHTML =
            '<div style="text-align:center;">Aucun post pour le moment.</div>';
          return;
        }
        feed.innerHTML = "";
        data.posts.forEach((post) => {
          // Style spécial pour les posts partagés
          const isShared = post.shared_from_id ? "shared-post" : "";
          const sharedIndicator = post.shared_from_id
            ? `<div style="background:#f0f8ff;border-left:4px solid #1877f2;padding:8px;margin-bottom:10px;border-radius:4px;font-size:0.9em;color:#1877f2;">
              <i class="fas fa-share"></i> Post partagé
            </div>`
            : "";

          feed.innerHTML += `
            <div class="fb-post ${isShared}" data-post-id="${post.id}">
                ${sharedIndicator}
                <div class="fb-post-header">
                    <a href="profile.html?user_id=${post.user_id}">
                      <img src="../../assets/images/${
                        post.avatar || "default-avatar.png"
                      }"
                        class="fb-avatar" alt="avatar" style="cursor:pointer;">
                    </a>
                    <div>
                        <a href="profile.html?user_id=${
                          post.user_id
                        }" class="fb-post-author" style="font-weight:bold;">
                          ${post.prenom} ${post.nom}
                        </a><br>
                        <span class="fb-post-date">${new Date(
                          post.created_at
                        ).toLocaleString("fr-FR")}</span>
                    </div>
                </div>
                <div class="fb-post-content">${post.content.replace(
                  /\n/g,
                  "<br>"
                )}</div>
                ${
                  post.image
                    ? `<img src="../../assets/images/${post.image}" class="fb-post-img" alt="image postée" style="width:100%;max-width:100%;aspect-ratio:16/9;max-height:320px;object-fit:cover;border-radius:10px;margin:8px 0;">`
                    : ""
                }
                <div class="fb-post-actions">
                    <button class="like-btn${
                      post.user_like === "like" ? " liked" : ""
                    }" data-id="${post.id}" data-type="like">
                      <i class="fas fa-thumbs-up"></i> ${post.likes}
                    </button>
                    <button class="dislike-btn${
                      post.user_like === "dislike" ? " disliked" : ""
                    }" data-id="${post.id}" data-type="dislike">
                      <i class="fas fa-thumbs-down"></i> ${post.dislikes}
                    </button>
                    <button class="toggle-comments-btn" data-id="${post.id}">
                      <i class="fas fa-comment"></i> ${
                        post.comments
                      } commentaire(s)
                    </button>
                    <button class="share-btn" onclick="sharePost(${post.id})">
                      <i class="fas fa-share"></i> Partager
                    </button>
                </div>
                <div class="fb-comments-container" id="comments-${
                  post.id
                }" style="display:none;"></div>
            </div>
            `;
        });

        // Listeners like/dislike avec styles hover
        document.querySelectorAll(".like-btn, .dislike-btn").forEach((btn) => {
          if (!btn) return; // Skip if button is null

          btn.onclick = function () {
            const postId = this.getAttribute("data-id");
            const type = this.getAttribute("data-type");
            const likeBtn = this.parentElement.querySelector(".like-btn");
            const dislikeBtn = this.parentElement.querySelector(".dislike-btn");

            if (!postId || !type) {
              console.error("Missing postId or type for like/dislike button");
              return;
            }

            fetch("../../api/posts/like_post.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded",
              },
              body: new URLSearchParams({
                user_id: userData.id,
                post_id: postId,
                type: type,
              }),
            })
              .then((r) => r.json())
              .then((data) => {
                if (data.success) {
                  // Met à jour les compteurs et l'état sans reload global
                  if (likeBtn) likeBtn.innerHTML = `👍 ${data.likes}`;
                  if (dislikeBtn) dislikeBtn.innerHTML = `👎 ${data.dislikes}`;
                  if (type === "like") {
                    if (data.action === "added" || data.action === "updated") {
                      if (likeBtn) likeBtn.classList.add("liked");
                      if (dislikeBtn) dislikeBtn.classList.remove("disliked");
                    } else {
                      if (likeBtn) likeBtn.classList.remove("liked");
                    }
                  } else {
                    if (data.action === "added" || data.action === "updated") {
                      if (dislikeBtn) dislikeBtn.classList.add("disliked");
                      if (likeBtn) likeBtn.classList.remove("liked");
                    } else {
                      if (dislikeBtn) dislikeBtn.classList.remove("disliked");
                    }
                  }
                } else {
                  alert(data.error || "Erreur like/dislike");
                }
              });
          };

          // Effet hover
          btn.onmouseenter = function () {
            this.style.background = "#f0f2f5";
          };
          btn.onmouseleave = function () {
            this.style.background = "none";
          };
        });

        // Listeners toggle commentaires
        document.querySelectorAll(".toggle-comments-btn").forEach((btn) => {
          if (!btn) return; // Skip if button is null

          btn.onclick = function () {
            const postId = this.getAttribute("data-id");
            if (!postId) {
              console.error("Missing postId for toggle comments button");
              return;
            }

            const container = document.getElementById("comments-" + postId);
            if (!container) {
              console.error(`Comments container for post ${postId} not found`);
              return;
            }

            if (container.style.display === "none") {
              loadComments(postId);
              container.style.display = "block";
            } else {
              container.style.display = "none";
            }
          };

          // Effet hover
          btn.onmouseenter = function () {
            this.style.background = "#f0f2f5";
          };
          btn.onmouseleave = function () {
            this.style.background = "none";
          };
        });

        // Effet hover pour le bouton partager
        document.querySelectorAll(".share-btn").forEach((btn) => {
          if (!btn) return; // Skip if button is null

          btn.onmouseenter = function () {
            this.style.background = "#f0f2f5";
          };
          btn.onmouseleave = function () {
            this.style.background = "none";
          };
        });
      });
  }

  // Chargement initial du flux
  loadFeed();
  // Rafraîchissement automatique toutes les 15 secondes
  setInterval(loadFeed, 15000);
});
