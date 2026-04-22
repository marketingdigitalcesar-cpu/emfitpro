<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'log_workout') {
        $exercises = json_encode($input['exercises']);
        $muscles = json_encode($input['muscles']);
        $duration = (int)$input['duration'];
        
        $stmt = $conn->prepare("INSERT INTO workouts_completed (user_id, exercises_json, muscle_groups, duration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $exercises, $muscles, $duration);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    }
} else {
    // Obtener progreso (músculos trabajados)
    $sql = "SELECT muscle_groups FROM workouts_completed WHERE user_id = $user_id ORDER BY completed_at DESC LIMIT 50";
    $result = $conn->query($sql);
    
    $muscle_counts = [];
    while ($row = $result->fetch_assoc()) {
        $groups = json_decode($row['muscle_groups'], true);
        if (is_array($groups)) {
            foreach ($groups as $muscle) {
                $muscle = strtolower(trim($muscle));
                $muscle_counts[$muscle] = ($muscle_counts[$muscle] ?? 0) + 1;
            }
        }
    }
    
    echo json_encode([
        'muscle_counts' => $muscle_counts,
        'total_workouts' => $result->num_rows
    ]);
}
?>
