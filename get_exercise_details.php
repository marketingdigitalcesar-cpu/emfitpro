<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isset($_GET['name'])) {
    echo json_encode(['error' => 'No exercise name provided']);
    exit;
}

$name = $_GET['name'];
$stmt = $conn->prepare("SELECT * FROM exercise_library WHERE name = ? OR name LIKE ? LIMIT 1");
$searchTerm = "%$name%";
$stmt->bind_param("ss", $name, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode($data);
} else {
    // Si no lo encuentra, devolver una respuesta amable
    echo json_encode([
        'name' => $name,
        'description' => 'No tenemos una descripción específica todavía, ¡pero puedes buscar el video aquí!',
        'video_url' => 'https://www.youtube.com/results?search_query=' . urlencode('ejercicio ' . $name)
    ]);
}
?>
