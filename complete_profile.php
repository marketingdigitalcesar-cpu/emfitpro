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
        // Disparar n8n para generar la primera rutina
        triggerN8NWorkout(['userId' => $userId, 'goal' => $goal, 'weight' => $weight, 'height' => $height]);
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
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #000; font-family: 'Outfit', sans-serif; color: white; margin: 0; overflow-y: auto; }
        .setup-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.9)), url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=1500');
            background-size: cover; background-position: center; z-index: -1;
        }
        .container {
            display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 40px 20px;
        }
        .setup-card {
            background: rgba(26, 26, 26, 0.95); padding: 40px; border-radius: 24px;
            max-width: 450px; width: 100%; border: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
        }
        h1 { font-size: 32px; margin-bottom: 10px; color: var(--accent-color); }
        p { color: #aaa; margin-bottom: 30px; font-size: 15px; }
        
        label { display: block; margin-bottom: 8px; font-size: 14px; font-weight: 700; color: #888; }
        input, select {
            width: 100%; padding: 16px; margin-bottom: 20px; background: #000;
            border: 1px solid #333; border-radius: 12px; color: white; font-size: 16px; box-sizing: border-box;
        }
        input:focus, select:focus { border-color: var(--accent-color); outline: none; }
        
        .btn-finish {
            width: 100%; padding: 18px; background: var(--accent-color); color: white;
            border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer;
            transition: 0.3s;
        }
        .btn-finish:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(232, 118, 26, 0.3); }
        
        .promo-pill {
            display: inline-block; padding: 5px 12px; background: rgba(232, 118, 26, 0.1);
            color: var(--accent-color); border-radius: 100px; font-size: 12px; font-weight: 700;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="setup-bg"></div>
    <div class="container">
        <div class="setup-card">
            <span class="promo-pill">PASO FINAL</span>
            <h1>Casi listo, <?php echo explode(' ', $_SESSION['user_name'] ?? 'Atleta')[0]; ?></h1>
            <p>Personaliza tu experiencia para que nuestra IA diseñe el plan perfecto para ti.</p>

            <?php if($error): ?>
                <div style="background: rgba(255,0,0,0.1); color: #ff4d4d; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; border: 1px solid rgba(255,0,0,0.1);">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label>EDAD</label>
                <input type="number" name="age" placeholder="Ej: 25" required min="10" max="100">

                <div style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label>PESO (KG)</label>
                        <input type="number" step="0.1" name="weight" placeholder="Ej: 75.5" required>
                    </div>
                    <div style="flex: 1;">
                        <label>ALTURA (CM)</label>
                        <input type="number" name="height" placeholder="Ej: 175" required>
                    </div>
                </div>

                <label>TU OBJETIVO PRINCIPAL</label>
                <select name="goal" required>
                    <option value="ganar_musculo">Ganar Masa Muscular</option>
                    <option value="perder_grasa">Perder Grasa</option>
                    <option value="resistencia">Mejorar Resistencia</option>
                    <option value="salud">Salud General</option>
                </select>

                <button type="submit" class="btn-finish">Comenzar mi transformación</button>
            </form>
        </div>
    </div>
</body>
</html>
