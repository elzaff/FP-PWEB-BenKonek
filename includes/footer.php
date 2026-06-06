</div><!-- /.container -->

<footer class="mt-5 py-4" style="background-color:#0A0A0A;border-top:1px solid #2A2A2A;">
    <div class="container text-center">
        <p class="mb-0 text-muted">
            <i class="fas fa-music me-2" style="color:#FFC107;"></i>
            <strong style="color:#FFC107;">BenKonek</strong> &copy; <?= date('Y') ?> &ndash; Platform Matchmaking Musisi
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
        background: '#1E1E1E',
        color: '#E0E0E0',
    });
});
</script>
<?php endif; ?>
</body>
</html>
