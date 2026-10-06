# ArenaBook
## Deskripsi
Sistem booking lapangan Futsal & Badminton berbasis PHP Native + MySQL (tanpa framework).
## Fitur
Customer: register, login, lihat lapangan, pilih tanggal/slot, booking, booking saya, detail, batalkan, profile.
Admin: dashboard, CRUD lapangan (hapus -> nonaktif jika sudah ada booking), kelola jadwal (block/unblock), kelola booking (filter + ubah status), data customer.
## Teknologi
PHP 8+, MySQL/MariaDB (InnoDB), PDO, HTML, CSS, JavaScript. Berjalan di XAMPP.
## Struktur Folder
admin/, customer/, auth/, config/ (database, auth, functions), assets/ (css, js, images), index.php, database.sql.
## Database
Tabel: users, customers, fields, schedules, bookings. UNIQUE: users.email, bookings.booking_code, schedules(field_id,schedule_date,start_time,end_time).
## Instalasi
1. Salin folder ke `C:\xampp_new\htdocs\ArenaBook`.
2. Start Apache & MySQL di XAMPP Control Panel.
3. Buka http://localhost/phpmyadmin > Import > pilih `database.sql` (membuat DB `arenabook`; hati-hati, file ini menjalankan DROP DATABASE).
4. Jika MySQL Anda memakai password/port lain, ubah `config/database.php`.
5. Jika nama folder bukan `ArenaBook`, ubah konstanta `BASE` di `config/functions.php`.
## Cara Menjalankan
http://localhost/ArenaBook/
## Akun Demo
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@arenabook.test | Admin123! |
| Customer | customer@arenabook.test | Customer123! |

Login lewat satu halaman yang sama: `http://localhost/ArenaBook/auth/login.php`. Setelah login, sistem mengarahkan berdasarkan role:
- Admin -> `admin/dashboard.php`
- Customer -> `customer/index.php`

Ganti password akun demo sebelum project dipakai di luar komputer lokal.

## Akun dan Hak Akses

ArenaBook memiliki dua role yang disimpan di kolom `users.role`: `admin` dan `customer`. Setiap role hanya dapat membuka halamannya sendiri. Jika customer membuka halaman admin, ia dialihkan ke dashboard customer. Jika admin membuka halaman customer, ia dialihkan ke dashboard admin. Pengguna yang belum login dialihkan ke halaman login.

### Akun Admin
Admin adalah pengelola sistem. Akun admin tidak bisa dibuat lewat form register, hanya lewat database (`database.sql` atau perintah SQL).

Admin dapat:
- Melihat dashboard: total customer, total lapangan, dan jumlah booking per status, serta 5 booking terbaru.
- Mengelola lapangan (menu Courts): menambah, mengubah, dan menghapus. Lapangan yang sudah pernah dibooking tidak dihapus, tetapi dinonaktifkan (`inactive`) agar riwayat booking aman. Lapangan `inactive` tidak tampil di customer.
- Mengelola jadwal (menu Schedules): melihat slot per tanggal dan lapangan, serta mengubah slot `available` menjadi `blocked` atau sebaliknya. Slot berstatus `booked` tidak dapat diubah dari menu ini.
- Mengelola booking (menu Bookings): melihat semua booking, memfilter berdasarkan tanggal, lapangan, status, dan nama customer, membuka detail booking, dan mengubah status.
  - `pending` -> `confirmed` atau `cancelled`
  - `confirmed` -> `completed` atau `cancelled`
  - Jika booking dibatalkan, slot jadwalnya kembali `available`.
- Melihat daftar customer (menu Customers) beserta jumlah bookingnya.

### Akun Customer
Customer adalah pengguna yang memesan lapangan. Akun dibuat sendiri lewat halaman Register (`auth/register.php`) dengan nama, email, password (minimal 6 karakter), konfirmasi password, nomor HP, dan alamat. Setiap customer memiliki satu baris di tabel `users` (role `customer`) dan satu baris di tabel `customers`.

