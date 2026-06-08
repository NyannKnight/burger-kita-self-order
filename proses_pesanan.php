<?php
// proses_pesanan.php
include 'koneksi.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $meja = $data['meja'];
    $total = $data['total'];
    $metode = $data['metode']; // Menangkap metode bayar
    $items = $data['items'];

    // 1. Simpan Meja
    $conn->query("INSERT INTO pelanggan_meja (no_meja) VALUES ('$meja')");
    $id_pelanggan = $conn->insert_id;

    // 2. Simpan Pesanan (Status awal ditujukan untuk KASIR)
    $conn->query("INSERT INTO pesanan (id_pelanggan, total, metode_pembayaran, status) VALUES ('$id_pelanggan', '$total', '$metode', 'Menunggu Pembayaran')");
    $id_pesanan = $conn->insert_id;

    // 3. Simpan Detail
    foreach ($items as $item) {
        $id_menu = $item['id'];
        $qty = $item['qty'];
        $subtotal = $item['harga'] * $qty;
        $conn->query("INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, subtotal) VALUES ('$id_pesanan', '$id_menu', '$qty', '$subtotal')");
    }
    
    echo "Pesanan diteruskan ke Kasir";
} else {
    echo "Gagal memproses data.";
}
?>