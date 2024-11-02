<?php
$servername = "srv1367.hstgr.io"; // atau alamat server MySQL Anda
$username = "u102034059_giik"; // ganti dengan username database Anda
$password = "123@qweasDD"; // ganti dengan password database Anda
$dbname = "u102034059_giikshv"; // ganti dengan nama database Anda

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
}
?>
