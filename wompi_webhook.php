<?php
// wompi_webhook.php - Procesa las notificaciones de pago de Wompi
require_once 'config.php';

// 1. Obtener el cuerpo de la petición
$raw_body = file_get_contents('php://input');
$data = json_decode($raw_body, true);

if (!$data || !isset($data['data']['transaction'])) {
    http_response_code(400);
    exit("Invalid data");
}

$transaction = $data['data']['transaction'];
$id = $transaction['id'];
$status = $transaction['status'];
$amount_in_cents = $transaction['amount_in_cents'];
$reference = $transaction['reference'];
$timestamp = $data['timestamp'];
$signature = $data['signature']['checksum'];

// 2. Verificar la firma de seguridad (Evento)
// Orden: id + status + amount_in_cents + timestamp + secret_eventos
$concat = $id . $status . $amount_in_cents . $timestamp . WOMPI_EVENTS_SECRET;
$expected_signature = hash('sha256', $concat);

if ($signature !== $expected_signature) {
    error_log("Firma de Wompi inválida. Esperada: $expected_signature, Recibida: $signature");
    http_response_code(401);
    exit("Invalid signature");
}

// 3. Procesar si el estado es APPROVED
if ($status === 'APPROVED') {
    // Extraer User ID de la referencia (Formato: PRO-USERID-TIMESTAMP)
    $parts = explode('-', $reference);
    if (count($parts) >= 2) {
        $userId = intval($parts[1]);
        
        // Actualizar el plan del usuario a PRO por 30 días
        $stmt = $conn->prepare("UPDATE users SET plan = 'pro', plan_expires = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            error_log("Pago aprobado para usuario $userId. Plan actualizado a PRO.");
            
            // Opcional: Avisar a n8n que el pago fue exitoso
            // triggerN8NWorkout(['userId' => $userId, 'event' => 'payment_success']);
        }
    }
}

http_response_code(200);
echo "OK";
?>
