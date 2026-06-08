# BenKonek Product Notes

## Users

BenKonek ditujukan untuk musisi Indonesia dan band yang ingin saling menemukan. Contohnya gitaris yang mencari band, band yang kehilangan drummer sebelum gig, atau session player yang mencari proyek rekaman. Target pengguna terbiasa memakai WhatsApp dan butuh aplikasi yang cepat, langsung, dan mudah dipakai dari ponsel.

## Product Purpose

BenKonek adalah matchmaking board untuk skena musik Indonesia. Band memasang kebutuhan personel, musisi menampilkan instrumen dan profilnya, lalu kedua pihak terhubung langsung via WhatsApp. Aplikasi tidak memakai chat internal karena tujuan utamanya adalah mempercepat kontak nyata.

## Brand Personality

Tiga kata: analog, scene-built, direct.

Suara produk terasa seperti flyer di studio latihan: hangat, spesifik, dan tidak terlalu korporat. Informasi penting seperti instrumen, kota, genre, dan status lowongan harus selalu mudah ditemukan.

## Design Direction

1. Print, not pixels. Tampilan memakai nuansa kertas, tinta, garis tegas, katalog, dan stamp.
2. Classified ad sebagai unit utama. Lowongan diperlakukan seperti iklan kecil di majalah musik.
3. Loud where it counts. Aksen warna dipakai secukupnya untuk aksi dan status penting.
4. Specific beats slick. Konten lebih baik jelas dan konkret daripada terlalu marketing.
5. Direct connection. Semua flow mengarah ke aksi utama: membuat musisi dan band mulai ngobrol.

## Visual System

- Surface utama: paper/newsprint.
- Warna teks utama: ink gelap.
- Aksen utama: rust.
- Aksen sekunder: teal.
- Highlight: mustard.
- Aksi WhatsApp: hijau.
- Radius kecil, border tegas, dan shadow keras tanpa blur.

## Core Screens

- `index.php`: beranda, hero, statistik, dan lowongan terbaru.
- `pages/gigboard.php`: daftar lowongan, filter, pencarian, dan pagination.
- `pages/vacancy_detail.php`: detail lowongan dan aksi WhatsApp.
- `pages/musicians.php`: direktori musisi.
- `pages/login.php`: login multi user.
- `pages/register.php`: registrasi akun musisi atau band.
- `pages/dashboard.php`: dashboard sesuai role.
- `pages/vacancy_form.php`: CRUD lowongan band.
- `pages/users.php`: manajemen user untuk admin.
- `pages/rbac.php`: tabel RBAC dan matriks CRUD.

## Accessibility Notes

- Bahasa UI memakai Bahasa Indonesia.
- Warna teks utama harus kontras di atas latar kertas.
- Informasi penting tidak boleh hanya bergantung pada warna.
- Animasi punya fallback melalui `prefers-reduced-motion`.
- Link dan tombol harus tetap terbaca di mobile.
