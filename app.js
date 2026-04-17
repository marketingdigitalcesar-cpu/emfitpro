// Avatar Dropdown Toggle
function toggleDropdown() {
    document.getElementById("profile-dropdown").classList.toggle("show");
}

// Close dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.matches('.avatar-circle')) {
        const dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            const openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}

// Screen Management
function switchScreen(screenId, el) {
    // Hide all screens
    document.querySelectorAll('.screen').forEach(s => s.classList.add('hidden'));
    
    // Show target screen
    const target = document.getElementById(`screen-${screenId}`);
    if (target) target.classList.remove('hidden');
    
    // Update navigation UI
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    if (el && el.classList.contains('nav-item')) {
        el.classList.add('active');
    }

    // Background logic
    const bg = document.getElementById('bg-image');
    if (screenId === 'home') bg.src = 'assets/hero-home.png';
    else if (screenId === 'social') bg.src = 'https://images.unsplash.com/photo-1574673139084-c2a7df98bc53?auto=format&fit=crop&q=80&w=1000';
    else if (screenId === 'coach') bg.src = 'assets/hero-psych.png';
    else if (screenId === 'progress') bg.src = 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=1000';

    // Populate settings if needed
    if (screenId === 'settings') {
        document.getElementById('edit-weight').value = currentUser.stats.weight;
        document.getElementById('edit-goal').value = currentUser.stats.goal;
    }

    if (screenId === 'progress') generateCalendar();
}

// Coach AI Logic
let currentCoach = 'nutritionist';
function switchCoach(type) {
    currentCoach = type;
    document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
    event.target.classList.add('active');
    
    const header = document.querySelector('#screen-coach h2');
    const bg = document.getElementById('bg-image');
    
    if (type === 'nutritionist') {
        header.innerText = "Tu Nutricionista AI";
        bg.src = 'assets/hero-nutri.png';
    } else {
        header.innerText = "Tu Psicólogo AI";
        bg.src = 'assets/hero-psych.png';
    }
}

function sendMessageToCoach() {
    const input = document.getElementById('coach-input');
    const msg = input.value.trim();
    if (!msg) return;

    const chat = document.getElementById('chat-messages');
    chat.innerHTML += `<p style="text-align:right; margin-bottom:10px;"><b>Tú:</b> ${msg}</p>`;
    chat.innerHTML += `<p id="loading-ia" style="font-size:12px; opacity:0.6;">⏳ Procesando con n8n...</p>`;
    
    input.value = "";
    chat.scrollTop = chat.scrollHeight;

    // Conexión real con n8n
    fetch('https://agencia-ia-n8n.tjo0g6.easypanel.host/webhook/emfitpro-ai', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            message: msg,
            coach_type: currentCoach,
            user_id: currentUser.id,
            user_name: currentUser.name
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('loading-ia').remove();
        const reply = data.output || "¡Hola! Estoy listo para ayudarte con eso.";
        chat.innerHTML += `<p style="background:var(--glass); padding:10px; border-radius:10px; margin-bottom:10px;"><b>AI:</b> ${reply}</p>`;
        chat.scrollTop = chat.scrollHeight;
    })
    .catch(err => {
        document.getElementById('loading-ia').innerHTML = "⚠️ Error de conexión con el servidor.";
    });
}

// Workout Flow
function startWorkout() {
    document.getElementById('time-selector-overlay').classList.remove('hidden');
}

function setWorkoutDuration(mins) {
    document.getElementById('time-selector-overlay').classList.add('hidden');
    alert(`Generando rutina de ${mins} minutos enfocada en ${currentUser.stats.goal}...`);
    // Aquí se podría disparar un webhook a n8n para traer ejercicios reales
}

function saveProfileFromSettings() {
    const newWeight = document.getElementById('edit-weight').value;
    const newGoal = document.getElementById('edit-goal').value;

    alert("Guardando cambios en la nube...");
    // Simulación de guardado (debería ir a un PHP endpoint)
    currentUser.stats.weight = newWeight;
    currentUser.stats.goal = newGoal;
    
    document.querySelector('.stat-value').innerText = newWeight;
    switchScreen('home', document.querySelector('.nav-item'));
}

function generateCalendar() {
    const calendar = document.getElementById('workout-calendar');
    calendar.innerHTML = "";
    const today = new Date().getDate();
    for (let i = 1; i <= 30; i++) {
        const isToday = i === today;
        const isCompleted = [2, 5, 8, 12, 14, 15].includes(i);
        calendar.innerHTML += `
            <div class="calendar-day ${isToday ? 'today' : ''} ${isCompleted ? 'completed' : ''}">
                ${i}
            </div>
        `;
    }
}

// Header Dynamic Background
window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    if (window.scrollY > 20) header.classList.add('scrolled');
    else header.classList.remove('scrolled');
});

// Init
generateCalendar();
