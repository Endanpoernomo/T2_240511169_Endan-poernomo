<?php
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Email sudah digunakan!';
    } else {
        $query = "INSERT INTO users (nama, email, password) VALUES ('$nama', '$email', '$password')";
        if (mysqli_query($conn, $query)) {
            $success = 'Pendaftaran berhasil! Silakan <a href="login.php">Login</a>';
        } else {
            $error = 'Terjadi kesalahan sistem!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h4 class="fw-bold text-center mb-3">Buat Akun Baru</h4>
                <?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success py-2"><?= $success ?></div><?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Daftar</button>
                </form>
                <div class="text-center mt-3 text-muted">
                    Sudah punya akun? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
