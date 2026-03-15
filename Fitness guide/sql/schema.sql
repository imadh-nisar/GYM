-- Create database
CREATE DATABASE uni_gym;
USE uni_gym;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    weight FLOAT,
    height FLOAT,
    chest FLOAT,
    waist FLOAT,
    arms FLOAT,
    legs FLOAT,
    bmi FLOAT,
    plan VARCHAR(255),
    meals TEXT,
    workouts TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Workout templates (admin can define workouts)
CREATE TABLE workout_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    sets INT,
    reps INT,
    rest INT,
    category VARCHAR(50), -- e.g. Bulk, Maintain, Fat Loss
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Meal templates (admin can define meals)
CREATE TABLE meal_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50), -- e.g. Bulk, Maintain, Fat Loss
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Announcements (optional, for gym events)
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    bmi FLOAT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- 🗑 Drop old tables
DROP TABLE IF EXISTS workout_templates;
DROP TABLE IF EXISTS meal_templates;

-- 🏋️ Create workout_templates
CREATE TABLE workout_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    sets INT NOT NULL,
    reps INT NOT NULL,
    rest INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    bmi_min DECIMAL(5,2) DEFAULT NULL,
    bmi_max DECIMAL(5,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 🍽 Create meal_templates
CREATE TABLE meal_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    bmi_min DECIMAL(5,2) DEFAULT NULL,
    bmi_max DECIMAL(5,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 🏋️ Insert 20 workouts
INSERT INTO workout_templates 
(title, description, sets, reps, rest, category, bmi_min, bmi_max) VALUES
('Push-Ups','Classic bodyweight chest and triceps exercise.',3,12,60,'Strength',18.50,29.99),
('Pull-Ups','Upper body back and biceps exercise.',3,8,90,'Strength',20.00,29.99),
('Squats','Lower body strength for quads and glutes.',4,15,90,'Strength',18.50,35.00),
('Bench Press','Chest and triceps barbell exercise.',4,10,120,'Strength',20.00,29.99),
('Deadlift','Full-body compound lift.',4,6,150,'Strength',20.00,29.99),
('Jump Rope','High-intensity cardio skipping.',5,60,30,'Cardio',18.50,24.99),
('Burpees','Explosive full-body cardio.',3,15,60,'Cardio',18.50,29.99),
('Mountain Climbers','Core and cardio exercise.',4,30,45,'Cardio',18.50,29.99),
('Running','Steady-state cardio.',1,20,0,'Cardio',18.50,35.00),
('Cycling','Low-impact endurance cardio.',1,30,0,'Cardio',18.50,35.00),
('Plank','Isometric core stability.',3,60,45,'Core',18.50,35.00),
('Russian Twists','Rotational core with weight.',3,20,60,'Core',18.50,29.99),
('Leg Raises','Lower abdominal exercise.',3,15,60,'Core',18.50,29.99),
('Bicycle Crunches','Dynamic core for obliques.',3,20,60,'Core',18.50,29.99),
('Side Plank','Oblique stability exercise.',3,45,45,'Core',18.50,35.00),
('Yoga Sun Salutation','Dynamic yoga flow.',3,10,30,'Flexibility',18.50,35.00),
('Dynamic Stretching','Warm-up mobility routine.',1,10,0,'Flexibility',18.50,35.00),
('Foam Rolling','Self-myofascial release.',1,10,0,'Flexibility',18.50,35.00),
('Pilates Roll-Up','Core + flexibility.',3,12,45,'Flexibility',18.50,29.99),
('Cat-Cow Stretch','Spinal mobility yoga pose.',3,10,30,'Flexibility',18.50,35.00);

-- 🍽 Insert 20 meals
INSERT INTO meal_templates 
(title, description, category, bmi_min, bmi_max) VALUES
('Oatmeal with Berries','High-fiber oats topped with berries.','Breakfast',18.50,24.99),
('Greek Yogurt Parfait','Protein-rich yogurt with granola and fruit.','Breakfast',18.50,29.99),
('Egg White Omelette','Low-calorie omelette with spinach.','Breakfast',25.00,35.00),
('Avocado Toast','Whole grain bread with avocado.','Breakfast',18.50,29.99),
('Smoothie Bowl','Blended fruits with chia seeds.','Breakfast',18.50,24.99),
('Grilled Chicken Salad','Lean chicken with greens.','Lunch',18.50,29.99),
('Quinoa and Veggie Bowl','Protein-packed quinoa with veggies.','Lunch',18.50,24.99),
('Salmon with Brown Rice','Omega-3 salmon with rice.','Lunch',25.00,29.99),
('Turkey Wrap','Whole wheat wrap with turkey.','Lunch',18.50,29.99),
('Lentil Soup','Hearty lentil and vegetable soup.','Lunch',18.50,35.00),
('Grilled Fish with Veggies','Light fish dinner with vegetables.','Dinner',18.50,24.99),
('Chicken Stir-Fry','Chicken with colorful vegetables.','Dinner',25.00,29.99),
('Tofu Curry','Plant-based curry with tofu.','Dinner',18.50,29.99),
('Beef and Broccoli','Lean beef sautéed with broccoli.','Dinner',25.00,29.99),
('Vegetable Pasta','Whole grain pasta with tomato sauce.','Dinner',18.50,35.00),
('Mixed Nuts','Healthy fats and protein.','Snack',18.50,29.99),
('Fruit Salad','Seasonal fruits with lime.','Snack',18.50,24.99),
('Protein Shake','Whey protein blended with milk.','Snack',25.00,35.00),
('Rice Cakes with Peanut Butter','Light snack with carbs and fats.','Snack',18.50,29.99),
('Hummus with Veggies','Chickpea dip with carrot sticks.','Snack',18.50,35.00);

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    goal TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- WORKOUTS
CREATE TABLE workouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workout_name VARCHAR(100) NOT NULL,
    sets INT,
    reps INT,
    rest_time INT, -- seconds
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- MEALS
CREATE TABLE meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal_name VARCHAR(100) NOT NULL,
    calories INT,
    protein DECIMAL(5,2),
    carbs DECIMAL(5,2),
    fats DECIMAL(5,2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- APPOINTMENTS
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    goal TEXT,
    UNIQUE (email, appointment_date, appointment_time)
);

-- SAMPLE DATA
INSERT INTO users (name, email, password)
VALUES ('Imadh Nisar', 'imadh@example.com', 'hashed_password');

INSERT INTO workouts (user_id, workout_name, sets, reps, rest_time)
VALUES (1, 'Push Ups', 3, 15, 60);

INSERT INTO meals (user_id, meal_name, calories, protein, carbs, fats)
VALUES (1, 'Chicken Salad', 350, 30.5, 10.2, 12.0);

INSERT INTO appointments (name, email, appointment_date, appointment_time, goal)
VALUES ('Imadh Nisar', 'imadh@example.com', '2026-03-20', '10:30:00', 'Discuss new workout plan');