<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Desactivar visualización de errores inline para que no rompan el JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}

$user_id = $_SESSION['user_id'];
// Intentar obtener el nombre de varias fuentes de la sesión
$user_name = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? $_SESSION['display_name'] ?? 'Miembro Emfitpro';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'post_workout') {
        $content = $conn->real_escape_string($input['content']);
        $sql = "INSERT INTO community_posts (user_id, user_name, content) VALUES ($user_id, '$user_name', '$content')";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    }
} else {
    // Listar posts
    $result = $conn->query("SELECT * FROM community_posts ORDER BY created_at DESC LIMIT 20");
    if (!$result) {
        die(json_encode(['error' => $conn->error]));
    }
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    echo json_encode($posts);
}
?>
