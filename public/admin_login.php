<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Global Car Metepec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-crown"></i>
                <h2>Global Car Metepec</h2>
                <p>Acceso al panel de administración</p>
            </div>
            <div id="error-message" class="error-message" style="display: none;"></div>
            <form method="post">
                <div class="form-group">
                    <label for="username">USUARIO</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Ingresa tu usuario" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">CONTRASEÑA</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <span class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" name="login" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> INGRESAR
                </button>
            </form>
            <div style="margin-top:20px; text-align:center; font-size:0.8rem; color:var(--gray);">
                <i class="fas fa-shield-alt"></i> Acceso restringido
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const p = document.getElementById('password');
            const i = document.querySelector('.toggle-password i');
            if (p.type === 'password') {
                p.type = 'text';
                i.classList.remove('fa-eye');
                i.classList.add('fa-eye-slash');
            } else {
                p.type = 'password';
                i.classList.remove('fa-eye-slash');
                i.classList.add('fa-eye');
            }
        }
        <?php if (isset($error)): ?>
        document.getElementById('error-message').style.display = 'block';
        document.getElementById('error-message').innerText = '<?php echo addslashes($error); ?>';
        <?php endif; ?>
    </script>
</body>
</html>