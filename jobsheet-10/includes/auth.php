<?php
// Guard clause: di-include di baris paling atas setiap halaman yang
// membutuhkan login (sebelum header.php mengeluarkan output apa pun),
// agar header('Location: ...') masih bisa dipanggil.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== TAMBAHAN: kalau session sudah gak ada, coba cek cookie "Ingat Saya" =====
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require __DIR__ . '/koneksi.php';

    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare(
        "SELECT u.* FROM remember_tokens rt
         JOIN users u ON rt.user_id = u.id
         WHERE rt.token = :token AND rt.expires_at > NOW()"
    );
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Token valid — login-kan user otomatis lewat session baru
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
    } else {
        // Token gak valid/kedaluwarsa — hapus cookie basi ini
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
        ]);
    }
}
// ===== akhir tambahan =====

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}