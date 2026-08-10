CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100),
    semester VARCHAR(20),
    bio TEXT,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);-- Create Database
CREATE DATABASE IF NOT EXISTS `skillswap` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `skillswap`;

-- 1. Students / Users Table
CREATE TABLE IF NOT EXISTS `students` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `department` VARCHAR(100),
    `semester` VARCHAR(20),
    `bio` TEXT,
    `profile_image` VARCHAR(255) DEFAULT 'default.png',
    `time_credits` INT DEFAULT 5,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `category_id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Skills Table
CREATE TABLE IF NOT EXISTS `skills` (
    `skill_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `skill_name` VARCHAR(100) NOT NULL,
    `skill_level` ENUM('Beginner', 'Intermediate', 'Expert') NOT NULL,
    `skill_type` ENUM('offered', 'sought') NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Services Table
CREATE TABLE IF NOT EXISTS `services` (
    `service_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `availability` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Swap Requests Table
CREATE TABLE IF NOT EXISTS `swap_requests` (
    `request_id` INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id` INT NOT NULL,
    `receiver_id` INT NOT NULL,
    `offered_skill_id` INT,
    `requested_skill_id` INT,
    `message` TEXT,
    `status` ENUM('Pending', 'Accepted', 'Rejected', 'Completed') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`offered_skill_id`) REFERENCES `skills`(`skill_id`) ON DELETE SET NULL,
    FOREIGN KEY (`requested_skill_id`) REFERENCES `skills`(`skill_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Ratings Table
CREATE TABLE IF NOT EXISTS `ratings` (
    `rating_id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL UNIQUE,
    `rater_id` INT NOT NULL,
    `rated_student_id` INT NOT NULL,
    `rating` INT CHECK (`rating` >= 1 AND `rating` <= 5),
    `feedback` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `swap_requests`(`request_id`) ON DELETE CASCADE,
    FOREIGN KEY (`rater_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`rated_student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Data (Pre-populating Categories)
INSERT INTO `categories` (`category_name`) VALUES 
('Programming'), ('Design'), ('Language'), ('Music'), ('Academic'), ('Photography');