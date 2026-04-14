<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $goal = $_POST['goal'];

    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, age, weight, goal) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE age=?, weight=?, goal=?");
    $stmt->bind_param("iidssds", $userId, $age, $weight, $goal, $age, $weight, $goal);

    if ($stmt->execute()) {
        // Aquí podrías disparar n8n para generar la primera rutina
        triggerN8NWorkout(['userId' => $userId, 'goal' => $goal]);
        header("Location: index.html");
    }
}
?>
<!-- Frontend similar a register.php pero con más campos -->
