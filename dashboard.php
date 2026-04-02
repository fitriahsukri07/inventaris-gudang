<?php
session_start();
include 'conn.php';
$active_page = 'dashboard';

// Pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("location: login.php");
    exit;
}

// 1. Definisikan variabel role dan nama dari session
$role_user = $_SESSION['role'] ?? 'petugas';
$nama_user = $_SESSION['username'] ?? 'User';
$tgl_sekarang = date('Y-m-d');

// 2. Query Statistik (Menghitung FREKUENSI / JUMLAH TRANSAKSI)

// Total Jenis Produk yang terdaftar
$q_jenis = $conn->query("SELECT COUNT(*) AS total FROM barang");
$total_stokbarang = $q_jenis->fetch_assoc()['total'] ?? 0;

// Menghitung berapa kali terjadi transaksi masuk hari ini
$q_masuk = $conn->query("SELECT COUNT(*) AS total FROM transaksi WHERE status = 'masuk' AND tanggal_transaksi = '$tgl_sekarang'");
$total_barangmasuk = $q_masuk->fetch_assoc()['total'] ?? 0;

// Menghitung berapa kali terjadi transaksi keluar hari ini
$q_keluar = $conn->query("SELECT COUNT(*) AS total FROM transaksi WHERE status = 'keluar' AND tanggal_transaksi = '$tgl_sekarang'");
$total_barangkeluar = $q_keluar->fetch_assoc()['total'] ?? 0;

// Total Seluruh Unit Stok Fisik di Gudang (SUM stok_baik dari tabel barang)
$q_stok = $conn->query("SELECT SUM(stok_baik) AS total FROM barang");
$total_stok_saat_ini = $q_stok->fetch_assoc()['total'] ?? 0;

// 3. Logika Grafik Garis (Aktivitas 7 Hari Terakhir)
$labels = [];
$dataMasuk = [];
$dataKeluar = [];

