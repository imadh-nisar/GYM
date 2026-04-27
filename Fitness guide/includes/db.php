<?php
// Database connection file
$host = "localhost";
$user = "root";        // change if needed
$pass = "";            // your MySQL password
$db = "unigym";     // database name

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
  die("❌ Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);

if ($conn->connect_error) {
  die("❌ Database selection failed: " . $conn->connect_error);
}

// Ensure tables exist
$conn->query("CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  status ENUM('pending', 'approved', 'rejected', 'deleted') DEFAULT 'pending',
  weight FLOAT DEFAULT NULL,
  height FLOAT DEFAULT NULL,
  chest FLOAT DEFAULT NULL,
  waist FLOAT DEFAULT NULL,
  arms FLOAT DEFAULT NULL,
  legs FLOAT DEFAULT NULL,
  bmi FLOAT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Update existing users to approved status (for backward compatibility)
$conn->query("UPDATE users SET status = 'approved' WHERE status IS NULL OR status = ''");

$conn->query("CREATE TABLE IF NOT EXISTS workout_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  sets INT DEFAULT NULL,
  reps INT DEFAULT NULL,
  rest INT DEFAULT NULL,
  bmi_min DECIMAL(5,2) DEFAULT NULL,
  bmi_max DECIMAL(5,2) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS meal_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  bmi_min DECIMAL(5,2) DEFAULT NULL,
  bmi_max DECIMAL(5,2) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Ensure appointments table exists (for booking tracking)
$conn->query("CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    goal TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Weight history tracking table
$conn->query("CREATE TABLE IF NOT EXISTS weight_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    weight FLOAT NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Announcements table for admin-created announcements
$conn->query("CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Insert default admin if not exists
$admin_hash = password_hash('admin', PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO admins (username, email, password) VALUES ('admin', 'admin@unigym.local', '$admin_hash')");

// Insert some sample data if tables are empty
$check = $conn->query("SELECT COUNT(*) as count FROM workout_templates");
if ($check->fetch_assoc()['count'] == 0) {
  $conn->query("INSERT INTO workout_templates (title, description, sets, reps, rest, category, bmi_min, bmi_max) VALUES
      ('Push-Ups','Classic bodyweight chest and triceps exercise.',3,12,60,'Strength',18.50,29.99),
      ('Squats','Lower body strength for quads and glutes.',4,15,90,'Strength',18.50,35.00),
      ('Pull-Ups','Upper body back and biceps exercise.',3,8,90,'Strength',20.00,29.99),
      ('Plank','Isometric core stability.',3,60,45,'Core',18.50,35.00),
      ('Jump Rope','High-intensity cardio skipping.',5,60,30,'Cardio',18.50,24.99)");
}

$check = $conn->query("SELECT COUNT(*) as count FROM meal_templates");
if ($check->fetch_assoc()['count'] == 0) {
  $conn->query("INSERT INTO meal_templates (title, description, category, bmi_min, bmi_max) VALUES
      ('Oatmeal with Berries','High-fiber oats topped with berries.','Breakfast',18.50,24.99),
      ('Grilled Chicken Salad','Lean chicken with greens.','Lunch',18.50,29.99),
      ('Protein Shake','Whey protein blended with milk.','Snack',25.00,35.00),
      ('Grilled Fish with Veggies','Light fish dinner with vegetables.','Dinner',18.50,24.99)");
}

// Insert sample announcements if none exists
$check = $conn->query("SELECT COUNT(*) as count FROM announcements");
if ($check->fetch_assoc()['count'] == 0) {
  $adminId = $conn->query("SELECT id FROM admins LIMIT 1")->fetch_assoc()['id'] ?? 1;
  $conn->query("INSERT INTO announcements (title, content, is_active, created_by) VALUES 
      ('Welcome to GYMgeekS!','Welcome to your personal fitness journey! Update your measurements to get personalized workout and meal plans.',1,$adminId),
      ('New Equipment Available','Check out our new state-of-the-art cardio machines in the main gym area!',1,$adminId)");
}
?>