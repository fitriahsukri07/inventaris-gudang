<?php
session_start();
include 'conn.php';

// 1. PROTEKSI LOGIN & DEFINISI VARIABEL GLOBAL
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Menyamakan variabel dengan data session
$id_log    = $_SESSION['id_user'];
$role_user = $_SESSION['role']; // admin atau petugas

// 2. AMBIL DATA USER YANG SEDANG LOGIN (Untuk Profil)
$query_log = "SELECT * FROM users WHERE id_user = '$id_log'";
$result_log = $conn->query($query_log);
$u = $result_log->fetch_assoc();

// Cegah error jika data user login tidak ditemukan
if (!$u) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/* ================== PROSES TAMBAH USER ================== */
if (isset($_POST['tambah'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $gmail    = mysqli_real_escape_string($conn, $_POST['gmail']);
    $password = md5($_POST['password']);
    $role     = $_POST['role'];

    $cek_email = $conn->query("SELECT * FROM users WHERE gmail = '$gmail'");
    if ($cek_email->num_rows > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location='admin.php';</script>";
    } else {
        $sql = "INSERT INTO users (username, gmail, password, role) VALUES ('$username', '$gmail', '$password', '$role')";
        if ($conn->query($sql)) {
            echo "<script>alert('User berhasil ditambahkan!'); window.location='admin.php';</script>";
        }
    }
}

/* ================== PROSES PERBARUI AKUN (EDIT) ================== */
if (isset($_POST['edit'])) {
    $id_target = $_POST['id_user'];

    // Proteksi: Hanya admin atau pemilik akun itu sendiri yang bisa edit
    if ($role_user != 'admin' && $id_target != $id_log) {
        die("Akses dilarang.");
    }

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $gmail    = mysqli_real_escape_string($conn, $_POST['gmail']);

    // Role hanya bisa diubah oleh admin
    $role_baru = (isset($_POST['role']) && $role_user == 'admin') ? $_POST['role'] : $role_user;

    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $sql = "UPDATE users SET username='$username', gmail='$gmail', password='$password', role='$role_baru' WHERE id_user='$id_target'";
    } else {
        $sql = "UPDATE users SET username='$username', gmail='$gmail', role='$role_baru' WHERE id_user='$id_target'";
    }

    if ($conn->query($sql)) {
        // Update session jika mengedit akun sendiri
        if ($id_target == $id_log) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role_baru;
        }
        echo "<script>alert('Data berhasil diperbarui!'); window.location='admin.php';</script>";
    }
}

/* ================== PROSES HAPUS USER ================== */
if (isset($_GET['hapus']) && $role_user == 'admin') {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);

    if ($id_hapus == $id_log) {
        echo "<script>alert('Anda tidak bisa menghapus akun Anda sendiri!'); window.location='admin.php';</script>";
    } else {
        // Hapus data terkait di tabel transaksi (jika ada) untuk menghindari error FK
        $conn->query("DELETE FROM transaksi_masuk WHERE id_user = '$id_hapus'");
        $conn->query("DELETE FROM transaksi_keluar WHERE id_user = '$id_hapus'");

        if ($conn->query("DELETE FROM users WHERE id_user = '$id_hapus'")) {
            echo "<script>alert('User berhasil dihapus!'); window.location='admin.php';</script>";
        }
    }
}

/* ================== AMBIL DATA UNTUK TAMPILAN ================== */
if ($role_user == 'admin') {
    $users = $conn->query("SELECT * FROM users ORDER BY role ASC");
} else {
    $user_data = $u; // Gunakan data user login jika bukan admin
}

