const tabs = document.querySelectorAll(".tab-btn");
const loginForm = document.getElementById("login-form");
const registerForm = document.getElementById("register-form");
const statusEl = document.getElementById("auth-status");

function switchTab(tab) {
  tabs.forEach((t) => t.classList.toggle("active", t.dataset.tab === tab));
  if (tab === "login") {
    loginForm.classList.remove("hidden");
    registerForm.classList.add("hidden");
  } else {
    registerForm.classList.remove("hidden");
    loginForm.classList.add("hidden");
  }
  statusEl.textContent = "";
}

tabs.forEach((btn) =>
  btn.addEventListener("click", () => switchTab(btn.dataset.tab))
);

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    ...options,
  });
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

loginForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  try {
    const data = await fetchJSON("/api/auth_login.php", {
      method: "POST",
      body: JSON.stringify({
        username: document.getElementById("login-username").value,
        password: document.getElementById("login-password").value,
      }),
    });
    statusEl.textContent = data.message;
    window.location.href = data.user?.role === "admin" ? "/admin.php" : "/index.php";
  } catch (err) {
    statusEl.textContent = err.message;
  }
});

registerForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  try {
    const data = await fetchJSON("/api/auth_register.php", {
      method: "POST",
      body: JSON.stringify({
        username: document.getElementById("register-username").value,
        email: document.getElementById("register-email").value,
        password: document.getElementById("register-password").value,
      }),
    });
    statusEl.textContent = data.message;
    window.location.href = "/index.php";
  } catch (err) {
    statusEl.textContent = err.message;
  }
});

switchTab("login");

