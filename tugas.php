<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
$user_id = $_SESSION['user_id'];
$message = '';

// Path direktori upload tugas
$upload_dir = 'uploads/tugas/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// TOGGLE STATUS
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = $conn->prepare("UPDATE tugas SET status = IF(status='Selesai', 'Belum Selesai', 'Selesai') WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    header("Location: tugas.php");
    exit();
}

// DELETE TUGAS + FILE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Ambil path file dulu sebelum hapus record
    $stmt_file = $conn->prepare("SELECT file_path FROM tugas WHERE id = ? AND user_id = ?");
    $stmt_file->bind_param("ii", $id, $user_id);
    $stmt_file->execute();
    $res_file = $stmt_file->get_result()->fetch_assoc();
    if ($res_file && !empty($res_file['file_path']) && file_exists($res_file['file_path'])) {
        unlink($res_file['file_path']); // Hapus file fisik
    }

    $stmt = $conn->prepare("DELETE FROM tugas WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    header("Location: tugas.php");
    exit();
}

// ADD / EDIT TUGAS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $dosen = trim($_POST['dosen']);
    $deadline = $_POST['deadline'];
    $tugas_id = isset($_POST['tugas_id']) ? intval($_POST['tugas_id']) : 0;

    $file_path = null;

    // Handle Upload File jika ada
    if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_tugas']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['file_tugas']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $file_path = $target_file;
        }
    }

    if ($tugas_id > 0) {
        // Mode Update
        if ($file_path) {
            // Hapus file lama jika unggah file baru
            $stmt_old = $conn->prepare("SELECT file_path FROM tugas WHERE id = ? AND user_id = ?");
            $stmt_old->bind_param("ii", $tugas_id, $user_id);
            $stmt_old->execute();
            $old_data = $stmt_old->get_result()->fetch_assoc();
            if ($old_data && !empty($old_data['file_path']) && file_exists($old_data['file_path'])) {
                unlink($old_data['file_path']);
            }

            $stmt = $conn->prepare("UPDATE tugas SET judul = ?, dosen = ?, tanggal_deadline = ?, file_path = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssii", $judul, $dosen, $deadline, $file_path, $tugas_id, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE tugas SET judul = ?, dosen = ?, deadline = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssii", $judul, $dosen, $deadline, $tugas_id, $user_id);
        }
        $stmt->execute();
        $message = "Tugas berhasil diperbarui!";
    } else {
        // Mode Insert
        $stmt = $conn->prepare("INSERT INTO tugas (user_id, judul, dosen, tanggal_deadline, status, file_path) VALUES (?, ?, ?, ?, 'Belum Selesai', ?)");
        $stmt->bind_param("issss", $user_id, $judul, $dosen, $deadline, $file_path);
        $stmt->execute();
        $message = "Tugas berhasil ditambahkan!";
    }
}

// AMBIL DATA TUGAS
$stmt = $conn->prepare("SELECT * FROM tugas WHERE user_id = ? ORDER BY tanggal_deadline ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$tugas_list = $stmt->get_result();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-arrow-up text-primary"></i> Pengumpulan Tugas</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTugas" onclick="resetForm()">
        <i class="bi bi-plus-lg"></i> Tambah / Upload Tugas
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
                        <th>Judul Tugas</th>
                        <th>Dosen</th>
                        <th>Deadline</th>
                        <th>File Tugas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tugas_list->num_rows > 0): $no = 1; ?>
                        <?php while ($row = $tugas_list->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['dosen']) ?></td>
                                <td><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($row['tanggal_deadline'])) ?></td>
                                <td>
                                    <?php if (!empty($row['file_path']) && file_exists($row['file_path'])): ?>
                                        <a href="<?= $row['file_path'] ?>" class="btn btn-sm btn-outline-info" target="_blank">
                                            <i class="bi bi-download"></i> Unduh File
                                        </a>
                                
                                    <?php else: ?>
                                        <span class="text-muted small">Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="tugas.php?toggle=<?= $row['id'] ?>" class="text-decoration-none">
                                        <?php if ($row['status'] === 'Selesai'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success p-2">
                                                <i class="bi bi-check-circle-fill"></i> Selesai
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning p-2">
                                                <i class="bi bi-clock-history"></i> Belum Selesai
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick='editTugas(<?= json_encode($row) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="tugas.php?delete=<?= $row['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Yakin ingin menghapus tugas beserta filenya?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada tugas yang diunggah.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Tugas -->
<div class="modal fade" id="modalTugas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- PENTING: enctype untuk upload file -->
            <form action="tugas.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Tugas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="tugas_id" id="tugas_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Judul Tugas</label>
                        <input type="text" name="judul" id="input_judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Dosen</label>
                        <input type="text" name="dosen" id="input_dosen" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Deadline</label>
                        <input type="date" name="deadline" id="input_deadline" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File (PDF/Doc/Zip)</label>
                        <input type="file" name="file_tugas" class="form-control">
                        <div class="form-text">Kosongkan jika tidak ingin mengubah file saat edit.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('tugas_id').value = '';
    document.getElementById('input_judul').value = '';
    document.getElementById('input_dosen').value = '';
    document.getElementById('input_deadline').value = '';
    document.getElementById('modalTitle').innerText = 'Tambah Tugas Baru';
}

function editTugas(data) {
    document.getElementById('tugas_id').value = data.id;
    document.getElementById('input_judul').value = data.judul;
    document.getElementById('input_dosen').value = data.dosen;
    document.getElementById('input_deadline').value = data.deadline;
    document.getElementById('modalTitle').innerText = 'Edit Tugas';
    new bootstrap.Modal(document.getElementById('modalTugas')).show();
}
</script>
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