// FUNGSI RENDER DETAIL (DIPERBAIKI AGAR TIDAK NULL)
function renderDetailProfil($data)
{ ?>
    <div class="profile-container text-center">
        <div class="profile-header-bg shadow-sm"></div>
        <div class="profile-avatar-wrapper mb-3">
            <div class="profile-avatar-lg fw-bold shadow">
                <?= strtoupper(substr($data['username'] ?? 'U', 0, 1)); ?>
            </div>
            <span class="position-absolute bottom-0 end-0 bg-success border border-white border-4 rounded-circle p-2" style="margin-right: 15px; margin-bottom: 10px;"></span>
        </div>
        <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($data['username'] ?? 'Unknown'); ?></h3>
        <p class="text-muted mb-4"><i class="fas fa-envelope-open-text me-2"></i><?= htmlspecialchars($data['gmail'] ?? '-'); ?></p>

        <div class="row g-3 px-3 text-start">
            <div class="col-md-4">
                <div class="info-tile h-100 border-0 shadow-sm">
                    <small class="text-muted d-block mb-2 text-uppercase fw-semibold" style="font-size: 10px;">Akses</small>
                    <span class="badge <?= ($data['role'] ?? '') == 'admin' ? 'bg-primary' : 'bg-info text-dark'; ?> px-3 py-2 rounded-pill w-100">
                        <?= strtoupper($data['role'] ?? 'USER'); ?>
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-tile h-100 border-0 shadow-sm text-center">
                    <small class="text-muted d-block mb-1 text-uppercase fw-semibold" style="font-size: 10px;">ID Member</small>
                    <span class="fw-bold text-dark fs-5">#UZN-0<?= $data['id_user'] ?? '0'; ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-tile h-100 border-0 shadow-sm">
                    <small class="text-muted d-block mb-1 text-uppercase fw-semibold" style="font-size: 10px;">Keamanan</small>
                    <div class="d-flex align-items-center text-success fw-bold">
                        <i class="fas fa-user-check me-2"></i> Verified
                    </div>
                </div>
            </div>
        </div>
        <hr class="my-4 opacity-25">
        <button class="btn btn-primary px-5 py-2 rounded-4 fw-bold shadow transform-hover" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $data['id_user']; ?>">
            <i class="fas fa-fingerprint me-2"></i> Edit Profil
        </button>
    </div>
<?php } ?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun | F-ZONE</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --sidebar-bg: #0f172a;
            --accent-color: #6e94d2;
            --bg-light: #f8fafc;
        }

        body {
            background: var(--bg-light);
            font-family: 'Inter', sans-serif;
        }

        .main-sidebar {
            min-height: 100vh;
            width: 280px;
            background: var(--sidebar-bg);
            position: fixed;
            z-index: 1000;
        }

        .nav-link {
            color: #94a3b8 !important;
            padding: 12px 20px;
            margin: 4px 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: 0.3s;
        }

        .nav-link.active {
            background: var(--accent-color) !important;
            color: #fff !important;
        }

        .content-wrapper {
            margin-left: 280px;
            padding: 40px;
            width: calc(100% - 280px);
        }

        .card-custom {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: none;
        }

        .profile-header-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            height: 100px;
            border-radius: 20px 20px 0 0;
        }

        .profile-avatar-wrapper {
            margin-top: -70px;
            position: relative;
            display: inline-block;
        }

        .profile-avatar-lg {
            width: 100px;
            height: 100px;
            background: white;
            border: 6px solid white;
            border-radius: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: #3b82f6;
        }

        .info-tile {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 15px;
        }

        .transform-hover:hover {
            transform: translateY(-3px);
            transition: 0.3s;
        }

        @media (max-width: 992px) {
            .main-sidebar {
                width: 80px;
            }

            .content-wrapper {
                margin-left: 80px;
                width: calc(100% - 80px);
            }

            .nav-link span,
            .main-sidebar h5 {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="d-flex">
        <aside class="main-sidebar p-3 shadow">
            <div class="d-flex align-items-center mb-4 px-2">
                <div class="bg-primary p-2 rounded-3 me-2 text-white">
                    <i class="fas fa-boxes-stacked fa-lg"></i>
                </div>
                <h5 class="text-white mb-0 fw-bold">INVENTARIS<br> <span class="text-primary">GUDANG</span></h5>
            </div>

            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link <?= ($active_page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-th-large me-2"></i> Dashboard
                </a>

                <a href="barang.php" class="nav-link <?= ($active_page == 'barang') ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-check me-2"></i> Data Barang
                </a>

                <a href="transaksi_masuk.php" class="nav-link <?= ($active_page == 'transaksi_masuk') ? 'active' : ''; ?>">
                    <i class="fas fa-file-import me-2"></i> Transaksi Masuk
                </a>

                <a href="transaksi_keluar.php" class="nav-link <?= ($active_page == 'transaksi_keluar') ? 'active' : ''; ?>">
                    <i class="fas fa-file-export me-2"></i> Transaksi Keluar
                </a>

                <hr class="text-secondary">

                <a href="admin.php" class="nav-link <?= ($active_page == 'admin') ? 'active' : ''; ?>">
                    <?php if ($role_user == 'admin'): ?>
                        <i class="fas fa-users-cog me-2"></i> Manajemen Akun
                    <?php else: ?>
                        <i class="fas fa-user-circle me-2"></i> Akun Saya
                    <?php endif; ?>
                </a>

                <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-link text-danger mt-5">
                    <i class="fas fa-power-off me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="content-wrapper">
            <header class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold mb-0 text-dark"><?= ($role_user == 'admin') ? 'Manajemen Akun' : 'Detail Akun'; ?></h2>
                    <p class="text-muted small">Kelola informasi profil dan hak akses.</p>
                </div>
                <?php if ($role_user == 'admin'): ?>
                    <button class="btn btn-primary px-4 py-2 rounded-3" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus-circle me-1"></i> Tambah User</button>
                <?php endif; ?>
            </header>

            <?php if ($role_user == 'admin'): ?>
                <div class="card-custom shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 border-0">User</th>
                                    <th class="border-0">Email</th>
                                    <th class="border-0">Role</th>
                                    <th class="text-center border-0">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id_user']; ?>" style="cursor:pointer">
                                                <div class="profile-avatar-lg shadow-sm" style="width:35px; height:35px; font-size:18px; border:none; border-radius:10px; background:#e2e8f0; margin-top:0">
                                                    <?= strtoupper(substr($row['username'], 0, 1)); ?>
                                                </div>
                                                <span class="fw-bold text-dark ms-3"><?= $row['username']; ?></span>
                                            </div>
                                        </td>
                                        <td><?= $row['gmail']; ?></td>
                                        <td><span class="badge rounded-pill <?= $row['role'] == 'admin' ? 'bg-primary' : 'bg-info text-dark'; ?>"><?= strtoupper($row['role']); ?></span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light border shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_user']; ?>"><i class="fas fa-edit text-warning"></i></button>
                                            <?php if ($row['id_user'] != $id_log): ?>
                                                <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id_user']; ?>)" class="btn btn-sm btn-light border shadow-sm"><i class="fas fa-trash text-danger"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="row justify-content-center">
                    <div class="col-xl-8">
                        <div class="card-custom shadow-lg p-5">
                            <?php renderDetailProfil($u); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php
    $users_modal = $conn->query("SELECT * FROM users");
    while ($row_m = $users_modal->fetch_assoc()):
    ?>
        <div class="modal fade" id="modalDetail<?= $row_m['id_user']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
                    <div class="modal-body p-5">
                        <?php renderDetailProfil($row_m); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEdit<?= $row_m['id_user']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                    <form action="" method="POST">
                        <div class="modal-header border-0 p-4 pb-0">
                            <h5 class="fw-bold">Edit Akun</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <input type="hidden" name="id_user" value="<?= $row_m['id_user']; ?>">
                            <div class="mb-3">
                                <label class="small fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= $row_m['username']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold">Email</label>
                                <input type="email" name="gmail" class="form-control" value="<?= $row_m['gmail']; ?>" required>
                            </div>
                            <?php if ($role_user == 'admin'): ?>
                                <div class="mb-3">
                                    <label class="small fw-bold">Role</label>
                                    <select name="role" class="form-select">
                                        <option value="admin" <?= $row_m['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="petugas" <?= $row_m['role'] == 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="p-3 bg-light rounded-3">
                                <label class="small fw-bold text-primary">Password Baru (Kosongkan jika tidak ganti)</label>
                                <input type="password" name="password" class="form-control mt-1 shadow-sm">
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="submit" name="edit" class="btn btn-primary w-100 py-2 rounded-3 shadow">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                <form action="" method="POST">
                    <div class="modal-header border-0 p-4 pb-0">
                        <h5 class="fw-bold">Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="small fw-bold">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Email (Gmail)</label>
                            <input type="email" name="gmail" class="form-control" placeholder="user@gmail.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Role</label>
                            <select name="role" class="form-select">
                                <option value="petugas">Petugas</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" name="tambah" class="btn btn-primary w-100 py-2 rounded-3 shadow">Tambah User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: "Sesi Anda akan diakhiri.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus User?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'admin.php?hapus=' + id;
                }
            });
        }
    </script>
</body>

</html>