for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($tgl));

    // COUNT(*) untuk menghitung jumlah inputan data per hari
    $m = $conn->query("SELECT COUNT(*) as jml FROM transaksi WHERE status='masuk' AND tanggal_transaksi='$tgl'")->fetch_assoc();
    $dataMasuk[] = (int)($m['jml'] ?? 0);

    $k = $conn->query("SELECT COUNT(*) as jml FROM transaksi WHERE status='keluar' AND tanggal_transaksi='$tgl'")->fetch_assoc();
    $dataKeluar[] = (int)($k['jml'] ?? 0);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | F-ZONE COMPANY</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        :root {
            --sidebar-bg: #0f172a;
            --primary-blue: #3b82f6;
            --bg-body: #f1f5f9;
        }

        body {
            background: var(--bg-body);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        /* Sidebar Modern */
        .main-sidebar {
            width: 260px;
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            padding: 20px;
            z-index: 100;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
        }

        .nav-link {
            color: #94a3b8 !important;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(59, 130, 246, 0.15);
            color: #fff !important;
        }

        .nav-link.active {
            background: var(--primary-blue) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Content Area */
        .content-wrapper {
            margin-left: 260px;
            padding: 40px;
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .icon-shape {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .card-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
        }
    </style>
</head>

<body>

    <aside class="main-sidebar p-3 shadow">
        <div class="d-flex align-items-center mb-4 px-2">
            <div class="bg-primary p-2 rounded-3 me-2 text-white">
                <i class="fas fa-boxes-stacked fa-lg"></i>
            </div>
            <h5 class="text-white mb-0 fw-bold">INVENTARIS <span class="text-primary">GUDANG</span></h5>
        </div>

        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link <?= ($active_page == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-th-large me-2"></i> Dashboard
            </a>

            <a href="barang.php" class="nav-link <?= ($active_page == 'data_barang') ? 'active' : ''; ?>">
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
                <i class="fas fa-users-cog me-2"></i>Manajemen Akun
            </a>

            <a href="logout.php" class="nav-link text-danger mt-5">
                <i class="fas fa-power-off me-2"></i> Logout
            </a>
        </nav>
    </aside>
    <main class="content-wrapper">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h3 class="fw-bold m-0 text-dark">Ringkasan Aktivitas</h3>
                <p class="text-muted mb-0">Selamat datang, <strong><?= htmlspecialchars($nama_user); ?></strong>.</p>
            </div>
            <div class="text-end">
                <div class="small text-muted fw-medium"><?= date('l, d F Y'); ?></div>
                <span class="badge bg-white text-dark border shadow-sm px-3 py-2 mt-1" style="border-radius: 10px;">
                    <i class="fas fa-user-shield me-1 text-primary"></i> <?= ucfirst($role_user); ?>
                </span>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Jenis Produk</p>
                            <h4 class="fw-bold m-0"><?= number_format($total_stokbarang); ?></h4>
                        </div>
                        <div class="icon-shape bg-primary-subtle text-primary"><i class="fas fa-layer-group"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Aktivitas Masuk</p>
                            <h4 class="fw-bold m-0 text-success"><?= number_format($total_barangmasuk); ?></h4>
                        </div>
                        <div class="icon-shape bg-success-subtle text-success"><i class="fas fa-plus-circle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Aktivitas Keluar</p>
                            <h4 class="fw-bold m-0 text-danger"><?= number_format($total_barangkeluar); ?></h4>
                        </div>
                        <div class="icon-shape bg-danger-subtle text-danger"><i class="fas fa-minus-circle"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small mb-1">Stok Fisik Gudang</p>
                            <h4 class="fw-bold m-0 text-info"><?= number_format($total_stok_saat_ini); ?></h4>
                        </div>
                        <div class="icon-shape bg-info-subtle text-info"><i class="fas fa-warehouse"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="fw-bold m-0">Tren Frekuensi Aktivitas</h6>
                    <small class="text-muted">Berapa kali transaksi dilakukan dalam 7 hari terakhir</small>
                </div>
                <div class="d-flex gap-2">
                    <span class="small"><i class="fas fa-circle text-success me-1"></i> Masuk</span>
                    <span class="small"><i class="fas fa-circle text-danger me-1"></i> Keluar</span>
                </div>
            </div>
            <div style="height: 320px;">
                <canvas id="lineChartAktivitas"></canvas>
            </div>
        </div>

        <div class="card card-custom">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0"><i class="fas fa-history me-2 text-primary"></i>5 Transaksi Terakhir</h6>
                <a href="transaksi_masuk.php" class="btn btn-sm btn-light border px-3" style="border-radius: 8px;">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nama Barang</th>
                            <th>Oleh</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Waktu & Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT u.username, t.status, t.tanggal_transaksi, b.nama 
                                  FROM transaksi t 
                                  JOIN users u ON t.id_user = u.id_user 
                                  JOIN barang b ON t.id_barang = b.id_barang
                                  ORDER BY t.id_transaksi DESC LIMIT 5";
                        $notif = $conn->query($query);
                        if ($notif && $notif->num_rows > 0) {
                            while ($row = $notif->fetch_assoc()):
                                $isMasuk = ($row['status'] === 'masuk');
                        ?>
                                <tr>
                                    <td class="ps-4 fw-semibold"><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><span class="text-muted small"><i class="far fa-user me-1"></i><?= htmlspecialchars($row['username']); ?></span></td>
                                    <td>
                                        <span class="badge-status <?= $isMasuk ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                            <i class="fas <?= $isMasuk ? 'fa-arrow-down' : 'fa-arrow-up'; ?> me-1"></i>
                                            <?= ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="small fw-bold text-dark"><?= date('H:i', strtotime($row['tanggal_transaksi'])); ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?= date('d M Y', strtotime($row['tanggal_transaksi'])); ?></div>
                                    </td>
                                </tr>
                        <?php endwhile;
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-4'>Tidak ada transaksi terbaru.</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('lineChartAktivitas').getContext('2d');

            // Gradasi warna untuk area di bawah garis
            const gradientMasuk = ctx.createLinearGradient(0, 0, 0, 400);
            gradientMasuk.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
            gradientMasuk.addColorStop(1, 'rgba(16, 185, 129, 0)');

            const gradientKeluar = ctx.createLinearGradient(0, 0, 0, 400);
            gradientKeluar.addColorStop(0, 'rgba(239, 68, 68, 0.2)');
            gradientKeluar.addColorStop(1, 'rgba(239, 68, 68, 0)');

            new Chart(ctx, {
                type: 'line', // Tetap menggunakan Garis
                data: {
                    labels: <?= json_encode($labels); ?>,
                    datasets: [{
                            label: 'Aktivitas Masuk',
                            data: <?= json_encode($dataMasuk); ?>,
                            borderColor: '#10b981',
                            backgroundColor: gradientMasuk,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4, // Membuat garis melengkung halus
                            pointRadius: 4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#10b981',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Aktivitas Keluar',
                            data: <?= json_encode($dataKeluar); ?>,
                            borderColor: '#ef4444',
                            backgroundColor: gradientKeluar,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4, // Membuat garis melengkung halus
                            pointRadius: 4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#ef4444',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }, // Legend dimatikan karena sudah ada di header kustom
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1, // Karena menghitung frekuensi (kali), angka harus bulat
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                borderDash: [5, 5],
                                color: '#e2e8f0'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        });

        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: "Anda akan keluar dari sistem inventaris.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Keluar sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = 'logout.php';
            });
        }
    </script>
</body>

</html>