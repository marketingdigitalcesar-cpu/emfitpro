// State Management
let currentUser = JSON.parse(localStorage.getItem('emfitpro_user')) || {
    name: "Atleta",
    plan: "gratis",
    profileSet: false,
    stats: {
        age: 0,
        weight: 72,
        height: 170,
        goal: "ganar_musculo",
        equipment: "gym",
        injuries: "ninguna"
    }
};

function saveProfile() {
    currentUser.name = document.getElementById('setup-name').value || "Atleta";
    currentUser.stats.age = document.getElementById('setup-age').value;
    currentUser.stats.weight = document.getElementById('setup-weight').value;
    currentUser.stats.height = document.getElementById('setup-height').value;
    currentUser.stats.goal = document.getElementById('setup-goal').value;
    currentUser.stats.equipment = document.getElementById('setup-equipment').value;
    currentUser.stats.injuries = document.getElementById('setup-injuries').value;
    currentUser.profileSet = true;

    localStorage.setItem('emfitpro_user', JSON.stringify(currentUser));
    
    // UI Updates
    document.getElementById('user-name').innerText = currentUser.name;
    document.getElementById('onboarding-overlay').style.display = 'none';
    updateProUI();
    
    // ENVIAR A n8n (Simulado por ahora, conectar a OpenAI vía Webhook)
    console.log("Enviando datos a la IA de n8n para generar plan personalizado...");
    // fetch('https://n8n.kuepa.com/webhook/fitness-ai', { method: 'POST', body: JSON.stringify(currentUser) });
    
    alert("¡Perfil Guardado! Nuestra IA está preparando tu plan personalizado.");
}

function checkOnboarding() {
    if (!currentUser.profileSet) {
        document.getElementById('onboarding-overlay').style.display = 'block';
    } else {
        document.getElementById('onboarding-overlay').style.display = 'none';
        document.getElementById('user-name').innerText = currentUser.name;
    }
}

function switchScreen(screenId, el) {
    // Hide all screens
    document.querySelectorAll('.screen').forEach(s => s.classList.add('hidden'));
    
    // Show target screen
    document.getElementById(`screen-${screenId}`).classList.remove('hidden');
    
    // Update navigation UI
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    el.classList.add('active');

    // Update Immersive Background
    const bg = document.getElementById('bg-image');
    if (screenId === 'home') bg.src = 'assets/hero-home.png';
    else if (screenId === 'coach') bg.src = 'assets/hero-psych.png';
    else if (screenId === 'social') bg.src = 'https://images.unsplash.com/photo-1574673139084-c2a7df98bc53?auto=format&fit=crop&q=80&w=1000';
    else if (screenId === 'progress') bg.src = 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=1000';

    // Handle Pro features visualization
    updateProUI();

    // Pre-fill settings if going to settings
    if (screenId === 'settings') {
        document.getElementById('edit-age').value = currentUser.stats.age;
        document.getElementById('edit-weight').value = currentUser.stats.weight;
        document.getElementById('edit-height').value = currentUser.stats.height;
        document.getElementById('edit-equipment').value = currentUser.stats.equipment;
        document.getElementById('edit-injuries').value = currentUser.stats.injuries;
    }
}

function saveProfileFromSettings() {
    currentUser.stats.age = document.getElementById('edit-age').value;
    currentUser.stats.weight = document.getElementById('edit-weight').value;
    currentUser.stats.height = document.getElementById('edit-height').value;
    currentUser.stats.equipment = document.getElementById('edit-equipment').value;
    currentUser.stats.injuries = document.getElementById('edit-injuries').value;
    
    // Update main dashboard values if changed
    const statValues = document.querySelectorAll('.stat-value');
    if (statValues.length >= 2) {
        statValues[0].innerText = currentUser.stats.weight;
    }

    localStorage.setItem('emfitpro_user', JSON.stringify(currentUser));
    alert("¡Perfil Actualizado! Tu IA se ha sincronizado.");
}

