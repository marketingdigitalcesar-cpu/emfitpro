<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = file_get_contents('php://input');
    $data = json_decode($userInput, true);

    $apiKey = OPENAI_API_KEY;
    $url = 'https://api.openai.com/v1/chat/completions';

    $postData = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un psicólogo deportivo experto que motiva atletas basándose en su progreso.'],
            ['role' => 'user', 'content' => $data['message']]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $response = curl_exec($ch);
    echo $response;
    curl_close($ch);
}
?>
