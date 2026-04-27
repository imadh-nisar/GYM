-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 27, 2026 at 12:17 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unigym`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@unigym.local', '$2y$10$Jl.eC9lzjvWGqxnBZWwdJeF1O5AKLrYc/Ft.di/G5lXUI1deP59QC', '2026-04-26 10:24:40'),
(32, 'bee', 'bee@gmail.com', '$2y$10$O/oXxevv1o87i0pjk5OPQuP3As8H8iVtY3HrESn1JjalvF0LZAQlS', '2026-04-26 10:30:42');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `is_active`, `created_by`, `created_at`) VALUES
(1, 'Welcome to GYMgeekS!', 'Welcome to your personal fitness journey! Update your measurements to get personalized workout and meal plans.', 1, 1, '2026-04-26 10:24:54'),
(2, 'New Equipment Available', 'Check out our new state-of-the-art cardio machines in the main gym area!', 1, 1, '2026-04-26 10:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `goal` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_templates`
--

CREATE TABLE `meal_templates` (
  `id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `bmi_min` decimal(5,2) DEFAULT NULL,
  `bmi_max` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `meal_templates`
--

INSERT INTO `meal_templates` (`id`, `title`, `description`, `category`, `bmi_min`, `bmi_max`, `created_at`) VALUES
(1, 'Oatmeal with Berries', 'High-fiber oats topped with berries.', 'Breakfast', 18.50, 24.99, '2026-04-26 10:24:40'),
(2, 'Greek Yogurt Parfait', 'Protein-rich yogurt with granola and fruit.', 'Breakfast', 18.50, 29.99, '2026-04-26 10:24:40'),
(3, 'Egg White Omelette', 'Low-calorie omelette with spinach.', 'Breakfast', 25.00, 35.00, '2026-04-26 10:24:40'),
(4, 'Avocado Toast', 'Whole grain bread with avocado.', 'Breakfast', 18.50, 29.99, '2026-04-26 10:24:40'),
(5, 'Smoothie Bowl', 'Blended fruits with chia seeds.', 'Breakfast', 18.50, 24.99, '2026-04-26 10:24:40'),
(6, 'Grilled Chicken Salad', 'Lean chicken with greens.', 'Lunch', 18.50, 29.99, '2026-04-26 10:24:40'),
(7, 'Quinoa and Veggie Bowl', 'Protein-packed quinoa with veggies.', 'Lunch', 18.50, 24.99, '2026-04-26 10:24:40'),
(8, 'Salmon with Brown Rice', 'Omega-3 salmon with rice.', 'Lunch', 25.00, 29.99, '2026-04-26 10:24:40'),
(9, 'Turkey Wrap', 'Whole wheat wrap with turkey.', 'Lunch', 18.50, 29.99, '2026-04-26 10:24:40'),
(10, 'Lentil Soup', 'Hearty lentil and vegetable soup.', 'Lunch', 18.50, 35.00, '2026-04-26 10:24:40'),
(11, 'Grilled Fish with Veggies', 'Light fish dinner with vegetables.', 'Dinner', 18.50, 24.99, '2026-04-26 10:24:40'),
(12, 'Chicken Stir-Fry', 'Chicken with colorful vegetables.', 'Dinner', 25.00, 29.99, '2026-04-26 10:24:40'),
(13, 'Tofu Curry', 'Plant-based curry with tofu.', 'Dinner', 18.50, 29.99, '2026-04-26 10:24:40'),
(14, 'Beef and Broccoli', 'Lean beef sautéed with broccoli.', 'Dinner', 25.00, 29.99, '2026-04-26 10:24:40'),
(15, 'Vegetable Pasta', 'Whole grain pasta with tomato sauce.', 'Dinner', 18.50, 35.00, '2026-04-26 10:24:40'),
(16, 'Mixed Nuts', 'Healthy fats and protein.', 'Snack', 18.50, 29.99, '2026-04-26 10:24:40'),
(17, 'Fruit Salad', 'Seasonal fruits with lime.', 'Snack', 18.50, 24.99, '2026-04-26 10:24:40'),
(18, 'Protein Shake', 'Whey protein blended with milk.', 'Snack', 25.00, 35.00, '2026-04-26 10:24:40'),
(19, 'Rice Cakes with Peanut Butter', 'Light snack with carbs and fats.', 'Snack', 18.50, 29.99, '2026-04-26 10:24:40'),
(20, 'Hummus with Veggies', 'Chickpea dip with carrot sticks.', 'Snack', 18.50, 35.00, '2026-04-26 10:24:40'),
(21, 'Peanut Butter Banana Smoothie', 'Calorie-dense smoothie with nut butter and oats.', 'Breakfast', NULL, 18.49, '2026-04-26 10:24:40'),
(22, 'Protein Pancakes', 'High-protein pancakes topped with fruit.', 'Breakfast', NULL, 18.49, '2026-04-26 10:24:40'),
(23, 'Avocado Chicken Wrap', 'Calories and protein in a convenient wrap.', 'Lunch', NULL, 18.49, '2026-04-26 10:24:40'),
(24, 'Trail Mix', 'Nuts, seeds, and dried fruit for healthy calories.', 'Snack', NULL, 18.49, '2026-04-26 10:24:40'),
(25, 'Greek Yogurt with Granola', 'Creamy yogurt with crunchy granola and honey.', 'Snack', NULL, 18.49, '2026-04-26 10:24:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected','deleted') DEFAULT 'pending',
  `weight` float DEFAULT NULL,
  `height` float DEFAULT NULL,
  `chest` float DEFAULT NULL,
  `waist` float DEFAULT NULL,
  `arms` float DEFAULT NULL,
  `legs` float DEFAULT NULL,
  `bmi` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `status`, `weight`, `height`, `chest`, `waist`, `arms`, `legs`, `bmi`, `created_at`, `updated_at`) VALUES
(1, 'alan', 'alan@gmail.com', '$2y$10$tPsaAkXlWxA.CbC8LViPaOaJpZ2cZScQIYEpceYe2GDGihT4PTdlu', 'approved', 96, 174.98, 0, 0, 0, 0, 31.3541, '2026-04-26 16:04:17', '2026-04-26 16:04:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_meals`
--

CREATE TABLE `user_meals` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `meal_id` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_workouts`
--

CREATE TABLE `user_workouts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `workout_id` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weight_history`
--

CREATE TABLE `weight_history` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `weight` float NOT NULL,
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workout_templates`
--

CREATE TABLE `workout_templates` (
  `id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `sets` int DEFAULT NULL,
  `reps` int DEFAULT NULL,
  `rest` int DEFAULT NULL,
  `bmi_min` decimal(5,2) DEFAULT NULL,
  `bmi_max` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_templates`
--

INSERT INTO `workout_templates` (`id`, `title`, `description`, `category`, `sets`, `reps`, `rest`, `bmi_min`, `bmi_max`, `created_at`) VALUES
(1, 'Push-Ups', 'Classic bodyweight chest and triceps exercise.', 'Strength', 3, 12, 60, 18.50, 29.99, '2026-04-26 10:24:40'),
(2, 'Pull-Ups', 'Upper body back and biceps exercise.', 'Strength', 3, 8, 90, 20.00, 29.99, '2026-04-26 10:24:40'),
(3, 'Squats', 'Lower body strength for quads and glutes.', 'Strength', 4, 15, 90, 18.50, 35.00, '2026-04-26 10:24:40'),
(4, 'Bench Press', 'Chest and triceps barbell exercise.', 'Strength', 4, 10, 120, 20.00, 29.99, '2026-04-26 10:24:40'),
(5, 'Deadlift', 'Full-body compound lift.', 'Strength', 4, 6, 150, 20.00, 29.99, '2026-04-26 10:24:40'),
(6, 'Jump Rope', 'High-intensity cardio skipping.', 'Cardio', 5, 60, 30, 18.50, 24.99, '2026-04-26 10:24:40'),
(7, 'Burpees', 'Explosive full-body cardio.', 'Cardio', 3, 15, 60, 18.50, 29.99, '2026-04-26 10:24:40'),
(8, 'Mountain Climbers', 'Core and cardio exercise.', 'Cardio', 4, 30, 45, 18.50, 29.99, '2026-04-26 10:24:40'),
(9, 'Running', 'Steady-state cardio.', 'Cardio', 1, 20, 0, 18.50, 35.00, '2026-04-26 10:24:40'),
(10, 'Cycling', 'Low-impact endurance cardio.', 'Cardio', 1, 30, 0, 18.50, 35.00, '2026-04-26 10:24:40'),
(11, 'Plank', 'Isometric core stability.', 'Core', 3, 60, 45, 18.50, 35.00, '2026-04-26 10:24:40'),
(12, 'Russian Twists', 'Rotational core with weight.', 'Core', 3, 20, 60, 18.50, 29.99, '2026-04-26 10:24:40'),
(13, 'Leg Raises', 'Lower abdominal exercise.', 'Core', 3, 15, 60, 18.50, 29.99, '2026-04-26 10:24:40'),
(14, 'Bicycle Crunches', 'Dynamic core for obliques.', 'Core', 3, 20, 60, 18.50, 29.99, '2026-04-26 10:24:40'),
(15, 'Side Plank', 'Oblique stability exercise.', 'Core', 3, 45, 45, 18.50, 35.00, '2026-04-26 10:24:40'),
(16, 'Yoga Sun Salutation', 'Dynamic yoga flow.', 'Flexibility', 3, 10, 30, 18.50, 35.00, '2026-04-26 10:24:40'),
(17, 'Dynamic Stretching', 'Warm-up mobility routine.', 'Flexibility', 1, 10, 0, 18.50, 35.00, '2026-04-26 10:24:40'),
(18, 'Foam Rolling', 'Self-myofascial release.', 'Flexibility', 1, 10, 0, 18.50, 35.00, '2026-04-26 10:24:40'),
(19, 'Pilates Roll-Up', 'Core + flexibility.', 'Flexibility', 3, 12, 45, 18.50, 29.99, '2026-04-26 10:24:40'),
(20, 'Cat-Cow Stretch', 'Spinal mobility yoga pose.', 'Flexibility', 3, 10, 30, 18.50, 35.00, '2026-04-26 10:24:40'),
(21, 'Bodyweight Squats', 'Build lower-body strength with controlled reps.', 'Strength', 3, 12, 60, NULL, 18.49, '2026-04-26 10:24:40'),
(22, 'Resistance Band Rows', 'Upper back strengthening using bands.', 'Strength', 3, 15, 60, NULL, 18.49, '2026-04-26 10:24:40'),
(23, 'Glute Bridges', 'Glute and posterior chain activation.', 'Strength', 3, 15, 60, NULL, 18.49, '2026-04-26 10:24:40'),
(24, 'Farmer\'s Carry', 'Grip and core strength with loaded carries.', 'Strength', 3, 40, 60, NULL, 18.49, '2026-04-26 10:24:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `meal_templates`
--
ALTER TABLE `meal_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_meals`
--
ALTER TABLE `user_meals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_meal` (`user_id`,`meal_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `user_workouts`
--
ALTER TABLE `user_workouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_workout` (`user_id`,`workout_id`),
  ADD KEY `workout_id` (`workout_id`);

--
-- Indexes for table `weight_history`
--
ALTER TABLE `weight_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `workout_templates`
--
ALTER TABLE `workout_templates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_templates`
--
ALTER TABLE `meal_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_meals`
--
ALTER TABLE `user_meals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_workouts`
--
ALTER TABLE `user_workouts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `weight_history`
--
ALTER TABLE `weight_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `workout_templates`
--
ALTER TABLE `workout_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_meals`
--
ALTER TABLE `user_meals`
  ADD CONSTRAINT `user_meals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_meals_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meal_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_workouts`
--
ALTER TABLE `user_workouts`
  ADD CONSTRAINT `user_workouts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_workouts_ibfk_2` FOREIGN KEY (`workout_id`) REFERENCES `workout_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `weight_history`
--
ALTER TABLE `weight_history`
  ADD CONSTRAINT `weight_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
