document.addEventListener("DOMContentLoaded", function () {
  // Inscription (déjà présent)
  const form = document.getElementById("register-form");
  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(form);
      fetch("../../api/auth/register.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          const msg = document.getElementById("register-message");
          if (data.success) {
            msg.style.color = "green";
            msg.textContent =
              "Inscription réussie ! Vérifiez votre email pour confirmer votre compte.";
            form.reset();
          } else {
            msg.style.color = "red";
            msg.textContent = data.error || "Erreur lors de l'inscription.";
          }
        })
        .catch(() => {
          const msg = document.getElementById("register-message");
          msg.style.color = "red";
          msg.textContent = "Erreur réseau.";
        });
    });
  }

  // Connexion
  const loginForm = document.getElementById("login-form");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const email = loginForm.elements["email"].value;
      const password = loginForm.elements["password"].value;

      fetch("../../api/auth/login.php", {
        method: "POST",
        body: new FormData(loginForm),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success && data.user) {
            // Stocker l'utilisateur dans sessionStorage
            sessionStorage.setItem("user", JSON.stringify(data.user));
            // Redirection vers la page de loading
            if (data.user.role === "admin") {
              window.location.href = "../back-office/dashboard.html";
            } else {
              window.location.href = "home.html";
            }
            return;
          } else {
            document.getElementById("login-message").textContent =
              data.error || "Erreur de connexion.";
            document.getElementById("login-message").style.color = "red";
          }
        })
        .catch((error) => {
          document.getElementById("login-message").textContent =
            "Erreur réseau.";
          document.getElementById("login-message").style.color = "red";
        });
    });
  }

  // Mot de passe oublié
  const forgotForm = document.getElementById("forgot-form");
  if (forgotForm) {
    forgotForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(forgotForm);
      fetch("../../api/auth/forgot_password.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          const msg = document.getElementById("forgot-message");
          if (data.success) {
            msg.style.color = "green";
            msg.textContent =
              "Un email de réinitialisation a été envoyé si l’adresse existe.";
            forgotForm.reset();
          } else {
            msg.style.color = "red";
            msg.textContent = data.error || "Erreur lors de l’envoi.";
          }
        })
        .catch(() => {
          const msg = document.getElementById("forgot-message");
          msg.style.color = "red";
          msg.textContent = "Erreur réseau.";
        });
    });
  }

  // Réinitialisation du mot de passe
  const resetForm = document.getElementById("reset-form");
  if (resetForm) {
    resetForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(resetForm);
      fetch("../../api/auth/reset_password.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          const msg = document.getElementById("reset-message");
          if (data.success) {
            msg.style.color = "green";
            msg.textContent =
              "Mot de passe modifié ! Vous pouvez vous connecter.";
            resetForm.reset();
          } else {
            msg.style.color = "red";
            msg.textContent =
              data.error || "Erreur lors de la réinitialisation.";
          }
        })
        .catch(() => {
          const msg = document.getElementById("reset-message");
          msg.style.color = "red";
          msg.textContent = "Erreur réseau.";
        });
    });
  }
});
