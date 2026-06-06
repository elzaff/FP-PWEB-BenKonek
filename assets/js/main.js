function hubungiWhatsApp(nomor, namaTarget, judul) {
    if (!nomor) {
        Swal.fire({
            icon: 'warning', title: 'Nomor WhatsApp tidak tersedia',
            toast: true, position: 'top-end',
            showConfirmButton: false, timer: 2500,
            background: '#1E1E1E', color: '#E0E0E0',
        });
        return;
    }
    var cleaned = nomor.replace(/\D/g, '').replace(/^0/, '62');
    var pesan = encodeURIComponent('Halo ' + namaTarget + '! Saya tertarik dengan "' + judul + '" di BenKonek.');
    window.open('https://wa.me/' + cleaned + '?text=' + pesan, '_blank');
}

function konfirmasiHapus(url, pesan) {
    pesan = pesan || 'Data ini akan dihapus permanen!';
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: pesan,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#FFC107',
        cancelButtonColor: '#444',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: '#1E1E1E',
        color: '#E0E0E0',
    }).then(function (result) {
        if (result.isConfirmed) { window.location.href = url; }
    });
}
