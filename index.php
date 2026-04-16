<?php
require_once 'config.php';
require_once 'google_auth.php';

// --- LÓGICA DE ACCESO (REGISTRO Y LOGIN) ---
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // REGISTRO
    if (isset($_POST['register'])) {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $name = $_POST['name'];

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Ese correo ya está registrado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, plan, plan_expires) VALUES (?, ?, ?, 'pro', DATE_ADD(NOW(), INTERVAL 10 DAY))");
            $stmt->bind_param("sss", $name, $email, $password);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                header("Location: complete_profile.php");
                exit();
            } else {
                $error = "Error al crear la cuenta.";
            }
        }
    }
    // LOGIN
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php"); // Recarga para entrar al Dashboard
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "No encontramos ninguna cuenta con ese email.";
        }
    }
}

// --- SI NO HAY SESIÓN, MOSTRAR LANDING ---
if (!isset($_SESSION['user_id'])):
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>emfitpro | Transforma tu vida hoy</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body, html { margin: 0; padding: 0; height: 100%; font-family: 'Outfit', sans-serif; background: #000; overflow: hidden; }
        .split-screen { display: flex; height: 100vh; width: 100vw; }
        
        .left-side {
            flex: 1.2;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=1500');
            background-size: cover; background-position: center;
            display: flex; flex-direction: column; justify-content: center; padding: 0 8%; color: white;
        }
        .left-side h1 { font-size: 64px; margin-bottom: 10px; line-height: 1.1; font-weight: 700; }
        .left-side h1 span { color: var(--accent-color); }
        .left-side p { font-size: 20px; color: #ccc; max-width: 500px; line-height: 1.6; }

        .right-side {
            flex: 1; background: #0f0f0f;
            display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px;
        }
        .register-container { width: 100%; max-width: 400px; }
        .register-card { background: #1a1a1a; padding: 40px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.05); transition: 0.5s; }
        
        input { 
            width: 100%; padding: 16px; margin-bottom: 15px; border-radius: 12px; border: 1px solid #333; 
            background: #000; color: white; font-size: 16px; box-sizing: border-box;
        }
        input:focus { border-color: var(--accent-color); outline: none; }
        
        .btn-action { 
            width: 100%; padding: 16px; background: var(--accent-color); color: white; 
            border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer;
        }

        .google-btn {
            width: 100%; padding: 14px; background: white; color: black; border-radius: 12px;
            font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px;
            text-decoration: none; margin-top: 20px;
        }

        .promo-badge {
            background: rgba(232, 118, 26, 0.15); color: var(--accent-color); padding: 8px 16px;
            border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: inline-block;
        }

        .hidden { display: none; }

        @media (max-width: 900px) {
            .split-screen { flex-direction: column; overflow-y: auto; }
            .left-side { padding: 60px 30px; min-height: 40vh; }
            .left-side h1 { font-size: 40px; }
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <div class="left-side">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 40px;">emfitpro</div>
            <h1>Explora las cosas que <span>más te gustan</span> del fitness.</h1>
            <p>Únete a la élite. Más de 10,000 atletas ya están transformando sus cuerpos con nuestra IA coach personalizada.</p>
        </div>
        <div class="right-side">
            <div class="register-container">
                <!-- FORMULARIO DE REGISTRO -->
                <div id="card-register" class="register-card <?php echo isset($_POST['login']) ? 'hidden' : ''; ?>">
                    <div style="text-align: center;">
                        <span class="promo-badge">🎁 10 DÍAS PLAN PRO GRATIS</span>
                        <h2 style="color: white; margin-top: 0; margin-bottom: 30px;">Crear cuenta nueva</h2>
                    </div>

                    <?php if($error && isset($_POST['register'])): ?>
                        <div style="background: rgba(255,0,0,0.1); color: #ff4d4d; padding: 10px; border-radius: 10px; margin-bottom: 20px; text-align: center; border: 1px solid rgba(255,0,0,0.2);">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="text" name="name" placeholder="Nombre completo" required>
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                        <input type="password" name="password" placeholder="Nueva contraseña" required>
                        <button type="submit" name="register" class="btn-action">Empezar ahora</button>
                    </form>

                    <div style="text-align: center; margin: 25px 0; color: #555; position: relative;">
                        <hr style="border: 0; border-top: 1px solid #333;">
                        <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #1a1a1a; padding: 0 10px;">o</span>
                    </div>

                    <a href="<?php echo getGoogleLoginUrl(); ?>" class="google-btn">
                        <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" width="20">
                        Registrarse con Google
                    </a>
                    
                    <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">
                        ¿Ya tienes una cuenta? <a href="javascript:void(0)" onclick="toggleForm('login')" style="color: var(--accent-color); text-decoration: none; font-weight: 700;">Inicia sesión</a>
                    </p>
                </div>

                <!-- FORMULARIO DE LOGIN -->
                <div id="card-login" class="register-card <?php echo isset($_POST['login']) ? '' : 'hidden'; ?>">
                    <div style="text-align: center;">
                        <h2 style="color: white; margin-top: 0; margin-bottom: 30px;">Bienvenido de nuevo</h2>
                    </div>

                    <?php if($error && isset($_POST['login'])): ?>
                        <div style="background: rgba(255,0,0,0.1); color: #ff4d4d; padding: 10px; border-radius: 10px; margin-bottom: 20px; text-align: center; border: 1px solid rgba(255,0,0,0.2);">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                        <input type="password" name="password" placeholder="Contraseña" required>
                        <button type="submit" name="login" class="btn-action">Iniciar Sesión</button>
                    </form>

                    <div style="text-align: center; margin: 25px 0; color: #555; position: relative;">
                        <hr style="border: 0; border-top: 1px solid #333;">
                        <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #1a1a1a; padding: 0 10px;">o</span>
                    </div>

                    <a href="<?php echo getGoogleLoginUrl(); ?>" class="google-btn">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="20">
                        Entrar con Google
                    </a>
                    
                    <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">
                        ¿No tienes cuenta? <a href="javascript:void(0)" onclick="toggleForm('register')" style="color: var(--accent-color); text-decoration: none; font-weight: 700;">Regístrate aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleForm(type) {
            const reg = document.getElementById('card-register');
            const log = document.getElementById('card-login');
            if(type === 'login') {
                reg.classList.add('hidden');
                log.classList.remove('hidden');
            } else {
                log.classList.add('hidden');
                reg.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
<?php 
else: 
// --- DASHBOARD ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>emfitpro | Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <img id="bg-image" class="hero-bg" src="assets/hero-home.png" alt="Health Motivation">
    <div class="overlay-gradient"></div>

    <div class="app-container">
        <header style="background:transparent;">
            <div class="user-badge">
                <div id="user-avatar" style="background:#333; width:40px; height:40px; border-radius:50%; border:2px solid var(--accent-color)"></div>
                <div>
                    <h2 style="font-size: 16px;">Hola, <span id="user-name">Atleta</span>!</h2>
                    <span class="plan-tag" id="user-plan">Gratis</span>
                </div>
            </div>
            <div class="notification-area" onclick="window.location.href='logout.php'" style="cursor:pointer; font-size:20px;">🚪</div>
        </header>
        
        <div id="screen-home" class="screen">
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-value">72</div><div class="stat-label">PESO (KG)</div></div>
                <div class="stat-item"><div class="stat-value">12%</div><div class="stat-label">GRASA</div></div>
                <div class="stat-item"><div class="stat-value">3.2k</div><div class="stat-label">KCAL HOY</div></div>
            </div>
            <div class="card coach-section">
                <h3>🗣️ RECOMENDACIÓN DEL COACH</h3>
                <p style="font-size: 14px;">"Hoy te toca pierna. Mantén la intensidad y no olvides hidratarte cada 15 minutos."</p>
            </div>
            <div class="card">
                <h3>📅 RUTINA DE HOY</h3>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div><h4>Leg Day</h4><p style="font-size:12px;color:#666">6 Ejercicios</p></div>
                    <button class="btn-upgrade" onclick="startWorkout()">Empezar</button>
                </div>
            </div>
        </div>

        <nav>
            <a href="#" class="nav-item active">Inicio</a>
            <a href="#" class="nav-item">Comunidad</a>
            <a href="#" class="nav-item">Coach AI</a>
            <a href="#" class="nav-item">Progreso</a>
        </nav>
    </div>
    <script>
        // Sincronizar sesión de PHP con el estado de la App JS
        currentUser.name = "<?php echo $_SESSION['user_name'] ?? 'Atleta'; ?>";
        currentUser.plan = "<?php echo $_SESSION['user_plan'] ?? 'gratis'; ?>";
        currentUser.profileSet = true; // Si ya entró al dashboard, asumimos perfil básico
    </script>
    <script src="app.js"></script>
</body>
</html>
<?php endif; ?>
