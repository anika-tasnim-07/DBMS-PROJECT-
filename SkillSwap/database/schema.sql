-- ==========================================
-- SkillSwap Database Schema & Initial Data
-- ==========================================

CREATE DATABASE IF NOT EXISTS `skillswap`;
USE `skillswap`;

-- Disable Foreign Key checks for clean table setups
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `skill_test_results`;
DROP TABLE IF EXISTS `quiz_questions`;
DROP TABLE IF EXISTS `deletion_requests`;
DROP TABLE IF EXISTS `swap_requests`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `students`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. STUDENTS TABLE
CREATE TABLE `students` (
  `student_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `department` VARCHAR(100) DEFAULT NULL,
  `is_admin` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. CATEGORIES TABLE
CREATE TABLE `categories` (
  `category_id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Populate Default Categories
INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Programming'),
(2, 'Language'),
(3, 'Design'),
(4, 'Academics');

-- 3. SKILLS TABLE
CREATE TABLE `skills` (
  `skill_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `skill_name` VARCHAR(100) NOT NULL,
  `skill_type` ENUM('offered', 'wanted') NOT NULL DEFAULT 'offered',
  `skill_level` VARCHAR(50) DEFAULT 'Beginner',
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. SWAP REQUESTS TABLE
CREATE TABLE `swap_requests` (
  `request_id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT NOT NULL,
  `receiver_id` INT NOT NULL,
  `offered_skill_id` INT NOT NULL,
  `requested_skill_id` INT NOT NULL,
  `status` ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sender_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. DELETION REQUESTS TABLE
CREATE TABLE `deletion_requests` (
  `request_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `reason` TEXT DEFAULT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. QUIZ QUESTIONS TABLE
CREATE TABLE `quiz_questions` (
  `question_id` INT AUTO_INCREMENT PRIMARY KEY,
  `skill_name` VARCHAR(100) NOT NULL,
  `question_type` ENUM('single', 'checkbox') DEFAULT 'single',
  `question_text` TEXT NOT NULL,
  `option_a` VARCHAR(255) NOT NULL,
  `option_b` VARCHAR(255) NOT NULL,
  `option_c` VARCHAR(255) NOT NULL,
  `option_d` VARCHAR(255) NOT NULL,
  `correct_options` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. SKILL TEST RESULTS TABLE
CREATE TABLE `skill_test_results` (
  `result_id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  `skill_name` VARCHAR(100) NOT NULL,
  `score` INT NOT NULL,
  `total_questions` INT NOT NULL DEFAULT 30,
  `assigned_level` VARCHAR(50) NOT NULL,
  `taken_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- SEED DATA FOR QUIZ QUESTIONS
-- ==========================================

-- C++ Questions
INSERT INTO `quiz_questions` (`skill_name`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_options`) VALUES
('C++', 'single', 'Who created C++?', 'Bjarne Stroustrup', 'Dennis Ritchie', 'James Gosling', 'Guido van Rossum', 'A'),
('C++', 'single', 'Which concept is supported by C++?', 'Procedural', 'Object-Oriented', 'Generic', 'All of the above', 'D'),
('C++', 'single', 'Which keyword is used to declare a constant in C++?', 'const', 'final', 'static', 'define', 'A'),
('C++', 'single', 'What is the size of `char` data type in C++?', '1 byte', '2 bytes', '4 bytes', '8 bytes', 'A'),
('C++', 'single', 'Which operator is used for dynamic memory allocation in C++?', 'malloc', 'new', 'alloc', 'create', 'B'),
('C++', 'checkbox', 'Which of the following are valid C++ access specifiers?', 'public', 'private', 'protected', 'friendly', 'A,B,C'),
('C++', 'checkbox', 'Which keywords are used in C++ exception handling?', 'try', 'catch', 'throw', 'finally', 'A,B,C');

-- Spanish Questions
INSERT INTO `quiz_questions` (`skill_name`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_options`) VALUES
('Spanish', 'single', 'How do you say "Hello" in Spanish?', 'Hola', 'Bonjour', 'Ciao', 'Hallo', 'A'),
('Spanish', 'single', 'What does "Gracias" mean in English?', 'Please', 'Thank you', 'Goodbye', 'Welcome', 'B'),
('Spanish', 'single', 'Which word means "Water" in Spanish?', 'Leche', 'Agua', 'Jugo', 'Vino', 'B'),
('Spanish', 'single', 'How do you say "Good morning"?', 'Buenas noches', 'Buenos días', 'Buenas tardes', 'Hasta luego', 'B'),
('Spanish', 'checkbox', 'Which of the following are Spanish greetings?', 'Hola', 'Buenos días', 'Buenas noches', 'Goodbye', 'A,B,C');

-- Java Questions
INSERT INTO `quiz_questions` (`skill_name`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_options`) VALUES
('Java', 'single', 'Who developed Java?', 'James Gosling', 'Dennis Ritchie', 'Bjarne Stroustrup', 'Guido van Rossum', 'A'),
('Java', 'single', 'Which component executes Java bytecode?', 'JVM', 'JDK', 'JRE', 'JIT', 'A'),
('Java', 'single', 'Which keyword is used for inheritance in Java?', 'implements', 'extends', 'inherits', 'import', 'B'),
('Java', 'single', 'What is the default value of boolean in Java?', 'true', 'false', '0', 'null', 'B'),
('Java', 'checkbox', 'Which of the following are valid primitive data types in Java?', 'int', 'double', 'boolean', 'String', 'A,B,C');