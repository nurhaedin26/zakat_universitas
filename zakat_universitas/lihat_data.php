<?php
require_once 'includes/config.php';
require_once 'includes/function.php';

$data_zakat = get_zakat_data();
?>

<?php include 'includes/header.php'; ?>

<h2>Data Pembayaran Zakat</h2>

<div class="table-container">
    <table class="zakat-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pembayar</th>
                <th>Jenis Zakat</th>
                <th>Jumlah Zakat</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($data_zakat->num_rows > 0): ?>
                <?php $no = 1; ?>
                <?php while($row = $data_zakat->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_pembayar']); ?></td>
                        <td><?php echo ucfirst($row['jenis_zakat']); ?></td>
                        <td>
                            <?php 
                            if ($row['jenis_zakat'] == 'uang') {
                                echo 'Rp ' . number_format($row['jumlah_uang'], 0, ',', '.');
                            } else {
                                echo $row['jumlah_beras'] . ' kg';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada data pembayaran zakat</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>