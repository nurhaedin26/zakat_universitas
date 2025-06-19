<?php
require_once 'includes/config.php';
require_once 'includes/function.php';

// Update status to 'sukses' if action is confirm
if (isset($_GET['action']) && $_GET['action'] == 'confirm' && isset($_GET['id'])) {
    if (update_zakat_status($_GET['id'], 'sukses')) {
        $message = "Pembayaran telah dikonfirmasi sebagai sukses!";
    } else {
        $message = "Gagal mengkonfirmasi pembayaran.";
    }
}

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build SQL query based on filter
$sql = "SELECT * FROM pembayaran_zakat WHERE 1=1";

if ($filter == 'sukses') {
    $sql .= " AND status = 'sukses'";
} elseif ($filter == 'pending') {
    $sql .= " AND status = 'pending'";
}

$sql .= " ORDER BY tanggal_pembayaran DESC";
$riwayat_zakat = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>

<h2>Riwayat Pembayaran Zakat</h2>

<?php if (isset($message)): ?>
    <div class="alert <?php echo strpos($message, 'sukses') !== false ? 'alert-success' : 'alert-error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="filter-options">
    <a href="riwayat.php?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">Semua</a>
    <a href="riwayat.php?filter=sukses" class="filter-btn <?php echo $filter == 'sukses' ? 'active' : ''; ?>">Sukses</a>
    <a href="riwayat.php?filter=pending" class="filter-btn <?php echo $filter == 'pending' ? 'active' : ''; ?>">Pending</a>
</div>

<div class="table-container">
    <table class="zakat-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pembayar</th>
                <th>Jumlah Zakat</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($riwayat_zakat->num_rows > 0): ?>
                <?php $no = 1; ?>
                <?php while($row = $riwayat_zakat->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_pembayar']); ?></td>
                        <td>
                            <?php 
                            if ($row['jenis_zakat'] == 'uang') {
                                echo 'Rp ' . number_format($row['jumlah_uang'], 0, ',', '.');
                            } else {
                                echo $row['jumlah_beras'] . ' kg';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'sukses'): ?>
                                <span class="status-success">Sukses</span>
                            <?php else: ?>
                                <span class="status-pending">Pending</span>
                                <?php if ($filter != 'sukses'): ?>
                                    <a href="riwayat.php?action=confirm&id=<?php echo $row['id_pembayar']; ?>&filter=<?php echo $filter; ?>" 
                                       class="btn-confirm" 
                                       onclick="return confirm('Konfirmasi pembayaran ini sebagai sukses?')">
                                        Konfirmasi
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M Y H:i', strtotime($row['tanggal_pembayaran'])); ?></td>
                        <td>
                            <a href="edit_data.php?edit=1&id=<?php echo $row['id_pembayar']; ?>" class="btn-edit">Edit</a>
                            <a href="edit_data.php?action=delete&id=<?php echo $row['id_pembayar']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">
                        <?php 
                        if ($filter == 'sukses') {
                                echo 'Belum ada pembayaran yang sukses';
                            } elseif ($filter == 'pending') {
                                echo 'Tidak ada pembayaran yang pending';
                            } else {
                                echo 'Belum ada data pembayaran zakat';
                            }
                        ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>