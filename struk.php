<?php
include 'koneksi.php';

if (!isset($_GET['id'])) {
    die("ID Pesanan tidak ditemukan.");
}

$id_pesanan = $_GET['id'];

// Ambil data pesanan utama
$sql_pesanan = "SELECT p.*, m.no_meja FROM pesanan p 
                JOIN pelanggan_meja m ON p.id_pelanggan = m.id_pelanggan 
                WHERE p.id_pesanan = '$id_pesanan'";
$result = $conn->query($sql_pesanan);

if ($result->num_rows == 0) {
    die("Pesanan tidak ditemukan.");
}
$pesanan = $result->fetch_assoc();

// Ambil detail item pesanan
$sql_detail = "SELECT d.*, m.nama_menu, m.harga FROM detail_pesanan d 
               JOIN menu m ON d.id_menu = m.id_menu 
               WHERE d.id_pesanan = '$id_pesanan'";
$detail_items = $conn->query($sql_detail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #<?php echo $id_pesanan; ?></title>
    <style>
        /* Desain Khusus Kertas Printer Termal (58mm) */
        body { margin: 0; padding: 0; background-color: #e0e0e0; font-family: 'Courier New', Courier, monospace; color: #000; font-size: 12px; }
        .ticket { width: 250px; /* Ukuran standar 58mm */ max-width: 250px; background: white; margin: 20px auto; padding: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h3 { text-align: center; margin: 0 0 5px 0; font-size: 16px; text-transform: uppercase; }
        .centered { text-align: center; align-content: center; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td { padding: 2px 0; vertical-align: top; }
        .qty { width: 15%; text-align: left; }
        .item-name { width: 50%; }
        .price { width: 35%; text-align: right; }
        .total-row { font-weight: bold; font-size: 14px; }
        
        /* Tombol Print (Akan disembunyikan saat dicetak) */
        .btn-print { display: block; width: 250px; margin: 10px auto; padding: 10px; background: #2ed573; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; font-family: sans-serif;}
        .btn-print:hover { background: #26b963; }
        
        /* Hilangkan elemen selain struk saat diprint */
        @media print {
            body { background-color: transparent; }
            .ticket { margin: 0; padding: 0; box-shadow: none; width: 100%; }
            .btn-print { display: none; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk Sekarang</button>

    <div class="ticket">
        <h3>🍔 BURGER KITA</h3>
        <p class="centered" style="margin:0 0 10px 0;">
            Jl. Koding Bersama No. 1<br>
            Telp: 0812-3456-7890
        </p>
        
        <div class="divider"></div>
        
        <table>
            <tr><td><b>No. Nota:</b></td><td style="text-align: right;">#<?php echo $id_pesanan; ?></td></tr>
            <tr><td><b>Waktu:</b></td><td style="text-align: right;"><?php echo date('d/m/Y H:i', strtotime($pesanan['tanggal'])); ?></td></tr>
            <tr><td><b>Meja:</b></td><td style="text-align: right; font-size: 14px;"><b><?php echo $pesanan['no_meja']; ?></b></td></tr>
            <tr><td><b>Metode:</b></td><td style="text-align: right;"><?php echo $pesanan['metode_pembayaran']; ?></td></tr>
        </table>
        
        <div class="divider"></div>
        
        <table>
            <?php while($item = $detail_items->fetch_assoc()): ?>
            <tr>
                <td class="qty"><?php echo $item['jumlah']; ?>x</td>
                <td class="item-name"><?php echo $item['nama_menu']; ?></td>
                <td class="price"><?php echo number_format($item['harga'] * $item['jumlah'], 0, ',', '.'); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        
        <div class="divider"></div>
        
        <table>
            <tr class="total-row">
                <td colspan="2">TOTAL BELANJA</td>
                <td class="price">Rp<?php echo number_format($pesanan['total'], 0, ',', '.'); ?></td>
            </tr>
        </table>
        
        <div class="divider"></div>
        
        <p class="centered" style="margin-top: 15px;">
            -- Terima Kasih --<br>
            Silakan Nikmati Hidangan Anda
        </p>
    </div>

</body>
</html>