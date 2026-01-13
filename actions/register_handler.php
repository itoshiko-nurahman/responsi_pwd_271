<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $username  = trim(htmlspecialchars($_POST['username']));
  $nim       = trim(htmlspecialchars($_POST['nim']));
  
  $full_name = isset($_POST['full_name']) ? trim(htmlspecialchars($_POST['full_name'])) : $username;
  $password  = $_POST['password'];
  $role      = 'student';

  // Validasi NIM harus Angka
  if (!is_numeric($nim)) {
    $_SESSION['error'] = "Format NIM salah! NIM harus berupa angka.";
    header("Location: ../auth/register");
    exit;
  }

  // Validasi Panjang NIM
  if (strlen($nim) != 10) {
    $_SESSION['error'] = "Panjang NIM tidak valid!";
    header("Location: ../auth/register");
    exit;
  }

  try {
    // Cek Duplikasi (Username ATAU NIM)
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR nim = ?");
    $check->execute([$username, $nim]);

    if ($check->rowCount() > 0) {
      $_SESSION['error'] = "Username atau NIM sudah terdaftar!";
      header("Location: ../auth/register");
      exit;
    }

    // Hashing & Insert
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nim, full_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $role, $nim, $full_name]);

    $_SESSION['success'] = "Registrasi Berhasil! Silakan Login.";
    header("Location: ../auth/register");
  } catch (PDOException $e) {
    $_SESSION['error'] = "Terjadi kesalahan sistem saat mendaftar.";
    header("Location: ../auth/register");
    exit;
  }
} else {
  header("Location: ../auth/register");
  exit;
}
