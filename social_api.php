<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'No autorizado']));
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Usuario';

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
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $result = $conn->query("SELECT * FROM community_posts ORDER BY created_at DESC LIMIT $limit");
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    echo json_encode($posts);
}
?>
