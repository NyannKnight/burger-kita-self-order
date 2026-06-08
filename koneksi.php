<?php
// koneksi.php (Versi Online Hosting)
$host = "localhost"; // Biasanya tetap localhost pada shared hosting
$user = "kitp1744_burgerkita"; // Ganti dengan USERNAME database baru dari cPanel
$pass = "kitaburger"; // Ganti dengan PASSWORD database baru dari cPanel
$db   = "kitp1744_burger_kita"; // Ganti dengan NAMA database baru dari cPanel

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>