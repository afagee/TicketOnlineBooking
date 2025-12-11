const movieListEl = document.getElementById("movie-list");
const form = document.getElementById("movie-form");
const resetBtn = document.getElementById("reset-btn");
const idInput = document.getElementById("movie-id");
const titleInput = document.getElementById("title");
const durationInput = document.getElementById("duration");
const priceInput = document.getElementById("price");
const posterInput = document.getElementById("poster");
const descInput = document.getElementById("description");
const showDateInput = document.getElementById("show-date");
const showTimeInput = document.getElementById("show-time");
const addShowtimeBtn = document.getElementById("add-showtime");
const showtimeListEl = document.getElementById("showtime-list");
const resetDataBtn = document.getElementById("reset-data-btn");

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

let showtimeList = [];

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
  movieListEl.innerHTML = movies
    .map(
      (m) => `
      <article class="card">
        <img src="${m.poster}" alt="${m.title}" class="poster"
             data-fallbacks="https://picsum.photos/seed/${m.id}/600/900"
             onerror="fallbackPoster(this)">
        <div class="chip">⏱ ${m.duration} phút</div>
        <div class="chip">💸 ${m.price?.toLocaleString("vi-VN")} đ</div>
        <h3>${m.title}</h3>
        <p>${m.description}</p>
        <p class="eyebrow">Suất chiếu</p>
        <p>${m.showtimes.map((s) => s.time).join(" · ")}</p>
        <div class="actions">
          <button class="ghost" data-action="edit" data-id="${m.id}">Sửa</button>
          <button class="primary" data-action="delete" data-id="${m.id}">Xóa</button>
        </div>
      </article>
    `
    )
    .join("");

  movieListEl.querySelectorAll("button").forEach((btn) => {
    const id = btn.dataset.id;
    if (btn.dataset.action === "edit") {
      btn.addEventListener("click", () => loadToForm(id));
    } else {
      btn.addEventListener("click", () => deleteMovie(id));
    }
  });
}

async function loadMovies() {
  const data = await fetchJSON("/api/movies.php");
  renderMovies(data);
}

function loadToForm(id) {
  fetchJSON("/api/movies.php").then((movies) => {
    const movie = movies.find((m) => m.id === id);
    if (!movie) return;
    idInput.value = movie.id;
    titleInput.value = movie.title;
    durationInput.value = movie.duration;
    priceInput.value = movie.price || 0;
    posterInput.value = movie.poster;
    descInput.value = movie.description;
    showtimeList = movie.showtimes.map((s) => s.time);
    renderShowtimeList();
  });
}

async function deleteMovie(id) {
  if (!confirm("Xóa phim này?")) return;
  await fetchJSON("/api/admin_delete_movie.php", {
    method: "POST",
    body: JSON.stringify({ id }),
  });
  await loadMovies();
}

form.addEventListener("submit", async (e) => {
  e.preventDefault();
  const payload = {
    id: idInput.value || null,
    title: titleInput.value,
    duration: Number(durationInput.value),
    poster: posterInput.value,
    price: Number(priceInput.value),
    description: descInput.value,
    showtimes: showtimeList,
  };
  await fetchJSON("/api/admin_save_movie.php", {
    method: "POST",
    body: JSON.stringify(payload),
  });
  form.reset();
  idInput.value = "";
  showtimeList = [];
  renderShowtimeList();
  await loadMovies();
});

resetBtn.addEventListener("click", () => {
  form.reset();
  idInput.value = "";
  showtimeList = [];
  renderShowtimeList();
});

if (resetDataBtn) {
  resetDataBtn.addEventListener("click", async () => {
    if (!confirm("Xác nhận xóa toàn bộ ghế đã đặt và giữ chỗ?")) return;
    try {
      const res = await fetchJSON("/api/admin_reset_data.php", { method: "POST" });
      alert(res.message || "Đã reset");
    } catch (err) {
      alert(err.message);
    }
  });
}

addShowtimeBtn.addEventListener("click", () => {
  const date = showDateInput.value;
  const time = showTimeInput.value;
  if (!date || !time) return;
  const val = `${date} ${time}`;
  showtimeList.push(val);
  renderShowtimeList();
  showTimeInput.value = "";
});

function renderShowtimeList() {
  if (!showtimeList.length) {
    showtimeListEl.innerHTML = "<li>Chưa có suất chiếu</li>";
    return;
  }
  showtimeListEl.innerHTML = showtimeList
    .map(
      (t, idx) =>
        `<li><span>${t}</span><button data-idx="${idx}" class="ghost">Xóa</button></li>`
    )
    .join("");
  showtimeListEl.querySelectorAll("button").forEach((btn) => {
    btn.addEventListener("click", () => {
      const i = Number(btn.dataset.idx);
      showtimeList.splice(i, 1);
      renderShowtimeList();
    });
  });
}

loadMovies().catch(console.error);

