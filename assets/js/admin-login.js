document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("admin-login-form");
  const messageDiv = document.getElementById("admin-login-message");
  const loginBtn = document.querySelector(".admin-btn-login");

  loginForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    // Validation
    if (!email || !password) {
      showMessage("Veuillez remplir tous les champs", "error");
      return;
    }

    if (!isValidEmail(email)) {
      showMessage("Veuillez entrer une adresse email valide", "error");
      return;
    }

    // Désactiver le bouton pendant la connexion
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connexion...';

    try {
      const response = await fetch("../../api/admin/login.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: email,
          password: password,
        }),
      });

      const data = await response.json();

      if (data.success) {
        showMessage("Connexion réussie ! Redirection...", "success");

        // Stocker les informations admin
        sessionStorage.setItem(
          "admin",
          JSON.stringify({
            id: data.admin.id,
            email: data.admin.email,
            name: data.admin.name,
          })
        );

        // Redirection vers le dashboard
        setTimeout(() => {
          window.location.href = "dashboard.html";
        }, 1000);
      } else {
        showMessage(data.error || "Erreur de connexion", "error");
      }
    } catch (error) {
      console.error("Erreur:", error);
      showMessage("Erreur de connexion au serveur", "error");
    } finally {
      // Réactiver le bouton
      loginBtn.disabled = false;
      loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Se connecter';
    }
  });

  function showMessage(message, type) {
    messageDiv.textContent = message;
    messageDiv.className = `admin-message ${type}`;

    // Masquer le message après 5 secondes
    setTimeout(() => {
      messageDiv.style.display = "none";
    }, 5000);
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }
});
