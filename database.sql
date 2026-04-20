CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    google_id VARCHAR(100) UNIQUE,
    plan ENUM('gratis', 'pro') DEFAULT 'gratis',
    plan_expires DATETIME,
    stripe_customer_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INT PRIMARY KEY,
    age INT,
    weight FLOAT,
    height FLOAT,
    goal VARCHAR(50),
    experience VARCHAR(50),
    equipment VARCHAR(100),
    days_per_week INT,
    injuries TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    routine_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    workout_id INT,
    duration INT,
    calories INT,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS user_coach_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category ENUM('entrenador', 'nutricionista', 'sicologo'),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
