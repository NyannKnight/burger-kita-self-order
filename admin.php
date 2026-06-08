<?php
// admin.php
include 'koneksi.php';

// --- LOGIKA PEMROSESAN FORM ---

// 1. Simpan Pengaturan Refresh Otomatis
if (isset($_POST['simpan_pengaturan'])) {
    $waktu = max(5, (int)$_POST['refresh_waktu']); // Minimal 5 detik agar tidak crash
    $conn->query("UPDATE pengaturan SET nilai='$waktu' WHERE nama_pengaturan='refresh_waktu'");
    header("Location: admin.php?page=dashboard");
    exit;
}

// 2. Tambah / Edit / Hapus MENU
if (isset($_POST['simpan_menu'])) {
    $nama = $_POST['nama_menu'];
    $harga = $_POST['harga'];
    $kategori = $_POST['id_kategori'];
    $id_menu = $_POST['id_menu'] ?? '';
    
    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];
    
    if ($id_menu == '') { 
        if($gambar != '') { move_uploaded_file($tmp, "img/".$gambar); }
        $conn->query("INSERT INTO menu (id_kategori, nama_menu, harga, gambar) VALUES ('$kategori', '$nama', '$harga', '$gambar')");
    } else { 
        if ($gambar != '') { 
            move_uploaded_file($tmp, "img/".$gambar);
            $conn->query("UPDATE menu SET id_kategori='$kategori', nama_menu='$nama', harga='$harga', gambar='$gambar' WHERE id_menu='$id_menu'");
        } else { 
            $conn->query("UPDATE menu SET id_kategori='$kategori', nama_menu='$nama', harga='$harga' WHERE id_menu='$id_menu'");
        }
    }
    header("Location: admin.php?page=menu");
    exit;
}

if (isset($_GET['hapus_menu'])) {
    $id = $_GET['hapus_menu'];
    $conn->query("DELETE FROM menu WHERE id_menu='$id'");
    header("Location: admin.php?page=menu");
    exit;
}

