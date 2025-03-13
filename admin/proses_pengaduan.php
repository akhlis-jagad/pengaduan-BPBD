<?php
session_start();
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$id || !$action) {
        echo json_encode(['status' => 'error', 'message' => 'ID atau aksi tidak valid']);
        exit;
    }

    if ($action === 'approve') {
        $status = 'disetujui';
    } elseif ($action === 'reject') {
        $status = 'ditolak';
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM pengaduan WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Pengaduan berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus pengaduan']);
        }
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali']);
        exit;
    }

    // Proses update status pengaduan
    $stmt = $conn->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => "Pengaduan berhasil $status"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui pengaduan']);
    }
    exit;
}

?>
