# BenKonek

BenKonek adalah aplikasi web untuk mempertemukan musisi dan band lewat alur pendaftaran lowongan. Band dapat membuat profil, memasang lowongan personel, mengecek daftar musisi yang mendaftar, menerima atau menolak kandidat, dan mencetak daftar pendaftar. Musisi dapat membuat profil, mencari lowongan di GigBoard, lalu mendaftar ke lowongan yang dipilih dengan data profilnya.

Tagline dari dokumen spesifikasi: **Calling All Musicians, Satu Klik Menuju Band Impianmu**.

## Anggota Kelompok

| Nama | NRP |
| --- | --- |
| Fazle Mawla Wahyuhanda | 5054241020 |
| Muhammad Hisyam Al Arby | 5054241006 |

## Ringkasan

BenKonek dibuat untuk menjawab masalah konektivitas di skena musik lokal. Pencarian personel band lewat obrolan, grup media sosial, atau rekomendasi teman sering sulit difilter berdasarkan instrumen, lokasi, dan kebutuhan proyek. Aplikasi ini mengubah proses tersebut menjadi papan lowongan dan pendaftaran kandidat yang lebih rapi.

Spesifikasi awal di `docs/Spesifikasi_BenKonek.pdf` menggambarkan aplikasi matchmaking profesional dengan profil portofolio, Gig Board, filter pencarian, dan pertemuan antar pengguna. Implementasi repository ini memakai PHP native, MySQLi, Bootstrap 5, CSS3, dan JavaScript vanilla agar sesuai konteks tugas PWEB. Alur yang dipakai sekarang adalah pendaftaran lowongan, bukan chat internal.

## Fitur Utama

- Login multi user dengan password hash dan session PHP.
- Role akses: `guest`, `musician`, `band`, dan `admin`.
- Registrasi akun musisi atau band.
- Dashboard berbeda sesuai role yang login.
- Profil musisi berisi nama, kota, instrumen utama, level pengalaman, portofolio, WhatsApp, dan foto.
- Profil band berisi nama band, tahun terbentuk, genre, basecamp, WhatsApp, dan foto.
- GigBoard dengan daftar lowongan, filter instrumen, filter kota, realtime search, detail lowongan, tombol daftar, dan pagination.
- Profil musisi bersifat privat: hanya musisi pemilik yang mengelola profilnya, dan band hanya melihat detail musisi yang sudah mendaftar ke lowongan band tersebut.
- CRUD lowongan untuk role band.
- Pendaftaran lowongan: musisi mengirim lamaran, band melihat daftar pendaftar, menerima/menolak kandidat, dan mencetak daftar besar berisi semua pendaftar.
- Admin dashboard untuk statistik sistem.
- Manajemen user oleh admin: create, read, update, delete, reset password, ubah role, dan ubah status aktif.
- Tabel RBAC dan matriks keamanan CRUD untuk tugas PWEB bagian 4.

## Role dan Hak Akses

| Role | Hak akses utama |
| --- | --- |
| Guest | Melihat Beranda, GigBoard, dan Detail Lowongan. |
| Musician | Login, mengelola profil musisi sendiri, membaca lowongan, mendaftar lowongan, dan melihat status lamaran sendiri. |
| Band | Login, mengelola profil band sendiri, membuat/membaca/mengubah/menutup/membuka/menghapus lowongan milik sendiri, melihat pendaftar, menerima/menolak kandidat, dan mencetak daftar pendaftar. |
| Admin | Mengelola user, melihat statistik, membaca tabel RBAC, dan membaca matriks keamanan CRUD. |

Halaman dokumentasi RBAC tersedia di `pages/rbac.php` setelah login sebagai admin.

## Teknologi

- PHP 8.x native
- MySQL atau MariaDB
- MySQLi
- HTML5, CSS3, JavaScript vanilla
- Bootstrap 5
- Font Awesome
- SweetAlert2

## Struktur Folder

