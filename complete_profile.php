<?php
require 'config.php';

// Si no hay sesión, al index
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $age = $_POST['age'] ?? 0;
    $weight = $_POST['weight'] ?? 0;
    $height = $_POST['height'] ?? 0;
    $goal = $_POST['goal'] ?? 'ganar_musculo';

    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, age, weight, height, goal) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE age=?, weight=?, height=?, goal=?");
    $stmt->bind_param("idddssdds", $userId, $age, $weight, $height, $goal, $age, $weight, $height, $goal);

    if ($stmt->execute()) {
        // Obtener nombre y correo para el mail de bienvenida
        $userQuery = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
        $userQuery->bind_param("i", $userId);
        $userQuery->execute();
        $userData = $userQuery->get_result()->fetch_assoc();

        // Disparar n8n para generar la primera rutina y enviar bienvenida
        triggerN8NWorkout([
            'userId' => $userId, 
            'name'   => $userData['name'] ?? 'Atleta',
            'email'  => $userData['email'] ?? '',
            'goal'   => $goal, 
            'weight' => $weight, 
            'height' => $height
        ]);
        header("Location: index.php");
        exit();
    } else {
        $error = "Ocurrió un error al guardar tu perfil.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>emfitpro | Completa tu Perfil</title>
    <link rel="stylesheet" href="style.css?v=1.0.1">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="hero-bg-fixed" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.9)), url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=1500') center/cover;"></div>
    <div class="overlay-gradient-fixed"></div>
    <div class="app-container" style="display: flex; align-items: center; justify-content: center; padding-bottom: 20px;">
        <div class="setup-card" style="margin-top: 20px;">
            <span class="promo-badge">PASO FINAL</span>
            <h1 style="color: var(--accent-color); font-size: 32px; margin-bottom: 10px;">Casi listo, <?php echo explode(' ', $_SESSION['user_name'] ?? 'Atleta')[0]; ?></h1>
            <p style="color: #aaa; margin-bottom: 30px; font-size: 15px;">Personaliza tu experiencia para que nuestra IA diseñe el plan perfecto para ti.</p>

            <?php if($error): ?>
                <div style="background: rgba(255,0,0,0.1); color: #ff4d4d; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(255,0,0,0.2);">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase;">EDAD</label>
                <input type="number" name="age" placeholder="Ej: 25" required min="10" max="100">

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase;">PESO (KG)</label>
                        <input type="number" step="0.1" name="weight" placeholder="Ej: 75.5" required>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase;">ALTURA (CM)</label>
                        <input type="number" name="height" placeholder="Ej: 175" required>
                    </div>
                </div>

                <label style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: #888; text-transform: uppercase;">TU OBJETIVO PRINCIPAL</label>
                <select name="goal" required>
                    <option value="ganar_musculo">Ganar Masa Muscular</option>
                    <option value="perder_grasa">Perder Grasa</option>
                    <option value="resistencia">Mejorar Resistencia</option>
                    <option value="salud">Salud General</option>
                </select>

                <button type="submit" class="btn-upgrade" style="width: 100%; margin-top: 10px;">Comenzar mi transformación</button>
            </form>
        </div>
    </div>
</body>
</html>
