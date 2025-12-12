const seatsEl = document.getElementById("seats");
const statusEl = document.getElementById("status");
const selectedMovieEl = document.getElementById("selected-movie");
const selectedTimeEl = document.getElementById("selected-time");
const selectedPriceEl = document.getElementById("selected-price");
const confirmBtn = document.getElementById("confirm-btn");
const releaseBtn = document.getElementById("release-btn");
const summaryEl = document.getElementById("summary");

let selectedShow = null;
let seatState = [];
let me = null;

async function fetchJSON(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    ...options,
  });
  if (!res.ok) throw new Error(await res.text());
  return res.json();
}

function setShowContext(show) {
  selectedShow = show;
  selectedMovieEl.textContent = show.title;
  selectedTimeEl.textContent = show.time;
  selectedPriceEl.textContent = `Giá vé: ${Number(show.price || 0).toLocaleString("vi-VN")} đ / ghế`;
}

function renderSeats() {
  seatsEl.innerHTML = seatState
    .map(
      (s) => `
    <button class="seat ${s.status} ${s.status === "booked" ? "disabled" : ""}" data-code="${s.code}">
      ${s.code}
    </button>`
    )
    .join("");

  seatsEl.querySelectorAll(".seat").forEach((seat) => {
    seat.addEventListener("click", () => {
      const code = seat.dataset.code;
      const data = seatState.find((s) => s.code === code);
      if (!data || data.status === "booked" || data.status === "held") return;
      toggleSeat(code);
    });
  });

  updateSummary();
}

async function loadSeats() {
  if (!selectedShow) return;
  try {
    const data = await fetchJSON(`/api/index.php?route=seat-map&showId=${selectedShow.show}`);
    seatState = data.seats;
    statusEl.textContent = data.message || "";
    renderSeats();
  } catch (err) {
    statusEl.textContent = err.message;
  }
}

async function toggleSeat(code) {
  if (!selectedShow) return;
  const heldByYou = seatState.filter((s) => s.status === "held-you").map((s) => s.code);
  const exists = heldByYou.includes(code);
  let nextSeats = exists ? heldByYou.filter((s) => s !== code) : [...heldByYou, code];

  // If no seat left, release hold
  if (nextSeats.length === 0) {
    await releaseHold();
    return;
  }
  try {
    const payload = { showId: selectedShow.show, seats: nextSeats };
    const result = await fetchJSON("/api/index.php?route=hold", {
      method: "POST",
      body: JSON.stringify(payload),
    });
    statusEl.textContent = result.message;
    await loadSeats();
  } catch (err) {
    statusEl.textContent = err.message;
  }
}

async function confirmBooking() {
  if (!selectedShow) return;
  try {
    const res = await fetchJSON("/api/index.php?route=booking/confirm", {
      method: "POST",
      body: JSON.stringify({ showId: selectedShow.show }),
    });
    statusEl.textContent = `${res.message} - Tổng tiền ${Number(res.totalPrice || 0).toLocaleString("vi-VN")} đ`;
    alert(`${res.message}\nTổng tiền: ${Number(res.totalPrice || 0).toLocaleString("vi-VN")} đ\nGhế: ${res.booking?.seats?.join(", ") || ""}\nSuất: ${selectedShow.time}`);
    await loadSeats();
  } catch (err) {
    statusEl.textContent = err.message;
  }
}

async function releaseHold() {
  if (!selectedShow) return;
  try {
    const res = await fetchJSON("/api/index.php?route=hold/release", {
      method: "POST",
      body: JSON.stringify({ showId: selectedShow.show }),
    });
    statusEl.textContent = res.message;
    await loadSeats();
  } catch (err) {
    statusEl.textContent = err.message;
  }
}

function updateSummary() {
  const heldByYou = seatState.filter((s) => s.status === "held-you").map((s) => s.code);
  if (!selectedShow) {
    summaryEl.textContent = "";
    return;
  }
  const price = Number(selectedShow.price || 0);
  const total = heldByYou.length * price;
  summaryEl.textContent =
    heldByYou.length === 0
      ? "Chọn ghế để tính tiền."
      : `Ghế đã chọn: ${heldByYou.join(", ")} · Tổng: ${total.toLocaleString("vi-VN")} đ`;
}

confirmBtn.addEventListener("click", confirmBooking);
releaseBtn.addEventListener("click", releaseHold);

// boot
const showId = window.__SHOW_ID__;
if (!showId) {
  statusEl.textContent = "Thiếu mã suất chiếu.";
} else {
  Promise.all([fetchJSON("/api/index.php?route=auth/me"), fetchJSON("/api/index.php?route=movies")])
    .then(([auth, movies]) => {
      me = auth.user;
      if (!me) {
        window.location.href = "/login.php";
        return;
      }
      let found = null;
      movies.forEach((m) => {
        m.showtimes.forEach((s) => {
          if (s.id === showId) {
            found = { show: s.id, time: s.time, title: m.title, price: m.price };
          }
        });
      });
      if (!found) throw new Error("Không tìm thấy suất chiếu.");
      setShowContext(found);
      loadSeats();
    })
    .catch((err) => {
      statusEl.textContent = err.message;
    });
}


