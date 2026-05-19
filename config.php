<?php
// config.php
$host = 'localhost';
$dbname = 'db_kpu_klu';
$user = 'root';
$pass = ''; // Sesuaikan jika menggunakan password di XAMPP/Laragon

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to object
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage() . ". <br>Pastikan Anda telah membuat database 'db_kpu_klu' dan mengimpor file database.sql");
}
?>
