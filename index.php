<?php
// Hubungkan ke file koneksi sentral
include 'koneksi.php';

// Aktifkan pelaporan error sementara (agar jika ada masalah, muncul teks, bukan layar putih)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Menangkap kategori dari URL
    $kategori_aktif = isset($_GET['kategori']) ? $_GET['kategori'] : 'Burger';

    // Mengambil data kategori (NAMA TABEL HURUF KECIL SESUAI HOSTING)
    $sql_kat = "SELECT * FROM kategori";
    $result_kat = $conn->query($sql_kat);

    // Mengambil data menu
    $sql_menu = "SELECT m.*, k.nama_kategori FROM menu m 
                 JOIN kategori k ON m.id_kategori = k.id_kategori 
                 WHERE k.nama_kategori = '$kategori_aktif'";
    $result_menu = $conn->query($sql_menu);

} catch (Exception $e) {
    // Jika masih ada error database, tampilkan pesannya
    die("<div style='padding: 20px; background: #ffcccc; color: #d32f2f; margin: 20px; border-radius: 10px; font-family: sans-serif;'>
            <b>Oops! Ada masalah pada Database:</b><br>" . $e->getMessage() . "
         </div>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Burger Kita - Self Order</title>
    <!-- PENAMBAHAN BOOTSTRAP 5 SESUAI TECH STACK -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        /* CSS Terpadu */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; height: 100vh; background-color: #f4f4f6; flex-direction: column; color: #222; overflow: hidden; }
        
        /* CSS LOCKSCREEN POSTER */
        .lockscreen {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background-image: url('img/poster_burger.png'); 
            background-size: cover; background-position: center; z-index: 9999;
            display: flex; flex-direction: column; justify-content: flex-end; align-items: center;
            padding-bottom: 60px; user-select: none; cursor: pointer; 
        }
        .tap-hint {
            color: #ffffff; text-align: center; font-size: 26px; font-weight: 800;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.9), 0 0 25px rgba(211,47,47,0.9);
            animation: pulse 1.5s infinite; background: rgba(0,0,0,0.4);
            padding: 15px 35px; border-radius: 50px; border: 2px solid rgba(255,255,255,0.3); backdrop-filter: blur(5px);
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(255, 255, 255, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }

        /* CSS APLIKASI UTAMA */
        .main-container { display: flex; flex: 1; overflow: hidden; margin-bottom: 80px; }
        .sidebar { width: 110px; background: linear-gradient(180deg, #1c1c1c 0%, #3a3a3a 100%); display: flex; flex-direction: column; align-items: center; padding-top: 20px; color: white; border-radius: 0 30px 30px 0; box-shadow: 4px 0 15px rgba(0,0,0,0.1); }
        .table-box { background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; font-weight: bold; padding: 15px 10px; border-radius: 15px; text-align: center; margin-bottom: 30px; width: 80%; }
        .table-box select { margin-top: 8px; width: 100%; padding: 5px; font-size: 18px; font-weight: bold; border-radius: 8px; border: none; text-align: center; background: #fff; color: #333; cursor: pointer; }
        .menu-icon { text-align: center; font-size: 14px; cursor: pointer; opacity: 0.8; transition: 0.3s; }
        .menu-icon:hover { opacity: 1; }
        .content { flex: 1; padding: 25px 45px; overflow-y: auto; }
        .category-nav { display: flex; gap: 30px; border-bottom: 2px solid #e0e0e0; padding-bottom: 15px; margin-bottom: 20px; }
        .category-nav a { text-decoration: none; color: #888; font-size: 18px; font-weight: 600; padding: 5px 0; position: relative; transition: 0.3s; }
        .category-nav a:hover { color: #1c1c1c; }
        .category-nav a.active { color: #d32f2f; }
        .category-nav a.active::after { content: ''; position: absolute; bottom: -17px; left: 0; width: 100%; height: 3px; background-color: #d32f2f; border-radius: 3px; }
        .subtitle { color: #666; font-size: 15px; margin-bottom: 25px; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 25px; padding-bottom: 40px;}
        .menu-card { background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 6px 15px rgba(0,0,0,0.06); transition: transform 0.3s, box-shadow 0.3s; cursor: pointer; position: relative; border: 1px solid #f0f0f0; }
        .menu-card:hover { transform: translateY(-7px); box-shadow: 0 12px 20px rgba(0,0,0,0.1); }
        .menu-card img { width: 100%; height: 180px; object-fit: cover; }
        .menu-info { padding: 18px; text-align: center; }
        .menu-name { font-size: 17px; font-weight: 700; margin-bottom: 8px; color: #1a1a1a; }
        .menu-price { color: #d32f2f; font-weight: 800; font-size: 19px; }
        .bottom-bar { position: fixed; bottom: 0; width: 100%; background: white; height: 80px; display: flex; justify-content: flex-end; align-items: center; padding: 0 45px; box-shadow: 0 -4px 20px rgba(0,0,0,0.08); gap: 15px; z-index: 10; border-top: 1px solid #eee; }
        .btn { padding: 12px 24px; border-radius: 25px; font-weight: 600; cursor: pointer; border: 1px solid #ddd; background: white; color: #444; transition: 0.2s; }
        .btn:hover { background: #f9f9f9; }
        .btn-cart { background-color: #d32f2f; color: white; border: none; font-size: 16px; padding: 14px 35px; box-shadow: 0 4px 10px rgba(211,47,47,0.3); display: flex; align-items: center; gap: 10px; }
        .btn-cart:hover { background-color: #b71c1c; transform: scale(1.02); }
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
        .modal-content { background-color: #fff; padding: 30px; border-radius: 20px; width: 380px; max-width: 90%; text-align: center; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.2); animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 28px; font-weight: bold; color: #aaa; cursor: pointer; }
        .close-btn:hover { color: #333; }
        .modal-img { width: 100%; height: 200px; object-fit: cover; border-radius: 12px; margin-bottom: 20px; }
        .modal-title { font-size: 22px; font-weight: 800; color: #111; margin-bottom: 10px; }
        .modal-desc { font-size: 14px; color: #666; margin-bottom: 20px; line-height: 1.5; }
        .modal-price { font-size: 22px; color: #d32f2f; font-weight: bold; margin-bottom: 25px; }
        .qty-controls { display: flex; align-items: center; justify-content: center; gap: 20px; margin-bottom: 25px; }
        .qty-btn { background: #f0f0f0; border: none; width: 40px; height: 40px; border-radius: 10px; font-size: 20px; font-weight: bold; cursor: pointer; color: #333; transition: 0.2s; }
        .qty-btn:hover { background: #e0e0e0; }
        .qty-number { font-size: 24px; font-weight: bold; width: 30px; text-align: center; }
    </style>
</head>
<body onload="initApp()">

    <div id="lockscreen" class="lockscreen">
        <div class="tap-hint">
            👆 Sentuh Layar<br>Untuk Memesan
        </div>
    </div>

    <script>
        var layar = document.getElementById('lockscreen');
        if (sessionStorage.getItem('kiosk_unlocked') === 'true') {
            layar.style.display = 'none';
        } else {
            function bukaKunci() {
                if (layar.style.display !== 'none') {
                    layar.style.transition = 'opacity 0.5s ease';
                    layar.style.opacity = '0';
                    sessionStorage.setItem('kiosk_unlocked', 'true');
                    setTimeout(function(){ layar.style.display = 'none'; }, 500);
                }
            }
            window.addEventListener('click', bukaKunci);
            window.addEventListener('touchstart', bukaKunci);
        }
    </script>

    <div class="main-container">
        <div class="sidebar">
            <div class="table-box">
                Meja
                <select id="selectMeja" onchange="simpanMeja()">
                    <?php 
                    try {
                        // Nama tabel diperbarui jadi huruf kecil
                        $sql_occupied = "SELECT DISTINCT m.no_meja FROM pelanggan_meja m 
                                         JOIN pesanan p ON m.id_pelanggan = p.id_pelanggan 
                                         WHERE p.status NOT IN ('Lunas', 'Selesai')";
                        $result_occ = $conn->query($sql_occupied);
                        $meja_terisi = [];
                        if($result_occ && $result_occ->num_rows > 0) {
                            while($row_occ = $result_occ->fetch_assoc()) {
                                $meja_terisi[] = $row_occ['no_meja'];
                            }
                        }

                        $sql_meja = "SELECT nomor_meja FROM meja ORDER BY nomor_meja ASC";
                        $result_meja = $conn->query($sql_meja);
                        
                        if($result_meja && $result_meja->num_rows > 0) {
                            while($m = $result_meja->fetch_assoc()) {
                                $no = $m['nomor_meja'];
                                if(in_array($no, $meja_terisi)) {
                                    echo '<option value="'.$no.'" disabled style="background-color: #ddd; color: #888; font-style: italic;">Meja '.$no.' (Terisi)</option>';
                                } else {
                                    echo '<option value="'.$no.'">Meja '.$no.'</option>';
                                }
                            }
                        } else {
                            echo '<option value="0">Belum ada meja</option>';
                        }
                    } catch (Exception $e) {
                         echo '<option value="0">Error Load Meja</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="menu-icon">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="white"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/></svg><br>
                Menu
            </div>
        </div>

        <div class="content">
            <div class="category-nav">
                <?php 
                if (isset($result_kat) && $result_kat && $result_kat->num_rows > 0) {
                    while($row_kat = $result_kat->fetch_assoc()): 
                ?>
                    <a href="?kategori=<?php echo urlencode($row_kat['nama_kategori']); ?>" 
                       class="<?php echo ($kategori_aktif == $row_kat['nama_kategori']) ? 'active' : ''; ?>">
                       <?php echo htmlspecialchars($row_kat['nama_kategori']); ?>
                    </a>
                <?php 
                    endwhile; 
                } 
                ?>
            </div>

            <p class="subtitle">Menampilkan kategori: <strong><?php echo htmlspecialchars($kategori_aktif); ?></strong></p>

            <div class="menu-grid">
                <?php 
                if(isset($result_menu) && $result_menu && $result_menu->num_rows > 0): 
                    while($row = $result_menu->fetch_assoc()): 
                        $nama_aman = htmlspecialchars($row['nama_menu'], ENT_QUOTES);
                        $desc = "Sajian lezat " . $nama_aman . " yang diracik khusus dengan bahan berkualitas untuk kepuasan Anda.";
                        $img_aman = htmlspecialchars($row['gambar'], ENT_QUOTES);
                ?>
                    <div class="menu-card" 
                         data-id="<?php echo $row['id_menu']; ?>"
                         data-nama="<?php echo $nama_aman; ?>"
                         data-harga="<?php echo $row['harga']; ?>"
                         data-desc="<?php echo $desc; ?>"
                         data-img="img/<?php echo $img_aman; ?>"
                         onclick="triggerModal(this)">
                         
                        <img src="img/<?php echo $img_aman; ?>" alt="<?php echo $nama_aman; ?>" onerror="this.src='https://via.placeholder.com/250x180/3a3a3a/ffffff?text=No+Image'">
                        <div class="menu-info">
                            <div class="menu-name"><?php echo $row['nama_menu']; ?></div>
                            <div class="menu-price">Rp<?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <p style="color: #666;">Menu tidak tersedia pada kategori ini. (Pastikan tabel database Anda sudah terisi).</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bottom-bar">
        <button class="btn" onclick="resetAplikasi()" style="border-color: #ffcccc; color: #d32f2f; font-size: 13px;">🔄 Reset Sistem</button>
        <button onclick="document.getElementById('riwayatModal').style.display='flex'">Riwayat Pesanan</button>
        <button class="btn btn-cart" onclick="lihatKeranjang()">
            🛒 Keranjang: <span id="cart-total-item">0</span> Item | Rp<span id="cart-total-price">0</span>
        </button>
    </div>

    <div id="opsiModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="tutupModal()">&times;</span>
            <img id="modalImg" class="modal-img" src="" alt="Menu Image">
            <h2 id="modalNama" class="modal-title">Nama Menu</h2>
            <p id="modalDesc" class="modal-desc">Deskripsi singkat menu akan muncul di sini.</p>
            <h3 id="modalHarga" class="modal-price">Rp0</h3>
            
            <div class="qty-controls">
                <button class="qty-btn" onclick="ubahQty(-1)">-</button>
                <span id="modalQty" class="qty-number">1</span>
                <button class="qty-btn" onclick="ubahQty(1)">+</button>
            </div>
            
            <button class="btn btn-cart" style="width: 100%; justify-content: center; font-size: 18px;" onclick="simpanKeKeranjang()">Tambah Pesanan</button>
        </div>
    </div>

    <div id="paymentModal" class="modal">
        <div class="modal-content" style="width: 420px;">
            <span class="close-btn" onclick="tutupPaymentModal()">&times;</span>
            <h2 class="modal-title" style="margin-bottom: 5px;">Checkout Pesanan</h2>
            <p class="modal-desc" style="margin-bottom: 15px;">Selesaikan pembayaran Anda untuk melanjutkan.</p>
            
            <div style="background: #ffffff; border: 1px solid #eee; border-radius: 12px; padding: 15px; margin-bottom: 20px; max-height: 180px; overflow-y: auto; text-align: left; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02);">
                <p style="font-size: 14px; font-weight: bold; color: #555; margin-bottom: 10px; border-bottom: 2px solid #f0f0f0; padding-bottom: 5px;">Rincian Pesanan:</p>
                <div id="paymentItemList">
                    </div>
            </div>
            
            <div style="background: #f4f4f6; padding: 15px 20px; border-radius: 15px; margin-bottom: 20px; border: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                <p style="font-size: 15px; color: #555; font-weight: bold; margin: 0;">Total Tagihan</p>
                <h2 id="paymentTotal" style="color: #d32f2f; font-size: 28px; margin: 0;">Rp0</h2>
            </div>
            
            <div style="text-align: left; margin-bottom: 25px;">
                <label style="font-weight: 700; color: #333; margin-bottom: 10px; display: block; font-size: 14px;">Metode Pembayaran:</label>
                <select id="pilihMetodeBayar" style="width: 100%; padding: 12px; border-radius: 10px; border: 2px solid #ddd; font-size: 15px; font-weight: 600; color: #222; outline: none; cursor: pointer;">
                    <option value="QRIS">📱 QRIS (Scan Barcode)</option>
                    <option value="Transfer Bank">🏧 Transfer Bank (Virtual Account)</option>
                    <option value="Kartu Debit/Kredit">💳 Kartu Debit / Kredit</option>
                    <option value="Tunai (Kasir)">💵 Tunai (Bayar di Kasir)</option>
                </select>
            </div>
            
            <button class="btn btn-cart" style="width: 100%; justify-content: center; font-size: 18px; padding: 16px;" onclick="simulasiBayar()">Bayar Sekarang</button>
        </div>
    </div>
            
            <button class="btn btn-cart" style="width: 100%; justify-content: center; font-size: 18px; padding: 16px;" onclick="simulasiBayar()">Bayar Sekarang</button>
        </div>
    </div>

    <script>
        // SISA JAVASCRIPT APLIKASI
        let keranjang = [];
        let tempItem = {}; 

        function initApp() {
            try {
                let savedMeja = localStorage.getItem('nomorMeja');
                let selectEl = document.getElementById('selectMeja');
                
                if(savedMeja && selectEl) {
                    let optionToSelect = selectEl.querySelector(`option[value="${savedMeja}"]`);
                    if(optionToSelect && !optionToSelect.disabled) {
                        selectEl.value = savedMeja;
                    } else {
                        let firstAvailable = selectEl.querySelector('option:not([disabled])');
                        if(firstAvailable) {
                            selectEl.value = firstAvailable.value;
                            simpanMeja();
                        }
                    }
                }
                
                let savedKeranjang = localStorage.getItem('keranjangResto');
                if(savedKeranjang) {
                    keranjang = JSON.parse(savedKeranjang);
                }
                updateTampilanKeranjang();
            } catch(e) {
                console.error("Gagal memuat aplikasi:", e);
            }
        }

        function resetAplikasi() {
            localStorage.removeItem('nomorMeja');
            localStorage.removeItem('keranjangResto');
            sessionStorage.removeItem('kiosk_unlocked');
            alert('Sistem telah di-reset.');
            window.location.reload(true);
        }

        function simpanMeja() {
            let meja = document.getElementById('selectMeja').value;
            localStorage.setItem('nomorMeja', meja);
        }

        function triggerModal(element) {
            let id = element.getAttribute('data-id');
            let nama = element.getAttribute('data-nama');
            let harga = parseInt(element.getAttribute('data-harga'));
            let desc = element.getAttribute('data-desc');
            let imgSrc = element.getAttribute('data-img');

            tempItem = { id: id, nama: nama, harga: harga, qty: 1 }; 
            
            document.getElementById('modalNama').innerText = nama;
            document.getElementById('modalDesc').innerText = desc;
            document.getElementById('modalHarga').innerText = "Rp" + harga.toLocaleString('id-ID');
            document.getElementById('modalImg').src = imgSrc;
            document.getElementById('modalQty').innerText = tempItem.qty;
            
            document.getElementById('opsiModal').style.display = 'flex';
        }

        function tutupModal() { 
            document.getElementById('opsiModal').style.display = 'none'; 
        }

        function ubahQty(perubahan) {
            if(tempItem.qty + perubahan >= 1) {
                tempItem.qty += perubahan;
                document.getElementById('modalQty').innerText = tempItem.qty;
                document.getElementById('modalHarga').innerText = "Rp" + (tempItem.qty * tempItem.harga).toLocaleString('id-ID');
            }
        }

        function simpanKeKeranjang() {
            let existingItemIndex = keranjang.findIndex(i => i.id === tempItem.id);
            if(existingItemIndex > -1) {
                keranjang[existingItemIndex].qty += tempItem.qty;
            } else {
                keranjang.push({...tempItem});
            }

            localStorage.setItem('keranjangResto', JSON.stringify(keranjang));
            updateTampilanKeranjang();
            tutupModal();
        }

        function updateTampilanKeranjang() {
            let totalItem = 0, totalPrice = 0;
            keranjang.forEach(item => {
                totalItem += item.qty;
                totalPrice += (item.qty * item.harga);
            });
            document.getElementById('cart-total-item').innerText = totalItem;
            document.getElementById('cart-total-price').innerText = totalPrice.toLocaleString('id-ID');
        }

        function lihatKeranjang() {
            if(keranjang.length === 0) {
                alert("Keranjang masih kosong. Silakan pilih menu terlebih dahulu.");
                return;
            }
            
            let totalBiaya = 0;
            let listHTML = '<ul style="list-style: none; padding: 0; margin: 0;">';
            
            // Buat daftar item dari keranjang
            keranjang.forEach(item => {
                let subtotal = item.harga * item.qty;
                totalBiaya += subtotal;
                
                listHTML += `
                    <li style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #eee; padding: 10px 0;">
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: #222; font-size: 15px;">${item.nama}</div>
                            <div style="font-size: 13px; color: #888;">${item.qty}x @ Rp${item.harga.toLocaleString('id-ID')}</div>
                        </div>
                        <div style="font-weight: 800; color: #d32f2f; font-size: 15px;">
                            Rp${subtotal.toLocaleString('id-ID')}
                        </div>
                    </li>
                `;
            });
            
            listHTML += '</ul>';
            
            // Masukkan daftar ke HTML dan perbarui total
            document.getElementById('paymentItemList').innerHTML = listHTML;
            document.getElementById('paymentTotal').innerText = "Rp" + totalBiaya.toLocaleString('id-ID');
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function tutupPaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function simulasiBayar() {
            let noMeja = document.getElementById('selectMeja').value;
            let metode = document.getElementById('pilihMetodeBayar').value;
            let totalBiaya = keranjang.reduce((sum, item) => sum + (item.harga * item.qty), 0);
            
            if(!noMeja || noMeja == "0") {
                alert("Maaf, tidak ada meja kosong saat ini.");
                return;
            }
            
            if(metode !== "Tunai (Kasir)") {
                alert("⏳ Membuka gerbang pembayaran " + metode + "...\n\n✅ (Simulasi) Pembayaran Berhasil Dikonfirmasi!");
            } else {
                alert("⏳ Memproses pesanan...\n\nSilakan siapkan uang tunai dan lakukan pembayaran langsung di meja kasir.\nTerima kasih");
            }

            fetch('proses_pesanan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ meja: noMeja, total: totalBiaya, metode: metode, items: keranjang })
            })
            .then(response => response.text())
            .then(result => {
                alert("Sistem: " + result + ". Pesanan Anda sedang diproses!");
                keranjang = [];
                localStorage.removeItem('keranjangResto');
                sessionStorage.removeItem('kiosk_unlocked');
                window.location.reload(); 
            });
        }
        
        window.onclick = function(event) {
            let opsiModal = document.getElementById('opsiModal');
            let paymentModal = document.getElementById('paymentModal');
            
            if (event.target == opsiModal) tutupModal();
            if (event.target == paymentModal) tutupPaymentModal();
        }
    </script>
<!-- MODAL RIWAYAT PESANAN (DARK AESTHETIC) -->
<div id="riwayatModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(5px); align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #1a1a24; border: 1px solid #2d2d3f; border-radius: 16px; width: 400px; padding: 25px; color: #e0e0e0; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <span class="close-btn" onclick="document.getElementById('riwayatModal').style.display='none'" style="color: #a4b0be; float: right; font-size: 24px; cursor: pointer; transition: 0.3s;">&times;</span>
        <h2 style="margin-top: 0; color: #fff; border-bottom: 1px solid #2d2d3f; padding-bottom: 12px; font-weight: 600;">🕒 Riwayat Pesanan Meja</h2>
        
        <div id="isiRiwayat" style="margin-top: 20px; max-height: 250px; overflow-y: auto;">
            <!-- Isi riwayat akan dimunculkan di sini -->
            <p style="color: #a4b0be; text-align: center; font-style: italic; font-size: 14px;">Pilih meja terlebih dahulu atau belum ada pesanan di meja ini.</p>
        </div>
    </div>
</div>
</body>
</html>