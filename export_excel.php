<?php
require_once 'includes/config.php';
require_once 'includes/function.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=data_zakat.xls");
header("Pragma: no-cache");
header("Expires: 0");

$data_zakat = get_zakat_data();
?>

<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>ID Pembayar</th>
            <th>Nama Pembayar</th>
            <th>Nama Kepala Keluarga</th>
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
                    <td><?php echo $row['id_pembayar']; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pembayar']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_kepala_keluarga']); ?></td>
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
                <td colspan="6">Belum ada data pembayaran zakat</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>