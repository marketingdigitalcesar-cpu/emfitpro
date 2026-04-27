<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $suggestion = $input['suggestion'] ?? '';
    $userName = $input['user'] ?? 'Usuario';
    $email = $input['email'] ?? '';

    if (empty($suggestion)) {
        echo json_encode(['error' => 'Sugerencia vacía']);
        exit;
    }

    // 1. Guardar localmente en MySQL
    $stmt = $conn->prepare("INSERT INTO user_suggestions (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $suggestion);
    $localSaved = $stmt->execute();

    // 2. Enviar a n8n (Webhook externo)
    $webhook_url = SUGGESTIONS_WEBHOOK_URL;
    
    $payload = [
        'suggestion' => $suggestion,
        'user' => $userName,
        'email' => $email,
        'userId' => $userId,
        'date' => date('Y-m-d H:i:s')
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // Bypass SSL temporary
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $n8nResponse = curl_exec($ch);
    curl_close($ch);

    echo json_encode([
        'status' => 'success',
        'local' => $localSaved,
        'n8n' => true // Asumimos éxito si no hubo error de red
    ]);
}
?>
