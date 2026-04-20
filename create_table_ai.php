<?php
require 'config.php';

// Crear tabla de datos del coach
$sql = "CREATE TABLE IF NOT EXISTS user_coach_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category ENUM('entrenador', 'nutricionista', 'sicologo'),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "<div style='font-family:sans-serif; padding:20px; color: #2ecc71; border: 1px solid #2ecc71; border-radius: 8px; background: #e8f8f0;'>
            <h2>✅ ¡Éxito!</h2>
            <p>La tabla <b>user_coach_data</b> ha sido creada (o ya existía) correctamente.</p>
            <p>Tus entrenadores IA ahora pueden tener memoria persistente.</p>
            <a href='index.php' style='display:inline-block; margin-top:10px; padding:10px 20px; background:#2ecc71; color:white; text-decoration:none; border-radius:5px;'>Volver al Dashboard</a>
          </div>";
} else {
    echo "<div style='font-family:sans-serif; padding:20px; color: #e74c3c; border: 1px solid #e74c3c; border-radius: 8px; background: #fdedec;'>
            <h2>❌ Error</h2>
            <p>No se pudo crear la tabla: " . $conn->error . "</p>
          </div>";
}

$conn->close();
?>
