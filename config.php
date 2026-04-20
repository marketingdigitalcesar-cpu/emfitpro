<?php
// config.php - v2.0 - Actualizado: 2026-04-14
session_start();

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'u_tu_usuario';
$db_pass = getenv('DB_PASSWORD') ?: 'tu_contraseña';
$db_name = getenv('DB_NAME') ?: 'fitness_app';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configuración de APIs
define('OPENAI_API_KEY', 'tu_key_aqui');
define('STRIPE_SECRET_KEY', 'sk_test_..._aqui');
define('WELCOME_WEBHOOK_URL', 'https://agencia-ia-n8n.tjo0g6.easypanel.host/webhook/emfitpro-welcome');
define('COACH_CHAT_WEBHOOK_URL', 'https://agencia-ia-n8n.tjo0g6.easypanel.host/webhook/emfitpro-coach-chat');

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

// Función para enviar a n8n
if (!function_exists('triggerN8NWorkout')) {
    function triggerN8NWorkout($data) {
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];
        $context  = stream_context_create($options);
        return @file_get_contents(N8N_WEBHOOK_URL, false, $context);
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

if (!function_exists('triggerWelcomeToN8N')) {
    function triggerWelcomeToN8N($data) {
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];
        $context  = stream_context_create($options);
        return @file_get_contents(WELCOME_WEBHOOK_URL, false, $context);
    }
}
?>
