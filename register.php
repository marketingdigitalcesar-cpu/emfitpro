<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, plan, plan_expires) VALUES (?, ?, ?, 'pro', DATE_ADD(NOW(), INTERVAL 10 DAY))");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        // Disparar flujo de bienvenida en n8n
        triggerWelcomeToN8N(['name' => $name, 'email' => $email, 'userId' => $_SESSION['user_id']]);
        header("Location: complete_profile.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<?php require 'google_auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Registro - emfitpro</title>
    <link rel="stylesheet" href="style.css?v=1.0.1">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="hero-bg-fixed" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.9)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&q=80&w=1500') center/cover;"></div>
    <div class="overlay-gradient-fixed"></div>
    <div class="app-container" style="display: flex; align-items: center; justify-content: center; padding-bottom: 20px;">
        <div class="card" style="width: 100%; max-width: 400px; margin: 0; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.05);">
            <div style="text-align: center; margin-bottom: 30px;">
                <span class="promo-badge">🎁 10 DÍAS PLAN PRO GRATIS</span>
                <h2 style="color: white; margin-top: 0;">Únete a emfitpro</h2>
            </div>
            <form method="POST">
                <input type="text" name="name" placeholder="Tu Nombre" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button class="btn-action" type="submit">Crear Cuenta</button>
            </form>
            
            <div style="margin: 20px 0; text-align: center; color: #666; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">O continúa con</div>
            
            <a href="<?php echo getGoogleLoginUrl(); ?>" class="btn-action" style="background: white; color: black; text-decoration: none;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="20">
                Google
            </a>
            
            <p style="text-align: center; color: #666; font-size: 13px; margin-top: 25px;">
                ¿Ya tienes cuenta? <a href="index.php?login=1" style="color: var(--accent-color); font-weight: 700; text-decoration: none;">Iniciar sesión</a>
            </p>
        </div>
    </div>
</body>
</html>