function switchCoach(type) {
    document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
    event.target.classList.add('active');
    
    const bg = document.getElementById('bg-image');
    const chatHeader = document.querySelector('#screen-coach h2');
    const chatMessages = document.getElementById('chat-messages');
    
    chatMessages.innerHTML = `<p style="background:var(--glass); padding:10px; border-radius:10px; margin-bottom:10px; font-size:12px; opacity:0.7;">⏳ Sincronizando con Agente de IA en n8n...</p>`;

    if (type === 'nutritionist') {
        bg.src = 'assets/hero-nutri.png';
        chatHeader.innerText = "Tu Nutricionista AI";
    } else {
        bg.src = 'assets/hero-psych.png';
        chatHeader.innerText = "Tu Psicólogo AI";
    }

    // Call real n8n API
    fetch('https://n8n.kuepa.com/webhook/emfitpro-ai', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            message: "Hola, preséntate y dame un consejo inicial basado en mi perfil.",
            type: type,
            user: currentUser
        })
    })
    .then(res => res.json())
    .then(data => {
        const response = data.output || data[0]?.output || "Lo siento, la IA está descansando. Intenta de nuevo.";
        chatMessages.innerHTML = `<p style="background:var(--glass); padding:10px; border-radius:10px; margin-bottom:10px; font-size:14px;">
            <b>${chatHeader.innerText}:</b> ${response}
        </p>`;
    })
    .catch(err => {
        chatMessages.innerHTML = `<p style="color:red; background:rgba(255,0,0,0.1); padding:10px; border-radius:10px;">⚠️ Error de conexión con n8n. Verifica tu API Key.</p>`;
    });
}

function updateProUI() {
    const isPro = currentUser.plan === 'pro';
    
    // Coach AI Lock
    const coachLock = document.getElementById('pro-lock');
    if (coachLock) {
        if (isPro) coachLock.classList.add('hidden');
        else coachLock.classList.remove('hidden');
    }

    // Google Ads
    const adsBanner = document.getElementById('google-ads');
    if (adsBanner) {
        if (isPro) adsBanner.classList.add('hidden');
        else adsBanner.classList.remove('hidden');
    }

    // History Lock
    const historyLock = document.getElementById('history-lock');
    if (historyLock) {
        historyLock.style.display = isPro ? 'none' : 'block';
    }

    // Muscle Map Lock
    const muscleLock = document.getElementById('muscle-lock');
    if (muscleLock) {
        if (isPro) muscleLock.classList.add('hidden');
        else muscleLock.classList.remove('hidden');
    }

    // Plan Tag
    const planTag = document.getElementById('user-plan');
    if (planTag) {
        planTag.innerText = isPro ? "PRO" : "GRATIS";
        planTag.style.background = isPro ? "gold" : "var(--accent-color)";
        planTag.style.color = isPro ? "black" : "white";
    }
}

// Service Worker for PWA
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js')
      .then(reg => console.log('SW Registered!', reg))
      .catch(err => console.log('SW Error', err));
}

function startWorkout() {
    document.getElementById('time-selector-overlay').classList.remove('hidden');
}

let currentExercises = [];
let currentIndex = 0;

function setWorkoutDuration(mins) {
    document.getElementById('time-selector-overlay').classList.add('hidden');
    
    // Generar ejercicios ficticios para la demo
    currentExercises = [
        { name: "Sentadillas (Squats)", reps: "4 Series x 12 Reps" },
        { name: "Zancadas (Lunges)", reps: "3 Series x 10 Reps" },
        { name: "Puente Glúteo", reps: "4 Series x 15 Reps" }
    ];
    if (mins > 15) currentExercises.push({ name: "Peso Muerto Rumano", reps: "3 Series x 12 Reps" });
    if (mins > 30) currentExercises.push({ name: "Elevación de Talón", reps: "4 Series x 20 Reps" });

    // Mostrar resumen
    switchScreen('routine-overview', document.querySelector('.nav-item'));
    document.getElementById('overview-title').innerText = `Rutina Pierna (${mins} min)`;
    
    const list = document.getElementById('overview-exercises-list');
    list.innerHTML = "";
    currentExercises.forEach(ex => {
        list.innerHTML += `<div style="padding:15px 0; border-bottom:1px solid var(--glass); display:flex; justify-content:space-between;">
            <span>${ex.name}</span> <span style="color:var(--accent-color); font-size:12px;">${ex.reps}</span>
        </div>`;
    });
}

function initiateWorkoutFinal() {
    if (currentUser.plan === 'gratis') {
        runAdFlow();
    } else {
        startPlayer();
    }
}

function runAdFlow() {
    const adOverlay = document.getElementById('ad-video-overlay');
    const timerText = document.getElementById('ad-timer-text');
    adOverlay.classList.remove('hidden');
    
    let countdown = 5;
    const interval = setInterval(() => {
        countdown--;
        timerText.innerText = `El entrenamiento empieza en ${countdown}s...`;
        if (countdown <= 0) {
            clearInterval(interval);
            adOverlay.classList.add('hidden');
            startPlayer();
        }
    }, 1000);
}

