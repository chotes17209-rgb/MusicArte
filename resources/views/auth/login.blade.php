<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - MusicArte</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#2a1e63">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MusicArte">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#2a1e63">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MusicArte">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon-192.png') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-height: 100vh; padding: 1rem;
            display: flex; align-items: center; justify-content: center;
            background:
                radial-gradient(40rem 30rem at 10% 10%, rgba(123,92,224,.55), transparent 60%),
                radial-gradient(30rem 25rem at 90% 90%, rgba(242,177,52,.30), transparent 60%),
                linear-gradient(135deg, #1c1545, #2a1e63 50%, #3d2c8d);
            -webkit-font-smoothing: antialiased;
        }
        .login-card {
            width: 100%; max-width: 410px; border-radius: 22px; border: 1px solid rgba(255,255,255,.6);
            background: #fff; box-shadow: 0 30px 70px rgba(10,6,40,.45); padding: 2.2rem !important;
        }
        .login-card img { width: 84px; height: 84px; border-radius: 22px; object-fit: cover; box-shadow: 0 0 0 4px rgba(242,177,52,.7), 0 10px 24px rgba(61,44,141,.25); }
        .login-card h4 { letter-spacing: 2px; font-weight: 800 !important; }
        .form-label { color: #3b3a4d; }
        .form-control { border-radius: 12px; border-color: #e3e3ee; padding: .7rem .95rem; }
        .form-control:focus { border-color: #a99be3; box-shadow: 0 0 0 4px rgba(61,44,141,.12); }
        .form-check-input:checked { background-color: #3d2c8d; border-color: #3d2c8d; }
        .btn-morado {
            background: linear-gradient(135deg, #4b37a9, #3d2c8d); border: 0; border-radius: 12px;
            box-shadow: 0 8px 20px rgba(61,44,141,.3); transition: all .15s ease;
        }
        .btn-morado:hover { background: linear-gradient(135deg, #3d2c8d, #2a1e63); transform: translateY(-1px); box-shadow: 0 12px 26px rgba(61,44,141,.35); }
        .alert { border-radius: 12px; border: 0; }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="text-center mb-3">
            <img src="{{ asset('images/logo.png') }}" alt="MusicArte">
            <h4 class="fw-bold mt-3 mb-0" style="color:#3d2c8d">MUSICARTE</h4>
            <small class="text-muted">Centro Cultural &mdash; Panel de Gestion</small>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                @foreach ($errors->all() as $error)
                    <div class="small">{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Correo electronico</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="correo@musicarte.pe">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Contrasena</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Recordarme</label>
            </div>
            <button type="submit" class="btn btn-morado w-100 text-white fw-semibold py-2">Ingresar</button>
        </form>
        <p class="text-center text-muted small mt-3 mb-0">Sistema interno &mdash; acceso restringido al personal.</p>
    </div>
</body>
</html>
