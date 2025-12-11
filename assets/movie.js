const container = document.getElementById("movie-detail");

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
const movieId = window.__MOVIE_ID__;

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    ...options,
  });
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

function renderMovie(movie) {
  if (!movie) {
    container.innerHTML = "<p>Không tìm thấy phim.</p>";
    return;
  }

  const grouped = {};
  movie.showtimes.forEach((s) => {
    const parts = (s.time || "").split(" ");
    const date = parts[0] || "Khác";
    const time = parts[1] || s.time;
    if (!grouped[date]) grouped[date] = [];
    grouped[date].push({ ...s, date, timeOnly: time });
  });
  const dates = Object.keys(grouped);
  let selectedDate = dates[0];

  container.innerHTML = `
    <div class="movie-detail">
      <div class="movie-detail__poster">
        <img src="${movie.poster}" alt="${movie.title}" class="poster"
             data-fallbacks="https://picsum.photos/seed/${movie.id}/600/900"
             onerror="fallbackPoster(this)">
      </div>
      <div class="movie-detail__info">
        <div class="chip-row">
          <div class="chip">⏱ ${movie.duration} phút</div>
          <div class="chip">💸 ${Number(movie.price || 0).toLocaleString("vi-VN")} đ</div>
          <div class="chip">🎬 ${Array.isArray(movie.genres) ? movie.genres.join(" · ") : ""}</div>
        </div>
        <h2>${movie.title}</h2>
        <p><strong>Tóm tắt:</strong> ${movie.description}</p>
        <p class="muted"><strong>Diễn viên:</strong> ${Array.isArray(movie.cast) ? movie.cast.join(", ") : ""}</p>
        <div class="divider"></div>
        <p class="eyebrow">Chọn ngày chiếu</p>
        <div id="date-list" class="showtimes"></div>
        <p class="eyebrow">Chọn khung giờ</p>
        <div id="time-list" class="showtimes"></div>
      </div>
    </div>
  `;

  const dateListEl = document.getElementById("date-list");
  const timeListEl = document.getElementById("time-list");

  function renderDates() {
    dateListEl.innerHTML = dates
      .map(
        (d) =>
          `<button class="showtime-btn ${d === selectedDate ? "active" : ""}" data-date="${d}">${d}</button>`
      )
      .join("");
    dateListEl.querySelectorAll("button").forEach((btn) => {
      btn.addEventListener("click", () => {
        selectedDate = btn.dataset.date;
        renderDates();
        renderTimes(selectedDate);
      });
    });
  }

  function renderTimes(date) {
    const items = grouped[date] || [];
    timeListEl.innerHTML = items
      .map(
        (s) =>
          `<a class="showtime-btn" href="/seats.php?showId=${s.id}">${s.timeOnly}</a>`
      )
      .join("");
  }

  renderDates();
  renderTimes(selectedDate);
}

fetchJSON("/api/movies.php")
  .then((movies) => {
    const movie = movies.find((m) => m.id === movieId);
    renderMovie(movie);
  })
  .catch((err) => {
    container.innerHTML = `<p>Lỗi tải phim: ${err.message}</p>`;
  });

