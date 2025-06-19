<?php
require_once 'includes/config.php';
require_once 'includes/function.php';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM pembayaran_zakat WHERE id_pembayar = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Data berhasil dihapus!";
    } else {
        $message = "Error menghapus data: " . $conn->error;
    }
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id_pembayar'];
    $nama_pembayar = sanitize_input($_POST['nama_pembayar']);
    $nama_kepala_keluarga = sanitize_input($_POST['nama_kepala_keluarga']);
    $jumlah_anggota = sanitize_input($_POST['jumlah_anggota_keluarga']);
    $jenis_zakat = sanitize_input($_POST['jenis_zakat']);
    
    $jumlah_uang = null;
    $jumlah_beras = null;
    
    if ($jenis_zakat == 'uang') {
        $jumlah_uang = sanitize_input($_POST['jumlah_uang']);
    } else {
        $jumlah_beras = sanitize_input($_POST['jumlah_beras']);
    }
    
    // Handle file upload if new file is provided
    $target_file = null;
    if (!empty($_FILES["bukti_pembayaran"]["name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["bukti_pembayaran"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES["bukti_pembayaran"]["tmp_name"]);
        if($check === false) {
            $message = "File is not an image.";
            $uploadOk = 0;
        }
        
        if ($_FILES["bukti_pembayaran"]["size"] > 5000000) {
            $message = "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            if (!move_uploaded_file($_FILES["bukti_pembayaran"]["tmp_name"], $target_file)) {
                $message = "Sorry, there was an error uploading your file.";
            }
        }
    }
    
    // Update database
    $sql = "UPDATE pembayaran_zakat SET 
            nama_pembayar = ?, 
            nama_kepala_keluarga = ?, 
            jumlah_anggota_keluarga = ?, 
            jenis_zakat = ?, 
            jumlah_uang = ?, 
            jumlah_beras = ?" . 
            ($target_file ? ", bukti_pembayaran = ?" : "") . 
            " WHERE id_pembayar = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($target_file) {
        $stmt->bind_param("ssisdssi", $nama_pembayar, $nama_kepala_keluarga, $jumlah_anggota, $jenis_zakat, $jumlah_uang, $jumlah_beras, $target_file, $id);
        
    }
    
    if ($stmt->execute()) {
        $message = "Data berhasil diperbarui!";
    } else {
        $message = "Error memperbarui data: " . $conn->error;
    }
}

$data_zakat = get_zakat_data();
?>

<?php include 'includes/header.php'; ?>

<h2>Edit Data Pembayaran Zakat</h2>

<?php if (isset($message)): ?>
    <div class="alert <?php echo strpos($message, 'berhasil') !== false ? 'alert-success' : 'alert-error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['edit']) && isset($_GET['id'])): ?>
    <?php 
    $id = $_GET['id'];
    $data = get_zakat_by_id($id);
    if ($data): ?>
        <div class="edit-form-container">
            <h3>Edit Data Pembayaran Zakat</h3>
            <p class="edit-instruction">Silahkan edit data Anda</p>
            
            <form action="edit_data.php" method="post" enctype="multipart/form-data" class="zakat-form">
                <input type="hidden" name="edit" value="1">
                <input type="hidden" name="id_pembayar" value="<?php echo $data['id_pembayar']; ?>">
                
                <div class="form-group">
                    <label for="nama_pembayar">Nama Pembayar:</label>
                    <input type="text" id="nama_pembayar" name="nama_pembayar" value="<?php echo htmlspecialchars($data['nama_pembayar']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nama_kepala_keluarga">Nama Kepala Keluarga:</label>
                    <input type="text" id="nama_kepala_keluarga" name="nama_kepala_keluarga" value="<?php echo htmlspecialchars($data['nama_kepala_keluarga']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="jumlah_anggota_keluarga">Jumlah Anggota Keluarga:</label>
                    <input type="number" id="jumlah_anggota_keluarga" name="jumlah_anggota_keluarga" min="1" value="<?php echo $data['jumlah_anggota_keluarga']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Jenis Zakat:</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="jenis_zakat" value="uang" <?php echo $data['jenis_zakat'] == 'uang' ? 'checked' : ''; ?> onchange="toggleZakatType()"> Dengan Uang
                        </label>
                        <label>
                            <input type="radio" name="jenis_zakat" value="beras" <?php echo $data['jenis_zakat'] == 'beras' ? 'checked' : ''; ?> onchange="toggleZakatType()"> Dengan Beras
                        </label>
                    </div>
                </div>
                
                <div class="form-group" id="uang-group" style="<?php echo $data['jenis_zakat'] == 'beras' ? 'display: none;' : ''; ?>">
                    <label for="jumlah_uang">Jumlah Uang (Rp):</label>
                    <input type="number" id="jumlah_uang" name="jumlah_uang" min="1000" step="1000" value="<?php echo $data['jumlah_uang']; ?>" <?php echo $data['jenis_zakat'] == 'uang' ? 'required' : ''; ?>>
                </div>
                
                <div class="form-group" id="beras-group" style="<?php echo $data['jenis_zakat'] == 'uang' ? 'display: none;' : ''; ?>">
                    <label for="jumlah_beras">Jumlah Beras (kg):</label>
                    <input type="number" id="jumlah_beras" name="jumlah_beras" min="1" step="0.1" value="<?php echo $data['jumlah_beras']; ?>" <?php echo $data['jenis_zakat'] == 'beras' ? 'required' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label for="bukti_pembayaran">Bukti Pembayaran Baru (kosongkan jika tidak ingin mengubah):</label>
                    <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/*">
                    <?php if ($data['bukti_pembayaran']): ?>
                        <p>Bukti saat ini: <a href="<?php echo $data['bukti_pembayaran']; ?>" target="_blank">Lihat</a></p>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Simpan Perubahan</button>
                    <a href="edit_data.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-error">Data tidak ditemukan</div>
    <?php endif; ?>
<?php else: ?>
    <div class="table-container">
        <table class="zakat-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Pembayar</th>
                    <th>Nama Pembayar</th>
                    <th>Jenis Zakat</th>
                    <th>Jumlah</th>
                    <th>Aksi</th>
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
                            <td class="action-buttons">
                                <a href="edit_data.php?edit=1&id=<?php echo $row['id_pembayar']; ?>" class="btn-edit">Edit</a>
                                <a href="edit_data.php?action=delete&id=<?php echo $row['id_pembayar']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data pembayaran zakat</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="add-button-container">
        <a href="pembayaran.php" class="btn-add">Tambah Data Baru</a>
    </div>
<?php endif; ?>

<script>
function toggleZakatType() {
    const uangGroup = document.getElementById('uang-group');
    const berasGroup = document.getElementById('beras-group');
    const jenisUang = document.querySelector('input[name="jenis_zakat"][value="uang"]');
    
    if (jenisUang.checked) {
        uangGroup.style.display = 'block';
        berasGroup.style.display = 'none';
        document.getElementById('jumlah_uang').required = true;
        document.getElementById('jumlah_beras').required = false;
    } else {
        uangGroup.style.display = 'none';
        berasGroup.style.display = 'block';
        document.getElementById('jumlah_uang').required = false;
        document.getElementById('jumlah_beras').required = true;
    }
}
</script>

<?php include 'includes/footer.php'; ?>