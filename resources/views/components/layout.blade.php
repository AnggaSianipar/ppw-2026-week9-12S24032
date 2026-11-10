<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Informasi Perpustakaan Institut Teknologi Del">
    <title>{{ $title ?? 'SIPUS-DEL' }} - Institut Teknologi Del</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-del { background-color: #4C1D95; }
        .btn-del { background-color: #4C1D95; color: #ffffff; }
        .btn-del:hover { background-color: #3B0764; color: #ffffff; }
        .action-menu-button {
            width: 32px;
            height: 32px;
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 3px;
            padding: 0;
            border: 0;
            border-radius: 0.25rem;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
        }
        .action-menu-button:hover,
        .action-menu-button:focus {
            background-color: #f3f4f6;
        }
        .action-menu-button span {
            display: block;
            width: 14px;
            height: 2px;
            background-color: #6b7280;
            border-radius: 1px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm mb-4">
        <div class="container">
            @if (request()->routeIs('login'))
                <span class="navbar-brand fw-bold">Perpustakaan IT Del</span>
            @else
                <a class="navbar-brand fw-bold" href="{{ route('buku.index') }}">Perpustakaan IT Del</a>
                <div class="navbar-nav">
                    @auth
                        <a class="nav-link d-inline-block" href="{{ route('buku.index') }}">Buku</a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">Logout</button>
                        </form>
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <div class="container pb-5">
        @if (session('welcome'))
            <div id="welcome-notification"
                class="alert alert-success alert-dismissible fade show text-center"
                role="alert">
                {{ session('welcome') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Tutup"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Periksa kembali isian formulir:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @if (session('welcome'))
        <script>
            window.setTimeout(function () {
                const notification = document.getElementById('welcome-notification');

                if (notification) {
                    bootstrap.Alert.getOrCreateInstance(notification).close();
                }
            }, 2000);
        </script>
    @endif
</body>
</html>