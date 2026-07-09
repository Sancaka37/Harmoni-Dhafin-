<?php
include 'koneksi.php';
try {
    $stmt = $conn->query("SELECT id_pelanggan, username, email, role FROM pelanggan");
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch(Exception $e) {
    echo $e->getMessage();
}
?>
