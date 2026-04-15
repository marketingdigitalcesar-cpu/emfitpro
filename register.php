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
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .form-card { width: 100%; max-width: 350px; background: var(--card-bg); padding: 30px; border-radius: 20px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 10px; border: 1px solid var(--glass); background: #000; color: white; }
    </style>
</head>
<body>
    <div class="form-card">
        <div style="text-align:center; margin-bottom:20px;">
            <h2 style="margin-bottom:5px;">Únete a emfitpro</h2>
            <div style="display:inline-block; background:rgba(232, 118, 26, 0.2); color:var(--accent-color); padding:5px 12px; border-radius:10px; font-size:12px; font-weight:700; border:1px solid var(--accent-color);">
                🎁 10 DÍAS DE PRO GRATIS
            </div>
        </div>
        <form method="POST">
            <input type="text" name="name" placeholder="Tu Nombre" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button class="btn-upgrade" style="width:100%" type="submit">Crear Cuenta</button>
            
            <div style="margin: 20px 0; text-align:center; color: #666; font-size:12px;">O TAMBIÉN</div>
            
            <button type="button" onclick="window.location.href='<?php echo getGoogleLoginUrl(); ?>'" style="width:100%; padding:12px; border-radius:12px; border:1px solid #444; background:white; color:#000; font-weight:700; display:flex; align-items:center; justify-content:center; gap:10px; cursor:pointer;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="20">
                Entrar con Google
            </button>
        </form>
    </div>
</body>
</html>
