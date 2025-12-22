<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Ghế</title>
    <style>
        /* CSS Dark Mode & Neon */
        body {
            background-color: #120d1d;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background: linear-gradient(145deg, #1f1632, #160f25);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 900px;
            border: 1px solid #332747;
        }

        /* Phần thông tin phim */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #332747;
        }
        
        .movie-title h2 { margin: 0; font-size: 28px; color: #fff; }
        .movie-title p { margin: 5px 0 0; color: #aaa; font-size: 14px; }
        .price-tag { color: #dcb3ff; font-weight: bold; margin-top: 5px; display: block; }

        /* Chú thích ghế */
        .legend {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 13px;
            color: #ccc;
        }
        .legend span { display: flex; align-items: center; }
        .legend span::before {
            content: ''; display: inline-block; width: 16px; height: 16px;
            margin-right: 8px; border-radius: 4px;
        }
        .l-available::before { background: #e0e0e0; }
        .l-selected::before { background: #8a2be2; box-shadow: 0 0 8px #8a2be2; }
        .l-booked::before { background: #444; }

        /* Màn hình */
        .screen {
            background: linear-gradient(to bottom, rgba(255,255,255,0.1), transparent);
            height: 40px;
            width: 80%;
            margin: 0 auto 30px;
            border-top: 4px solid #aa4bfe;
            border-radius: 50% 50% 0 0 / 20px 20px 0 0;
            box-shadow: 0 -5px 20px rgba(170, 75, 254, 0.3);
            text-align: center;
            line-height: 40px;
            color: #aa4bfe;
            font-size: 12px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        /* Lưới ghế */
        .seat-map {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .row {
            display: flex;
            gap: 8px;
        }

        .seat {
            width: 40px;
            height: 35px;
            background-color: #e0e0e0;
            border-radius: 6px;
            border: none;
            font-size: 11px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: 0.2s;
        }
        
        .seat:hover:not(.booked) { transform: scale(1.15); background-color: #fff; }
        
        .seat.selected {
            background-color: #8a2be2;
            color: white;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.8);
            border: 1px solid #dcb3ff;
        }
        
        .seat.booked {
            background-color: #3e3e3e;
            color: #666;
            cursor: not-allowed;
            border: 1px solid #333;
        }

        /* Footer */
        .footer-action {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #160f25;
            padding: 20px;
            border-radius: 12px;
        }

        .total-price { font-size: 18px; color: #fff; }
        .total-price span { color: #dcb3ff; font-weight: bold; font-size: 24px; }

        .btn-confirm {
            background: linear-gradient(90deg, #aa4bfe, #d62976);
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 0 20px rgba(214, 41, 118, 0.4);
            transition: 0.3s;
        }
        .btn-confirm:hover { transform: translateY(-2px); filter: brightness(1.2); }

    </style>
</head>
<body>

<div class="container">
    <div class="header-section">
        <div class="movie-title">
            <span style="color:#aa4bfe; font-size:12px; font-weight:bold;">ĐANG ĐẶT VÉ</span>
            <h2>Interstellar</h2>
            <p>21:00 - Hôm nay | Rạp 3 - Tầng 2</p>
            <span class="price-tag">115.000 đ / ghế</span>
        </div>
        <div>
            <button onclick="history.back()" style="background:transparent; border:1px solid #555; color:#ccc; padding:8px 15px; border-radius:5px; cursor:pointer;">Quay lại</button>
        </div>
    </div>

    <div class="legend">
        <span class="l-available">Trống</span>
        <span class="l-selected">Đang chọn</span>
        <span class="l-booked">Đã đặt</span>
    </div>

    <div class="screen">MÀN HÌNH</div>

    <div class="seat-map" id="seatContainer"></div>

    <div class="footer-action">
        <div class="total-info">
            <div style="font-size:13px; color:#aaa; margin-bottom:5px;">Ghế bạn chọn: <span id="selectedSeatsText" style="color:#fff">...</span></div>
            <div class="total-price">Tổng tiền: <span id="totalPrice">0</span> đ</div>
        </div>
        <button class="btn-confirm" onclick="alert('Đặt vé thành công!')">Xác nhận đặt vé</button>
    </div>
    
    <p style="text-align:center; font-size:12px; color:#555; margin-top:10px;">Ghế sẽ được giữ trong 5 phút</p>
</div>

<script>
    const rows = ['A', 'B', 'C', 'D', 'E', 'F'];
    const seatsPerRow = 10;
    const pricePerSeat = 115000;
    const container = document.getElementById('seatContainer');
    let selectedSeats = [];

    // Tạo ghế
    rows.forEach(rowChar => {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row';
        
        for (let i = 1; i <= seatsPerRow; i++) {
            const seatId = rowChar + i;
            const btn = document.createElement('button');
            btn.className = 'seat';
            btn.innerText = seatId;
            
            // Random ghế đã đặt
            if (Math.random() < 0.15) {
                btn.classList.add('booked');
            } else {
                btn.onclick = () => toggleSeat(btn, seatId);
            }
            
            rowDiv.appendChild(btn);
        }
        container.appendChild(rowDiv);
    });

    function toggleSeat(btn, id) {
        if (btn.classList.contains('selected')) {
            btn.classList.remove('selected');
            selectedSeats = selectedSeats.filter(s => s !== id);
        } else {
            btn.classList.add('selected');
            selectedSeats.push(id);
        }
        updateUI();
    }

    function updateUI() {
        const text = selectedSeats.length > 0 ? selectedSeats.join(', ') : '...';
        document.getElementById('selectedSeatsText').innerText = text;
        
        const total = selectedSeats.length * pricePerSeat;
        document.getElementById('totalPrice').innerText = total.toLocaleString('vi-VN');
    }
</script>

</body>
</html>
