<?php
include 'config.php'; // Koneksi ke database

// Ambil ID data yang akan diupdate
$id = $_POST['id'];
$email = $_POST['email'];
$fullName = $_POST['full_name'];
$birthDate = $_POST['birth_date'];
$phoneNumber = $_POST['phone_number'];
$residence = $_POST['residence'];
$origin = $_POST['origin'];

// Proses upload gambar jika ada
$photoPath = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $photo = $_FILES['photo'];
    $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $newFileName = uniqid() . '.' . $ext;
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/'; // Simpan langsung di root directory

    if (move_uploaded_file($photo['tmp_name'], $uploadDir . $newFileName)) {
        $photoPath = $newFileName; // Simpan nama file saja, bukan path lengkap
    }
}

// Update data di database
$sql = "UPDATE submissions SET 
        email = :email,
        full_name = :full_name,
        birth_date = :birth_date,
        phone_number = :phone_number,
        residence = :residence,
        origin = :origin";

// Hanya update path gambar jika ada gambar baru
if ($photoPath !== null) {
    $sql .= ", photo_path = :photo_path";
}

$sql .= " WHERE id = :id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':full_name', $fullName);
$stmt->bindParam(':birth_date', $birthDate);
$stmt->bindParam(':phone_number', $phoneNumber);
$stmt->bindParam(':residence', $residence);
$stmt->bindParam(':origin', $origin);

if ($photoPath !== null) {
    $stmt->bindParam(':photo_path', $photoPath);
}

$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
    echo "Data berhasil diperbarui";
} else {
    echo "Gagal memperbarui data";
}
?>