Customer dapat:
- Melihat dashboard: statistik booking, booking mendatang terdekat, dan daftar lapangan aktif dengan harga per jam.
- Memesan lapangan (menu Book Court): memilih tanggal, memilih slot yang tersedia, mengisi catatan, lalu mengonfirmasi booking. Jadwal otomatis dibuat saat tanggal dibuka. Tanggal lampau, jam yang sudah lewat hari ini, serta slot `booked` atau `blocked` tidak bisa dipilih.
- Melihat booking miliknya sendiri (menu Booking Saya) dan membuka detailnya. Customer tidak bisa melihat booking customer lain.
- Membatalkan booking yang berstatus `pending` atau `confirmed` selama tanggal booking belum lewat. Booking `completed` atau `cancelled` tidak bisa dibatalkan. Setelah dibatalkan, slot kembali `available`.
- Mengubah profil (menu Profile): nama, nomor HP, dan alamat. Email tidak dapat diubah.

Harga booking selalu diambil dari database (`fields.price_per_hour`) saat booking dibuat, bukan dari input browser.

### Ringkasan Hak Akses
| Fitur | Admin | Customer |
|-------|:-----:|:--------:|
| Login dan logout | Ya | Ya |
| Register sendiri | Tidak | Ya |
| Membuat booking | Tidak | Ya |
| Melihat booking miliknya | - | Ya |
| Melihat semua booking | Ya | Tidak |
| Membatalkan booking | Ya (pending/confirmed) | Ya (milik sendiri, tanggal belum lewat) |
| Mengubah status booking | Ya | Tidak |
| Kelola lapangan | Ya | Tidak |
| Kelola jadwal (block/unblock) | Ya | Tidak |
| Melihat daftar customer | Ya | Tidak |
| Mengubah profil sendiri | Tidak ada halamannya | Ya |

### Menambah Admin Baru
Belum ada halaman untuk ini. Buat hash password dengan PHP, lalu masukkan lewat SQL.

1. Di Command Prompt: `C:\xampp_new\php\php.exe -r "echo password_hash('PasswordBaru123!', PASSWORD_DEFAULT);"`
2. Di phpMyAdmin, tab SQL: `INSERT INTO users(name,email,password,role) VALUES('Nama Admin','email@contoh.test','<hash dari langkah 1>','admin');`

Lupa password belum bisa direset dari aplikasi. Admin dapat membuat hash baru dengan cara yang sama lalu mengubahnya lewat `UPDATE users SET password='<hash>' WHERE email='...';`.

## Alur Booking
Customer pilih tanggal -> sistem membuat 15 slot (07:00-22:00) per lapangan aktif (INSERT IGNORE) -> pilih slot -> submit -> transaksi: kunci schedule (FOR UPDATE), validasi, insert booking (harga dari DB), schedule jadi booked, commit.
## Status Booking
pending -> confirmed -> completed; pending/confirmed -> cancelled (jadwal kembali available).
## Status Schedule
available, booked, blocked.
## Keamanan
PDO prepared statement, password_hash/password_verify, session + role check, htmlspecialchars, token CSRF pada semua form POST, harga/customer_id/field_id selalu diverifikasi server.
## Testing
Jalankan manual: register, login, lihat 6 lapangan, jam lewat disabled, booking, cek tabel bookings & schedules, booking slot sama oleh customer lain (harus ditolak), buka booking customer lain (ditolak), cancel, login admin, confirm/complete, block slot, cek query `SELECT * FROM ...`.
## Troubleshooting
- Apache tidak jalan: port 80 bentrok (Skype/IIS); ganti port di httpd.conf.
- MySQL tidak jalan: port 3306 bentrok atau proses mysqld lama; cek log di xampp/mysql/data.
- Database tidak ditemukan / PDO error / Access denied: import database.sql; cek user/password di config/database.php; aktifkan extension pdo_mysql di php.ini.
- 404: pastikan folder bernama ArenaBook di htdocs dan BASE sesuai.
- Session error: pastikan folder session PHP bisa ditulis dan tidak ada output sebelum header.
- Booking gagal / slot tidak muncul: cek tabel fields (status active) dan log error Apache (xampp/apache/logs/error.log).
- CSS/JS tidak muncul: cek BASE dan hard refresh (Ctrl+F5).
## Pengembangan Selanjutnya
Pembayaran online, notifikasi email/WhatsApp, laporan pendapatan, pagination, reset password.
