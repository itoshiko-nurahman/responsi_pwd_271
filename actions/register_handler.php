<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = htmlspecialchars($_POST['username']);
  $nim = htmlspecialchars($_POST['nim']); // Tangkap NIM
  $password = $_POST['password'];
  $role = 'student';

  $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $check->execute([$username]);

  if ($check->rowCount() > 0) {
    $_SESSION['error'] = "Username sudah digunakan!";
    header("Location: ../auth/register");
    exit;
  }

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  try {
    // Simpan Username, Password, Role, dan NIM
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nim, full_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $role, $nim, $username]); // full_name default username dulu

    $_SESSION['success'] = "Registrasi Berhasil! Silakan Login.";
    header("Location: ../auth/register");
  } catch (PDOException $e) {
    $_SESSION['error'] = "Gagal mendaftar.";
    header("Location: ../auth/register");
  }
}
