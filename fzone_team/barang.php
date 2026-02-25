<?php
session_start();
include "conn.php";

$active_page = 'data_barang';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role_user = $_SESSION['role'] ?? 'petugas';

// --- LOGIKA UPDATE (Sinkron dengan Stok Total) ---
if (isset($_POST['ubah_barang'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = (int) $_POST['harga'];
    $s_baik  = (int)$_POST['stok_baik'];
    $s_rusak = (int)$_POST['stok_rusak'];

    $cek = $conn->query("SELECT stok FROM barang WHERE id_barang = $id")->fetch_assoc();
    $stok_total_saat_ini = (int)$cek['stok'];

    if (($s_baik + $s_rusak) != $stok_total_saat_ini) {
        echo "<script>
                alert('Gagal! Total Baik + Rusak harus berjumlah $stok_total_saat_ini. Gunakan menu Transaksi Masuk untuk menambah stok.');
                window.location='barang.php?edit=$id';
              </script>";
    } else {
        $sql = "UPDATE barang SET nama='$nama', harga='$harga', stok_baik='$s_baik', stok_rusak='$s_rusak' WHERE id_barang=$id";
        if ($conn->query($sql)) {
            echo "<script>alert('Data Barang Berhasil Diperbarui!'); window.location='barang.php';</script>";
        }
    }
    exit();
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $edit_data = $conn->query("SELECT * FROM barang WHERE id_barang=$id")->fetch_assoc();
}
$result = $conn->query("SELECT * FROM barang ORDER BY id_barang DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang | F-ZONE COMPANY</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-blue: #0f172a;
            --accent-blue: #3eb4c7;
            --sidebar-bg: #1e293b;
            --bg-light: #f8fafc;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
            margin: 0;
        }

        /* --- SIDEBAR STYLE (Sesuai Dashboard) --- */
        .main-sidebar {
            min-height: 100vh;
            position: fixed;
            width: 260px;
            background-color: var(--sidebar-bg) !important;
            padding: 30px 20px;
            z-index: 1000;
        }

        .nav-link {
            color: #94a3b8 !important;
            border-radius: 12px;
            margin-bottom: 8px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: 0.25s;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: var(--accent-blue) !important;
            color: #fff !important;
            box-shadow: 0 8px 15px rgba(62, 180, 199, 0.2);
        }

        /* --- CONTENT AREA --- */
        .content-wrapper {
            margin-left: 260px;
            padding: 40px;
            width: calc(100% - 260px);
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        /* --- TABLE & UI --- */
        .table thead th {
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 1px;
            padding: 15px;
            border: none;
        }

        .table tbody td {
            padding: 18px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge-custom {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .bg-stok {
            background: #e0f2fe;
            color: #0369a1;
        }

        .bg-baik {
            background: #dcfce7;
            color: #15803d;
        }

        .bg-rusak {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-edit {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 6px 12px;
            color: #64748b;
            transition: 0.2s;
        }

        .btn-edit:hover {
            background: var(--primary-blue);
            color: #fff;
        }

        @media (max-width: 991px) {
            .main-sidebar {
                width: 80px;
                padding: 20px 10px;
            }

            .nav-link span,
            .sidebar-brand-text {
                display: none;
            }

            .content-wrapper {
                margin-left: 80px;
                width: calc(100% - 80px);
            }
        }
    </style>
</head>

<body>

    <div class="wrapper d-flex">
        <aside class="main-sidebar shadow">
            <div class="d-flex align-items-center mb-5 px-2">
                <div class="bg-primary p-2 rounded-3 me-2 text-white">
                    <i class="fas fa-boxes-stacked fa-lg"></i>
                </div>
                <h5 class="text-white mb-0 fw-bold sidebar-brand-text">INVENTARIS<br> <span style="color:var(--accent-blue)">GUDANG</span></h5>
            </div>

            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-th-large me-3"></i> <span>Dashboard</span>
                </a>
                <a href="barang.php" class="nav-link active">
                    <i class="fas fa-box me-3"></i> <span>Data Barang</span>
                </a>
                <a href="transaksi_masuk.php" class="nav-link">
                    <i class="fas fa-file-import me-3"></i> <span>Barang Masuk</span>
                </a>
                <a href="transaksi_keluar.php" class="nav-link">
                    <i class="fas fa-file-export me-3"></i> <span>Barang Keluar</span>
                </a>

                <div style="margin-top: 100px;">
                    <hr class="text-secondary opacity-25">
                    <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-link text-danger">
                        <i class="fas fa-power-off me-3"></i> <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <div class="content-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Manajemen Stok</h2>
                    <p class="text-muted">Pantau data inventaris dan klasifikasi kondisi barang.</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-white text-dark shadow-sm p-2 px-3 rounded-pill border">
                        <i class="fas fa-circle-user text-primary me-2"></i><?= ucfirst($role_user) ?>
                    </span>
                </div>
            </div>

            <?php if ($edit_data): ?>
                <div class="card mb-4 border-start border-4 border-info animate-fade">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3 text-info"><i class="fas fa-edit me-2"></i> Perbarui Barang: <?= $edit_data['nama'] ?></h6>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $edit_data['id_barang'] ?>">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="small fw-bold mb-1">Nama Barang</label>
                                    <input type="text" name="nama" class="form-control" value="<?= $edit_data['nama'] ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="small fw-bold mb-1">Harga (Rp)</label>
                                    <input type="number" name="harga" class="form-control" value="<?= $edit_data['harga'] ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="small fw-bold mb-1 text-success">Jumlah Baik</label>
                                    <input type="number" name="stok_baik" class="form-control border-success-subtle" value="<?= $edit_data['stok_baik'] ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="small fw-bold mb-1 text-danger">Jumlah Rusak</label>
                                    <input type="number" name="stok_rusak" class="form-control border-danger-subtle" value="<?= $edit_data['stok_rusak'] ?>" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2">
                                    <button type="submit" name="ubah_barang" class="btn btn-primary px-4 w-100 fw-bold">Update</button>
                                    <a href="barang.php" class="btn btn-light border px-4">Batal</a>
                                </div>
                            </div>
                            <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                                <i>* Total Baik + Rusak tidak boleh melebihi stok sistem (<?= $edit_data['stok'] ?>).</i>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama & Kode Barang</th>
                                <th>Total Stok</th>
                                <th>Harga Satuan</th>
                                <th>Status Kondisi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($d = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= $d['nama'] ?></div>
                                        <div style="font-size: 0.7rem;" class="text-muted">BRG-<?= str_pad($d['id_barang'], 4, '0', STR_PAD_LEFT) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-custom bg-stok"><?= $d['stok'] ?> Unit</span>
                                    </td>
                                    <td class="fw-semibold">
                                        Rp <?= number_format($d['harga'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <span class="badge-custom bg-baik" title="Kondisi Baik">B: <?= $d['stok_baik'] ?></span>
                                            <span class="badge-custom bg-rusak" title="Kondisi Rusak">R: <?= $d['stok_rusak'] ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="?edit=<?= $d['id_barang'] ?>" class="btn btn-edit">
                                            <i class="fas fa-pen-nib me-1"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: "Sesi Anda akan berakhir.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Keluar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            })
        }
    </script>
</body>

</html>