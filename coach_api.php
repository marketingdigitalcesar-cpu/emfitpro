<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = file_get_contents('php://input');
    $data = json_decode($userInput, true);

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'No session']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    // Obtener datos del perfil para darle contexto a la IA
    $stmt = $conn->prepare("SELECT u.name, p.weight, p.height, p.age, p.goal FROM users u LEFT JOIN user_profiles p ON u.id = p.user_id WHERE u.id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();

    // Obtener última recomendación de este rol para memoria a largo plazo
    $current_role = $data['role'] ?? 'entrenador';
    $stmt_mem = $conn->prepare("SELECT content FROM user_coach_data WHERE user_id = ? AND category = ? ORDER BY created_at DESC LIMIT 1");
    $stmt_mem->bind_param("is", $userId, $current_role);
    $stmt_mem->execute();
    $hist_data = $stmt_mem->get_result()->fetch_assoc();
    $last_insight = $hist_data['content'] ?? 'Ninguna previa.';

    // Preparar el envío a n8n con contexto histórico
    $payload = [
        'userId' => $userId,
        'userName' => $userData['name'] ?? 'Atleta',
        'role' => $current_role,
        'message' => $data['message'],
        'lastInteraction' => $last_insight,
        'profile' => [
            'weight' => $userData['weight'],
            'height' => $userData['height'],
            'age' => $userData['age'],
            'goal' => $userData['goal']
        ]
    ];

    // URL de n8n (Asegúrate de configurar esta constante en config.php)
    $webhook_url = COACH_CHAT_WEBHOOK_URL;

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // Bypass SSL temporary for debugging
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    if ($curlError) {
        echo json_encode(['error' => 'Error de conexión cURL', 'details' => $curlError]);
    } elseif ($httpCode !== 200) {
        echo json_encode(['error' => 'n8n retornó un error', 'code' => $httpCode, 'response' => $response]);
    } else {
        echo $response;
    }
    
    curl_close($ch);
}
?>
