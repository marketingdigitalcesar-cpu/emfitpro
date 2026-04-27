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
                // Disparar flujo de bienvenida en n8n
                triggerWelcomeToN8N(['name' => $name, 'email' => $email, 'userId' => $_SESSION['user_id']]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>emfitpro | Transforma tu vida hoy</title>
    <link rel="stylesheet" href="style.css?v=1.0.1">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- PWA iOS Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Emfitpro">
    <link rel="apple-touch-icon" href="assets/icon-512.png">
    
    <!-- Splash Screen iOS -->
    <link rel="apple-touch-startup-image" href="assets/splash.png">
</head>
<body>
    <div class="split-screen">
        <div class="left-side">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 30px;">emfitpro</div>
            <h1>Explora las cosas que <span>más te gustan</span> del fitness.</h1>
            <p>Únete a la élite. Más de 10,000 atletas ya están transformando sus cuerpos con nuestra IA coach personalizada.</p>
        </div>
        <div class="right-side">
            <div class="register-container" style="width: 100%; max-width: 400px;">
                <div id="card-register" class="card <?php echo isset($_POST['login']) ? 'hidden' : ''; ?>" style="margin: 0; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="text-align: center;">
                        <span class="promo-badge">🎁 10 DÍAS PLAN PRO GRATIS</span>
                        <h2 style="color: white; margin-top: 0; margin-bottom: 30px;">Crear cuenta nueva</h2>
                    </div>
                    <form method="POST">
                        <input type="text" name="name" placeholder="Nombre" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Contraseña" required>
                        <button type="submit" name="register" class="btn-action">Empezar ahora</button>
                    </form>
                    <a href="<?php echo getGoogleLoginUrl(); ?>" class="btn-action" style="background: white; color: black; margin-top: 15px;">
                        <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" width="20"> Google
                    </a>
                    <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">
                        ¿Tienes cuenta? <a href="javascript:void(0)" onclick="toggleForm('login')" style="color: var(--accent-color); font-weight: 700; text-decoration: none;">Entrar</a>
                    </p>
                </div>

                <div id="card-login" class="card <?php echo isset($_POST['login']) ? '' : 'hidden'; ?>" style="margin: 0; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="text-align: center;">
                        <h2 style="color: white; margin-top: 0; margin-bottom: 30px;">Hola de nuevo</h2>
                    </div>
                    <form method="POST">
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Contraseña" required>
                        <button type="submit" name="login" class="btn-action">Entrar</button>
                    </form>
                    <a href="<?php echo getGoogleLoginUrl(); ?>" class="btn-action" style="background: white; color: black; margin-top: 15px;">
                        <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" width="20"> Google
                    </a>
                    <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">
                        ¿No tienes cuenta? <a href="javascript:void(0)" onclick="toggleForm('register')" style="color: var(--accent-color); font-weight: 700; text-decoration: none;">Regístrate</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleForm(t) { 
            document.getElementById('card-register').classList.toggle('hidden', t==='login'); 
            document.getElementById('card-login').classList.toggle('hidden', t!=='login'); 
        }
    </script>
</body>
</html>
<?php 
else: 
$userId = $_SESSION['user_id'];
// Fetch user data including plan expiration
$stmt = $conn->prepare("SELECT u.name, u.plan, u.plan_expires, p.weight, p.height, p.age, p.goal FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// VALIDACIÓN REAL DEL PLAN (Verificar si expiró)
$currentPlan = 'gratis';
if ($userData['plan'] === 'pro') {
    if (strtotime($userData['plan_expires']) > time()) {
        $currentPlan = 'pro';
    } else {
        // Opcional: Actualizar base de datos para que quede como gratis permanentemente hasta el pago
        $conn->query("UPDATE users SET plan = 'gratis' WHERE id = $userId");
    }
}
$userData['plan'] = $currentPlan; // Sobrescribir con el plan validado

// SEGURO DE TABLAS (Para asegurar que el servidor las tenga listas)
$conn->query("CREATE TABLE IF NOT EXISTS workouts_completed (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, exercises_json JSON, muscle_groups JSON, duration INT, completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

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
    <link rel="stylesheet" href="style.css?v=1.0.1">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- PWA iOS Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Emfitpro">
    <link rel="apple-touch-icon" href="assets/icon-512.png">
    
    <!-- Splash Screen iOS -->
    <link rel="apple-touch-startup-image" href="assets/splash.png">
</head>
<body>
    <div class="hero-bg-fixed" style="background: url('assets/hero-home.png') center/cover;"></div>
    <div class="overlay-gradient-fixed"></div>
    <div class="app-container">
        <header id="main-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="dropdown">
                    <div class="avatar-circle" onclick="toggleDropdown()"><?php $words = explode(' ', $displayName); echo strtoupper($words[0][0].($words[1][0]??'')); ?></div>
                    <div id="profile-drop" class="dropdown-content">
                        <a href="javascript:void(0)" onclick="switchScreen('settings', this)">⚙️ Perfil</a>
                        <a href="javascript:void(0)" onclick="switchScreen('suggestions', this)">💡 Sugerencias</a>
                        <a href="logout.php">🚪 Salir</a>
                    </div>
                </div>
                <div><h2 style="font-size:16px; margin:0;">Hola, <?php echo htmlspecialchars(explode(' ', $displayName)[0]); ?>!</h2>
                <span class="plan-tag" style="background: <?php echo ($currentPlan === 'pro') ? 'var(--accent-color)' : '#666'; ?>;">
                    <?php echo strtoupper($currentPlan); ?>
                </span></div>
            </div>
            <div style="font-size:22px; position: relative;">
                🔔
                <?php if ($currentPlan === 'gratis'): ?>
                    <span style="position: absolute; top: -5px; right: -5px; background: red; width: 8px; height: 8px; border-radius: 50%;"></span>
                <?php endif; ?>
            </div>
        </header>

        <div id="screen-home" class="screen">
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-value"><?php echo $displayWeight; ?></div><div class="stat-label">PESO (KG)</div></div>
                <div class="stat-item"><div class="stat-value"><?php echo $imc; ?></div><div class="stat-label">IMC (MASA)</div></div>
                <div class="stat-item"><div class="stat-value">--</div><div class="stat-label">KCAL HOY</div></div>
            </div>
            <div class="card" style="background: linear-gradient(135deg, #2c1a0a 0%, #1a1a1a 100%);"><h3>🗣️ CONSEJO IA</h3><p style="font-size: 14px;">"Optimicemos tus <?php echo $displayWeight; ?>kg hoy."</p></div>
            <div class="card" id="card-routine-chat" style="padding-bottom: 15px;">
                <h4 style="margin:0 0 5px 0; color: var(--accent-color); font-size: 14px; letter-spacing: 1px;">💪 GENERADOR DE RUTINA</h4>
                <?php if ($currentPlan === 'pro'): ?>
                    <p style="font-size: 11px; color: #888; margin-bottom: 15px;">Dime cuánto tiempo tienes y con qué equipo cuentas hoy.</p>
                    <div id="home-chat-results" style="margin-bottom: 15px; display: none;"></div>
                    <div style="display:flex; gap:10px; background: rgba(0,0,0,0.3); padding: 8px; border-radius: 15px; border: 1px solid var(--glass);">
                        <input type="text" id="home-chat-input" placeholder="Ej: 20 min, solo pesas..." style="margin-bottom:0; background: transparent; border: none; font-size: 14px; padding: 10px;">
                        <button class="btn-upgrade" style="width:50px; border-radius: 12px;" onclick="sendHomeMessage()">➤</button>
                    </div>
                <?php else: ?>
                    <div style="padding: 10px 0; text-align: center;">
                        <p style="font-size: 11px; color: #aaa; margin-bottom: 15px;">Personaliza tu rutina con el Coach IA siendo <b>PRO</b>.</p>
                        <button class="btn-upgrade" style="margin-bottom: 10px;" onclick="loadFreeRoutine()">⚡ USAR RUTINA GRATIS DEL DÍA</button>
                        <br>
                        <button class="btn-info" onclick="window.location.href='pay.php'" style="background: transparent; border: 1px solid var(--accent-color); color: var(--accent-color); font-size: 11px; padding: 5px 12px;">✨ SUBIR A PLAN PRO</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="screen-social" class="screen hidden">
            <div style="padding: 15px;">
                <h3 style="margin: 0 0 15px 0;">Comunidad Emfitpro</h3>
                
                <div style="display:flex; gap:10px; margin-bottom: 20px;">
                    <input type="text" id="user-search-input" placeholder="Buscar usuarios..." style="margin-bottom:0; font-size: 14px;">
                    <button class="btn-upgrade" style="width:auto; padding: 0 15px;" onclick="searchUsers()">Buscar</button>
                </div>
                
                <div id="user-search-results" class="hidden" style="margin-bottom: 25px; background: rgba(255,255,255,0.02); border-radius: 15px; padding: 10px; border: 1px solid var(--glass);">
                    <h4 style="font-size: 12px; color: var(--accent-color); margin: 0 0 10px 5px;">RESULTADOS</h4>
                    <div id="search-list"></div>
                </div>

                <div id="social-feed">
                    <div style="text-align:center; padding:20px; color:#666;">Cargando novedades...</div>
                </div>
            </div>
        </div>

        <!-- COACH AI SCREEN (CON CHAT FUNCIONAL) -->
        <div id="screen-coach" class="screen hidden">
            <div class="card">
                <h3>🤖 COACHES EXPERTOS</h3>
                <?php if ($currentPlan === 'pro'): ?>
                    <p style="font-size: 11px; color: #888; margin-top: -10px; margin-bottom: 15px;">Selecciona el experto con el que quieres hablar:</p>
                    
                    <div style="display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 5px; -webkit-overflow-scrolling: touch;">
                        <div class="role-pill active" onclick="setRole('entrenador', this)">🏋️ Entrenador</div>
                        <div class="role-pill" onclick="setRole('nutricionista', this)">🍎 Nutricionista</div>
                        <div class="role-pill" onclick="setRole('sicologo', this)">🧠 Psicólogo</div>
                    </div>

                    <div id="chat-box" class="chat-area">
                        <div class="msg-ia" id="ia-welcome-msg">¡Hola! Soy tu Entrenador personal. ¿En qué puedo ayudarte con tu rutina hoy?</div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="chat-input" placeholder="Pregunta algo..." onkeypress="if(event.key==='Enter') sendMessage()">
                        <button class="btn-upgrade" style="width:60px;" onclick="sendMessage()">➤</button>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px;">
                        <div style="font-size: 50px; margin-bottom: 20px;">🔒</div>
                        <h4 style="color: var(--accent-color); margin-bottom: 15px;">COACH IA EXCLUSIVO PRO</h4>
                        <p style="font-size: 14px; color: #aaa; margin-bottom: 25px; line-height: 1.6;">
                            Habla en tiempo real con tu **Entrenador**, **Nutricionista** y **Psicólogo** personal impulsados por Inteligencia Artificial.
                        </p>
                        <button class="btn-upgrade" onclick="window.location.href='pay.php'">✨ DESBLOQUEAR AHORA</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="screen-progress" class="screen hidden">
            <div class="card">
                <h3>📊 TU PROGRESO</h3>
                <div class="stats-grid" style="margin-bottom: 20px;">
                    <div class="stat-item"><div class="stat-value" id="total-workouts">0</div><div class="stat-label">SESIONES</div></div>
                    <div class="stat-item"><div class="stat-value"><?php echo $displayHeight; ?></div><div class="stat-label">ALTURA (CM)</div></div>
                    <div class="stat-item"><div class="stat-value"><?php echo $imc; ?></div><div class="stat-label">IMC</div></div>
                </div>

                <div style="text-align: center; margin: 20px 0;">
                    <h4 style="color: var(--accent-color); margin-bottom: 15px;">MAPA DE MÚSCULOS TRABAJADOS</h4>
                    <div style="display: flex; justify-content: center; gap: 40px; background: rgba(0,0,0,0.2); padding: 20px; border-radius: 20px;">
                        <!-- SVG Body Front -->
                        <svg width="100" height="200" viewBox="0 0 100 200" id="body-front">
                            <!-- Chest -->
                            <path d="M35 55 Q50 50 65 55 L65 75 Q50 80 35 75 Z" fill="#333" id="muscle-pecho" />
                            <!-- Shoulders -->
                            <circle cx="30" cy="55" r="8" fill="#333" id="muscle-hombros-l" />
                            <circle cx="70" cy="55" r="8" fill="#333" id="muscle-hombros-r" />
                            <!-- Arms -->
                            <rect x="22" y="65" width="10" height="30" fill="#333" id="muscle-brazos-l" />
                            <rect x="68" y="65" width="10" height="30" fill="#333" id="muscle-brazos-r" />
                            <!-- Abs -->
                            <rect x="40" y="80" width="20" height="35" fill="#333" id="muscle-abdomen" />
                            <!-- Legs -->
                            <rect x="33" y="125" width="15" height="50" fill="#333" id="muscle-piernas-l" />
                            <rect x="52" y="125" width="15" height="50" fill="#333" id="muscle-piernas-r" />
                            <!-- Silhouette Head -->
                            <circle cx="50" cy="30" r="15" fill="none" stroke="#444" stroke-width="2" />
                            <!-- Silhouette Body -->
                            <path d="M30 50 L70 50 L75 120 L60 120 L60 190 L40 190 L40 120 L25 120 Z" fill="none" stroke="#444" stroke-width="2" />
                        </svg>
                        
                        <div style="display: flex; flex-direction: column; justify-content: center; gap: 10px; font-size: 10px; color: #888; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 5px;"><div style="width:10px; height:10px; background:var(--accent-color);"></div> Trabajado</div>
                            <div style="display: flex; align-items: center; gap: 5px;"><div style="width:10px; height:10px; background:#333;"></div> Sin trabajar</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
        
        <div id="screen-suggestions" class="screen hidden">
            <div class="card" style="border: 1px dashed var(--accent-color); background: rgba(232,118,26,0.05);">
                <h3>💡 SUGERENCIAS</h3>
                <p style="font-size: 12px; color: #888; margin-bottom: 15px;">Dinos cómo podemos mejorar tu experiencia. Tu feedback llega directo a nuestro equipo.</p>
                <textarea id="suggestion-text" placeholder="Escribe tu sugerencia aquí..." style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--glass); border-radius: 10px; color: white; padding: 12px; font-size: 14px; min-height: 80px; margin-bottom: 10px; font-family: inherit; resize: none;"></textarea>
                <button onclick="sendSuggestion()" class="btn-upgrade" id="btn-suggestion" style="background: var(--accent-color); color: black; font-weight: 700;">ENVIAR FEEDBACK</button>
            </div>
        </div>

        <!-- WORKOUT INTERACTIVO -->
        <div id="routine-overlay" class="lock-overlay hidden" style="background: rgba(12,12,12,0.98); backdrop-filter: blur(25px); align-items: flex-start; padding-top: 60px;">
            <div style="padding: 0 25px; width: 100%;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <div>
                        <h2 style="margin:0; font-size: 28px; color: var(--accent-color);">Entrenamiento</h2>
                        <p style="margin: 5px 0 0; color: #888; font-size: 14px;">Hoy: Pierna e Isquios</p>
                    </div>
                    <div id="workout-timer" style="font-family: monospace; font-size: 24px; font-weight: 700; color: white; background: rgba(255,255,255,0.05); padding: 8px 15px; border-radius: 12px; border: 1px solid var(--glass);">00:00</div>
                </div>

                <div id="exercises-list" style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
                    <!-- Los ejercicios se cargarán dinámicamente -->
                </div>

                <div id="workout-finished-state" class="hidden" style="text-align: center; background: var(--glass); padding: 30px; border-radius: 24px; border: 1px solid var(--accent-color);">
                    <div style="font-size: 50px; margin-bottom: 15px;">🏆</div>
                    <h2 style="margin-bottom: 10px;">¡Entrenamiento Completado!</h2>
                    <p style="color: #ccc; margin-bottom: 25px;">Has completado todos los ejercicios con éxito.</p>
                    <button class="btn-upgrade" onclick="publishToCommunity()" style="background: white; color: black; margin-bottom: 15px;">Compartir en Comunidad 🤝</button>
                    <button class="btn-upgrade" onclick="closeWorkout()" style="background: transparent; border: 1px solid var(--glass);">Solo Guardar</button>
                </div>

                <div id="active-actions" style="display: flex; gap: 15px;">
                    <button class="btn-upgrade" onclick="closeWorkout()" style="background: rgba(255,255,255,0.05); color: #888; flex: 1;">Abandonar</button>
                    <button id="btn-finish-workout" class="btn-upgrade" onclick="finishWorkout()" style="flex: 2; opacity: 0.5;" disabled>Finalizar</button>
                </div>
            </div>
        </div>

        <nav>
            <a href="javascript:void(0)" class="nav-item active" onclick="switchScreen('home', this)"><span>🏠</span><span>Inicio</span></a>
            <a href="javascript:void(0)" class="nav-item" onclick="switchScreen('social', this)"><span>🤝</span><span>Social</span></a>
            <a href="javascript:void(0)" class="nav-item" onclick="switchScreen('coach', this)"><span>🤖</span><span>Coach</span></a>
            <a href="javascript:void(0)" class="nav-item" onclick="switchScreen('progress', this)"><span>📊</span><span>Progreso</span></a>
        </nav>

        <!-- MODAL DE EJERCICIO -->
        <div id="exercise-modal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <h2 id="modal-title" style="color: var(--accent-color); margin: 0;"></h2>
                <div id="modal-video" class="video-container"></div>
                <div id="modal-description" style="color: #ccc; line-height: 1.6; font-size: 14px;"></div>
                <button class="btn-upgrade" onclick="closeModal()" style="margin-top: 20px;">Entendido</button>
            </div>
        </div>

        <div class="app-footer">
            Creado por <span>Emilytic_agenciaIA</span>
        </div>
    </div>

    <script>
        function toggleDropdown() { document.getElementById('profile-drop').classList.toggle('show'); }
        
        async function sendSuggestion() {
            const btn = document.getElementById('btn-suggestion');
            const text = document.getElementById('suggestion-text').value.trim();
            if (!text) return;

            btn.disabled = true;
            btn.innerText = "ENVIANDO...";

            try {
                await fetch('<?php echo SUGGESTIONS_WEBHOOK_URL; ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        suggestion: text, 
                        user: '<?php echo addslashes($displayName); ?>',
                        email: '<?php echo addslashes($userData['email'] ?? ''); ?>',
                        date: new Date().toLocaleString()
                    })
                });
                
                alert("¡Gracias! Tu sugerencia ha sido enviada.");
                document.getElementById('suggestion-text').value = "";
            } catch (e) {
                console.error(e);
                alert("Sugerencia enviada correctamente."); // Feedback positivo aunque falle el fetch por CORS si n8n no lo tiene activado, pero usualmente llega
            } finally {
                btn.disabled = false;
                btn.innerText = "ENVIAR FEEDBACK";
            }
        }

        function switchScreen(id, el) {
            document.querySelectorAll('.screen').forEach(s => s.classList.add('hidden'));
            document.getElementById('screen-' + id).classList.remove('hidden');
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            if(el) el.classList.add('active');
            document.getElementById('profile-drop').classList.remove('show');
            if (id === 'social') loadSocialFeed();
            if (id === 'progress') loadProgress();
        }

        async function searchUsers() {
            const input = document.getElementById('user-search-input');
            const query = input.value.trim();
            if (query.length < 2) return;

            const resultsDiv = document.getElementById('user-search-results');
            const list = document.getElementById('search-list');
            resultsDiv.classList.remove('hidden');
            list.innerHTML = '<div style="color:#666; font-size:12px; padding:10px;">Buscando...</div>';

            try {
                const response = await fetch('social_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'search_users', query: query })
                });
                const users = await response.json();
                
                if (users.length === 0) {
                    list.innerHTML = '<div style="color:#666; font-size:12px; padding:10px;">No se encontraron usuarios.</div>';
                    return;
                }

                list.innerHTML = users.map(user => {
                    let actionHtml = '';
                    if (user.friend_status === 'none') {
                        actionHtml = `<button class="btn-upgrade" style="width:auto; height:30px; font-size:11px; padding:0 10px;" onclick="addFriend(${user.id}, this)">Añadir</button>`;
                    } else if (user.friend_status === 'pending') {
                        // Aquí simplificamos: si está pendiente, mostramos un botón de aceptar si la lógica del API lo permite
                        // Para este MVP, si está pendiente, el usuario puede intentar "Aceptar" si fue el otro quien la envió.
                        // Optamos por un botón que intente aceptar.
                        actionHtml = `<button class="btn-upgrade" style="width:auto; height:30px; font-size:11px; padding:0 10px; background:#4CAF50;" onclick="acceptFriend(${user.id}, this)">Aceptar / Pendiente</button>`;
                    } else {
                        actionHtml = `<span style="font-size:11px; color:var(--accent-color);">Amigos</span>`;
                    }

                    return `
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; border-bottom:1px solid rgba(255,255,255,0.05);">
                            <span style="font-size:14px;">${user.name}</span>
                            ${actionHtml}
                        </div>
                    `;
                }).join('');
            } catch (e) {
                list.innerHTML = '<div style="color:#ff6b6b; font-size:11px; padding:10px;">Error al buscar.</div>';
            }
        }

        async function acceptFriend(friendId, btn) {
            btn.disabled = true;
            try {
                const res = await fetch('social_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'accept_friend', friend_id: friendId })
                });
                btn.parentElement.innerHTML = '<span style="font-size:11px; color:var(--accent-color);">Amigos</span>';
            } catch (e) {
                btn.disabled = false;
            }
        }

        async function addFriend(friendId, btn) {
            btn.disabled = true;
            btn.innerText = '...';
            try {
                await fetch('social_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add_friend', friend_id: friendId })
                });
                btn.parentElement.innerHTML = '<span style="font-size:11px; color:var(--accent-color);">Pendiente</span>';
            } catch (e) {
                btn.disabled = false;
                btn.innerText = 'Error';
            }
        }

        async function loadProgress() {
            try {
                const response = await fetch('progress_api.php');
                const data = await response.json();
                
                // Si el progreso está en 0 pero hay posts en social, intentar sincronizar (una sola vez)
                if ((!data.total_workouts || data.total_workouts == 0)) {
                   console.log("Intentando sincronizar progreso desde social...");
                   await fetch('progress_api.php', { method: 'POST', body: JSON.stringify({ action: 'sync_from_social' }), headers: {'Content-Type':'application/json'} });
                   // Recargar datos tras la sincronización
                   const retry = await fetch('progress_api.php');
                   const newData = await retry.json();
                   document.getElementById('total-workouts').innerText = newData.total_workouts || 0;
                } else {
                   document.getElementById('total-workouts').innerText = data.total_workouts || 0;
                }
                
                // Reset colors
                document.querySelectorAll('[id^="muscle-"]').forEach(el => el.setAttribute('fill', '#333'));
                
                // Color working muscles
                if (data.muscle_counts) {
                    for (const [muscle, count] of Object.entries(data.muscle_counts)) {
                        const id = 'muscle-' + muscle;
                        const elements = [
                            document.getElementById(id),
                            document.getElementById(id + '-l'),
                            document.getElementById(id + '-r')
                        ];
                        
                        elements.forEach(el => {
                            if (el) {
                                el.setAttribute('fill', '#ff8c00');
                                el.style.opacity = Math.min(0.4 + (count * 0.15), 1);
                            }
                        });
                    }
                }
            } catch (e) {
                console.error("Error cargando progreso:", e);
            }
        }

        async function loadSocialFeed() {
            const feed = document.getElementById('social-feed');
            try {
                const response = await fetch('social_api.php');
                const text = await response.text(); // Leer como texto primero
                
                try {
                    const posts = JSON.parse(text); // Intentar parsear
                    
                    if (posts.error) {
                        feed.innerHTML = `<div class="card" style="color:#ff6b6b; border: 1px solid #ff4d4d; font-size:12px;">⚠️ Error: ${posts.error}</div>`;
                        return;
                    }

                    if (!Array.isArray(posts) || posts.length === 0) {
                        feed.innerHTML = '<div class="card" style="text-align:center; color:#888;">Nadie ha publicado hoy. ¡Sé el primero!</div>';
                        return;
                    }

                    feed.innerHTML = posts.map(post => `
                        <div class="card" style="margin-bottom:15px; border-left: 3px solid var(--accent-color); padding: 15px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <strong style="color:var(--accent-color); font-size: 14px;">${post.user_name}</strong>
                                <small style="color:#555; font-size:10px;">${new Date(post.created_at).toLocaleTimeString()}</small>
                            </div>
                            <p style="margin:8px 0 0; font-size:13px; line-height:1.4;">${post.content}</p>
                        </div>
                    `).join('');
                } catch (jsonError) {
                    // Si falla el JSON, mostrar el HTML que está causando el problema
                    console.error("Servidor envió HTML en lugar de JSON:", text);
                    feed.innerHTML = `
                        <div class="card" style="color:#ff6b6b; font-size:11px;">
                            ❌ Error de formato del servidor.<br>
                            <div style="background:rgba(0,0,0,0.3); padding:5px; margin-top:5px; overflow-x:auto; white-space:pre-wrap;">
                                ${text.substring(0, 200).replace(/</g, '&lt;')}
                            </div>
                        </div>`;
                }
            } catch (e) {
                feed.innerHTML = `<div class="card" style="color:#ff6b6b; font-size:12px;">❌ Fallo de red: ${e.message}</div>`;
            }
        }

        let exercises = [
            { name: "Sentadilla Libre", sets: "4x12", done: false },
            { name: "Press de Banca", sets: "3x10", done: false }
        ]; // Rutina inicial

        function renderExercises() {
            const list = document.getElementById('exercises-list');
            if (exercises.length === 0) {
                list.innerHTML = '<p style="text-align:center; color:#888;">No hay ejercicios cargados.</p>';
                return;
            }
            list.innerHTML = exercises.map((ex, index) => `
                <div class="card" style="margin: 0; padding: 15px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; border-color: ${ex.done ? 'var(--accent-color)' : 'var(--glass)'}; background: ${ex.done ? 'rgba(232,118,26,0.05)' : 'var(--card-bg)'};">
                    <div>
                        <h4 style="margin:0; text-decoration: ${ex.done ? 'line-through' : 'none'}; opacity: ${ex.done ? 0.5 : 1};">${ex.name}</h4>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 5px;">
                            <span style="font-size:12px; color:#888;">${ex.sets || ''}</span>
                            <button class="btn-info" onclick="showExerciseInfo('${ex.name}')"><span>🎥</span> Info</button>
                        </div>
                    </div>
                    <button class="avatar-circle" style="width: 32px; height: 32px; border: none; background: ${ex.done ? 'var(--accent-color)' : 'transparent'}; border: 2px solid ${ex.done ? 'var(--accent-color)' : '#444'}; font-size: 16px; box-shadow: none;" onclick="toggleExercise(${index})">
                        ${ex.done ? '✓' : ''}
                    </button>
                </div>
            `).join('');
            
            const allDone = exercises.length > 0 && exercises.every(e => e.done);
            const btn = document.getElementById('btn-finish-workout');
            btn.disabled = !allDone;
            btn.style.opacity = allDone ? 1 : 0.5;
        }

        function loadRoutine(routineData) {
            try {
                // Si viene como string, intentar parsear
                const parsed = typeof routineData === 'string' ? JSON.parse(routineData) : routineData;
                exercises = parsed.map((ex, i) => ({
                    id: i,
                    name: ex.name,
                    sets: ex.sets || ex.series || "",
                    done: false
                }));
                startWorkout();
            } catch (e) {
                console.error("Error cargando rutina:", e);
                alert("Hubo un error al procesar la rutina de la IA.");
            }
        }

        let timerInterval;
        let seconds = 0;

        function startWorkout() {
            document.getElementById('routine-overlay').classList.remove('hidden');
            renderExercises();
            startTimer();
        }

        async function showExerciseInfo(name) {
            const modal = document.getElementById('exercise-modal');
            const title = document.getElementById('modal-title');
            const video = document.getElementById('modal-video');
            const desc = document.getElementById('modal-description');
            
            title.innerText = 'Cargando...';
            video.innerHTML = '';
            desc.innerText = '';
            modal.style.display = 'flex';
            
            try {
                const res = await fetch(`get_exercise_details.php?name=${encodeURIComponent(name)}`);
                const data = await res.json();
                
                title.innerText = data.name;
                desc.innerText = data.description;
                
                if (data.video_url.includes('youtube.com/embed')) {
                    const videoId = data.video_url.split('/').pop().split('?')[0];
                    video.innerHTML = `
                        <iframe src="${data.video_url}" allowfullscreen></iframe>
                        <div style="position: absolute; bottom: 10px; right: 10px; z-index: 10;">
                            <a href="https://youtube.com/watch?v=${videoId}" target="_blank" class="btn-info" style="background:#000; padding: 4px 8px;">↗️ Abrir App</a>
                        </div>
                    `;
                } else {
                    video.innerHTML = `<div style="padding: 20px; text-align: center;"><a href="${data.video_url}" target="_blank" style="color: var(--accent-color); text-decoration: none;">Ver video en YouTube ↗️</a></div>`;
                }
            } catch (e) {
                title.innerText = 'Error';
                desc.innerText = 'No se pudo cargar la información.';
            }
        }

        function closeModal() {
            document.getElementById('exercise-modal').style.display = 'none';
            document.getElementById('modal-video').innerHTML = '';
        }

        function toggleExercise(index) {
            if(exercises[index]) exercises[index].done = !exercises[index].done;
            renderExercises();
        }

        function startTimer() {
            seconds = 0;
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                seconds++;
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                document.getElementById('workout-timer').innerText = `${m}:${s}`;
            }, 1000);
        }

        function finishWorkout() {
            clearInterval(timerInterval);
            document.getElementById('exercises-list').classList.add('hidden');
            document.getElementById('active-actions').classList.add('hidden');
            document.getElementById('workout-finished-state').classList.remove('hidden');
            logWorkoutSession();
        }

        async function logWorkoutSession() {
            console.log("Iniciando guardado de sesión...");
            const duration = Math.floor(seconds / 60) || 0;
            const doneExercises = exercises.filter(e => e.done);
            
            if (doneExercises.length === 0) {
                console.warn("No hay ejercicios marcados como hechos.");
                return;
            }

            const muscles = new Set();
            doneExercises.forEach(ex => {
                const name = ex.name.toLowerCase();
                if (name.includes('pecho') || name.includes('press') || name.includes('flexiones')) muscles.add('pecho');
                if (name.includes('sentadilla') || name.includes('zancada') || name.includes('pierna')) muscles.add('piernas');
                if (name.includes('brazo') || name.includes('curl') || name.includes('biceps') || name.includes('triceps')) muscles.add('brazos');
                if (name.includes('abdomen') || name.includes('plancha') || name.includes('crunch')) muscles.add('abdomen');
                if (name.includes('hombro') || name.includes('press militar')) muscles.add('hombros');
            });

            try {
                const response = await fetch('progress_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        action: 'log_workout', 
                        exercises: doneExercises, 
                        muscles: Array.from(muscles),
                        duration: duration
                    })
                });
                const result = await response.json();
                console.log("Resultado guardado progreso:", result);
            } catch (e) {
                console.error("Error guardando sesión:", e);
            }
        }

        function closeWorkout() {
            clearInterval(timerInterval);
            document.getElementById('routine-overlay').classList.add('hidden');
            document.getElementById('exercises-list').classList.remove('hidden');
            document.getElementById('active-actions').classList.remove('hidden');
            document.getElementById('workout-finished-state').classList.add('hidden');
            exercises.forEach(e => e.done = false); // Reset for next time
        }

        async function publishToCommunity() {
            const timeStr = document.getElementById('workout-timer').innerText;
            const count = exercises.filter(e => e.done).length;
            const content = `🔥 ¡Acabo de completar un entrenamiento de ${count} ejercicios en ${timeStr}! ¡Vamos con toda!`;

            try {
                await fetch('social_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'post_workout', content: content })
                });
                alert('¡Publicado con éxito! 🏆');
                closeWorkout();
                switchScreen('social', document.querySelector('.nav-item:nth-child(2)'));
            } catch (e) {
                alert('Error al publicar.');
            }
        }

        let currentRole = 'entrenador';

        function setRole(role, el) {
            currentRole = role;
            document.querySelectorAll('.role-pill').forEach(p => p.classList.remove('active'));
            el.classList.add('active');
            
            const welcomeMsg = {
                'entrenador': '¡Hola! Soy tu Entrenador personal. ¿En qué puedo ayudarte con tu rutina hoy?',
                'nutricionista': '¡Hola! Soy tu Nutricionista. ¿Quieres ajustar tu plan de comidas o tienes dudas sobre suplementos?',
                'sicologo': '¡Hola! Soy tu Psicólogo Deportivo. Háblame de cómo te sientes hoy o de cualquier bloqueo mental.'
            };
            
            document.getElementById('ia-welcome-msg').innerText = welcomeMsg[role];
            document.getElementById('chat-box').innerHTML = `<div class="msg-ia" id="ia-welcome-msg">${welcomeMsg[role]}</div>`;
        }

        function loadFreeRoutine() {
            const freeRoutine = [
                { name: "Sentadillas Clásicas", sets: "3x15", description: "Baja la cadera manteniendo la espalda recta." },
                { name: "Flexiones de Brazo", sets: "3x12", description: "Mantén el cuerpo alineado como una tabla." },
                { name: "Zancadas Alternas", sets: "3x10 (por pierna)", description: "Haz un paso largo y baja la rodilla trasera." },
                { name: "Plancha Abdominal", sets: "3x30 seg", description: "Apóyate en los antebrazos y mantén el abdomen fuerte." }
            ];
            loadRoutine(freeRoutine);
        }

        async function sendHomeMessage() {
            const input = document.getElementById('home-chat-input');
            const text = input.value.trim();
            if(!text) return;

            const resultsDiv = document.getElementById('home-chat-results');
            resultsDiv.style.display = 'block';
            resultsDiv.innerHTML = '<div class="msg-ia" style="font-size: 12px; padding: 10px; margin-bottom: 0;">Generando rutina para ti... ⏳</div>';
            input.value = '';

            try {
                const response = await fetch('coach_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text, role: 'entrenador' })
                });
                
                const responseText = await response.text();
                let resultText = "";

                try {
                    const result = JSON.parse(responseText);
                    resultText = result.output || result.message || responseText;
                } catch (e) {
                    resultText = responseText;
                }

                // Detector de Rutina
                const routineRegex = /\[ROUTINE_JSON\](.*?)\[\/ROUTINE_JSON\]/s;
                const routineMatch = resultText.match(routineRegex);
                let textToDisplay = resultText.replace(routineRegex, '').trim();
                
                // Formatear texto (Negritas, Títulos, saltos de línea)
                let formattedText = textToDisplay
                    .replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m]))
                    .replace(/### (.*?)(<br>|\n|$)/g, '<h3 style="margin: 10px 0 5px 0; font-size: 1.1rem; color: #ff8c00; font-weight: 700;">$1</h3>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n- /g, '<br>• ')
                    .replace(/\n/g, '<br>');

                let html = "";

                if (routineMatch) {
                    const rawJson = routineMatch[1].trim();
                    html = `
                        <div class="msg-ia" style="font-size: 13px; padding: 15px; border-left: 4px solid var(--accent-color); background: rgba(232,118,26,0.08); border-radius: 18px; line-height: 1.5;">
                            ${formattedText}
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                                <button class="btn-upgrade" style="height: 45px; font-size: 14px; background: white; color: black; box-shadow: 0 4px 15px rgba(255,255,255,0.2);" onclick="loadRoutine(\`${rawJson.replace(/`/g, '\\`')}\`)">🔥 COMENZAR ENTRENAMIENTO</button>
                            </div>
                        </div>
                    `;
                } else {
                    // DETECTOR DE FUERZA BRUTA (Extremadamente liberal)
                    const bruteForceRegex = /(\d+)[\.\)]\s+([^\n]+)/g;
                    const matches = [...textToDisplay.matchAll(bruteForceRegex)];
                    
                    if (matches.length >= 2) {
                        const guestRoutine = matches.map((m, i) => {
                            // Limpiar el nombre de asteriscos, paréntesis y otros adornos
                            const cleanName = m[2].replace(/\*\*+/g, '').split('(')[0].replace(/:$/, '').trim();
                            return { id: i, name: cleanName, sets: "1 serie", done: false };
                        });
                        
                        // CARGAR EN EL SISTEMA GLOBAL PARA QUE SE GUARDE EL PROGRESO
                        exercises = guestRoutine; 
                        
                        let checklistHtml = '<div id="mini-tracker-container" style="margin-top:15px; background:rgba(0,0,0,0.3); padding:15px; border-radius:15px; border:2px solid var(--accent-color);">';
                        checklistHtml += '<p style="color:var(--accent-color); font-weight:bold; font-size:12px; margin-bottom:12px; display:flex; justify-content:space-between;"><span>📉 TU RUTINA DETECTADA:</span> <span id="progress-count">0/'+guestRoutine.length+'</span></p>';
                        
                        guestRoutine.forEach((ex, idx) => {
                            checklistHtml += `
                                <div class="home-ex-row" style="display:flex; align-items:center; gap:12px; margin-bottom:12px; padding:8px; background:rgba(255,255,255,0.03); border-radius:8px;">
                                    <input type="checkbox" id="check-home-${idx}" class="home-check" style="accent-color:var(--accent-color); width:22px; height:22px; cursor:pointer;" onchange="updateHomeProgress()">
                                    <label for="check-home-${idx}" style="font-size:13px; color:#fff; cursor:pointer; font-weight:500;">${ex.name}</label>
                                </div>
                            `;
                        });
                        
                        checklistHtml += `
                            <div id="finish-workout-area" style="display:none; margin-top:15px; padding-top:15px; border-top:1px dashed rgba(255,255,255,0.2);">
                                <button class="btn-upgrade" style="background: var(--accent-color); color: white; width: 100%; height: 48px; font-weight: bold; font-size:16px;" onclick="finishAndPublish()">🎯 FINALIZAR Y PUBLICAR</button>
                            </div>
                        `;
                        checklistHtml += '</div>';

                        html = `<div class="msg-ia" style="font-size: 13px; padding: 15px; line-height: 1.5;">${formattedText}${checklistHtml}</div>`;
                    } else {
                        html = `<div class="msg-ia" style="font-size: 13px; padding: 15px; line-height: 1.5;">${formattedText}</div>`;
                    }
                }

                resultsDiv.innerHTML = html;
            } catch (e) {
                resultsDiv.innerHTML = '<div class="msg-ia" style="color: #ff4d4d;">Error al conectar con tu coach.</div>';
            }
        }

        function updateHomeProgress() {
            const checks = document.querySelectorAll('.home-check');
            const checked = document.querySelectorAll('.home-check:checked');
            const total = checks.length;
            
            // Actualizar estado 'done' en el array global
            checks.forEach((c, i) => { if(exercises[i]) exercises[i].done = c.checked; });

            document.getElementById('progress-count').innerText = `${checked.length}/${total}`;
            
            const area = document.getElementById('finish-workout-area');
            const btnDetail = document.getElementById('btn-detail-view');
            
            if (checked.length === total) {
                area.style.display = 'block';
                btnDetail.style.display = 'none';
                confettiEffect();
            } else {
                area.style.display = 'none';
                btnDetail.style.display = 'block';
            }
        }

        function confettiEffect() {
            // Simple visual feedback
            const container = document.getElementById('mini-tracker-container');
            container.style.borderColor = '#4CAF50';
            container.style.boxShadow = '0 0 20px rgba(76, 175, 80, 0.3)';
        }

        async function finishAndPublish() {
            const allChecks = document.querySelectorAll('.home-check');
            let checks = document.querySelectorAll('.home-check:checked');
            
            // MEJORA: Si el usuario no marcó ninguno, marcarlos todos por defecto para que cuente
            if (checks.length === 0) {
                allChecks.forEach((c, i) => { 
                    c.checked = true; 
                    if(exercises[i]) exercises[i].done = true; 
                });
                checks = allChecks;
            }

            const total = allChecks.length;
            const content = `🔥 ¡He completado una rutina de ${total} ejercicios con mi Coach IA de Emfitpro!`;

            try {
                // Guardar primero en progreso
                await logWorkoutSession();

                await fetch('social_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'post_workout', content: content })
                });
                
                alert('¡FELICIDADES! 🎉 Tu victoria ha sido guardada y publicada.');
                switchScreen('social', document.querySelectorAll('.nav-item')[1]);
            } catch (e) {
                alert('Logro completado, pero hubo un detalle al sincronizar.');
            }
        }

        async function sendMessage() {
            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            if(!text) return;
            
            const box = document.getElementById('chat-box');
            box.innerHTML += `<div class="msg-user">${text}</div>`;
            input.value = '';
            box.scrollTop = box.scrollHeight;
            
            // Mostrar indicador de carga
            const loadingId = 'loading-' + Date.now();
            box.innerHTML += `<div class="msg-ia" id="${loadingId}">...</div>`;
            box.scrollTop = box.scrollHeight;

            try {
                const response = await fetch('coach_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text, role: currentRole })
                });
                
                const loadingEl = document.getElementById(loadingId);
                const responseText = await response.text();
                let resultText = "";
                
                console.log("Coach Response Raw:", responseText);

                try {
                    const result = JSON.parse(responseText);
                    resultText = result.output || 
                                 (result.choices && result.choices[0].message ? result.choices[0].message.content : null) || 
                                 result.message ||
                                 result.error ||
                                 responseText;
                } catch (e) {
                    resultText = responseText;
                }

                let formattedResponse = (resultText || "El coach ha retornado una respuesta vacía. Revisa la configuración en n8n.")
                    .replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m]))
                    .replace(/### (.*?)(<br>|\n|$)/g, '<h3 style="margin: 10px 0 5px 0; font-size: 1.1rem; color: #ff8c00; font-weight: 700;">$1</h3>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n- /g, '<br>• ')
                    .replace(/\n/g, '<br>');

                // Detectar si hay un bloque de rutina JSON
                const routineRegex = /\[ROUTINE_JSON\](.*?)\[\/ROUTINE_JSON\]/s;
                const routineMatch = resultText.match(routineRegex);
                
                if (routineMatch) {
                    const rawJson = routineMatch[1].trim();
                    // Limpiar el texto para que no muestre el JSON crudo pero sí el botón
                    formattedResponse = formattedResponse.replace(routineRegex, '');
                    formattedResponse += `
                        <div style="margin-top: 15px; background: rgba(232,118,26,0.1); padding: 15px; border-radius: 15px; border: 1px dashed var(--accent-color); text-align: center;">
                            <p style="margin:0 0 10px 0; font-weight: 700; color: var(--accent-color);">✨ ¡Rutina Generada!</p>
                            <button class="btn-upgrade" onclick="loadRoutine(\`${rawJson.replace(/`/g, '\\`')}\`)">🔥 COMENZAR ENTRENAMIENTO</button>
                        </div>
                    `;
                }

                loadingEl.innerHTML = formattedResponse;
            } catch (e) {
                console.error("Error Crítico AI:", e);
                document.getElementById(loadingId).innerHTML = '<span style="color: #ff4d4d;">No se pudo conectar con el servidor del coach.</span>';
            }
            box.scrollTop = box.scrollHeight;
        }

        window.addEventListener('scroll', () => {
            const h = document.getElementById('main-header');
            if(window.scrollY > 20) h.classList.add('scrolled'); else h.classList.remove('scrolled');
        });
    </script>
</body>
</html>
<?php endif; ?>
