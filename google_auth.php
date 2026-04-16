<?php
// google_auth.php - Configuración de Google Login
require_once 'config.php';

// CONFIGURACIÓN DE GOOGLE
// IMPORTANTE: Debes configurar estas variables en el panel de Entorno de Easypanel
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET'));

// Determinar la URL de redirección automáticamente (Forzamos https para producción)
$protocol = "https";
$host = $_SERVER['HTTP_HOST'];
define('GOOGLE_REDIRECT_URL', $protocol . '://' . $host . '/google_callback.php');

/**
 * Genera la URL de autorización de Google
 */
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
?>
