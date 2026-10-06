<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · MusicArte</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MusicArte">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon-192.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --acento: #800080; --acento-hover: #660066; --borde: #e6e5e0; --borde-fuerte: #d4d3cd; --texto: #1d1c1a; --texto-2: #55534d; --texto-3: #8a8880; }
        body {
            font-family: 'IBM Plex Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            font-size: .875rem; color: var(--texto); background: #f6f6f4;
            min-height: 100vh; margin: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem 1rem;
            -webkit-font-smoothing: antialiased;
        }
        .marca { display: flex; flex-direction: column; align-items: center; text-align: center; gap: .7rem; margin-bottom: 1.5rem; }
        .marca img { width: 104px; height: 104px; border-radius: 50%; object-fit: cover; background: #fff; box-shadow: 0 0 0 5px #fff, 0 0 0 6px var(--borde), 0 12px 30px rgba(128,0,128,.18); }
        .marca strong { display: block; font-size: 1.35rem; font-weight: 600; line-height: 1.1; color: var(--acento); letter-spacing: -.01em; }
        .marca span { display: block; font-size: .8rem; color: var(--texto-3); margin-top: .15rem; }
        .panel { width: 100%; max-width: 380px; background: #fff; border: 1px solid var(--borde); border-radius: 10px; padding: 1.75rem; }
        .panel h1 { font-size: 1.125rem; font-weight: 600; margin: 0 0 .25rem; letter-spacing: -.01em; }
        .panel p.sub { color: var(--texto-3); margin: 0 0 1.25rem; }
        .form-label { font-size: .8125rem; font-weight: 500; color: var(--texto-2); margin-bottom: .3rem; }
        .form-control { font-size: .875rem; padding: .5rem .75rem; border: 1px solid var(--borde-fuerte); border-radius: 6px; }
        .form-control:focus { border-color: var(--acento); box-shadow: 0 0 0 3px rgba(128,0,128,.12); }
        .form-check-input:checked { background-color: var(--acento); border-color: var(--acento); }
        .form-check-input:focus { box-shadow: 0 0 0 3px rgba(128,0,128,.12); }
        .btn-ingresar { background: var(--acento); border: 1px solid var(--acento); color: #fff; font-weight: 500; font-size: .875rem; padding: .55rem; border-radius: 6px; }
        .btn-ingresar:hover, .btn-ingresar:focus { background: var(--acento-hover); border-color: var(--acento-hover); color: #fff; }
        .alert { font-size: .8125rem; border-radius: 8px; background: #fdeeec; border: 1px solid #f3c7c1; color: #7a1a12; padding: .6rem .8rem; }
        .pie { margin-top: 1.25rem; color: var(--texto-3); font-size: .75rem; }
    </style>
</head>
<body>
    <div class="marca">
        <img src="{{ asset('images/logo.png') }}" alt="MusicArte">
        <div>
            <strong>MusicArte</strong>
            <span>Centro Cultural</span>
        </div>
    </div>

    <main class="panel">
        <h1>Iniciar sesión</h1>
        <p class="sub">Ingresa con tu cuenta del centro.</p>

        @if ($errors->any())
            <div class="alert mb-3">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Mantener la sesión iniciada</label>
            </div>
            <button type="submit" class="btn btn-ingresar w-100">Ingresar</button>
        </form>
    </main>

    <p class="pie">Acceso solo para el personal de MusicArte.</p>
</body>
</html>
