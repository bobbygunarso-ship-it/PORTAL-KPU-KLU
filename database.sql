CREATE DATABASE IF NOT EXISTS db_kpu_klu;
USE db_kpu_klu;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default password is 'password123'
INSERT INTO users (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('kepegawaian', 'publik') NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL DEFAULT '#',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed initial data
INSERT INTO services (type, title, description, icon, url) VALUES
('kepegawaian', 'E-Presensi', 'Sistem absensi dan kehadiran elektronik pegawai KPU.', 'fas fa-user-clock', '#'),
('kepegawaian', 'SIMPEG', 'Sistem Informasi Manajemen Kepegawaian internal.', 'fas fa-users-gear', '#'),
('kepegawaian', 'MyASN', 'Portal layanan kepegawaian BKN untuk pembaruan data mandiri.', 'fas fa-id-card', '#'),
('kepegawaian', 'E-Kinerja', 'Penilaian dan pelaporan kinerja Aparatur Sipil Negara.', 'fas fa-chart-line', '#'),
('publik', 'Website Resmi', 'Berita, pengumuman, regulasi, dan informasi seputar kegiatan KPU Kabupaten Lombok Utara.', 'fas fa-globe', '#'),
('publik', 'Cek DPT Online', 'Periksa status pendaftaran Anda sebagai pemilih dan lokasi TPS pada Pemilu atau Pemilihan.', 'fas fa-user-check', '#'),
('publik', 'JDIH', 'Jaringan Dokumentasi dan Informasi Hukum. Akses produk-produk hukum kepemiluan.', 'fas fa-scale-balanced', '#'),
('publik', 'E-PPID', 'Layanan permohonan informasi publik secara online oleh masyarakat sesuai UU KIP.', 'fas fa-circle-info', '#'),
('publik', 'SP4N LAPOR!', 'Sistem Pengelolaan Pengaduan Pelayanan Publik Nasional. Sampaikan laporan Anda.', 'fas fa-bullhorn', '#'),
('publik', 'Tanggapan Masyarakat', 'Saluran resmi untuk memberikan masukan dan tanggapan terkait tahapan penyelenggaraan pemilu.', 'fas fa-comments', '#');
