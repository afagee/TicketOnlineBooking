const moviesEl = document.getElementById("movies");
const featuredWrapper = document.querySelector(".swiper-container.phim-hot .swiper-wrapper");
let featuredSwiper = null;
const userInfoEl = document.getElementById("user-info");
const loginLink = document.getElementById("login-link");
const logoutLink = document.getElementById("logout-link");
const bookingsSection = document.getElementById("bookings-section");
const bookingsList = document.getElementById("bookings-list");

// Danh sách phim nổi bật theo thứ tự mong muốn
const FEATURED_IDS = [
  "mv-1",  // Dune: Part Two
  "mv-2",  // Interstellar
  "mv-6",  // Spider-Man: Across the Spider-Verse
  "mv-11", // The Marvels
  "mv-13", // John Wick: Chapter 4
  "mv-17", // The Dark Knight
  "mv-4",  // Avatar: The Way of Water
];

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
  // Sắp xếp để phim nổi bật lên đầu danh sách lưới
  const order = new Map(FEATURED_IDS.map((id, idx) => [id, idx]));
  const sorted = [...movies].sort((a, b) => {
    const aIdx = order.has(a.id) ? order.get(a.id) : Number.MAX_SAFE_INTEGER;
    const bIdx = order.has(b.id) ? order.get(b.id) : Number.MAX_SAFE_INTEGER;
    if (aIdx !== bIdx) return aIdx - bIdx;
    return a.title.localeCompare(b.title, "vi");
  });

  moviesEl.innerHTML = sorted
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

function renderFeatured(movies) {
  if (!featuredWrapper) return;
  const featured = FEATURED_IDS
    .map((id) => movies.find((m) => m.id === id))
    .filter(Boolean)
    .slice(0, 7);

  featuredWrapper.innerHTML = featured
    .map(
      (m) => `
      <div class="swiper-slide" data-movie="${m.id}">
        <img src="${m.poster}" alt="${m.title}" loading="lazy"
             onerror="fallbackPoster(this)">
        <h3>${m.title}</h3>
      </div>
    `
    )
    .join("");

  // Init swiper once slides are ready
  if (!featuredSwiper && typeof Swiper !== "undefined") {
    featuredSwiper = new Swiper(".swiper-container.phim-hot", {
      loop: true,
      slidesPerView: 5,
      spaceBetween: 10,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      breakpoints: {
        0: { slidesPerView: 2 },
        768: { slidesPerView: 4 },
        1024: { slidesPerView: 5 },
      },
    });
  } else if (featuredSwiper) {
    featuredSwiper.update();
  }

  // Click on slide -> go to showtimes
  featuredWrapper.querySelectorAll(".swiper-slide").forEach((slide) => {
    slide.addEventListener("click", () => {
      const id = slide.dataset.movie;
      if (id) window.location.href = `/movie.php?id=${id}`;
    });
  });
}

fetchJSON("/api/index.php?route=movies")
  .then((movies) => {
    renderMovies(movies);
    renderFeatured(movies);
  })
  .catch(console.error);

// Auth + bookings
async function loadUserAndBookings() {
  try {
    const auth = await fetchJSON("/api/index.php?route=auth/me");
    const user = auth.user;
    if (user) {
      if (userInfoEl) userInfoEl.innerHTML = `<span class="badge">👤 ${user.username}</span>`;
      if (loginLink) loginLink.classList.add("hidden");
      if (logoutLink) logoutLink.classList.remove("hidden");
      const bookings = await fetchJSON("/api/index.php?route=my-bookings");
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

// Modal elements
const ticketModal = document.getElementById("ticket-modal");
const modalOverlay = ticketModal?.querySelector(".modal-overlay");
const modalClose = ticketModal?.querySelector(".modal-close");
const ticketCancelBtn = document.getElementById("ticket-cancel-btn");

let currentTicket = null;

function openTicketModal(booking) {
  if (!ticketModal) return;
  currentTicket = booking;
  
  document.getElementById("ticket-movie").textContent = booking.movieTitle;
  document.getElementById("ticket-time").textContent = booking.showTime;
  document.getElementById("ticket-seats").textContent = booking.seats?.join(", ") || "";
  document.getElementById("ticket-price").textContent = Number(booking.totalPrice || 0).toLocaleString("vi-VN") + " đ";
  document.getElementById("ticket-booked-at").textContent = new Date(booking.bookedAt).toLocaleString("vi-VN");
  
  ticketModal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function closeTicketModal() {
  if (!ticketModal) return;
  ticketModal.classList.add("hidden");
  document.body.style.overflow = "";
  currentTicket = null;
}

// Close modal events
modalOverlay?.addEventListener("click", closeTicketModal);
modalClose?.addEventListener("click", closeTicketModal);
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeTicketModal();
});

// Cancel ticket from modal
ticketCancelBtn?.addEventListener("click", async () => {
  if (!currentTicket) return;
  const { showId, seats, movieTitle } = currentTicket;
  if (!confirm(`Bạn có chắc muốn hủy vé "${movieTitle}"?\nGhế: ${seats?.join(", ")}`)) return;
  
  try {
    const res = await fetchJSON("/api/index.php?route=my-bookings/cancel", {
      method: "POST",
      body: JSON.stringify({ showId, seats }),
    });
    alert(res.message || "Đã hủy vé thành công!");
    closeTicketModal();
    const bookings = await fetchJSON("/api/index.php?route=my-bookings");
    renderBookings(bookings);
  } catch (err) {
    alert(err.message);
  }
});

function renderBookings(data) {
  if (!bookingsSection || !bookingsList) return;
  if (!data || !data.length) {
    bookingsSection.classList.add("hidden");
    return;
  }
  bookingsSection.classList.remove("hidden");
  bookingsList.innerHTML = data
    .map(
      (b, idx) =>
        `<div class="booking-card" data-booking-idx="${idx}">
          <div class="booking-header">
            <h3>${b.movieTitle}</h3>
            <span class="booking-status">✅ Đã thanh toán</span>
          </div>
          <div class="booking-info">
            <div class="info-item">
              <span class="info-icon">📅</span>
              <span>${b.showTime}</span>
            </div>
            <div class="info-item">
              <span class="info-icon">💺</span>
              <span class="seats-badge">${b.seats?.join(", ") || ""}</span>
            </div>
            <div class="info-item">
              <span class="info-icon">💰</span>
              <span class="price-tag">${Number(b.totalPrice || 0).toLocaleString("vi-VN")} đ</span>
            </div>
          </div>
          <div class="booking-footer">
            <span class="booked-time">🕐 ${new Date(b.bookedAt).toLocaleString("vi-VN")}</span>
            <button class="btn-view-detail">Xem chi tiết</button>
          </div>
        </div>`
    )
    .join("");

  // Store bookings data for modal
  bookingsList.bookingsData = data;

  // Click to view detail
  bookingsList.querySelectorAll(".booking-card").forEach((card) => {
    card.addEventListener("click", (e) => {
      // Don't open modal if clicking on button (button has its own handler)
      if (e.target.classList.contains("btn-view-detail") || e.target.closest(".btn-view-detail")) {
        const idx = parseInt(card.dataset.bookingIdx);
        openTicketModal(bookingsList.bookingsData[idx]);
      }
    });
    
    // Also allow clicking the whole card
    card.querySelector(".btn-view-detail")?.addEventListener("click", (e) => {
      e.stopPropagation();
      const idx = parseInt(card.dataset.bookingIdx);
      openTicketModal(bookingsList.bookingsData[idx]);
    });
  });
}

loadUserAndBookings();

