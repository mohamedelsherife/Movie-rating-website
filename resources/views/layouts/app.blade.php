<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') · CineRate</title>
    <meta name="description" content="CineRate admin: manage the movies and TV series in the catalogue.">
    <meta name="theme-color" content="#09090d">
    <meta name="robots" content="noindex">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/admin.js') }}" defer></script>
</head>
<body class="@yield('body-class')">
    <a href="#main" class="skip-link">Skip to content</a>

    {{-- Navigation and footer are only shown to a logged-in admin (not on the login page). --}}
    @auth('admin')
    <nav class="navbar" aria-label="Main">
        <div class="container navbar-inner">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                <span class="brand-mark" aria-hidden="true"><i class="fa-solid fa-clapperboard"></i></span>
                <span class="brand-name">CineRate</span>
                <span class="brand-tag">Admin</span>
            </a>

            <div class="nav-links">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <i class="fa-solid fa-chart-simple" aria-hidden="true"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('admin.movies.index') }}" class="nav-link {{ request()->routeIs('admin.movies.index', 'admin.movies.edit') ? 'is-active' : '' }}" @if (request()->routeIs('admin.movies.index', 'admin.movies.edit')) aria-current="page" @endif>
                    <i class="fa-solid fa-film" aria-hidden="true"></i><span>Movies</span>
                </a>
                <a href="{{ route('admin.series.index') }}" class="nav-link {{ request()->routeIs('admin.series.*') ? 'is-active' : '' }}" @if (request()->routeIs('admin.series.*')) aria-current="page" @endif>
                    <i class="fa-solid fa-tv" aria-hidden="true"></i><span>TV Series</span>
                </a>
                <a href="{{ route('admin.movies.create') }}" class="nav-link nav-cta {{ request()->routeIs('admin.movies.create') ? 'is-active' : '' }}" @if (request()->routeIs('admin.movies.create')) aria-current="page" @endif>
                    <i class="fa-solid fa-plus" aria-hidden="true"></i><span>Add Movie</span>
                </a>

                <form action="{{ route('admin.logout') }}" method="POST" class="nav-logout">
                    @csrf
                    <button type="submit" class="nav-link" title="Log out {{ auth('admin')->user()->name }}">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Log out</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>
    @endauth

    <main class="container main" id="main" tabindex="-1">
        @yield('content')
    </main>

    @auth('admin')
    <footer class="footer">
        <div class="container footer-inner">
            <span><i class="fa-solid fa-clapperboard"></i> CineRate</span>
            <span>Movie Rating Platform · Admin Panel · {{ date('Y') }}</span>
        </div>
    </footer>
    @endauth

    {{-- Flash notifications --}}
    <div class="toast-stack" aria-live="polite">
        @if (session('success'))
            <x-alert type="success" :timeout="5000" class="toast">{{ session('success') }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert type="error" :timeout="8000" class="toast">{{ session('error') }}</x-alert>
        @endif
    </div>

    {{-- Shared confirmation dialog: used by every form that has data-confirm --}}
    <x-modal id="confirm-dialog" icon="trash-can" title="Are you sure?">
        <p class="modal-text" data-confirm-message></p>
        <x-slot:actions>
            <x-button variant="ghost" value="cancel">Cancel</x-button>
            <x-button variant="danger" value="confirm" icon="trash-can">Yes, delete</x-button>
        </x-slot:actions>
    </x-modal>
</body>
</html>
