document.addEventListener("DOMContentLoaded", function () {
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

  // Charger les infos du profil
  fetch("../../api/users/get_profile.php?user_id=" + user.id)
    .then((r) => r.json())
    .then((data) => {
      if (data.success) fillProfile(data.profile);
    });

  function fillProfile(p) {
    document.getElementById("profile-avatar").src =
      "../../assets/images/" + (p.avatar || "default-avatar.png");
    document.getElementById("profile-prenom").value = p.prenom;
    document.getElementById("profile-nom").value = p.nom;
    document.getElementById("profile-email").value = p.email;
    document.getElementById("profile-date-naissance").value = p.date_naissance;
    document.getElementById("profile-genre").value = p.genre;
  }

  // Modification des infos
  document.getElementById("profile-form").onsubmit = function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("user_id", user.id);
    fetch("../../api/users/update_profile.php", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((data) => {
        const msg = document.getElementById("profile-message");
        if (data.success) {
          msg.style.color = "green";
          msg.textContent = "Profil mis à jour !";
        } else {
          msg.style.color = "red";
          msg.textContent = data.error || "Erreur.";
        }
      });
  };

  // Upload avatar
  document.getElementById("avatar-input").onchange = function () {
    const file = this.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append("user_id", user.id);
    formData.append("avatar", file);
    fetch("../../api/users/update_avatar.php", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((data) => {
        const msg = document.getElementById("profile-message");
        if (data.success) {
          document.getElementById("profile-avatar").src =
            "../../assets/images/" + data.avatar;
          msg.style.color = "green";
          msg.textContent = "Photo de profil mise à jour !";
        } else {
          msg.style.color = "red";
          msg.textContent = data.error || "Erreur.";
        }
      });
  };

  // Changement de mot de passe (modale simple)
  document.getElementById("change-password-btn").onclick = function () {
    const newPwd = prompt("Nouveau mot de passe :");
    if (!newPwd || newPwd.length < 6) {
      alert("Mot de passe trop court.");
      return;
    }
    const confirmPwd = prompt("Confirmez le mot de passe :");
    if (newPwd !== confirmPwd) {
      alert("Les mots de passe ne correspondent pas.");
      return;
    }
    const currentPwd = prompt("Votre mot de passe actuel :");
    if (!currentPwd) {
      alert("Mot de passe actuel requis.");
      return;
    }
    fetch("../../api/users/change_password.php", {
      method: "POST",
      body: new URLSearchParams({
        user_id: user.id,
        current_password: currentPwd,
        new_password: newPwd,
        confirm_password: confirmPwd,
      }),
    })
      .then((r) => r.json())
      .then((data) => {
        const msg = document.getElementById("profile-message");
        if (data.success) {
          msg.style.color = "green";
          msg.textContent = "Mot de passe modifié !";
        } else {
          msg.style.color = "red";
          msg.textContent = data.error || "Erreur.";
        }
      });
  };
});