```text
benkonek/
  assets/
    css/style.css
    images/
    js/main.js
  config/
    database.example.php
    database.php
    session.php
  database/
    setup.sql
    dummy_data.sql
  docs/
    PRODUCT.md
    Spesifikasi_BenKonek.pdf
  includes/
    footer.php
    header.php
  pages/
    connect.php
    connections.php
    dashboard.php
    gigboard.php
    login.php
    logout.php
    rbac.php
    register.php
    struk.php
    users.php
    vacancy_detail.php
    vacancy_form.php
  index.php
  README.md
```

Catatan: `config/database.php` dipakai untuk konfigurasi lokal dan diabaikan oleh Git. Gunakan `config/database.example.php` sebagai template.

## Instalasi Lokal

1. Clone repository ke folder web server, misalnya `c:\laragon\www\benkonek`.

2. Salin konfigurasi database:

```powershell
Copy-Item config\database.example.php config\database.php
```

3. Sesuaikan host, user, password, dan nama database di `config/database.php`.

4. Import struktur database:

```powershell
Get-Content -Raw database\setup.sql | C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root -proot
```

5. Import data dummy:

```powershell
Get-Content -Raw database\dummy_data.sql | C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root -proot
```

6. Jalankan lewat Laragon atau PHP built-in server:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -S 127.0.0.1:8000 -t .
```

7. Buka aplikasi:

```text
http://127.0.0.1:8000
```

## Akun Demo

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@benkonek.test` | `admin123` |
| Musician | `fazle.mawla.wahyuhanda@student.benkonek.test` | `password123` |
| Band | `modjoermakmoer@band.benkonek.test` | `password123` |

File `database/dummy_data.sql` berisi 47 akun musisi dari daftar nama tugas, 10 akun band dummy, 15 lowongan, dan contoh data pendaftaran lowongan.

## Database

Tabel utama:

- `users`
- `musicians`
- `bands`
- `vacancies`
- `connections`

Tabel RBAC:

- `app_modules`
- `roles`
- `permissions`
- `role_permissions`
- `crud_security_matrix`

Struktur database mengikuti inti spesifikasi BenKonek: user, profil musisi, profil band, lowongan, dan pendaftaran lowongan. Implementasi tambahan RBAC dipakai untuk pembagian akses dan dokumentasi tugas PWEB.

## Keamanan

- Password disimpan menggunakan `password_hash()`.
- Login memverifikasi password memakai `password_verify()`.
- Session ID diregenerasi setelah login berhasil.
- Query database menggunakan prepared statement MySQLi.
- Form aksi penting memakai CSRF token.
- Role guard tersedia melalui helper session, seperti `requireLogin()`, `requireRole()`, dan `requireAdmin()`.
- Output HTML memakai `htmlspecialchars()` untuk mengurangi risiko XSS.

## Modul dari Spesifikasi

Modul yang sudah diimplementasikan:

- Autentikasi: registrasi, login, logout.
- Profil Musisi: data personal, instrumen, pengalaman, portofolio, kontak WhatsApp; detailnya tidak dibuka sebagai direktori publik.
- Profil Band: data band, genre, lokasi basecamp, kontak WhatsApp.
- GigBoard: pembuatan lowongan, pencarian, filter, detail, status open/closed.
- Pendaftaran Lowongan: musisi mendaftar lowongan, band melihat pendaftar, menerima/menolak kandidat, dan mencetak daftar pendaftar.
- Admin: statistik sistem, manajemen user, RBAC, dan matriks CRUD.

Rencana pengembangan lanjutan dari dokumen spesifikasi:

- Notifikasi real-time untuk lowongan baru.
- Review dan rating setelah kolaborasi atau manggung.
- Peta interaktif berdasarkan lokasi.
- Verifikasi identitas untuk studio, band resmi, atau musisi profesional.

## Pengujian Cepat

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l index.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l pages\login.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l pages\connect.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l pages\connections.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l pages\struk.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l pages\users.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l pages\rbac.php
```
