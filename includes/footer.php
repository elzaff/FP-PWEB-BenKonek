</div><!-- /.container -->

<footer class="mt-5 py-5">
    <div class="container text-center">
        <p class="mb-1" style="font-family:var(--font-mono);letter-spacing:.18em;text-transform:uppercase;font-size:.72rem;">
            <i class="fas fa-record-vinyl me-2"></i>Side B
        </p>
        <p class="mb-0 text-muted">
            <strong>BenKonek</strong> &copy; <?= date('Y') ?> &ndash; tempat ketemu musisi &amp; band Indonesia
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/assets/js/main.js"></script>

<?php if (!empty($flashMessage)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: <?= json_encode($flashType) ?>,
        title: <?= json_encode($flashMessage) ?>,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        background: '#efe7d3',
        color: '#211c17',
        iconColor: '#cf3f17',
    });
});
</script>
<?php endif; ?>
</body>
</html>
