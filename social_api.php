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
    } elseif ($action === 'search_users') {
        $query = $conn->real_escape_string($input['query']);
        $sql = "SELECT id, name FROM users WHERE name LIKE '%$query%' AND id != $user_id LIMIT 10";
        $result = $conn->query($sql);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            // Verificar estado de amistad
            $friend_id = $row['id'];
            $status_sql = "SELECT status FROM friendships WHERE (user_id = $user_id AND friend_id = $friend_id) OR (user_id = $friend_id AND friend_id = $user_id)";
            $status_res = $conn->query($status_sql);
            $row['friend_status'] = $status_res->num_rows > 0 ? $status_res->fetch_assoc()['status'] : 'none';
            $users[] = $row;
        }
        echo json_encode($users);
    } elseif ($action === 'add_friend') {
        $friend_id = (int)$input['friend_id'];
        $sql = "INSERT INTO friendships (user_id, friend_id, status) VALUES ($user_id, $friend_id, 'pending') ON DUPLICATE KEY UPDATE status='pending'";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    } elseif ($action === 'accept_friend') {
        $friend_id = (int)$input['friend_id'];
        $sql = "UPDATE friendships SET status = 'accepted' WHERE (user_id = $friend_id AND friend_id = $user_id)";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    }
} else {
    // Listar posts (propios y de otros)
    $sql = "SELECT p.*, u.name as user_name 
            FROM community_posts p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC LIMIT 20";
    $result = $conn->query($sql);
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
