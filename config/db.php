<?php
$host = 'localhost';
$db   = 'library_db';
$user = 'root';
$pass = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Koneksi Gagal: " . $e->getMessage());
}

define('BASE_URL', 'http://localhost/responsi_pwd_271/');