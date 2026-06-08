/* BenKonek — vanilla JS. Palette matches v2 retro/newsprint design system. */

var BK = {
    paper: '#efe7d3',
    ink:   '#211c17',
    rust:  '#cf3f17',
    muted: '#6b6151'
};

/* Open WhatsApp with a prefilled message. Called from listing/contact buttons. */
function hubungiWhatsApp(nomor, namaTarget, judul) {
    if (!nomor) {
        Swal.fire({
            icon: 'warning', title: 'Nomor WhatsApp tidak tersedia',
            toast: true, position: 'top-end',
            showConfirmButton: false, timer: 2500,
            background: BK.paper, color: BK.ink, iconColor: BK.rust
        });
        return;
    }
    var cleaned = nomor.replace(/\D/g, '').replace(/^0/, '62');
    var pesan = encodeURIComponent('Halo ' + namaTarget + '! Saya tertarik dengan "' + judul + '" di BenKonek.');
    window.open('https://wa.me/' + cleaned + '?text=' + pesan, '_blank');
}

/* Confirm-then-delete dialog (GET redirect, kept for backward compat). */
function konfirmasiHapus(url, pesan) {
    pesan = pesan || 'Data ini akan dihapus permanen!';
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: pesan,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: BK.rust,
        cancelButtonColor: BK.muted,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: BK.paper,
        color: BK.ink,
        iconColor: BK.rust
    }).then(function (result) {
        if (result.isConfirmed) { window.location.href = url; }
    });
}

/* Confirm-then-submit a POST form (CSRF-safe delete). */
function konfirmasiHapusForm(formId, pesan) {
    pesan = pesan || 'Data ini akan dihapus permanen!';
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: pesan,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: BK.rust,
        cancelButtonColor: BK.muted,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        background: BK.paper,
        color: BK.ink,
        iconColor: BK.rust
    }).then(function (result) {
        if (result.isConfirmed) {
            var form = document.getElementById(formId);
            if (form) form.submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Marquee: duplicate the track once so the -50% loop is seamless. */
    if (!reduce) {
        document.querySelectorAll('.ticker__track').forEach(function (track) {
            if (track.dataset.cloned) return;
            track.dataset.cloned = '1';
            track.innerHTML = track.innerHTML + track.innerHTML;
        });
    }

    /* Prevent double-submit on all POST forms. */
    document.querySelectorAll('form[method="POST"]').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (form.dataset.submitted) return;
            form.dataset.submitted = '1';
            var btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
            }
        });
    });

    /* Reveal-on-scroll: content is visible by default (CSS), this only enhances. */
    var reveal = document.querySelectorAll('[data-reveal]');
    if (reveal.length) {
        if (reduce || !('IntersectionObserver' in window)) {
            reveal.forEach(function (el) { el.classList.add('is-in'); });
        } else {
            var io = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) {
                        var el = e.target;
                        var delay = el.dataset.reveal ? parseInt(el.dataset.reveal, 10) || 0 : 0;
                        setTimeout(function () { el.classList.add('is-in'); }, delay);
                        obs.unobserve(el);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
            reveal.forEach(function (el) { io.observe(el); });
        }
    }
});
