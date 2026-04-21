<?php
// pay.php - Página de pago Premium con Wompi
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = ""; // Deberíamos obtenerlo de la DB

// Obtener email del usuario
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($u = $res->fetch_assoc()) {
    $user_email = $u['email'];
}

// Configurar transacción
$amount = PRO_PLAN_PRICE_COP;
$currency = "COP";
$reference = "PRO-" . $user_id . "-" . time();

// GENERAR FIRMA DE INTEGRIDAD (Obligatoria para Wompi)
// Concatenar: referencia + monto_en_centavos + moneda + secreto_integridad
$concat = $reference . $amount . $currency . WOMPI_INTEGRITY_SECRET;
$integrity_signature = hash('sha256', $concat);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>emfitpro | Membresía PRO</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #000; font-family: 'Outfit', sans-serif; color: white; margin: 0; }
        .pay-container {
            max-width: 500px; margin: 60px auto; padding: 40px;
            background: rgba(26, 26, 26, 0.95); border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.05); text-align: center;
            backdrop-filter: blur(10px);
        }
        .icon-crown { font-size: 50px; margin-bottom: 20px; display: block; }
        h1 { font-size: 32px; color: var(--accent-color); margin-bottom: 10px; }
        .price { font-size: 48px; font-weight: 700; margin: 30px 0; }
        .price span { font-size: 20px; color: #888; }
        
        .benefits { text-align: left; margin-bottom: 40px; }
        .benefits li { margin-bottom: 15px; list-style: none; display: flex; align-items: center; color: #ccc; }
        .benefits li::before { content: '✓'; color: var(--accent-color); font-weight: bold; margin-right: 15px; }
        
        form button { cursor: pointer; }
    </style>
</head>
<body>
    <div class="pay-container">
        <span class="icon-crown">👑</span>
        <h1>Hazte PRO</h1>
        <p>Desbloquea tu máximo potencial con nuestra IA avanzada.</p>

        <div class="price">
            $20.000 <span>/ mes</span>
        </div>

        <ul class="benefits">
            <li>Rutinas 100% personalizadas por IA</li>
            <li>Consultas ilimitadas con tu Coach IA</li>
            <li>Plan nutricional inteligente</li>
            <li>Seguimiento de progreso detallado</li>
        </ul>

        <!-- BOTÓN DE WOMPI -->
        <form>
            <script
                src="https://checkout.wompi.co/widget.js"
                data-render="button"
                data-public-key="<?php echo WOMPI_PUBLIC_KEY; ?>"
                data-currency="<?php echo $currency; ?>"
                data-amount-in-cents="<?php echo $amount; ?>"
                data-reference="<?php echo $reference; ?>"
                data-signature:integrity="<?php echo $integrity_signature; ?>"
                data-customer-email="<?php echo $user_email; ?>"
                data-redirect-url="https://emfitpro.com/index.php?status=pending"
            ></script>
        </form>
        
        <p style="margin-top: 20px; font-size: 12px; color: #555;">Pago seguro procesado por Wompi Bancolombia</p>
    </div>
</body>
</html>
