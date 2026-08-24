<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - Musica Arte</title>
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #2a1e63, #3d2c8d 60%, #5a3fc0);
        }
        .login-card { width: 100%; max-width: 400px; border-radius: 18px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,.3); }
        .login-card img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; box-shadow: 0 0 0 4px #f2b134; }
        .btn-morado { background: #3d2c8d; border-color: #3d2c8d; }
        .btn-morado:hover { background: #2a1e63; border-color: #2a1e63; }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="text-center mb-3">
            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Musica Arte">
            <h4 class="fw-bold mt-3 mb-0" style="color:#3d2c8d">MUSICA ARTE</h4>
            <small class="text-muted">Centro Cultural &mdash; Panel de Gestion</small>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger py-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="small"><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login.attempt')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Correo electronico</label>
                <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required autofocus placeholder="correo@musicarte.pe">
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
<?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/auth/login.blade.php ENDPATH**/ ?>