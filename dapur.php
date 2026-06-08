<?php
// dapur.php
include 'koneksi.php';

// Ambil durasi refresh dari database
$q_waktu = $conn->query("SELECT nilai FROM pengaturan WHERE nama_pengaturan='refresh_waktu'");
$waktu_refresh = ($q_waktu && $q_waktu->num_rows > 0) ? $q_waktu->fetch_assoc()['nilai'] : '15';

if (isset($_POST['status_baru'])) {
    $id = $_POST['id_pesanan'];
    $status_baru = $_POST['status_baru'];
    $conn->query("UPDATE pesanan SET status = '$status_baru' WHERE id_pesanan = '$id'");
    header("Location: dapur.php");
}

$sql = "SELECT p.*, m.no_meja FROM pesanan p 
        JOIN pelanggan_meja m ON p.id_pelanggan = m.id_pelanggan 
        WHERE p.status IN ('Menunggu Dimasak', 'Sedang Dimasak') ORDER BY p.tanggal ASC";
$pesanan = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="<?php echo $waktu_refresh; ?>">
    <title>Sistem Dapur - Burger Kita</title>
    <!-- PENAMBAHAN BOOTSTRAP 5 SESUAI TECH STACK -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        body { background-color: #121212; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; padding: 30px; }
        h1 { color: #d32f2f; border-bottom: 2px solid #333; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .refresh-badge { font-size: 14px; background: #333; color: white; padding: 5px 15px; border-radius: 20px; font-weight: normal; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .order-card { background: #1e1e1e; border: 1px solid #333; border-radius: 12px; padding: 20px; }
        .table-no { font-size: 24px; font-weight: bold; color: #fff; margin-bottom: 10px; }
        .status { padding: 5px 10px; border-radius: 5px; font-size: 14px; font-weight: bold; background: #333; }
        .status.menunggu { color: #ffb300; } .status.diproses { color: #43a047; }
        ul { list-style: none; padding: 10px; background: #2a2a2a; border-radius: 8px; margin: 15px 0; }
        ul li { margin-bottom: 8px; border-bottom: 1px solid #333; padding-bottom: 5px; }
        button { width: 100%; padding: 12px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; color: white; margin-top: 10px; font-size: 16px;}
        .btn-masak { background-color: #1976d2; } .btn-selesai { background-color: #388e3c; }
    </style>
</head>
<body>
    <h1>
        👨‍🍳 Layar Dapur - Antrean Memasak
        <span class="refresh-badge">🔄 Auto-Refresh: <?php echo $waktu_refresh; ?> Detik</span>
    </h1>
    <div class="card-grid">
        <?php while($row = $pesanan->fetch_assoc()): ?>
            <div class="order-card">
                <div class="table-no">Meja No. <?php echo $row['no_meja']; ?></div>
                <div>Status: <span class="status <?php echo ($row['status'] == 'Sedang Dimasak') ? 'diproses' : 'menunggu'; ?>">
                    <?php echo $row['status']; ?>
                </span></div>
                <ul>
                    <?php 
                    $id_pes = $row['id_pesanan'];
                    $detail = $conn->query("SELECT d.jumlah, m.nama_menu FROM detail_pesanan d 
                        JOIN menu m ON d.id_menu = m.id_menu 
                        WHERE d.id_pesanan = '$id_pes'");
                    while($d = $detail->fetch_assoc()) { echo "<li style='font-size:16px;'><b>{$d['jumlah']}x</b> - {$d['nama_menu']}</li>"; }
                    ?>
                </ul>
                <form method="POST">
                    <input type="hidden" name="id_pesanan" value="<?php echo $row['id_pesanan']; ?>">
                    <?php if($row['status'] == 'Menunggu Dimasak'): ?>
                        <button type="submit" name="status_baru" value="Sedang Dimasak" class="btn-masak">🔥 Mulai Masak</button>
                    <?php elseif($row['status'] == 'Sedang Dimasak'): ?>
                        <button type="submit" name="status_baru" value="Selesai" class="btn-selesai">✅ Selesai & Antarkan!</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>