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
define('WELCOME_WEBHOOK_URL', 'https://agencia-ia-n8n.tjo0g6.easypanel.host/webhook-test/emfitpro-welcome');
define('COACH_CHAT_WEBHOOK_URL', 'https://n8n.kuepa.com/webhook/emfitpro-coach-chat');

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
