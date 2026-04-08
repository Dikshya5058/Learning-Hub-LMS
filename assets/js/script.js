async function login() {
  const email = document.getElementById("loginEmail").value.trim();
  const password = document.getElementById("loginPassword").value;
  const error = document.getElementById("loginError");

  try {
    const response = await fetch('admin_login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    const result = await response.json();
    if (result.status === "success") {
      window.location.href = "dashboard.php";
    } else {
      error.textContent = result.message;
    }
  } catch (err) {
    error.textContent = "Error connecting to server.";
  }
}

function logout() {
    window.location.href = "admin_logout.php";
}