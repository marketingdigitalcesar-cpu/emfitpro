<?php
// google_callback.php - Procesa el retorno de Google OAuth
require_once 'config.php';
require_once 'google_auth.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // 1. Intercambiar el código por un Token de Acceso
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
    
    if (!isset($data['access_token'])) {
        die("Error al obtener el access token. Verifica tu Client ID y Secret.");
    }

    $access_token = $data['access_token'];

    // 2. Obtener datos del usuario desde Google API
    $ch = curl_init('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $access_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $user_info_response = curl_exec($ch);
    $google_user = json_decode($user_info_response, true);

    if (!$google_user || !isset($google_user['email'])) {
        die("Error al obtener la información del usuario de Google.");
    }

    $google_id = $google_user['id'];
    $email = $google_user['email'];
    $name = $google_user['name'];

    // 3. Verificar si el usuario ya existe en la base de datos
    // Primero intentamos buscar por email
    $stmt = $conn->prepare("SELECT id, google_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // El usuario ya existe, iniciamos sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_plan'] = $user['plan'];
        
        // Si no tiene el google_id guardado, lo actualizamos
        if (empty($user['google_id'])) {
            $update = $conn->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $update->bind_param("si", $google_id, $user['id']);
            $update->execute();
        }
        
        header("Location: index.php");
        exit();
    } else {
        // Usuario nuevo: Registrarlo con 10 días de Plan Pro Gratis
        $stmt = $conn->prepare("INSERT INTO users (name, email, google_id, plan, plan_expires) VALUES (?, ?, ?, 'pro', DATE_ADD(NOW(), INTERVAL 10 DAY))");
        $stmt->bind_param("sss", $name, $email, $google_id);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_plan'] = 'pro';
            header("Location: complete_profile.php"); // Enviamos a completar perfil si es nuevo
            exit();
        } else {
            die("Error al crear la cuenta con Google.");
        }
    }
} else {
    // Si no hay código, redirigir al inicio
    header("Location: index.php");
    exit();
}
?>
