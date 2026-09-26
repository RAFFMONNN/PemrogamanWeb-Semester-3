<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// ===== TAMBAHAN: siapkan tempat simpan percobaan gagal =====
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

$maxAttempts = 3;
$attemptsForUser = $_SESSION['login_attempts'][$username] ?? 0;

// ===== TAMBAHAN: cek dulu, sudah kelewat batas belum, SEBELUM cek password =====
if ($attemptsForUser >= $maxAttempts) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'pesan' => "Terlalu banyak percobaan gagal untuk username \"$username\". Coba lagi nanti."
    ];
    header('Location: login.php');
    exit;
}
// ===== akhir tambahan =====

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    // ===== TAMBAHAN: login berhasil, reset counter untuk username ini =====
    unset($_SESSION['login_attempts'][$username]);
    // ===== akhir tambahan =====

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $stmtToken = $pdo->prepare(
            "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)"
        );
        $stmtToken->execute([
            'user_id' => $user['id'],
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        setcookie('remember_token', $token, [
            'expires' => strtotime('+30 days'),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        if (isset($_COOKIE['remember_token'])) {
            $stmtHapus = $pdo->prepare("DELETE FROM remember_tokens WHERE token = :token");
            $stmtHapus->execute(['token' => $_COOKIE['remember_token']]);

            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
            ]);
        }
    }

    header('Location: ../index.php');
    exit;
}

// ===== TAMBAHAN: login gagal, naikkan counter untuk username ini =====
$_SESSION['login_attempts'][$username] = $attemptsForUser + 1;
$sisaPercobaan = $maxAttempts - $_SESSION['login_attempts'][$username];

if ($sisaPercobaan > 0) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'pesan' => "Username atau password salah. Sisa percobaan: $sisaPercobaan."
    ];
} else {
    $_SESSION['flash'] = [
        'type' => 'error',
        'pesan' => "Username atau password salah. Terlalu banyak percobaan gagal, coba lagi nanti."
    ];
}
// ===== akhir tambahan =====

header('Location: login.php');
exit;