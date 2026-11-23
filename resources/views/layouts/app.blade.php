<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') · CineRate</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-inner">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                <span class="brand-mark"><i class="fa-solid fa-clapperboard"></i></span>
                <span class="brand-name">CineRate</span>
                <span class="brand-tag">Admin</span>
            </a>

            <div class="nav-links">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-chart-simple"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('admin.movies.index') }}" class="nav-link {{ request()->routeIs('admin.movies.index', 'admin.movies.edit') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-film"></i><span>Movies</span>
                </a>
                <a href="{{ route('admin.movies.create') }}" class="nav-link nav-cta {{ request()->routeIs('admin.movies.create') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-plus"></i><span>Add Movie</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="container main">
        @if(session('success'))
            <div class="toast" role="status">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <div class="container footer-inner">
            <span><i class="fa-solid fa-clapperboard"></i> CineRate</span>
            <span>Movie Rating Platform · Admin Panel · {{ date('Y') }}</span>
        </div>
    </footer>

    {{-- Shared delete confirmation dialog, used by any form with data-confirm --}}
    <dialog id="confirm-dialog" class="modal">
        <form method="dialog" class="modal-box">
            <div class="modal-icon"><i class="fa-solid fa-trash-can"></i></div>
            <h3 class="modal-title">Delete movie?</h3>
            <p class="modal-text" id="confirm-message"></p>
            <div class="modal-actions">
                <button type="submit" value="cancel" class="btn btn-ghost">Cancel</button>
                <button type="submit" value="confirm" class="btn btn-danger">Yes, delete</button>
            </div>
        </form>
    </dialog>

    <script>
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!form.dataset.confirm) {
                return;
            }
            event.preventDefault();

            const dialog = document.getElementById('confirm-dialog');
            if (!dialog || typeof dialog.showModal !== 'function') {
                if (window.confirm(form.dataset.confirm)) {
                    form.submit();
                }
                return;
            }

            document.getElementById('confirm-message').textContent = form.dataset.confirm;
            dialog.returnValue = '';
            dialog.showModal();
            dialog.addEventListener('close', function () {
                if (dialog.returnValue === 'confirm') {
                    form.submit();
                }
            }, { once: true });
        });
    </script>

    @stack('scripts')
</body>
</html>
