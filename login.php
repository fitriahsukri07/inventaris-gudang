<?php
session_start();
include 'conn.php';

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' AND password ='$password'");
    $cek = mysqli_num_rows($query);

    if ($cek > 0) {
        // ... kode setelah query login berhasil ...
        $data = mysqli_fetch_assoc($query);

        $_SESSION['login'] = true;
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role']; // <--- PASTIKAN BARIS INI ADA

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | F-ZONE Inventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            /* Background Biru Langit Gradasi */
            background: linear-gradient(135deg, #4895b8 0%, #3b7f9d 50%, #7dd3fc 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Elemen Dekoratif (Awan/Cahaya) */
        .bg-circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            filter: blur(60px);
            z-index: 1;
            animation: move 10s infinite alternate;
        }

        @keyframes move {
            from {
                transform: translate(0, 0);
            }

            to {
                transform: translate(50px, 100px);
            }
        }

        .login-box {
            /* Container Biru Langit Transparan (Glassmorphism) */
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 50px;
            width: 100%;
            max-width: 400px;
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            z-index: 10;
            transition: transform 0.3s ease;
        }

        .login-box:hover {
            transform: translateY(-5px);
        }

        .login-box h2 {
            font-weight: 800;
            color: #0369a1;
            /* Biru Tua */
            font-size: 26px;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .login-box p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 30px;
        }

        /* Group Styling */
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 700;
            color: #0c4a6e;
            margin-left: 5px;
            margin-bottom: 8px;
            display: block;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #0ea5e9;
            transition: 0.3s;
        }

        .form-group input {
            width: 100%;
            padding: 14px 15px 14px 50px;
            border-radius: 15px;
            border: 2px solid #e0f2fe;
            background: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            color: #0c4a6e;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0ea5e9;
            background: #fff;
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.2);
        }

        /* Tombol Biru Cerah */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #0284c7, #0ea5e9);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3);
            margin-top: 10px;
        }

        .btn-login:hover {
            box-shadow: 0 15px 25px rgba(14, 165, 233, 0.4);
            transform: scale(1.02);
            filter: brightness(1.1);
        }

        /* Error Alert yang lebih manis */
        .error {
            background: #fff1f2;
            color: #e11d48;
            padding: 12px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #ffe4e6;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .footer-text {
            margin-top: 25px;
            font-size: 12px;
            color: #94a3b8;
        }

        .footer-text b {
            color: #0ea5e9;
        }
    </style>

    <div class="bg-circle" style="width: 300px; height: 300px; top: -50px; left: -50px;"></div>
    <div class="bg-circle" style="width: 400px; height: 400px; bottom: -100px; right: -50px; animation-delay: 2s;"></div>
</head>

<body>
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="login-box">
        <h2>INVENTARIS <span style="color: #1d976c;">GUDANG</span></h2>
        <p>Silakan login untuk mengelola stok</p>

        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Username" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">
                Masuk Sekarang <i class="fas fa-arrow-right" style="margin-left: 8px; font-size: 14px;"></i>
            </button>
        </form>

        <div class="footer-text">
            &copy; 2026 Inventaris Gudang. All Rights Reserved.
        </div>
    </div>
</body>

</html>