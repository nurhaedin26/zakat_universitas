<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function get_zakat_data() {
    global $conn;
    $sql = "SELECT * FROM pembayaran_zakat ORDER BY tanggal_pembayaran DESC";
    $result = $conn->query($sql);
    return $result;
}

function get_zakat_by_id($id) {
    global $conn;
    $sql = "SELECT * FROM pembayaran_zakat WHERE id_pembayar = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function update_zakat_status($id, $status) {
    global $conn;
    $sql = "UPDATE pembayaran_zakat SET status = ? WHERE id_pembayar = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
}
?>