// 3. Tambah / Hapus / Kosongkan MEJA
if (isset($_POST['tambah_meja'])) {
    $nomor = $_POST['nomor_meja'];
    $cek = $conn->query("SELECT * FROM meja WHERE nomor_meja='$nomor'");
    if($cek->num_rows == 0) {
        $conn->query("INSERT INTO meja (nomor_meja) VALUES ('$nomor')");
    }
    header("Location: admin.php?page=meja");
    exit;
}
if (isset($_GET['hapus_meja'])) {
    $id = $_GET['hapus_meja'];
    $conn->query("DELETE FROM meja WHERE id_meja='$id'");
    header("Location: admin.php?page=meja");
    exit;
}
if (isset($_GET['kosongkan_meja'])) {
    $no_meja = $_GET['kosongkan_meja'];
    $conn->query("UPDATE pesanan p JOIN pelanggan_meja m ON p.id_pelanggan = m.id_pelanggan 
                  SET p.status = 'Selesai' 
                  WHERE m.no_meja = '$no_meja' AND p.status NOT IN ('Lunas', 'Selesai')");
    header("Location: admin.php?page=meja");
    exit;
}

// 4. RESET LAYANAN PENJUALAN (OMZET)
if (isset($_POST['reset_laporan'])) {
    // 1. Hapus detail pesanan terlebih dahulu untuk menghindari error relasi
    $conn->query("DELETE FROM detail_pesanan");
    
    // 2. Hapus data pesanan utama
    $conn->query("DELETE FROM pesanan");
    
    // 3. RESET PENGHITUNG ID (AUTO INCREMENT) KEMBALI KE 1
    $conn->query("ALTER TABLE pesanan AUTO_INCREMENT = 1");
    $conn->query("ALTER TABLE detail_pesanan AUTO_INCREMENT = 1");
    
    // Redirect kembali ke halaman keuangan agar data langsung segar
    header("Location: admin.php?page=keuangan");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Burger Kita</title>
    <!-- PENAMBAHAN BOOTSTRAP 5 SESUAI TECH STACK -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        /* Desain UI Admin Gelap yang Estetis */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { display: flex; height: 100vh; background-color: #0f0f13; color: #e0e0e0; }
        .sidebar { width: 250px; background: #1a1a24; border-right: 1px solid #2d2d3f; padding: 20px 0; }
        .sidebar h2 { text-align: center; color: #ff4757; margin-bottom: 30px; letter-spacing: 1px; }
        .sidebar a { display: block; padding: 15px 25px; color: #a4b0be; text-decoration: none; font-size: 16px; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #2d2d3f; color: #fff; border-left-color: #ff4757; }
        .content { flex: 1; padding: 40px; overflow-y: auto; }
        h1 { font-size: 28px; margin-bottom: 25px; color: #fff; font-weight: 600; }
        
        table { width: 100%; border-collapse: collapse; background: #1a1a24; border-radius: 8px; overflow: hidden; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #2d2d3f; }
        th { background: #222230; color: #ff4757; font-weight: 600; }
        tr:hover { background: #252535; }
        
        .form-box { background: #1a1a24; padding: 25px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #2d2d3f; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; color: #a4b0be; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; background: #0f0f13; border: 1px solid #2d2d3f; color: #fff; border-radius: 5px; outline: none; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; color: white; display: inline-block; text-decoration: none;}
        .btn-primary { background: #ff4757; } .btn-primary:hover { background: #ff6b81; }
        .btn-success { background: #2ed573; } .btn-success:hover { background: #26b963; }
        .btn-danger { background: #ff6348; padding: 6px 12px; font-size: 13px; }
        
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: #1a1a24; padding: 25px; border-radius: 8px; border: 1px solid #2d2d3f; text-align: center; }
        .card h3 { color: #a4b0be; font-size: 16px; margin-bottom: 10px; }
        .card .amount { font-size: 28px; font-weight: bold; color: #2ed573; }
        
        /* Modal */
        .admin-modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.75); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
        .admin-modal-content { background: #1a1a24; padding: 30px; border-radius: 12px; width: 450px; max-width: 90%; border: 1px solid #2d2d3f; position: relative; }
        .admin-close { position: absolute; top: 15px; right: 20px; color: #a4b0be; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🍔 ADMIN KITA</h2>
        <a href="?page=dashboard" class="<?php echo $page=='dashboard'?'active':''; ?>">📊 Dashboard</a>
        <a href="?page=menu" class="<?php echo $page=='menu'?'active':''; ?>">🍔 Kelola Menu</a>
        <a href="?page=meja" class="<?php echo $page=='meja'?'active':''; ?>">🍽️ Kelola Meja</a>
        <a href="?page=keuangan" class="<?php echo $page=='keuangan'?'active':''; ?>">💰 Laporan Penjualan</a>
        <a href="index.php" target="_blank" style="margin-top: 50px; color:#1e90ff;">Lihat Web Pelanggan ➔</a>
    </div>

    <div class="content">
        <?php if ($page == 'dashboard'): ?>
            <h1>Dashboard Ringkasan</h1>
            <?php 
                $menu_count = $conn->query("SELECT COUNT(*) as tot FROM menu")->fetch_assoc()['tot'];
                $meja_count = $conn->query("SELECT COUNT(*) as tot FROM meja")->fetch_assoc()['tot'];
                
                $q_waktu = $conn->query("SELECT nilai FROM pengaturan WHERE nama_pengaturan='refresh_waktu'");
                $waktu_sekarang = ($q_waktu && $q_waktu->num_rows > 0) ? $q_waktu->fetch_assoc()['nilai'] : '15';
            ?>
            <div class="card-grid">
                <div class="card"><h3>Total Menu Aktif</h3><div class="amount" style="color:#1e90ff;"><?php echo $menu_count; ?></div></div>
                <div class="card"><h3>Total Meja Tersedia</h3><div class="amount" style="color:#1e90ff;"><?php echo $meja_count; ?></div></div>
            </div>

            <div class="form-box" style="max-width: 500px;">
                <h3>⚙️ Pengaturan Sistem</h3><br>
                <form method="POST">
                    <div class="form-group">
                        <label>Kecepatan Refresh Otomatis Kasir & Dapur (Detik)</label>
                        <input type="number" name="refresh_waktu" value="<?php echo $waktu_sekarang; ?>" required min="5">
                        <small style="color:#a4b0be; margin-top:5px; display:block;">Disarankan: 15 atau 30 detik.</small>
                    </div>
                    <button type="submit" name="simpan_pengaturan" class="btn btn-primary">Simpan Pengaturan</button>
                </form>
            </div>

        <?php elseif ($page == 'menu'): ?>
            <h1>Manajemen Menu Restoran</h1>
            <div class="form-box">
                <h3>Tambah Menu Baru</h3><br>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Menu</label>
                        <input type="text" name="nama_menu" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="id_kategori">
                            <?php 
                            $kats = $conn->query("SELECT * FROM kategori");
                            while($k = $kats->fetch_assoc()) { echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>"; }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" required>
                    </div>
                    <div class="form-group">
                        <label>Upload Gambar (.jpg)</label>
                        <input type="file" name="gambar" accept=".jpg, .jpeg, .png" required>
                    </div>
                    <button type="submit" name="simpan_menu" class="btn btn-primary">Tambah Menu Baru</button>
                </form>
            </div>

            <table>
                <tr><th>Gambar</th><th>Nama Menu</th><th>Kategori</th><th>Harga</th><th>Aksi</th></tr>
                <?php
                $menus = $conn->query("SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON m.id_kategori = k.id_kategori ORDER BY m.id_kategori");
                while($m = $menus->fetch_assoc()):
                ?>
                <tr>
                    <td><img src="img/<?php echo $m['gambar']; ?>" style="width:50px; height:50px; object-fit:cover; border-radius:5px;"></td>
                    <td><?php echo $m['nama_menu']; ?></td>
                    <td><?php echo $m['nama_kategori']; ?></td>
                    <td>Rp<?php echo number_format($m['harga'], 0, ',', '.'); ?></td>
                    <td>
                        <button class="btn btn-primary" style="padding: 6px 12px; font-size:13px;" onclick="editMenu('<?php echo $m['id_menu']; ?>', '<?php echo addslashes($m['nama_menu']); ?>', '<?php echo $m['id_kategori']; ?>', '<?php echo $m['harga']; ?>')">Edit</button>
                        <a href="?page=menu&hapus_menu=<?php echo $m['id_menu']; ?>" class="btn btn-danger" onclick="return confirm('Hapus menu ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

            <div id="editMenuModal" class="admin-modal">
                <div class="admin-modal-content">
                    <span class="admin-close" onclick="document.getElementById('editMenuModal').style.display='none'">&times;</span>
                    <h2 style="margin-bottom: 20px; color: #fff;">✏️ Edit Menu</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_menu" id="edit_id_menu">
                        <div class="form-group"><label>Nama Menu</label><input type="text" name="nama_menu" id="edit_nama" required></div>
                        <div class="form-group"><label>Kategori</label>
                            <select name="id_kategori" id="edit_kategori">
                                <?php $kats_edit = $conn->query("SELECT * FROM kategori"); while($k = $kats_edit->fetch_assoc()) { echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>"; } ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Harga (Rp)</label><input type="number" name="harga" id="edit_harga" required></div>
                        <div class="form-group"><label>Ganti Gambar (.jpg)</label><input type="file" name="gambar" accept=".jpg, .jpeg, .png">
                        <small style="color:#a4b0be;">*Kosongkan jika tidak ubah gambar.</small></div>
                        <button type="submit" name="simpan_menu" class="btn btn-primary" style="width: 100%;">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
            <script>
                function editMenu(id, nama, kategori, harga) {
                    document.getElementById('edit_id_menu').value = id;
                    document.getElementById('edit_nama').value = nama;
                    document.getElementById('edit_kategori').value = kategori;
                    document.getElementById('edit_harga').value = harga;
                    document.getElementById('editMenuModal').style.display = 'flex';
                }
            </script>

        <?php elseif ($page == 'meja'): ?>
            <h1>Manajemen Nomor Meja</h1>
            
            <div class="form-box" style="border-left: 5px solid #2ed573;">
                <h3>➕ Tambah Jumlah Meja Baru</h3>
                <p style="color: #a4b0be; margin-bottom: 15px; font-size: 14px;">Masukkan nomor meja yang ingin ditambahkan ke sistem.</p>
                <form method="POST" style="display: flex; gap: 15px; align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0; flex: 1;">
                        <input type="number" name="nomor_meja" required placeholder="Contoh: 7" style="font-size: 18px; font-weight: bold;">
                    </div>
                    <button type="submit" name="tambah_meja" class="btn btn-success" style="padding: 12px 25px; font-size: 16px;">Simpan Meja</button>
                </form>
            </div>
            
            <table style="max-width: 600px;">
                <tr><th>ID Internal</th><th>Nomor Meja</th><th>Status / Aksi</th></tr>
                <?php
                $mejas = $conn->query("SELECT * FROM meja ORDER BY nomor_meja ASC");
                while($mj = $mejas->fetch_assoc()):
                ?>
                <tr>
                    <td>#<?php echo $mj['id_meja']; ?></td>
                    <td style="font-weight: bold; font-size: 18px; color: #fff;">Meja <?php echo $mj['nomor_meja']; ?></td>
                    <td>
                        <a href="?page=meja&kosongkan_meja=<?php echo $mj['nomor_meja']; ?>" class="btn btn-primary" onclick="return confirm('Kosongkan Meja <?php echo $mj['nomor_meja']; ?>?')" style="background: #1e90ff; margin-right: 5px;">🧹 Kosongkan</a>
                        <a href="?page=meja&hapus_meja=<?php echo $mj['id_meja']; ?>" class="btn btn-danger" onclick="return confirm('Hapus meja ini permanen?')">🗑️ Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

        <?php elseif ($page == 'keuangan'): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Laporan Penjualan</h1>
                
                <form method="POST" onsubmit="return confirm('PERINGATAN KERAS!\n\nTindakan ini akan MENGHAPUS PERMANEN semua riwayat transaksi pelanggan dan mengembalikan Omzet menjadi Rp0.\n\nApakah Anda benar-benar yakin?')">
                    <button type="submit" name="reset_laporan" class="btn" style="background-color: #ff3838; padding: 12px 20px; font-size: 14px; box-shadow: 0 4px 10px rgba(255,56,56,0.3);">
                        🚨 Reset Semua Laporan & Omzet
                    </button>
                </form>
            </div>
            
            <?php 
                $pemasukan = $conn->query("SELECT SUM(total) as in_total FROM pesanan WHERE status != 'Menunggu Pembayaran'")->fetch_assoc()['in_total'] ?? 0;
                $total_transaksi = $conn->query("SELECT COUNT(id_pesanan) as jml_trx FROM pesanan WHERE status != 'Menunggu Pembayaran'")->fetch_assoc()['jml_trx'] ?? 0;
            ?>
            
            <div class="card-grid">
                <div class="card">
                    <h3>Total Pendapatan (Omzet)</h3>
                    <div class="amount" style="color: #2ed573;">Rp<?php echo number_format($pemasukan, 0, ',', '.'); ?></div>
                </div>
                <div class="card">
                    <h3>Total Transaksi Sukses</h3>
                    <div class="amount" style="color: #1e90ff;"><?php echo $total_transaksi; ?> Pesanan</div>
                </div>
            </div>

            <div class="form-box" style="margin-bottom: 15px; padding: 15px 25px;">
                <h3 style="margin: 0; color: #fff;">📜 Riwayat Pembelian Pelanggan</h3>
            </div>
            
            <table>
                <tr>
                    <th>Waktu Pesanan</th>
                    <th>ID Pesanan</th>
                    <th>Metode Bayar</th>
                    <th>Status</th>
                    <th>Total Belanja</th>
                    <th>Aksi</th>
                </tr>
                <?php
                $riwayat_pesanan = $conn->query("SELECT * FROM pesanan WHERE status != 'Menunggu Pembayaran' ORDER BY tanggal DESC LIMIT 50");
                
                if($riwayat_pesanan && $riwayat_pesanan->num_rows > 0):
                    while($rp = $riwayat_pesanan->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo date('d-m-Y H:i', strtotime($rp['tanggal'])); ?></td>
                    <td style="font-weight: bold; color: #a4b0be;">#<?php echo $rp['id_pesanan']; ?></td>
                    <td><span style="background: #e1b12c; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 13px; font-weight: bold;"><?php echo $rp['metode_pembayaran']; ?></span></td>
                    <td>
                        <?php if($rp['status'] == 'Selesai'): ?>
                            <span style="color: #2ed573; font-weight: bold;">✔ Selesai</span>
                        <?php else: ?>
                            <span style="color: #1e90ff;"><?php echo $rp['status']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="color:#2ed573; font-weight: bold; font-size: 16px;">Rp<?php echo number_format($rp['total'], 0, ',', '.'); ?></td>
                    
                    <td>
                        <a href="struk.php?id=<?php echo $rp['id_pesanan']; ?>" target="_blank" class="btn" style="background-color: #f1f2f6; color: #2f3542; padding: 5px 10px; font-size: 13px; text-decoration: none; border-radius: 4px; border: 1px solid #ced6e0;">🖨️ Struk</a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else:
                ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #a4b0be; padding: 20px;">Belum ada riwayat transaksi pelanggan.</td>
                </tr>
                <?php endif; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>