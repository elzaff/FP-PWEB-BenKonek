# BenKonek

BenKonek adalah website untuk mempertemukan band dan musisi lewat lowongan. Band bisa memasang kebutuhan personel, musisi bisa mencari gig yang cocok, lalu mendaftar dengan data dari profilnya. Setelah musisi mendaftar, band dapat melihat detail pendaftar, mengubah status seleksi, dan mencetak daftar pendaftar.

## Deploy

```text
https://fp-pweb-benkonek.page.gd
```

## Anggota Kelompok

| Nama | NRP |
| --- | --- |
| Fazle Mawla Wahyuhanda | 5054241020 |
| Muhammad Hisyam Al Arby | 5054241006 |

## Akun Demo

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@benkonek.test` | `admin123` |
| Musician | `fazle.mawla.wahyuhanda@student.benkonek.test` | `password123` |
| Band | `modjoermakmoer@band.benkonek.test` | `password123` |

## Alur Utama Website

1. Pengunjung membuka Beranda atau GigBoard untuk melihat lowongan.
2. User registrasi sebagai musisi atau band.
3. Musisi melengkapi profil berisi instrumen, level, kota, bio, portofolio, kontak, dan foto.
4. Band melengkapi profil band lalu membuat lowongan personel.
5. Musisi membuka detail lowongan dan mendaftar.
6. Data profil musisi tersimpan sebagai pendaftaran ke band terkait.
7. Band membuka halaman Pendaftaran untuk melihat daftar pendaftar.
8. Band bisa menerima, menolak, atau mencetak daftar pendaftar.

Catatan: BenKonek tidak memakai direktori musisi publik. Detail musisi hanya terlihat oleh band setelah musisi tersebut mendaftar ke lowongan band terkait.

## Halaman Website

| Halaman | File | Fungsi |
| --- | --- | --- |
| Beranda | `index.php` | Menampilkan identitas BenKonek, statistik singkat, CTA, dan lowongan terbaru. |
| Register | `pages/register.php` | Membuat akun sebagai musisi atau band. |
| Login | `pages/login.php` | Masuk ke dashboard sesuai role akun. |
| Logout | `pages/logout.php` | Mengakhiri session user. |
| Dashboard | `pages/dashboard.php` | Musisi mengelola profil, band mengelola profil, admin melihat statistik. |
| GigBoard | `pages/gigboard.php` | Menampilkan daftar lowongan dengan filter, search, dan pagination. |
| Detail Lowongan | `pages/vacancy_detail.php` | Menampilkan detail lowongan dan form daftar untuk musisi. |
| Form Lowongan | `pages/vacancy_form.php` | Band membuat, mengubah, membuka, menutup, dan menghapus lowongan. |
| Pendaftaran | `pages/connections.php` | Musisi melihat lamaran sendiri, band melihat daftar pendaftar. |
| Proses Pendaftaran | `pages/connect.php` | Handler submit lamaran dan update status pendaftaran. |
| Cetak Pendaftar | `pages/struk.php` | Band mencetak daftar besar pendaftar lowongannya. |
| Manajemen User | `pages/users.php` | Admin mengelola akun user. |
| RBAC | `pages/rbac.php` | Admin melihat dokumentasi role, permission, dan matriks akses. |
| Musisi | `pages/musicians.php` | Redirect ke GigBoard karena tidak ada direktori musisi publik. |

## Frontend

Frontend dibuat dengan HTML, CSS, Bootstrap 5, Font Awesome, dan JavaScript vanilla. Tampilan memakai gaya poster musik dan katalog gig dengan warna kertas, hitam, merah rust, hijau, dan mustard.

Bagian frontend utama:

- Hero section dengan visual vinyl.
- Card lowongan di GigBoard.
- Form login, register, profil, dan lowongan.
- Tabel pendaftaran untuk musisi dan band.
- Tampilan print daftar pendaftar untuk band.
- Interaksi ringan seperti search, filter, konfirmasi aksi, reveal animation, dan state tombol form.

## Backend

Backend dibuat dengan PHP native dan MySQLi. Setiap halaman PHP langsung menangani kebutuhan request masing-masing, dengan helper bersama untuk koneksi database dan session.

Backend menangani:

- Registrasi dan login user.
- Session login dan pembagian role `musician`, `band`, dan `admin`.
- Penyimpanan profil musisi dan profil band.
- CRUD lowongan oleh band.
- Submit lamaran musisi ke lowongan.
- Daftar pendaftar untuk band.
- Update status pendaftaran: `Pending`, `Accepted`, atau `Rejected`.
- Print daftar pendaftar dalam format halaman struk besar.

## Database

Database memakai MySQL/MariaDB. Struktur utama ada di `database/setup.sql`, sedangkan data demo ada di `database/dummy_data.sql`.

### Tabel Utama

| Tabel | Fungsi |
| --- | --- |
| `users` | Data akun login semua role. |
| `musicians` | Detail profil musisi. |
| `bands` | Detail profil band. |
| `vacancies` | Lowongan personel yang dibuat band. |
| `connections` | Data pendaftaran musisi ke lowongan band. |

### Detail Tabel `users`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | INT | Primary key akun. |
| `full_name` | VARCHAR(150) | Nama user. |
| `email` | VARCHAR(100) | Email login, dibuat unique. |
| `password_hash` | VARCHAR(255) | Password yang sudah di-hash. |
| `role` | ENUM | Role: `musician`, `band`, atau `admin`. |
| `is_active` | BOOLEAN | Status aktif akun. |
| `created_at` | TIMESTAMP | Waktu akun dibuat. |

Relasi:

- Satu user role musisi punya satu profil di `musicians`.
- Satu user role band punya satu profil di `bands`.

### Detail Tabel `musicians`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | INT | Primary key profil musisi. |
| `user_id` | INT | Relasi ke `users.id`, unique. |
| `full_name` | VARCHAR(150) | Nama musisi. |
| `bio` | TEXT | Deskripsi singkat musisi. |
| `location_city` | VARCHAR(100) | Kota domisili. |
| `primary_instrument` | VARCHAR(100) | Instrumen utama. |
| `experience_level` | ENUM | `Beginner`, `Intermediate`, `Advanced`, atau `Professional`. |
| `portfolio_url` | VARCHAR(255) | Link portofolio. |
| `whatsapp_number` | VARCHAR(20) | Nomor kontak. |
| `photo_profile` | VARCHAR(255) | Nama file foto profil. |

Relasi:

- `musicians.user_id` mengarah ke `users.id`.
- Jika user dihapus, profil musisi ikut terhapus.
- Data musisi masuk ke band lewat tabel `connections` setelah musisi mendaftar lowongan.

### Detail Tabel `bands`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | INT | Primary key profil band. |
| `user_id` | INT | Relasi ke `users.id`, unique. |
| `band_name` | VARCHAR(150) | Nama band. |
| `formation_year` | INT | Tahun terbentuk. |
| `main_genre` | VARCHAR(100) | Genre utama. |
| `basecamp_location` | VARCHAR(150) | Kota atau lokasi basecamp. |
| `bio` | TEXT | Deskripsi band. |
| `whatsapp_number` | VARCHAR(20) | Nomor kontak band. |
| `photo_profile` | VARCHAR(255) | Nama file foto profil band. |

Relasi:

- `bands.user_id` mengarah ke `users.id`.
- Jika user dihapus, profil band ikut terhapus.
- Satu band bisa punya banyak lowongan di `vacancies`.
- Satu band bisa punya banyak pendaftaran di `connections`.

### Detail Tabel `vacancies`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | INT | Primary key lowongan. |
| `band_id` | INT | Relasi ke `bands.id`. |
| `title` | VARCHAR(200) | Judul lowongan. |
| `description` | TEXT | Deskripsi kebutuhan band. |
| `needed_instrument` | VARCHAR(100) | Instrumen yang dicari. |
| `project_type` | ENUM | `Permanent`, `Session`, `Recording`, atau `Gig`. |
| `status` | ENUM | `Open` atau `Closed`. |
| `created_at` | TIMESTAMP | Waktu lowongan dibuat. |

Relasi:

- `vacancies.band_id` mengarah ke `bands.id`.
- Jika band dihapus, lowongannya ikut terhapus.
- Satu lowongan bisa punya banyak pendaftaran di `connections`.

### Detail Tabel `connections`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | INT | Primary key pendaftaran. |
| `musician_id` | INT | Relasi ke `musicians.id`. |
| `band_id` | INT | Relasi ke `bands.id`. |
| `vacancy_id` | INT | Relasi ke `vacancies.id`. |
| `message` | VARCHAR(500) | Pesan opsional dari musisi saat mendaftar. |
| `status` | ENUM | `Pending`, `Accepted`, atau `Rejected`. |
| `created_at` | TIMESTAMP | Waktu pendaftaran dibuat. |

Aturan penting:

- Kombinasi `musician_id` dan `vacancy_id` dibuat unique lewat `uniq_apply`.
- Satu musisi tidak bisa mendaftar ke lowongan yang sama dua kali.
- Band hanya melihat pendaftaran yang masuk ke lowongan miliknya.
- Fitur print mengambil data dari tabel ini dan menggabungkannya dengan data `musicians`, `bands`, dan `vacancies`.

### Tabel RBAC

| Tabel | Fungsi |
| --- | --- |
| `app_modules` | Daftar modul aplikasi, seperti auth, dashboard, lowongan, dan pendaftaran. |
| `roles` | Daftar role: `guest`, `musician`, `band`, dan `admin`. |
| `permissions` | Daftar permission per modul. |
| `role_permissions` | Relasi many-to-many antara role dan permission. |
| `crud_security_matrix` | Matriks akses create, read, update, delete per role dan modul. |

Tabel RBAC dipakai untuk dokumentasi akses di halaman `pages/rbac.php`.

### Relasi Database Singkat

```text
users 1--1 musicians
users 1--1 bands
bands 1--N vacancies
musicians 1--N connections
bands 1--N connections
vacancies 1--N connections
roles N--N permissions melalui role_permissions
```

## Struktur Kode

```text
benkonek/
|-- index.php
|   |-- Halaman beranda: hero, statistik singkat, CTA, dan lowongan terbaru.
|
|-- README.md
|   |-- Dokumentasi singkat project.
|
|-- config/
|   |-- database.php
|   |   |-- Koneksi MySQLi ke database.
|   |   |-- Dipakai oleh halaman yang butuh query database.
|   |
|   |-- session.php
|       |-- Helper session.
|       |-- Mengecek user login atau belum.
|       |-- Membatasi halaman berdasarkan role.
|       |-- Menyediakan CSRF token.
|       |-- Mengambil data user yang sedang login.
|
|-- includes/
|   |-- header.php
|   |   |-- Template bagian atas halaman.
|   |   |-- Berisi meta tag, CDN CSS, navbar, dan flash message.
|   |
|   |-- footer.php
|       |-- Template bagian bawah halaman.
|       |-- Berisi footer, CDN script, dan pemanggilan script utama.
|
|-- pages/
|   |-- login.php
|   |   |-- Form login, verifikasi akun, dan pengisian session.
|   |
|   |-- register.php
|   |   |-- Form registrasi musisi atau band.
|   |
|   |-- logout.php
|   |   |-- Menghapus session user.
|   |
|   |-- dashboard.php
|   |   |-- Dashboard role musisi, band, dan admin.
|   |
|   |-- gigboard.php
|   |   |-- Daftar lowongan, filter, search, dan pagination.
|   |
|   |-- vacancy_detail.php
|   |   |-- Detail lowongan dan form daftar.
|   |
|   |-- vacancy_form.php
|   |   |-- CRUD lowongan milik band.
|   |
|   |-- connect.php
|   |   |-- Handler submit lamaran dan update status.
|   |
|   |-- connections.php
|   |   |-- Daftar lamaran musisi atau pendaftar band.
|   |
|   |-- struk.php
|   |   |-- Print daftar pendaftar untuk band.
|   |
|   |-- users.php
|   |   |-- Manajemen user oleh admin.
|   |
|   |-- rbac.php
|   |   |-- Dokumentasi role, permission, dan matriks akses.
|   |
|   |-- musicians.php
|       |-- Redirect ke GigBoard.
|
|-- assets/
|   |-- css/
|   |   |-- style.css
|   |       |-- Styling utama website.
|   |
|   |-- js/
|   |   |-- main.js
|   |       |-- Interaksi frontend.
|   |
|   |-- images/
|       |-- vinyl.webp
|       |   |-- Gambar vinyl hero section.
|       |
|       |-- uploads/
|           |-- Folder foto profil upload.
|
|-- database/
|   |-- setup.sql
|   |   |-- Schema tabel, RBAC, permission, dan admin awal.
|   |
|   |-- dummy_data.sql
|       |-- Data demo.
|
|-- docs/
    |-- Dokumen Spesifikasi_BenKonek.pdf
        |-- Dokumen spesifikasi project.
```

## Data Dummy

File `database/dummy_data.sql` berisi data contoh untuk mencoba aplikasi:

- 47 akun musisi.
- 10 akun band.
- 15 lowongan.
- 7 data pendaftaran lowongan.

Data dummy dipakai agar alur website langsung bisa dicoba dari sisi musisi dan band.
