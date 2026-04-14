<?php
require 'config.php';

// CONFIGURACIÓN DE GOOGLE (Cargada desde variables de entorno por seguridad)
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '263595625424-fo2fprj8q12k0hparf7k5t6n5kdkt8lm.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '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');
// Determinar la URL base automáticamente (localhost o dominio real)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
define('GOOGLE_REDIRECT_URL', $protocol . '://' . $host . '/google_callback.php');

// Generar URL para el botón
function getGoogleLoginUrl() {
    $params = [
        'response_type' => 'code',
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URL,
        'scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
        'access_type' => 'offline',
        'prompt' => 'select_account'
    ];
    return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
}

// PROCESAR RESPUESTA DE GOOGLE (google_callback.php)
// Nota: En un entorno real, este código iría en un archivo llamado google_callback.php
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // 1. Intercambiar el código por un Token
    $post_data = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URL,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    $access_token = $data['access_token'];

    // 2. Obtener datos del usuario (Nombre y Email)
    $ch = curl_init('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $access_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $user_info_response = curl_exec($ch);
    $user = json_decode($user_info_response, true);

    // 3. Registrar o Iniciar Sesión en tu Base de Datos
    $email = $user['email'];
    $name = $user['name'];
    
    // Aquí pondríamos la lógica de MySQL para guardar al usuario
    // Por ahora, simulamos que ya entró:
    $_SESSION['user_id'] = 1; // ID ficticio
    $_SESSION['user_name'] = $name;
    
    header("Location: index.html");
    exit();
}
?>
