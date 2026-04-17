<?php
require_once 'config.php';
require_once 'google_auth.php';

// --- LÓGICA DE ACCESO ---
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            } else { $error = "Error al crear la cuenta."; }
        }
    }
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
                header("Location: index.php");
                exit();
            } else { $error = "Contraseña incorrecta."; }
        } else { $error = "No encontramos ninguna cuenta con ese email."; }
    }
}

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
        .right-side { flex: 1; background: #0f0f0f; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 40px; }
        .register-container { width: 100%; max-width: 400px; }
        .register-card { background: #1a1a1a; padding: 40px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.05); transition: 0.5s; }
        input { width: 100%; padding: 16px; margin-bottom: 15px; border-radius: 12px; border: 1px solid #333; background: #000; color: white; font-size: 16px; box-sizing: border-box; }
        .btn-action { width: 100%; padding: 16px; background: var(--accent-color); color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; }
        .google-btn { width: 100%; padding: 14px; background: white; color: black; border-radius: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; margin-top: 20px; }
        .promo-badge { background: rgba(232, 118, 26, 0.15); color: var(--accent-color); padding: 8px 16px; border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 20px; display: inline-block; }
        .hidden { display: none; }
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
                <div id="card-register" class="register-card <?php echo isset($_POST['login']) ? 'hidden' : ''; ?>">
                    <div style="text-align: center;"><span class="promo-badge">🎁 10 DÍAS PLAN PRO GRATIS</span><h2 style="color: white; margin-top: 0; margin-bottom: 30px;">Crear cuenta nueva</h2></div>
                    <form method="POST">
                        <input type="text" name="name" placeholder="Nombre completo" required>
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                        <input type="password" name="password" placeholder="Nueva contraseña" required>
                        <button type="submit" name="register" class="btn-action">Empezar ahora</button>
                    </form>
                    <a href="<?php echo getGoogleLoginUrl(); ?>" class="google-btn"><img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" width="20">Registrarse con Google</a>
                    <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">¿Ya tienes una cuenta? <a href="javascript:void(0)" onclick="toggleForm('login')" style="color: var(--accent-color); text-decoration: none; font-weight: 700;">Inicia sesión</a></p>
                </div>
                <div id="card-login" class="register-card <?php echo isset($_POST['login']) ? '' : 'hidden'; ?>">
                    <div style="text-align: center;"><h2 style="color: white; margin-top: 0; margin-bottom: 30px;">Bienvenido de nuevo</h2></div>
                    <form method="POST"><input type="email" name="email" placeholder="Correo electrónico" required><input type="password" name="password" placeholder="Contraseña" required><button type="submit" name="login" class="btn-action">Iniciar Sesión</button></form>
                    <a href="<?php echo getGoogleLoginUrl(); ?>" class="google-btn"><img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="20">Entrar con Google</a>
                    <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">¿No tienes cuenta? <a href="javascript:void(0)" onclick="toggleForm('register')" style="color: var(--accent-color); text-decoration: none; font-weight: 700;">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>
    <script>function toggleForm(type) { const reg = document.getElementById('card-register'); const log = document.getElementById('card-login'); if(type === 'login') { reg.classList.add('hidden'); log.classList.remove('hidden'); } else { log.classList.add('hidden'); reg.classList.remove('hidden'); } }</script>
</body>
</html>
<?php 
else: 
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u.name, u.plan, p.weight, p.height, p.goal FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

if (!$userData || empty($userData['name']) || $userData['weight'] === null || $userData['height'] === null) {
    header("Location: complete_profile.php");
    exit();
}

$displayName = $userData['name'];
$displayPlan = strtoupper($userData['plan']);
$displayWeight = $userData['weight'];
$displayHeight = $userData['height'];

// Calculo de IMC (Indice de Masa Corporal)
$imc = 0;
if ($displayHeight > 0) {
    $heightInMeters = $displayHeight / 100;
    $imc = round($displayWeight / ($heightInMeters * $heightInMeters), 1);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>emfitpro | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #0c0c0c; --card-bg: rgba(30,30,30, 0.7); --accent-color: #E8761A; --text-primary: #ffffff; --text-secondary: #d1d1d1; --glass: rgba(255, 255, 255, 0.1); --glass-heavy: rgba(255, 255, 255, 0.15); }
        body { background-color: var(--bg-color); color: var(--text-primary); margin:0; padding:0; font-family: 'Outfit', sans-serif; overflow-x: hidden; }
        .app-container { max-width: 500px; margin: 0 auto; min-height: 100vh; padding-bottom: 100px; position: relative; }
        header { padding: 24px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; transition: 0.3s; }
        header.scrolled { background: rgba(12,12,12,0.95); backdrop-filter: blur(20px); border-bottom: 1px solid var(--glass-heavy); }
        .hero-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; object-fit: cover; opacity: 0.4; background: url('assets/hero-home.png') center/cover; }
        .overlay-gradient { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%); z-index: -1; }
        .avatar-circle { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-color), #f79c42); display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; border: 2px solid white; cursor: pointer; box-shadow: 0 4px 15px rgba(232,118,26,0.3); }
        .dropdown { position: relative; }
        .dropdown-content { display: none; position: absolute; top: 55px; left: 0; background: rgba(20,20,20,0.98); min-width: 180px; border-radius: 12px; border: 1px solid var(--glass-heavy); backdrop-filter: blur(10px); z-index:1001; }
        .dropdown-content a { color: white; padding: 14px 16px; text-decoration: none; display: block; font-size: 14px; }
        .dropdown-content.show { display: block; }
        .plan-tag { background: var(--accent-color); color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 0 20px 20px; }
        .stat-item { background: var(--card-bg); padding: 15px; border-radius: 18px; text-align: center; border: 1px solid var(--glass); backdrop-filter: blur(10px); }
        .stat-value { font-size: 22px; font-weight: 700; color: var(--accent-color); }
        .stat-label { font-size: 10px; color: var(--text-secondary); margin-top: 4px; }
        .card { background: var(--card-bg); border-radius: 24px; padding: 24px; margin: 0 20px 20px; border: 1px solid var(--glass-heavy); backdrop-filter: blur(20px); }
        .btn-upgrade { background: var(--accent-color); color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; }
        nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 500px; background: rgba(20,20,20,0.9); backdrop-filter: blur(20px); display: flex; justify-content: space-around; padding: 15px 0 30px; border-top: 1px solid var(--glass); z-index:1000; }
        .nav-item { color: #888; text-decoration: none; font-size: 11px; display: flex; flex-direction: column; align-items: center; gap: 5px; }
        .nav-item.active { color: var(--accent-color); }
        .hidden { display: none !important; }
        .pill-container { display: flex; gap: 10px; padding: 0 20px 20px; overflow-x: auto; }
        .pill { padding: 10px 20px; background: rgba(255,255,255,0.05); border-radius: 50px; border: 1px solid var(--glass); font-size: 13px; color: white; white-space: nowrap; cursor: pointer; }
        .pill.active { background: var(--accent-color); border: none; }
    </style>
</head>
<body>
    <div class="hero-bg"></div><div class="overlay-gradient"></div>
    <div class="app-container">
        <header id="main-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="dropdown">
                    <div class="avatar-circle" onclick="toggleDropdown()"><?php $words = explode(' ', $displayName); echo strtoupper($words[0][0] . ($words[1][0] ?? '')); ?></div>
                    <div id="profile-drop" class="dropdown-content"><a href="javascript:void(0)" onclick="switchScreen('settings')">⚙️ Perfil</a><a href="logout.php">🚪 Salir</a></div>
                </div>
                <div><h2 style="font-size:16px; margin:0;">Hola, <?php echo htmlspecialchars(explode(' ', $displayName)[0]); ?>!</h2><span class="plan-tag"><?php echo $displayPlan; ?></span></div>
            </div>
            <div style="font-size:22px;">🔔</div>
        </header>

        <div id="screen-home" class="screen">
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-value"><?php echo $displayWeight; ?></div><div class="stat-label">PESO (KG)</div></div>
                <div class="stat-item"><div class="stat-value"><?php echo $imc; ?></div><div class="stat-label">IMC (MASA)</div></div>
                <div class="stat-item"><div class="stat-value">--</div><div class="stat-label">KCAL HOY</div></div>
            </div>
            <div class="card" style="background: linear-gradient(135deg, #2c1a0a 0%, #1a1a1a 100%);">
                <h3>🗣️ CONSEJO IA</h3>
                <p style="font-size: 14px;">"Tu IMC es de <?php echo $imc; ?> (<?php echo ($imc < 25) ? 'Normal' : 'Sobrepeso'; ?>). <?php echo $userData['goal'] == 'ganar_musculo' ? 'Enfócate en series pesadas hoy.' : 'Prioriza el gasto calórico.'; ?>"</p>
            </div>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div><h4 style="margin:0;">Rutina de Hoy</h4><p style="margin:5px 0 0; font-size:12px; color:#888;">Pierna & Glúteo</p></div>
                    <button class="btn-upgrade" onclick="alert('Generando rutina...')">Empezar</button>
                </div>
            </div>
        </div>

        <div id="screen-social" class="screen hidden"><div class="card"><h3>🤝 COMUNIDAD</h3><p>Próximamente: Comparte tus logros con otros atletas.</p></div></div>
        <div id="screen-coach" class="screen hidden">
            <div class="pill-container"><div class="pill active">Nutricionista</div><div class="pill">Psicólogo</div></div>
            <div class="card" style="height:350px;"><h3>🤖 CHAT CON IA</h3><div id="chat-box" style="font-size:14px; color:#aaa;">Hola! Estoy listo para optimizar tu dieta basada en tus <?php echo $displayWeight; ?>kg de peso.</div></div>
        </div>
        <div id="screen-progress" class="screen hidden"><div class="card"><h3>📊 TU PROGRESO</h3><p>Altura: <?php echo $displayHeight; ?> cm</p><p>Peso: <?php echo $displayWeight; ?> kg</p><p>IMC: <?php echo $imc; ?></p></div></div>

        <nav>
            <a href="javascript:void(0)" class="nav-item active" onclick="switchScreen('home', this)"><span>🏠</span><span>Inicio</span></a>
            <a href="javascript:void(0)" class="nav-item" onclick="switchScreen('social', this)"><span>🤝</span><span>Social</span></a>
            <a href="javascript:void(0)" class="nav-item" onclick="switchScreen('coach', this)"><span>🤖</span><span>Coach</span></a>
            <a href="javascript:void(0)" class="nav-item" onclick="switchScreen('progress', this)"><span>📊</span><span>Progreso</span></a>
        </nav>
    </div>

    <script>
        function toggleDropdown() { document.getElementById('profile-drop').classList.toggle('show'); }
        function switchScreen(id, el) {
            document.querySelectorAll('.screen').forEach(s => s.classList.add('hidden'));
            document.getElementById('screen-' + id).classList.remove('hidden');
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            if(el) el.classList.add('active');
            toggleDropdown(); // Cerrar dropdown si venía de ahí
        }
        window.addEventListener('scroll', () => {
            const header = document.getElementById('main-header');
            if(window.scrollY > 20) header.classList.add('scrolled');
            else header.classList.remove('scrolled');
        });
    </script>
</body>
</html>
<?php endif; ?>
