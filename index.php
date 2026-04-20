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
    <link rel="stylesheet" href="style.css?v=1.0.1">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="hero-bg-fixed" style="background: url('assets/hero-home.png') center/cover;"></div>
    <div class="overlay-gradient-fixed"></div>
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
                <h3>🤖 COACHES EXPERTOS</h3>
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

        const exercises = [
            { id: 1, name: "Squats", sets: "4x12", done: false },
            { id: 2, name: "Lunges", sets: "3x15", done: false },
            { id: 3, name: "Leg Press", sets: "3x12", done: false },
            { id: 4, name: "Calf Raises", sets: "4x20", done: false }
        ];

        let timerInterval;
        let seconds = 0;

        function startWorkout() {
            document.getElementById('routine-overlay').classList.remove('hidden');
            renderExercises();
            startTimer();
        }

        function renderExercises() {
            const list = document.getElementById('exercises-list');
            list.innerHTML = exercises.map(ex => `
                <div class="card" style="margin: 0; padding: 15px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; border-color: ${ex.done ? 'var(--accent-color)' : 'var(--glass)'}; background: ${ex.done ? 'rgba(232,118,26,0.05)' : 'var(--card-bg)'};">
                    <div>
                        <h4 style="margin:0; text-decoration: ${ex.done ? 'line-through' : 'none'}; opacity: ${ex.done ? 0.5 : 1};">${ex.name}</h4>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 5px;">
                            <span style="font-size:12px; color:#888;">${ex.sets}</span>
                            <button class="btn-info" onclick="showExerciseInfo('${ex.name}')"><span>🎥</span> Info</button>
                        </div>
                    </div>
                    <button class="avatar-circle" style="width: 32px; height: 32px; border: none; background: ${ex.done ? 'var(--accent-color)' : 'transparent'}; border: 2px solid ${ex.done ? 'var(--accent-color)' : '#444'}; font-size: 16px; box-shadow: none;" onclick="toggleExercise(${ex.id})">
                        ${ex.done ? '✓' : ''}
                    </button>
                </div>
            `).join('');
            
            const allDone = exercises.every(e => e.done);
            const btn = document.getElementById('btn-finish-workout');
            btn.disabled = !allDone;
            btn.style.opacity = allDone ? 1 : 0.5;
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
                    video.innerHTML = `<iframe src="${data.video_url}" allowfullscreen></iframe>`;
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

        function toggleExercise(id) {
            const ex = exercises.find(e => e.id === id);
            if(ex) ex.done = !ex.done;
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
        }

        function closeWorkout() {
            clearInterval(timerInterval);
            document.getElementById('routine-overlay').classList.add('hidden');
            document.getElementById('exercises-list').classList.remove('hidden');
            document.getElementById('active-actions').classList.remove('hidden');
            document.getElementById('workout-finished-state').classList.add('hidden');
            exercises.forEach(e => e.done = false); // Reset for next time
        }

        function publishToCommunity() {
            const timeStr = document.getElementById('workout-timer').innerText;
            alert(`¡Entrenamiento publicado en la comunidad! Tiempo total: ${timeStr}. Estás motivando a otros 🎉`);
            closeWorkout();
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

                const formattedResponse = (resultText || "El coach ha retornado una respuesta vacía. Revisa la configuración en n8n.")
                    .replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m]))
                    .replace(/### (.*?)(<br>|\n|$)/g, '<h3 style="margin: 10px 0 5px 0; font-size: 1.1rem; color: #ff8c00; font-weight: 700;">$1</h3>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\n- /g, '<br>• ')
                    .replace(/\n/g, '<br>');

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
