<?php
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['email'] = $row['email'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Email tidak terdaftar!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card-login { border-radius: 1rem; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-login p-4 bg-white">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary"><i class="bi bi-mortarboard-fill"></i> Login</h3>
                    <p class="text-muted">Silakan masuk ke akun Anda</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="******" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 font-weight-bold mb-3">Login</button>
                </form>
                <div class="text-center text-muted">
                    Belum punya akun? <a href="register.php" class="text-decoration-none">Daftar Akun</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
