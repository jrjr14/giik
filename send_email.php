<?php
// Aktifkan penanganan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Koneksi ke database
$servername = "srv1367.hstgr.io";
$username = "u102034059_giik";
$password = "123@qweasDD";
$dbname = "u102034059_giikshv";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan lakukan sanitasi
    $email = htmlspecialchars($_POST['email']);
    $fullName = htmlspecialchars($_POST['fullName']);
    $birthDate = htmlspecialchars($_POST['birthDate']);
    $phoneNumber = htmlspecialchars($_POST['phoneNumber']);
    $residence = htmlspecialchars($_POST['residence']);
    $origin = htmlspecialchars($_POST['origin']);
    $photoPath = '';

    // Upload file foto
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true); // Buat folder jika belum ada
        }
        $photoPath = $targetDir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath);
    }

    // Masukkan data ke database
    try {
        $stmt = $conn->prepare("INSERT INTO submissions (email, full_name, birth_date, phone_number, photo_path, residence, origin) VALUES (:email, :full_name, :birth_date, :phone_number, :photo_path, :residence, :origin)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':birth_date', $birthDate);
        $stmt->bindParam(':phone_number', $phoneNumber);
        $stmt->bindParam(':photo_path', $photoPath);
        $stmt->bindParam(':residence', $residence);
        $stmt->bindParam(':origin', $origin);
        $stmt->execute();
        echo "Data berhasil disimpan di database dan email konfirmasi telah dikirim ke $email.";
    } catch (Exception $e) {
        echo "Gagal menyimpan data ke database: " . $e->getMessage();
    }

    // Mengirim email ke pengguna
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'mail.smtp2go.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shv.homegiik.org';
        $mail->Password = '123@qweasDD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('noreply@shv.homegiik.org', 'GIIK Sihanoukville');
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = "Konfirmasi Pendaftaran di GIIK Sihanoukville";
        $mail->Body = "
            Terimakasih Sdr/Sdri $fullName telah mengisi form pendaftaran di <a href='https://shv.homegiik.org'>https://shv.homegiik.org</a><br>
            Tuhan Memberkati Bapak dan Ibu.<br><br>
            Berikut data yang Bapak/Ibu daftarkan:<br><br>
            <p><strong>Nama Lengkap:</strong> $fullName</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Tanggal Lahir:</strong> $birthDate</p>
            <p><strong>Nomor HP:</strong> $phoneNumber</p>
            <p><strong>Tempat Tinggal:</strong> $residence</p>
            <p><strong>Tempat Asal:</strong> $origin</p>
            <p>Big Regards : <strong>GEREJA INTERDOMINASI INDONESIA DI KAMBOJA</strong></p>
        ";

        if ($photoPath) {
            $mail->addAttachment($photoPath);
        }

        $mail->send();
    } catch (Exception $e) {
        echo "Email konfirmasi gagal dikirim. Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Request tidak valid.";
}
