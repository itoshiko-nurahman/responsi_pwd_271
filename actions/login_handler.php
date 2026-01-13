<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $username = trim(htmlspecialchars($_POST['username']));
  $password = $_POST['password'];

  if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Silakan isi username dan password!";
    // UBAH DISINI: Arahkan kembali ke folder auth
    header("Location: ../auth/login");
    exit;
  }

  try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

      // --- LOGIN SUKSES ---
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['nim'] = $user['nim'] ?? null;

      // Redirect ke Dashboard (Naik satu folder ke root)
      header("Location: ../index");
      exit;
    } else {
      // --- LOGIN GAGAL ---
      $_SESSION['error'] = "Username atau password salah!";
      header("Location: ../auth/login");
      exit;
    }
  } catch (PDOException $e) {
    $_SESSION['error'] = "Terjadi kesalahan sistem.";
    header("Location: ../auth/login");
    exit;
  }
} else {
  header("Location: ../auth/login");
  exit;
}
