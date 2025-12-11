const moviesEl = document.getElementById("movies");
const userInfoEl = document.getElementById("user-info");
const loginLink = document.getElementById("login-link");
const logoutLink = document.getElementById("logout-link");
const bookingsSection = document.getElementById("bookings-section");
const bookingsList = document.getElementById("bookings-list");

function fallbackPoster(img) {
  const list = (img.dataset.fallbacks || "").split("|").filter(Boolean);
  const idx = Number(img.dataset.fallbackIndex || 0);
  if (idx < list.length) {
    img.dataset.fallbackIndex = idx + 1;
    img.src = list[idx];
  } else {
    img.src = "https://placehold.co/600x900?text=No+Image";
  }
}

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    ...options,
  });
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

function renderMovies(movies) {
  moviesEl.innerHTML = movies
    .map(
      (m) => `
      <article class="card" data-movie="${m.id}">
        <img src="${m.poster}" alt="${m.title}" class="poster" loading="lazy"
             data-fallbacks="https://picsum.photos/seed/${m.id}/600/900"
             onerror="fallbackPoster(this)">
        <div class="chip">⏱ ${m.duration} phút</div>
        <div class="chip">💸 ${Number(m.price || 0).toLocaleString("vi-VN")} đ</div>
        <div class="chip">🎬 ${Array.isArray(m.genres) ? m.genres.slice(0, 2).join(" · ") : ""}</div>
        <h3>${m.title}</h3>
        <div class="actions">
          <button class="primary" data-id="${m.id}">Xem suất chiếu</button>
        </div>
      </article>
    `
    )
    .join("");

  moviesEl.querySelectorAll("button[data-id]").forEach((btn) => {
    btn.addEventListener("click", () => {
      window.location.href = `/movie.php?id=${btn.dataset.id}`;
    });
  });
}

fetchJSON("/api/movies.php").then(renderMovies).catch(console.error);

// Auth + bookings
async function loadUserAndBookings() {
  try {
    const auth = await fetchJSON("/api/auth_me.php");
    const user = auth.user;
    if (user) {
      if (userInfoEl) userInfoEl.innerHTML = `<span class="badge">👤 ${user.username}</span>`;
      if (loginLink) loginLink.classList.add("hidden");
      if (logoutLink) logoutLink.classList.remove("hidden");
      const bookings = await fetchJSON("/api/my_bookings.php");
      renderBookings(bookings);
    } else {
      if (userInfoEl) userInfoEl.textContent = "Khách";
      if (loginLink) loginLink.classList.remove("hidden");
      if (logoutLink) logoutLink.classList.add("hidden");
    }
  } catch (err) {
    console.error(err);
  }
}

function renderBookings(data) {
  if (!bookingsSection || !bookingsList) return;
  if (!data || !data.length) {
    bookingsSection.classList.add("hidden");
    return;
  }
  bookingsSection.classList.remove("hidden");
  bookingsList.innerHTML = data
    .map(
      (b) =>
        `<li>
          <div>
            <strong>
              ${b.movieId ? `<a href="/movie.php?id=${b.movieId}">${b.movieTitle}</a>` : b.movieTitle}
            </strong>
            <div class="muted">Suất chiếu: ${b.showTime}</div>
          </div>
          <div class="actions">
            <span class="muted">${new Date(b.bookedAt).toLocaleString("vi-VN")}</span>
            <button class="ghost" data-cancel data-show="${b.showId}" data-seats="${b.seats?.join(",") || ""}">Hủy vé</button>
          </div>
        </li>`
    )
    .join("");

  bookingsList.querySelectorAll("button[data-cancel]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const showId = btn.dataset.show;
      const seats = (btn.dataset.seats || "").split(",").filter(Boolean);
      if (!showId || !seats.length) return;
      if (!confirm(`Hủy vé suất ${showId} cho ghế: ${seats.join(", ")}?`)) return;
      try {
        const res = await fetchJSON("/api/my_cancel_booking.php", {
          method: "POST",
          body: JSON.stringify({ showId, seats }),
        });
        alert(res.message || "Đã hủy");
        const bookings = await fetchJSON("/api/my_bookings.php");
        renderBookings(bookings);
      } catch (err) {
        alert(err.message);
      }
    });
  });
}

loadUserAndBookings();

