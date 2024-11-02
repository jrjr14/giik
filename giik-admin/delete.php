<?php
session_start();
include 'session.php'; // Cek sesi agar hanya pengguna yang login yang bisa menghapus
include 'config.php'; // Koneksi ke database

// Cek apakah ada parameter id di URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data untuk mendapatkan path file foto jika ada
    $stmt = $conn->prepare("SELECT photo_path FROM submissions WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Hapus data dari database
    $stmt = $conn->prepare("DELETE FROM submissions WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Jika ada file foto yang tersimpan, hapus file dari server
        if (!empty($data['photo_path']) && file_exists(__DIR__ . '/uploads/' . $data['photo_path'])) {
            unlink(__DIR__ . '/uploads/' . $data['photo_path']);
        }
        // Redirect kembali ke dashboard setelah menghapus
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Gagal menghapus data.";
    }
} else {
    // Jika tidak ada id di URL, kembalikan ke dashboard
    header("Location: dashboard.php");
    exit();
}
