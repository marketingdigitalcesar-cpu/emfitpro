<?php
require_once 'config.php';
require_once 'google_auth.php';

// --- LÓGICA DE ACTUALIZACIÓN DE PERFIL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $userId = $_SESSION['user_id'];
    $u_weight = $_POST['weight'];
    $u_height = $_POST['height'];
    $u_age = $_POST['age'];
    $u_goal = $_POST['goal'];
    $stmt = $conn->prepare("UPDATE user_profiles SET weight = ?, height = ?, age = ?, goal = ? WHERE user_id = ?");
    $stmt->bind_param("ddisi", $u_weight, $u_height, $u_age, $u_goal, $userId);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

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
        .left-side { flex: 1.2; background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=1500'); background-size: cover; background-position: center; display: flex; flex-direction: column; justify-content: center; padding: 0 8%; color: white; }
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
        <div class="right-side"><div class="register-container">
            <div id="card-register" class="register-card <?php echo isset($_POST['login']) ? 'hidden' : ''; ?>">
                <div style="text-align: center;"><span class="promo-badge">🎁 10 DÍAS PLAN PRO GRATIS</span><h2 style="color: white; margin-top: 0; margin-bottom: 30px;">Crear cuenta nueva</h2></div>
                <form method="POST"><input type="text" name="name" placeholder="Nombre" required><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Contraseña" required><button type="submit" name="register" class="btn-action">Empezar ahora</button></form>
                <a href="<?php echo getGoogleLoginUrl(); ?>" class="google-btn"><img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" width="20">Google</a>
                <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">¿Tienes cuenta? <a href="javascript:void(0)" onclick="toggleForm('login')" style="color: var(--accent-color); font-weight: 700;">Entrar</a></p>
            </div>
            <div id="card-login" class="register-card <?php echo isset($_POST['login']) ? '' : 'hidden'; ?>">
                <div style="text-align: center;"><h2 style="color: white; margin-top: 0; margin-bottom: 30px;">Hola de nuevo</h2></div>
                <form method="POST"><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Contraseña" required><button type="submit" name="login" class="btn-action">Entrar</button></form>
                <a href="<?php echo getGoogleLoginUrl(); ?>" class="google-btn">Entrar con Google</a>
                <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">¿No tienes cuenta? <a href="javascript:void(0)" onclick="toggleForm('register')" style="color: var(--accent-color); font-weight: 700;">Regístrate</a></p>
            </div>
        </div></div>
    </div>
    <script>function toggleForm(t) { document.getElementById('card-register').classList.toggle('hidden', t==='login'); document.getElementById('card-login').classList.toggle('hidden', t!=='login'); }</script>
</body>
</html>
<?php 
else: 
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT u.name, u.plan, p.weight, p.height, p.age, p.goal FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

if (!$userData || empty($userData['name']) || $userData['weight'] === null) { header("Location: complete_profile.php"); exit(); }

$displayName = $userData['name'];
$displayWeight = $userData['weight'];
$displayHeight = $userData['height'];
$displayAge = $userData['age'];
$imc = ($displayHeight > 0) ? round($displayWeight / (($displayHeight/100)**2), 1) : 0;
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
        header { padding: 24px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        header.scrolled { background: rgba(12,12,12,0.9); backdrop-filter: blur(20px); border-bottom: 1px solid var(--glass); }
        .hero-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; object-fit: cover; opacity: 0.4; background: url('assets/hero-home.png') center/cover; }
        .overlay-gradient { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%); z-index: -1; }
        .avatar-circle { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-color), #f79c42); display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; border: 2px solid white; cursor: pointer; box-shadow: 0 4px 15px rgba(232,118,26,0.3); }
        .dropdown { position: relative; }
        .dropdown-content { display: none; position: absolute; top: 55px; left: 0; background: rgba(25,25,25,0.98); min-width: 180px; border-radius: 12px; border: 1px solid var(--glass-heavy); backdrop-filter: blur(10px); z-index:1001; }
        .dropdown-content a { color: white; padding: 14px 16px; text-decoration: none; display: block; font-size: 14px; }
        .dropdown-content.show { display: block; }
        .plan-tag { background: var(--accent-color); color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 0 20px 20px; }
        .stat-item { background: var(--card-bg); padding: 15px; border-radius: 18px; text-align: center; border: 1px solid var(--glass); backdrop-filter: blur(10px); }
        .stat-value { font-size: 20px; font-weight: 700; color: var(--accent-color); }
        .stat-label { font-size: 9px; color: var(--text-secondary); margin-top: 4px; }
        .card { background: var(--card-bg); border-radius: 24px; padding: 20px; margin: 0 15px 15px; border: 1px solid var(--glass-heavy); backdrop-filter: blur(20px); }
        .btn-upgrade { background: var(--accent-color); color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; width: 100%; }
        nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 500px; background: rgba(20,20,20,0.9); backdrop-filter: blur(20px); display: flex; justify-content: space-around; padding: 15px 0 30px; border-top: 1px solid var(--glass); z-index:1000; }
        .nav-item { color: #888; text-decoration: none; font-size: 11px; display: flex; flex-direction: column; align-items: center; gap: 5px; cursor: pointer; }
        .nav-item.active { color: var(--accent-color); }
        .hidden { display: none !important; }
        .chat-area { height: 300px; overflow-y: auto; background: rgba(0,0,0,0.2); border-radius: 15px; padding: 10px; margin-bottom: 10px; font-size: 14px; display: flex; flex-direction: column; gap: 10px; }
        .msg-ia { background: var(--glass); padding: 10px; border-radius: 12px; align-self: flex-start; max-width: 80%; }
        .msg-user { background: var(--accent-color); padding: 10px; border-radius: 12px; align-self: flex-end; max-width: 80%; }
        input[type="text"] { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #333; background: #000; color: white; box-sizing: border-box; }
        .lock-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 2000; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 30px; }
    </style>
</head>
<body>
    <div class="hero-bg"></div><div class="overlay-gradient"></div>
    <div class="app-container">
        <header id="main-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="dropdown">
                    <div class="avatar-circle" onclick="toggleDropdown()"><?php $words = explode(' ', $displayName); echo strtoupper($words[0][0].($words[1][0]??'')); ?></div>
                    <div id="profile-drop" class="dropdown-content"><a href="javascript:void(0)" onclick="switchScreen('settings', this)">⚙️ Perfil</a><a href="logout.php">🚪 Salir</a></div>
                </div>
                <div><h2 style="font-size:16px; margin:0;">Hola, <?php echo htmlspecialchars(explode(' ', $displayName)[0]); ?>!</h2><span class="plan-tag">PRO</span></div>
            </div>
            <div style="font-size:22px;">🔔</div>
        </header>

        <div id="screen-home" class="screen">
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-value"><?php echo $displayWeight; ?></div><div class="stat-label">PESO (KG)</div></div>
                <div class="stat-item"><div class="stat-value"><?php echo $imc; ?></div><div class="stat-label">IMC (MASA)</div></div>
                <div class="stat-item"><div class="stat-value">--</div><div class="stat-label">KCAL HOY</div></div>
            </div>
            <div class="card" style="background: linear-gradient(135deg, #2c1a0a 0%, #1a1a1a 100%);"><h3>🗣️ CONSEJO IA</h3><p style="font-size: 14px;">"Optimicemos tus <?php echo $displayWeight; ?>kg hoy."</p></div>
            <div class="card" id="card-routine">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div><h4 style="margin:0;">Rutina Activa</h4><p id="routine-name" style="margin:5px 0 0; font-size:12px; color:#888;">Nivel Intermedio</p></div>
                    <button class="btn-upgrade" style="width:auto;" onclick="startWorkout()">Ver Rutina</button>
                </div>
            </div>
        </div>

        <div id="screen-social" class="screen hidden"><div class="card"><h3>🤝 COMUNIDAD</h3><p>Muro social en desarrollo.</p></div></div>

        <!-- COACH AI SCREEN (CON CHAT FUNCIONAL) -->
        <div id="screen-coach" class="screen hidden">
            <div class="card">
                <h3>🤖 COACH INTELIGENTE</h3>
                <div id="chat-box" class="chat-area">
                    <div class="msg-ia">¡Hola <?php echo htmlspecialchars(explode(' ', $displayName)[0]); ?>! Soy tu coach. ¿Qué te gustaría consultar hoy sobre tu nutrición?</div>
                </div>
                <div style="display:flex; gap:10px;">
                    <input type="text" id="chat-input" placeholder="Pregunta algo..." onkeypress="if(event.key==='Enter') sendMessage()">
                    <button class="btn-upgrade" style="width:60px;" onclick="sendMessage()">➤</button>
                </div>
            </div>
        </div>

        <div id="screen-progress" class="screen hidden"><div class="card"><h3>📊 ESTADÍSTICAS</h3><p>Talla: <?php echo $displayHeight; ?>cm</p></div></div>

        <div id="screen-settings" class="screen hidden">
            <div class="card"><h3>⚙️ PERFIL</h3>
                <form method="POST"><input type="hidden" name="update_profile" value="1">
                <label style="font-size:11px;color:#888;">PESO (KG)</label><input type="number" step="0.1" name="weight" value="<?php echo $displayWeight; ?>" required>
                <label style="font-size:11px;color:#888;">ALTURA (CM)</label><input type="number" name="height" value="<?php echo $displayHeight; ?>" required>
                <label style="font-size:11px;color:#888;">EDAD</label><input type="number" name="age" value="<?php echo $displayAge; ?>" required>
                <label style="font-size:11px;color:#888;">OBJETIVO</label><select name="goal" style="width:100%;padding:12px;background:#000;color:white;border-radius:10px;"><option value="ganar_musculo" <?php if($userData['goal']=='ganar_musculo')echo 'selected';?>>Ganar Músculo</option><option value="perder_grasa" <?php if($userData['goal']=='perder_grasa')echo 'selected';?>>Perder Grasa</option></select>
                <button type="submit" class="btn-upgrade" style="margin-top:20px;">Guardar cambios</button></form>
            </div>
        </div>

        <!-- OVERLAY DE RUTINA -->
        <div id="routine-overlay" class="lock-overlay hidden">
            <h2 id="workout-title">Tu Rutina</h2>
            <div id="exercises-list" style="margin:20px 0; text-align:left; width:100%;">
                <p>🏋️ Squat - 4x12</p>
                <p>🏋️ Lunges - 3x15</p>
                <p>🏋️ Leg Press - 3x12</p>
            </div>
            <button class="btn-upgrade" onclick="document.getElementById('routine-overlay').classList.add('hidden')">Cerrar</button>
        </div>

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
            document.getElementById('profile-drop').classList.remove('show');
        }

        function sendMessage() {
            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            if(!text) return;
            const box = document.getElementById('chat-box');
            box.innerHTML += `<div class="msg-user">${text}</div>`;
            input.value = '';
            box.scrollTop = box.scrollHeight;
            
            // Simulación respuesta IA (Aquí conectarías con n8n)
            setTimeout(() => {
                box.innerHTML += `<div class="msg-ia">Procesando tu consulta sobre "${text}"... Estoy analizando tus datos de IMC (<?php echo $imc;?>) para responderte.</div>`;
                box.scrollTop = box.scrollHeight;
            }, 1000);
        }

        function startWorkout() {
            document.getElementById('routine-overlay').classList.remove('hidden');
        }

        window.addEventListener('scroll', () => {
            const h = document.getElementById('main-header');
            if(window.scrollY > 20) h.classList.add('scrolled'); else h.classList.remove('scrolled');
        });
    </script>
</body>
</html>
<?php endif; ?>
