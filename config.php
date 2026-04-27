<?php
session_start();

// --- CARGADOR DE VARIABLES DE ENTORNO (.env) ---
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'app_entrenador_fit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Limpieza automática de planes expirados (Global)
$conn->query("UPDATE users SET plan = 'gratis' WHERE plan = 'pro' AND plan_expires < NOW()");

// Configuración de APIs (Cargadas desde el Entorno)
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY'));
define('WELCOME_WEBHOOK_URL', getenv('WELCOME_WEBHOOK_URL'));
define('COACH_CHAT_WEBHOOK_URL', getenv('COACH_CHAT_WEBHOOK_URL'));
define('SUGGESTIONS_WEBHOOK_URL', getenv('SUGGESTIONS_WEBHOOK_URL') ?: 'https://agencia-ia-n8n.tjo0g6.easypanel.host/webhook/emfitpro-suggestions');
define('N8N_WEBHOOK_URL', getenv('WELCOME_WEBHOOK_URL')); // Reusamos el de bienvenida

// WOMPI CONFIGURATION
define('WOMPI_PUBLIC_KEY', getenv('WOMPI_PUBLIC_KEY'));
define('WOMPI_PRIVATE_KEY', getenv('WOMPI_PRIVATE_KEY'));
define('WOMPI_EVENTS_SECRET', getenv('WOMPI_EVENTS_SECRET'));
define('WOMPI_INTEGRITY_SECRET', getenv('WOMPI_INTEGRITY_SECRET'));
define('PRO_PLAN_PRICE_COP', getenv('PRO_PLAN_PRICE_COP') ?: 2000000);

// Función para verificar suscripción
if (!function_exists('checkUserPlan')) {
    function checkUserPlan($userId) {
        global $conn;
        $sql = "SELECT plan, plan_expires FROM users WHERE id = $userId";
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();
        
        if ($user['plan'] == 'pro' && strtotime($user['plan_expires']) > time()) {
            return 'pro';
        }
        return 'gratis';
    }
}

// Función para enviar a n8n (Entrenamientos/Rutinas)
if (!function_exists('triggerN8NWorkout')) {
    function triggerN8NWorkout($data) {
        $ch = curl_init(N8N_WEBHOOK_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}

// Función para flujo de bienvenida
// Verificación automática de tablas necesarias
$conn->query("CREATE TABLE IF NOT EXISTS user_coach_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category ENUM('entrenador', 'nutricionista', 'sicologo'),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Tabla Biblioteca de Ejercicios
$conn->query("CREATE TABLE IF NOT EXISTS exercise_library (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE,
    description TEXT,
    video_url VARCHAR(255),
    muscle_group VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insertar algunos ejemplos si la tabla está vacía
$check = $conn->query("SELECT id FROM exercise_library LIMIT 1");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO exercise_library (name, description, video_url, muscle_group) VALUES 
    ('Sentadilla Libre', 'Mantén la espalda recta y baja la cadera hasta que tus muslos estén paralelos al suelo.', 'https://www.youtube.com/embed/InVCP7870-U', 'Piernas'),
    ('Press de Banca', 'Empuja la barra hacia arriba manteniendo los codos a 45 grados de tu cuerpo.', 'https://www.youtube.com/embed/8-9-9b98dOQ', 'Pecho'),
    ('Peso Muerto', 'Mantén la barra pegada a tus piernas y la espalda neutra durante todo el movimiento.', 'https://www.youtube.com/embed/r4MzxtBKyNE', 'Espalda')");
}

// Crear tabla de Comunidad
$conn->query("CREATE TABLE IF NOT EXISTS community_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'workout',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Crear tabla de Amistades
$conn->query("CREATE TABLE IF NOT EXISTS friendships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, friend_id)
)");

// Crear tabla de Entrenamientos Completados (Log para progreso)
$conn->query("CREATE TABLE IF NOT EXISTS workouts_completed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exercises_json JSON,
    muscle_groups JSON, -- Lista de grupos musculares trabajados
    duration INT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Crear tabla de Sugerencias (Local)
$conn->query("CREATE TABLE IF NOT EXISTS user_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

if (!function_exists('triggerWelcomeToN8N')) {
    function triggerWelcomeToN8N($data) {
        $ch = curl_init(WELCOME_WEBHOOK_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
?>
