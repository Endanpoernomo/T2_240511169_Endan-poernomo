<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
$user_id = $_SESSION['user_id'];
$message = '';

// Path direktori upload materi
$upload_dir = 'uploads/materi/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// DELETE MATERI + FILE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt_file = $conn->prepare("SELECT file_path FROM materi WHERE id = ? AND user_id = ?");
    $stmt_file->bind_param("ii", $id, $user_id);
    $stmt_file->execute();
    $res_file = $stmt_file->get_result()->fetch_assoc();
    if ($res_file && !empty($res_file['file_path']) && file_exists($res_file['file_path'])) {
        unlink($res_file['file_path']);
    }

    $stmt = $conn->prepare("DELETE FROM materi WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    header("Location: materi.php");
    exit();
}

// TAMBAH MATERI BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $dosen = trim($_POST['dosen']);
    $tanggal = $_POST['tanggal'];
    $deskripsi = trim($_POST['deskripsi']);
    $file_path = null;

    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_materi']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['file_materi']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $file_path = $target_file;
        }
    }
$stmt = $conn->prepare("INSERT INTO materi (user_id, judul, dosen, tanggal, deskripsi, link_file) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $judul, $dosen, $tanggal, $deskripsi, $file_path);
    $stmt->execute();
    $message = "Materi berhasil diunggah!";
}

// AMBIL DATA MATERI
$stmt = $conn->prepare("SELECT * FROM materi WHERE user_id = ? ORDER BY tanggal DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$materi_list = $stmt->get_result();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-book text-primary"></i> Materi Pembelajaran</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMateri">
        <i class="bi bi-upload"></i> Upload Materi Baru
    </button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
            <input type="text" id="searchTugas" class="form-control" placeholder="Cari judul tugas, dosen, atau status...">
        </div>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Judul Materi</th>
                        <th>Nama Dosen</th>
                        <th>Tanggal Materi</th>
                        <th>Deskripsi</th>
                        <th>Berkas Materi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($materi_list->num_rows > 0): $no = 1; ?>
                        <?php while ($row = $materi_list->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['dosen']) ?></td>
                                <td><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td>
                                
    <?php if (!empty($row['link_file']) && file_exists($row['link_file'])): ?>
        <a href="<?= htmlspecialchars($row['link_file']) ?>" class="btn btn-sm btn-outline-success" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> Unduh
        </a>
    <?php else: ?>
        <span class="text-muted small">Tidak ada berkas</span>
    <?php endif; ?>
</td>
                            
                                <td>
                                    <a href="materi.php?delete=<?= $row['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Yakin ingin menghapus materi ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada materi pembelajaran.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Upload Materi -->
<div class="modal fade" id="modalMateri" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="materi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Materi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Materi</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Dosen</label>
                        <input type="text" name="dosen" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Materi</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Ringkas</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Berkas Materi (PDF/PPT/Zip)</label>
                        <input type="file" name="file_materi" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
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
<?php require_once 'includes/footer.php'; ?>