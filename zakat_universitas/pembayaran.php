<?php
require_once 'includes/config.php';
require_once 'includes/function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_pembayar = sanitize_input($_POST['nama_pembayar']);
    $nama_kepala_keluarga = sanitize_input($_POST['nama_kepala_keluarga']);
    $jumlah_anggota = sanitize_input($_POST['jumlah_anggota_keluarga']);
    $jenis_zakat = sanitize_input($_POST['jenis_zakat']);
    
     $jumlah_uang = null;
    $jumlah_beras = null; // Inisialisasi awal

    if ($jenis_zakat == 'uang') {
        $jumlah_uang = sanitize_input($_POST['jumlah_uang']);
        // Jika jenis zakat adalah uang, set jumlah_beras menjadi 0, BUKAN NULL
        $jumlah_beras = 0; 
    } else { // jenis_zakat == 'beras'
        $jumlah_beras = sanitize_input($_POST['jumlah_beras']);
        // Jika jenis zakat adalah beras, set jumlah_uang menjadi 0, BUKAN NULL
        $jumlah_uang = 0;
    }
    
    // Handle file upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["bukti_pembayaran"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["bukti_pembayaran"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $error = "File is not an image.";
        $uploadOk = 0;
    }
    
    // Check file size
    if ($_FILES["bukti_pembayaran"]["size"] > 5000000) {
        $error = "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $message = "Sorry, your file was not uploaded. " . $error;
    } else {
  if (move_uploaded_file($_FILES["bukti_pembayaran"]["tmp_name"], $target_file)) {
            // Insert into database
            $sql = "INSERT INTO pembayaran_zakat (nama_pembayar, nama_kepala_keluarga, jumlah_anggota_keluarga, jenis_zakat, jumlah_uang, jumlah_beras, bukti_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
$stmt->bind_param("ssisdss", $nama_pembayar, $nama_kepala_keluarga, $jumlah_anggota, $jenis_zakat, $jumlah_uang, $jumlah_beras, $target_file);            
            if ($stmt->execute()) {
                $message = "Pembayaran zakat berhasil disimpan!";
            } else {
                $message = "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2>Form Pembayaran Zakat</h2>

<?php if (isset($message)): ?>
    <div class="alert <?php echo strpos($message, 'berhasil') !== false ? 'alert-success' : 'alert-error'; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<form action="pembayaran.php" method="post" enctype="multipart/form-data" class="zakat-form">
    <div class="form-group">
        <label for="nama_pembayar">Nama Pembayar:</label>
        <input type="text" id="nama_pembayar" name="nama_pembayar" required>
    </div>
    
    <div class="form-group">
        <label for="nama_kepala_keluarga">Nama Kepala Keluarga:</label>
        <input type="text" id="nama_kepala_keluarga" name="nama_kepala_keluarga" required>
    </div>
    
    <div class="form-group">
        <label for="jumlah_anggota_keluarga">Jumlah Anggota Keluarga:</label>
        <input type="number" id="jumlah_anggota_keluarga" name="jumlah_anggota_keluarga" min="1" required>
    </div>
    
    <div class="form-group">
        <label>Jenis Zakat:</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="jenis_zakat" value="uang" checked onchange="toggleZakatType()"> Dengan Uang
            </label>
            <label>
                <input type="radio" name="jenis_zakat" value="beras" onchange="toggleZakatType()"> Dengan Beras
            </label>
        </div>
    </div>
    
    <div class="form-group" id="uang-group">
        <label for="jumlah_uang">Jumlah Uang (Rp):</label>
        <input type="number" id="jumlah_uang" name="jumlah_uang" min="1000" step="1000" required>
    </div>
    
    <div class="form-group" id="beras-group" style="display: none;">
        <label for="jumlah_beras">Jumlah Beras (kg):</label>
        <input type="number" id="jumlah_beras" name="jumlah_beras" min="1" step="0.1">
    </div>
    
    <div class="form-group">
        <label for="bukti_pembayaran">Bukti Pembayaran:</label>
        <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/*" required>
    </div>
    
    <button type="submit" class="btn-submit">Bayar Zakat</button>
</form>

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