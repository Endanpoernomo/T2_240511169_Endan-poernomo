<?php $page = basename($_SERVER['PHP_SELF']); ?>
<nav id="sidebar" class="d-flex flex-column justify-content-between p-3">
    <div>
        <a href="index.php" class="d-flex align-items-center text-white text-decoration-none p-3 mb-3 border-bottom border-secondary">
            <i class="bi bi-mortarboard-fill fs-3 text-primary me-2"></i>
            <span class="fs-5 fw-bold">upload tugas</span>
        </a>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?= $page == 'index.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="tugas.php" class="nav-link <?= $page == 'tugas.php' ? 'active' : '' ?>">
                    <i class="bi bi-journal-check me-2"></i> Pengumpulan Tugas
                </a>
            </li>
            <li class="nav-item">
                <a href="materi.php" class="nav-link <?= $page == 'materi.php' ? 'active' : '' ?>">
                    <i class="bi bi-book me-2"></i> Materi Pembelajaran
                </a>
            </li>
        </ul>
    </div>
    <div class="border-top border-secondary pt-3">
        <div class="d-flex align-items-center px-3 mb-3">
            <div class="bg-primary rounded-circle text-white d-flex justify-content-center align-items-center fw-bold me-2" style="width: 35px; height: 35px;">
                <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <h6 class="mb-0 text-white text-truncate"><?= htmlspecialchars($_SESSION['nama']) ?></h6>
                <small class="text-muted text-truncate d-block"><?= htmlspecialchars($_SESSION['email']) ?></small>
            </div>
        </div>
        <a href="logout.php" class="btn btn-outline-danger w-100 rounded-3">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</nav>
<div id="content">
