<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Travel Tour')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Unbounded:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/travel-system.css') }}">
    @stack('styles')
</head>
<body>
    @yield('content')

    <script>
        // Modal helper global (dipakai untuk Konfirmasi Hapus, Bukti Pembayaran, Penolakan, dll.)
        document.addEventListener('click', function (e) {
            const openBtn = e.target.closest('[data-modal-open]');
            if (openBtn) {
                const modal = document.getElementById(openBtn.dataset.modalOpen);
                if (modal) modal.hidden = false;
                return;
            }
            const closeBtn = e.target.closest('[data-modal-close]');
            if (closeBtn) {
                const modal = document.getElementById(closeBtn.dataset.modalClose);
                if (modal) modal.hidden = true;
                return;
            }
            if (e.target.classList && e.target.classList.contains('tt-modal')) {
                e.target.hidden = true;
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.tt-modal:not([hidden])').forEach(m => m.hidden = true);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>