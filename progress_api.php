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

    if ($action === 'sync_from_social') {
        // Contar cuántos posts tiene el usuario
        $stmt = $conn->prepare("SELECT COUNT(*) as post_count FROM community_posts WHERE user_id = ? AND content LIKE '%completado una rutina%'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $post_count = $stmt->get_result()->fetch_assoc()['post_count'];
        
        // Contar cuántas sesiones ya tiene registradas
        $stmt = $conn->prepare("SELECT COUNT(*) as session_count FROM workouts_completed WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $session_count = $stmt->get_result()->fetch_assoc()['session_count'];
        
        // Si hay más posts que sesiones, crear las faltantes como "General"
        if ($post_count > $session_count) {
            $diff = $post_count - $session_count;
            for ($i = 0; $i < $diff; $i++) {
                $empty_json = json_encode([]);
                $general_muscles = json_encode(['general']);
                $stmt = $conn->prepare("INSERT INTO workouts_completed (user_id, exercises_json, muscle_groups, duration) VALUES (?, ?, ?, 30)");
                $stmt->bind_param("iss", $user_id, $empty_json, $general_muscles);
                $stmt->execute();
            }
            echo json_encode(['status' => 'success', 'synced' => $diff]);
        } else {
            echo json_encode(['status' => 'already_synced']);
        }
    }
    elseif ($action === 'log_workout') {
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
