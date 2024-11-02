<?php
session_start();
include 'session.php';
include 'config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM submissions";
if (!empty($search)) {
    $sql .= " WHERE email LIKE :search
              OR full_name LIKE :search
              OR birth_date LIKE :search
              OR phone_number LIKE :search
              OR residence LIKE :search
              OR origin LIKE :search";
}

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="icon" href="https://shv.homegiik.org/images/favicon.png" type="image/x-icon">
</head>
<body>
    <div class="container">
        <h2>Data Pendaftaran</h2>
        <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout.php">Logout</a></p>

        <!-- Form Pencarian -->
        <form method="GET" action="dashboard.php" class="mb-3">
            <input type="text" name="search" placeholder="Cari data..." value="<?php echo htmlspecialchars($search); ?>" class="form-control mb-2">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Email</th>
                        <th>Nama Lengkap</th>
                        <th>Tanggal Lahir</th>
                        <th>Nomor HP</th>
                        <th>Tempat Tinggal</th>
                        <th>Tempat Asal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                      <td>
    <?php if (!empty($row['photo_path'])): ?>
        <a href="#" data-toggle="modal" data-target="#imageModal" data-image="https://shv.homegiik.org/<?php echo htmlspecialchars($row['photo_path']); ?>">
            <img src="https://shv.homegiik.org/<?php echo htmlspecialchars($row['photo_path']); ?>" alt="Foto" width="50" height="50">
        </a>
    <?php else: ?>
        Tidak ada foto
    <?php endif; ?>
</td>

                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['birth_date']); ?></td>
                        <td>
                            <?php 
                            $phoneNumber = preg_replace('/[^0-9]/', '', $row['phone_number']);
                            if (substr($phoneNumber, 0, 1) == '0') {
                                $phoneNumber = '62' . substr($phoneNumber, 1);
                            }
                            ?>
                            <a href="https://wa.me/<?php echo $phoneNumber; ?>" target="_blank">
                                <?php echo htmlspecialchars($row['phone_number']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['residence']); ?></td>
                        <td><?php echo htmlspecialchars($row['origin']); ?></td>
                        <td>
                            <button type="button" class="btn btn-primary edit-btn" data-id="<?php echo $row['id']; ?>">Edit</button>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal untuk Preview Gambar -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Preview Gambar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: absolute; right: 10px; top: 10px; background: none; border: none; font-size: 24px; color: black;">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Preview Gambar" style="max-width: 100%; max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Edit Data -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <form id="editForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="id">
                        <div class="form-group text-center">
                            <label>Foto Saat Ini:</label><br>
                            <img id="currentPhoto" src="" alt="Foto Saat Ini" width="100" height="100" class="rounded mb-3">
                        </div>
                        <div class="form-group">
                            <label for="editPhoto">Ganti Foto</label>
                            <input type="file" class="form-control-file" id="editPhoto" name="photo" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="editEmail">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="editFullName">Nama Lengkap</label>
                            <input type="text" class="form-control" id="editFullName" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="editBirthDate">Tanggal Lahir</label>
                            <input type="date" class="form-control" id="editBirthDate" name="birth_date" required>
                        </div>
                        <div class="form-group">
                            <label for="editPhoneNumber">Nomor HP</label>
                            <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                        </div>
                        <div class="form-group">
                            <label for="editResidence">Tempat Tinggal</label>
                            <input type="text" class="form-control" id="editResidence" name="residence">
                        </div>
                        <div class="form-group">
                            <label for="editOrigin">Tempat Asal</label>
                            <input type="text" class="form-control" id="editOrigin" name="origin">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript untuk Modal dan AJAX -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // Saat gambar diklik, tampilkan gambar di modal preview
        $('#imageModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var imageUrl = button.data('image');
            $('#modalImage').attr('src', imageUrl);
        });

        // Saat tombol edit diklik, ambil data dan tampilkan di modal
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            $.ajax({
                url: 'get_data.php',
                type: 'GET',
                data: { id: id },
                success: function(response) {
                    var data = JSON.parse(response);
                    $('#editId').val(data.id);
                    $('#editEmail').val(data.email);
                    $('#editFullName').val(data.full_name);
                    $('#editBirthDate').val(data.birth_date);
                    $('#editPhoneNumber').val(data.phone_number);
                    $('#editResidence').val(data.residence);
                    $('#editOrigin').val(data.origin);
                    $('#currentPhoto').attr('src', 'https://shv.homegiik.org/' + data.photo_path);
                    $('#editModal').modal('show');
                }
            });
        });

        // AJAX untuk mengirim data yang telah diedit termasuk foto
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: 'update_data.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert('Data berhasil diperbarui!');
                    $('#editModal').modal('hide');
                    location.reload();
                }
            });
        });
    });
    </script>
</body>
</html>
