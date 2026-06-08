<?php
// kasir.php
include 'koneksi.php';

// Ambil durasi refresh dari database
$q_waktu = $conn->query("SELECT nilai FROM pengaturan WHERE nama_pengaturan='refresh_waktu'");
$waktu_refresh = ($q_waktu && $q_waktu->num_rows > 0) ? $q_waktu->fetch_assoc()['nilai'] : '15';

if (isset($_POST['konfirmasi_bayar'])) {
    $id = $_POST['id_pesanan'];
    $conn->query("UPDATE pesanan SET status = 'Menunggu Dimasak' WHERE id_pesanan = '$id'");
    // Arahkan proses ini langsung ke halaman struk
    header("Location: struk.php?id=" . $id);
    exit;
}

// Cari bagian $sql dan ubah menjadi:
$sql = "SELECT p.*, m.no_meja FROM pesanan p 
        JOIN pelanggan_meja m ON p.id_pelanggan = m.id_pelanggan 
        WHERE p.status = 'Menunggu Pembayaran' ORDER BY p.tanggal ASC";
$pesanan = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="<?php echo $waktu_refresh; ?>">
    <title>Sistem Kasir - Burger Kita</title>
    <!-- PENAMBAHAN BOOTSTRAP 5 SESUAI TECH STACK -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        body { background-color: #1a1a2e; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; padding: 30px; }
        h1 { color: #00d2d3; border-bottom: 2px solid #273c75; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .refresh-badge { font-size: 14px; background: #273c75; color: white; padding: 5px 15px; border-radius: 20px; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #16213e; border-radius: 10px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #0f3460; }
        th { background-color: #0f3460; color: #fff; }
        .metode { color: #fff; background: #e1b12c; padding: 3px 8px; border-radius: 4px; font-size: 13px; font-weight: bold; }
        .btn-bayar { background-color: #10ac84; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <h1>
        Kasir - Menunggu Pembayaran
        <span class="refresh-badge"> Auto-Refresh: <?php echo $waktu_refresh; ?> Detik</span>
    </h1>
    <table>
        <tr><th>No Pesanan</th><th>Waktu</th><th>No Meja</th><th>Metode Bayar</th><th>Total Tagihan</th><th>Aksi</th></tr>
        <?php while($row = $pesanan->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id_pesanan']; ?></td>
                <td><?php echo date('H:i', strtotime($row['tanggal'])); ?></td>
                <td style="font-weight:bold;">Meja <?php echo $row['no_meja']; ?></td>
                <td><span class="metode"><?php echo $row['metode_pembayaran']; ?></span></td>
                <td style="color: #ff9f43; font-weight: bold; font-size:18px;">Rp<?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                <td>
                    <form method="POST" target="_blank" onsubmit="setTimeout(function(){ window.location.reload(); }, 1000);">
                        <input type="hidden" name="id_pesanan" value="<?php echo $row['id_pesanan']; ?>">
                        <button type="submit" name="konfirmasi_bayar" class="btn-bayar">✅ Konfirmasi & Cetak</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>