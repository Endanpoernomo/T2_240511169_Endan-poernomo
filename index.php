<?php
include 'includes/header.php';
include 'includes/sidebar.php';

$user_id = $_SESSION['user_id'];

// Count Statistics
$total_tugas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tugas WHERE user_id = $user_id"))['total'];
$selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tugas WHERE user_id = $user_id AND status = 'Selesai'"))['total'];
$belum = $total_tugas - $selesai;
$total_materi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM materi WHERE user_id = $user_id"))['total'];

$recent_tugas = mysqli_query($conn, "SELECT * FROM tugas WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
?>

<h3 class="fw-bold mb-4">Dashboard Overview</h3>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 bg-white border-start border-primary border-4">
            <div class="text-muted small">Total Tugas</div>
            <div class="h3 fw-bold mb-0 text-dark"><?= $total_tugas ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 bg-white border-start border-warning border-4">
            <div class="text-muted small">Belum Selesai</div>
            <div class="h3 fw-bold mb-0 text-warning"><?= $belum ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 bg-white border-start border-success border-4">
            <div class="text-muted small">Tugas Selesai</div>
            <div class="h3 fw-bold mb-0 text-success"><?= $selesai ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 bg-white border-start border-info border-4">
            <div class="text-muted small">Total Materi</div>
            <div class="h3 fw-bold mb-0 text-info"><?= $total_materi ?></div>
        </div>
    </div>
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
            <input type="text" id="searchTugas" class="form-control" placeholder="Cari judul tugas, dosen, atau status...">
        </div>
    </div>
</div>
<div class="card p-4">
    <h5 class="fw-bold mb-3">Tugas Terbaru</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Judul Tugas</th>
                    <th>Nama Dosen</th>
                    <th>Deadline</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($recent_tugas) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($recent_tugas)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['judul']) ?></td>
                            <td><?= htmlspecialchars($row['dosen']) ?></td>
                            <td><?= $row['tanggal_deadline'] ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'Selesai' ? 'success' : 'warning' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">Belum ada tugas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.getElementById('searchTugas').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('table tbody tr');

    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
<?php include 'includes/footer.php'; ?>