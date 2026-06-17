# BenKonek

BenKonek adalah website untuk mempertemukan band dan musisi lewat lowongan. Band bisa memasang kebutuhan personel, musisi bisa mencari gig yang cocok, lalu mendaftar dengan data dari profilnya. Setelah musisi mendaftar, band dapat melihat detail pendaftar, mengubah status seleksi, dan mencetak daftar pendaftar.

Website deploy:

```text
https://fp-pweb-benkonek.page.gd
```

## Anggota Kelompok

| Nama | NRP |
| --- | --- |
| Fazle Mawla Wahyuhanda | 5054241020 |
| Muhammad Hisyam Al Arby | 5054241006 |

## Gambaran Frontend

Tampilan BenKonek memakai gaya poster musik dan katalog gig. Warna utama menggunakan nuansa kertas, hitam, merah rust, hijau, dan mustard. Komponen visualnya dibuat dengan HTML, CSS, Bootstrap 5, Font Awesome, dan JavaScript vanilla.

Frontend berisi:

- Hero section di beranda dengan visual vinyl.
- Card lowongan di GigBoard.
- Form login, register, profil musisi, profil band, dan form lowongan.
- Tabel pendaftaran untuk musisi dan band.
- Halaman cetak daftar pendaftar untuk band.
- Interaksi ringan seperti search, filter, konfirmasi aksi, dan tombol print.

## Halaman Website

| Halaman | Fungsi |
| --- | --- |
| Beranda | Menampilkan identitas BenKonek, ringkasan statistik, dan lowongan terbaru. |
| Register | Membuat akun sebagai musisi atau band. |
| Login | Masuk ke dashboard sesuai role akun. |
| Dashboard | Mengelola profil musisi, profil band, atau data admin. |
| GigBoard | Menampilkan daftar lowongan band, lengkap dengan filter dan pencarian. |
| Detail Lowongan | Menampilkan detail lowongan dan form daftar untuk musisi. |
| Pendaftaran | Musisi melihat lamaran yang dikirim, band melihat daftar pendaftar lowongannya. |
| Cetak Pendaftar | Band mencetak daftar besar berisi data musisi yang mendaftar. |
| Manajemen User | Admin mengelola akun pengguna. |
| RBAC | Admin melihat dokumentasi role dan hak akses sistem. |

Catatan: BenKonek tidak memakai direktori musisi publik. Detail musisi hanya terlihat oleh band setelah musisi tersebut mendaftar ke lowongan band terkait.

## Gambaran Backend

Backend dibuat dengan PHP native dan MySQLi. Struktur backend masih sederhana agar mudah dibaca sebagai project Pemrograman Web.

Backend menangani:

- Registrasi dan login user.
- Session dan pembagian role `musician`, `band`, dan `admin`.
- Penyimpanan profil musisi dan profil band.
- CRUD lowongan oleh band.
- Proses daftar lowongan oleh musisi.
- Daftar pendaftar untuk band.
- Update status pendaftaran: `Pending`, `Accepted`, atau `Rejected`.
- Print daftar pendaftar dalam format halaman struk besar.

## Gambaran Database

Database memakai MySQL/MariaDB. Tabel intinya:

| Tabel | Fungsi |
| --- | --- |
| `users` | Menyimpan akun, email, password hash, role, dan status aktif. |
| `musicians` | Menyimpan detail profil musisi. |
| `bands` | Menyimpan detail profil band. |
| `vacancies` | Menyimpan lowongan yang dibuat oleh band. |
| `connections` | Menyimpan pendaftaran musisi ke lowongan band. |

Selain itu ada tabel dokumentasi akses:

- `app_modules`
- `roles`
- `permissions`
- `role_permissions`
- `crud_security_matrix`

## Struktur Folder

```text
benkonek/
  assets/
    css/
    images/
    js/
  config/
  database/
    setup.sql
    dummy_data.sql
  docs/
  includes/
  pages/
  index.php
  README.md
```

## Akun Demo

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@benkonek.test` | `admin123` |
| Musician | `fazle.mawla.wahyuhanda@student.benkonek.test` | `password123` |
| Band | `modjoermakmoer@band.benkonek.test` | `password123` |

## Data Dummy

File `database/dummy_data.sql` berisi data contoh untuk mencoba aplikasi:

- 47 akun musisi.
- 10 akun band.
- 15 lowongan.
- 7 data pendaftaran lowongan.

Data dummy dipakai agar alur website langsung bisa dicoba dari sisi musisi dan band.
