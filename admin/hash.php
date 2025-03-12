<?php
include '../koneksi.php';

// Password yang ingin diubah
$username = 'admin';
$new_password = password_hash('suri123', PASSWORD_DEFAULT); // Hash password baru

// Update password di database
$sql = "UPDATE users SET password='$new_password' WHERE username='$username'";
$conn->query($sql);

echo "Password admin berhasil diupdate!";
?>