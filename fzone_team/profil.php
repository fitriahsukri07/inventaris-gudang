<?php
session_start();
include 'conn.php';

// Proteksi login
if (!isset($_SESSION['login'])) {
    header("location: login.php");
    exit;
}

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("location: admin.php");
    exit;
}

$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika user tidak ditemukan
if (!$data) {
    echo "User tidak ditemukan!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil <?= $data['username']; ?> | F-ZONE</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .profile-card {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            background: linear-gradient(135deg, #1e293b, #3b82f6);
            height: 150px;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }

        .profile-img {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: #3b82f6;
            border: 5px solid white;
            margin-bottom: -60px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-body {
            padding: 80px 30px 40px;
            text-align: center;
        }

        .badge-role {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
        }

        .info-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 15px;
            margin-top: 20px;
            text-align: left;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-img">
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>

            <div class="profile-body">
                <h2 class="fw-bold mb-1"><?= $data['username']; ?></h2>
                <p class="text-muted mb-3"><?= $data['gmail']; ?></p>

                <span class="badge-role <?= $data['role'] == 'admin' ? 'bg-primary text-white' : 'bg-success text-white'; ?>">
                    <i class="fas <?= $data['role'] == 'admin' ? 'fa-crown' : 'fa-user-tag'; ?> me-2"></i>
                    <?= $data['role']; ?>
                </span>

                <div class="info-box shadow-sm">
                    <div class="mb-3">
                        <label class="text-muted small d-block">ID Pengguna</label>
                        <span class="fw-bold">#0<?= $data['id_user']; ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Alamat Email</label>
                        <span class="fw-bold"><?= $data['gmail']; ?></span>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small d-block">Status Akun</label>
                        <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Aktif</span>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <a href="admin.php" class="btn btn-outline-secondary w-100 py-2 rounded-3">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                    <?php if ($_SESSION['id_user'] == $data['id_user']): ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>