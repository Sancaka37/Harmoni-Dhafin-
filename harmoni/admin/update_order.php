<?php
include '../koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idStr = $_POST['id_pesanan'] ?? ''; // e.g. #HRM-0021
    $status = $_POST['status'] ?? '';

    if (empty($idStr) || empty($status)) {
        echo json_encode(['error' => 'Data tidak lengkap']);
        exit;
    }

    $id = intval(preg_replace('/[^0-9]/', '', $idStr));

    try {
        $stmt = $conn->prepare("UPDATE pesanan SET status_pesanan = :status WHERE id_pesanan = :id");
        $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);

        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