function startPlayer() {
    currentIndex = 0;
    switchScreen('workout-play', document.querySelector('.nav-item'));
    updateExerciseUI();
    startTimer();
}

function updateExerciseUI() {
    const ex = currentExercises[currentIndex];
    document.getElementById('exercise-name').innerText = ex.name;
    document.getElementById('exercise-reps').innerText = ex.reps;
    document.getElementById('exercise-count').innerText = `Ejercicio ${currentIndex + 1} de ${currentExercises.length}`;
    document.getElementById('exercise-progress').style.width = `${((currentIndex + 1) / currentExercises.length) * 100}%`;
}

function nextExercise() {
    if (currentIndex < currentExercises.length - 1) {
        currentIndex++;
        updateExerciseUI();
    } else {
        clearInterval(workoutTimer);
        document.getElementById('success-modal').classList.remove('hidden');
    }
}

function shareWorkout() {
    // 1. PUBLICAR EN LA COMUNIDAD INTERNA
    const feed = document.getElementById('feed-container');
    const newPost = document.createElement('div');
    newPost.className = 'card';
    newPost.style.margin = '0 0 15px 0';
    newPost.innerHTML = `
        <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
            <div style="width:30px; height:30px; border-radius:50%; background:var(--accent-color);"></div>
            <span style="font-size:14px; font-weight:700;">${currentUser.name} (Tú)</span>
            <span style="font-size:10px; color:var(--text-secondary)">recién publicado</span>
        </div>
        <p style="font-size:14px; margin-bottom:10px;">¡Nueva rutina completada! 🦵🔥 Superando mis límites cada día.</p>
        <div style="background:var(--glass); border-radius:10px; padding:10px; display:flex; justify-content:space-between;">
            <span>⏱️ 5-30m</span>
            <span>🔥 350 kcal</span>
        </div>
    `;
    
    // Insertar al principio del feed
    feed.prepend(newPost);
    
    alert("¡Publicado en la comunidad interna! 🏁");
    document.getElementById('success-modal').classList.add('hidden');

    // 2. COMPARTIR EN HISTORIAS / EXTERNO
    setTimeout(() => {
        const text = `¡Acabo de terminar mi rutina en emfitpro! 🔥 350 kcal menos. ¡Únete a mi equipo!`;
        if (navigator.share) {
            navigator.share({ title: 'Logro emfitpro', text: text, url: 'https://tuapp.com' })
            .catch(console.error);
        }
        switchScreen('social', document.querySelectorAll('.nav-item')[1]);
    }, 1000);
}

function shareProfile() {
    const text = `¡Entrena conmigo en emfitpro! La mejor app con coaches de IA.`;
    if (navigator.share) {
        navigator.share({ title: 'Invitar a emfitpro', text: text, url: window.location.href });
    } else {
        alert("Enlace de invitación copiado al portapapeles 📋");
    }
}

let workoutTimer;
function startTimer() {
    let seconds = 0;
    const timerDisplay = document.getElementById('timer');
    clearInterval(workoutTimer);
    workoutTimer = setInterval(() => {
        seconds++;
        let mins = Math.floor(seconds / 60).toString().padStart(2, '0');
        let secs = (seconds % 60).toString().padStart(2, '0');
        timerDisplay.innerText = `${mins}:${secs}`;
    }, 1000);
}

function checkWeightReminder() {
    // Simulación: Solo se muestra si pasaron 30 días desde el registro
    const registerDate = new Date("2026-03-10"); // Fecha ejemplo de hace un mes
    const today = new Date();
    const diffTime = Math.abs(today - registerDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays >= 30) {
        document.getElementById('weight-check-banner').classList.remove('hidden');
    }
}

function generateCalendar() {
    const calendar = document.getElementById('workout-calendar');
    if (!calendar) return;
    
    calendar.innerHTML = "";
    // Días de ejemplo completados
    const completedDays = [2, 5, 8, 10, 12, 13]; 
    const today = new Date().getDate();

    for (let i = 1; i <= 30; i++) {
        const isCompleted = completedDays.includes(i);
        const isToday = i === today;
        
        calendar.innerHTML += `
            <div class="calendar-day ${isCompleted ? 'completed' : ''} ${isToday ? 'today' : ''}">
                ${i}
            </div>
        `;
    }
}

// Header Dynamic Background
window.addEventListener('scroll', () => {
    const headers = document.querySelectorAll('header');
    headers.forEach(header => {
        if (window.scrollY > 20) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
});

// Initial UI Update
checkOnboarding();
updateProUI();
checkWeightReminder();
generateCalendar();
