<?php
die("<h1>¡SI LLEGUÉ AL PHP!</h1> Si ves esto, el código está funcionando. Si ves el Dashboard, es un problema de caché.");
require 'config.php';

// Si llega aquí, es porque tiene sesión. Mostramos el contenido de la app.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>emfitpro | Your Pro Coach</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <img id="bg-image" class="hero-bg" src="assets/hero-home.png" alt="Health Motivation">
    <div class="overlay-gradient"></div>

    <div class="app-container">
        <!-- Onboarding / Profile Setup -->
        <div id="onboarding-overlay" class="lock-overlay hidden" style="background: linear-gradient(180deg, rgba(0,0,0,0.8), rgba(0,0,0,1)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=1000'); background-size: cover; background-position: center; z-index: 9999; padding: 30px; overflow-y: auto;">
            <div style="max-width: 400px; margin: 0 auto;">
                <h2 style="color:var(--accent-color); font-size: 28px; margin-bottom: 5px;">Bienvenido a emfitpro</h2>
                <p style="color:var(--text-secondary); font-size: 14px; margin-bottom: 25px;">Configura tu perfil para que nuestra IA diseñe tu plan perfecto.</p>
                <div class="card" style="margin:0; padding:20px; text-align: left;">
                    <label style="font-size:12px; color:var(--accent-color);">Nombre</label>
                    <input type="text" id="setup-name" placeholder="Tu nombre" style="width:100%; padding:12px; background:#000; border:1px solid var(--glass); color:white; border-radius:10px; margin-bottom:15px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div>
                            <label style="font-size:12px; color:var(--accent-color);">Edad</label>
                            <input type="number" id="setup-age" placeholder="Años" style="width:100%; padding:12px; background:#000; border:1px solid var(--glass); color:white; border-radius:10px; margin-bottom:15px;">
                        </div>
                        <div>
                            <label style="font-size:12px; color:var(--accent-color);">Peso (kg)</label>
                            <input type="number" id="setup-weight" placeholder="kg" style="width:100%; padding:12px; background:#000; border:1px solid var(--glass); color:white; border-radius:10px; margin-bottom:15px;">
                        </div>
                    </div>
                    <label style="font-size:12px; color:var(--accent-color);">Altura (cm)</label>
                    <input type="number" id="setup-height" placeholder="cm" style="width:100%; padding:12px; background:#000; border:1px solid var(--glass); color:white; border-radius:10px; margin-bottom:15px;">
                    <label style="font-size:12px; color:var(--accent-color);">Objetivo</label>
                    <select id="setup-goal" style="width:100%; padding:12px; background:#000; border:1px solid var(--glass); color:white; border-radius:10px; margin-bottom:15px;">
                        <option value="perder_grasa">Perder Grasa</option>
                        <option value="ganar_musculo">Ganar Músculo</option>
                        <option value="resistencia">Mejorar Resistencia</option>
                    </select>
                    <button onclick="saveProfile()" class="btn-upgrade" style="width:100%; margin-top:10px;">COMENZAR MI TRANSFORMACIÓN 🚀</button>
                    <button onclick="window.location.href='logout.php'" style="width:100%; margin-top:20px; background:transparent; border:none; color:#666; font-size:12px;">Cerrar Sesión</button>
                </div>
            </div>
        </div>

        <!-- Dashboard Screen -->
        <div id="screen-home" class="screen">
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
            <div id="weight-check-banner" class="card hidden" style="background: linear-gradient(90deg, #E8761A 0%, #ff9a44 100%); margin-top:20px; padding:15px; border:none; color:white;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <p style="font-size:13px; font-weight:700;">⚖️ Mes cumplido! Actualiza tu peso</p>
                    <button style="background:white; color:#E8761A; border:none; padding:5px 10px; border-radius:8px; font-size:11px; font-weight:700;">ACTUALIZAR</button>
                </div>
            </div>
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-value">72</div><div class="stat-label">PESO (KG)</div></div>
                <div class="stat-item"><div class="stat-value">12%</div><div class="stat-label">GRASA</div></div>
                <div class="stat-item"><div class="stat-value">3.2k</div><div class="stat-label">KCAL HOY</div></div>
            </div>
            <div class="card coach-section">
                <h3>🗣️ RECOMENDACIÓN DEL COACH</h3>
                <p style="font-size: 14px; line-height: 1.5;">"Hoy te toca pierna. Mantén la intensidad y no olvides hidratarte cada 15 minutos."</p>
            </div>
            <div class="card">
                <h3>📅 RUTINA DE HOY</h3>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="color:var(--accent-color)">Leg Day (Piernas/Glúteo)</h4>
                        <p style="font-size: 12px; color:var(--text-secondary)">6 Ejercicios • 50 min</p>
                    </div>
                    <button class="btn-upgrade" style="margin:0; padding:8px 16px" onclick="startWorkout()">Empezar</button>
                </div>
            </div>
        </div>

        <!-- Coach AI Screen -->
        <div id="screen-coach" class="screen hidden">
            <header style="background:transparent;"><h2>Tu Staff Elite AI</h2></header>
            <div class="pill-container">
                <div class="pill active" onclick="switchCoach('psychologist')">🧠 Psicólogo</div>
                <div class="pill" onclick="switchCoach('nutritionist')">🥗 Nutricionista</div>
            </div>
            <div class="card coach-section" style="height: 60vh; display: flex; flex-direction: column;">
                <div id="chat-messages" style="flex:1; overflow-y:auto; padding-bottom:10px;">
                    <p style="background:var(--glass); padding:10px; border-radius:10px; margin-bottom:10px; font-size:14px;"><b>Coach:</b> ¡Hola! Soy tu psicólogo deportivo. ¿Cómo te sientes hoy?</p>
                </div>
                <div style="display: flex; gap: 10px; padding-top:10px; border-top:1px solid var(--glass)">
                    <input type="text" placeholder="Escribe al coach..." style="flex:1; background:#000; border:1px solid var(--glass); color:white; padding:10px; border-radius:10px;">
                    <button style="background:var(--accent-color); border:none; color:white; padding:10px 15px; border-radius:10px;">➜</button>
                </div>
            </div>
        </div>

        <!-- Progress Screen -->
        <div id="screen-progress" class="screen hidden">
            <header><h2>Tu Progreso</h2></header>
            <div class="card" style="background: var(--card-bg);">
                <div style="display:flex; justify-content:space-around; text-align:center;">
                    <div><div class="stat-value">12,450</div><div class="stat-label">KCAL TOTALES</div></div>
                    <div><div class="stat-value">14</div><div class="stat-label">SESIONES</div></div>
                </div>
            </div>
            <div class="card">
                <h3>🗓️ CALENDARIO</h3>
                <div class="calendar-grid" id="workout-calendar"></div>
            </div>
        </div>

        <nav>
            <a href="#" class="nav-item active" onclick="switchScreen('home', this)">Inicio</a>
            <a href="#" class="nav-item" onclick="switchScreen('social', this)">Comunidad</a>
            <a href="#" class="nav-item" onclick="switchScreen('coach', this)">Coach AI</a>
            <a href="#" class="nav-item" onclick="switchScreen('progress', this)">Progreso</a>
        </nav>
    </div>
    <script src="app.js"></script>
</body>
</html>
