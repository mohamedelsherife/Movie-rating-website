<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') · CineRate</title>
    <meta name="description" content="CineRate admin: manage the movies and TV series in the catalogue.">
    <meta name="theme-color" content="#09090d">
    <meta name="robots" content="noindex">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script>document.documentElement.classList.add('js');</script>
    <script src="{{ asset('js/admin.js') }}" defer></script>
</head>
<body class="@yield('body-class')">
    <a href="#main" class="skip-link">Skip to content</a>

    {{-- Navigation and footer are only shown to a logged-in admin (not on the login page). --}}
    @auth('admin')
    @php
        // One list of sections, used by both the navbar and the footer.
        $navItems = [
            ['label' => 'Dashboard', 'icon' => 'chart-simple', 'route' => 'admin.dashboard',     'active' => 'admin.dashboard'],
            ['label' => 'Movies',    'icon' => 'film',         'route' => 'admin.movies.index',  'active' => 'admin.movies.*'],
            ['label' => 'TV Series', 'icon' => 'tv',           'route' => 'admin.series.index',  'active' => 'admin.series.*'],
        ];

        // The quick "Add" button follows the section you are in.
        $addAction = request()->routeIs('admin.series.*')
            ? ['label' => 'Add series', 'route' => 'admin.series.create']
            : ['label' => 'Add movie',  'route' => 'admin.movies.create'];

        $admin = auth('admin')->user();
    @endphp

    <nav class="navbar" aria-label="Main">
        <div class="container navbar-inner">
            <a href="{{ route('admin.dashboard') }}" class="brand nav-brand" aria-label="CineRate admin dashboard">
                <img src="{{ asset('images/logo.png') }}" class="brand-mark" alt="" aria-hidden="true">
                <span class="brand-name">CineRate</span>
                <span class="brand-tag">Admin</span>
            </a>

            <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="nav-menu" aria-label="Open menu" data-nav-toggle>
                <span class="nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
            </button>

            <div class="nav-menu" id="nav-menu" data-nav-menu>
                <ul class="nav-links">
                    @foreach ($navItems as $item)
                        @php $isActive = request()->routeIs($item['active']); @endphp
                        <li>
                            <a href="{{ route($item['route']) }}" class="nav-link {{ $isActive ? 'is-active' : '' }}" @if ($isActive) aria-current="page" @endif>
                                <i class="fa-solid fa-{{ $item['icon'] }}" aria-hidden="true"></i><span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="nav-actions">
                    <x-button :href="route($addAction['route'])" icon="plus" size="sm" class="nav-add">{{ $addAction['label'] }}</x-button>

                    <div class="nav-account">
                        <span class="nav-avatar" aria-hidden="true">{{ Str::upper(Str::substr($admin->name, 0, 1)) }}</span>
                        <span class="nav-account-text">
                            <strong>{{ $admin->name }}</strong>
                            <small>{{ $admin->email }}</small>
                        </span>
                        <form action="{{ route('admin.logout') }}" method="POST" class="nav-logout">
                            @csrf
                            <button type="submit" class="nav-logout-btn" title="Log out">
                                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Log out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="container main" id="main" tabindex="-1">
        @yield('content')
    </main>

    @auth('admin')
    <footer class="footer">
        <div class="container footer-main">
            <div class="footer-brand">
                <a href="{{ route('admin.dashboard') }}" class="brand">
                    <img src="{{ asset('images/logo.png') }}" class="brand-mark" alt="" aria-hidden="true">
                    <span class="brand-name">CineRate</span>
                </a>
                <p>The admin side of CineRate, where the movie and TV series catalogue is curated.</p>
            </div>

            <nav class="footer-nav" aria-label="Footer">
                <div class="footer-col">
                    <h2 class="footer-heading">Catalogue</h2>
                    <ul>
                        @foreach ($navItems as $item)
                            <li><a href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div class="footer-col">
                    <h2 class="footer-heading">Manage</h2>
                    <ul>
                        <li><a href="{{ route('admin.movies.create') }}">Add a movie</a></li>
                        <li><a href="{{ route('admin.series.create') }}">Add a TV series</a></li>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="container footer-bottom">
            <span>&copy; {{ date('Y') }} CineRate. All rights reserved.</span>
            <a href="#main" class="footer-top">Back to top <i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a>
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
