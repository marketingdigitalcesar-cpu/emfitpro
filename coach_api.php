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

    // Preparar el envío a n8n
    $payload = [
        'userId' => $userId,
        'userName' => $userData['name'] ?? 'Atleta',
        'role' => $data['role'] ?? 'entrenador',
        'message' => $data['message'],
        'profile' => [
            'weight' => $userData['weight'],
            'height' => $userData['height'],
            'age' => $userData['age'],
            'goal' => $userData['goal']
        ]
    ];

    // URL de n8n (Asegúrate de configurar esta constante en config.php)
    $webhook_url = defined('COACH_CHAT_WEBHOOK_URL') ? COACH_CHAT_WEBHOOK_URL : 'https://agencia-ia-n8n.tjo0g6.easypanel.host/webhook/emfitpro-coach-chat';

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode !== 200) {
        echo json_encode(['error' => 'Error comunicando con n8n', 'code' => $httpCode]);
    } else {
        echo $response;
    }
    
    curl_close($ch);
}
?>
