
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS gym_db;
USE gym_db;

CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(120),
    package_name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    specialty VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    trainer_id INT,
    schedule_time DATETIME NOT NULL,
    capacity INT DEFAULT 20,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);

CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(100) NOT NULL,
    duration_months INT NOT NULL,
    price DECIMAL(12,2) NOT NULL
);

INSERT INTO packages (package_name, duration_months, price)
SELECT 'Gói 1 tháng', 1, 500000
WHERE NOT EXISTS (
    SELECT 1 FROM packages WHERE package_name = 'Gói 1 tháng'
);

INSERT INTO packages (package_name, duration_months, price)
SELECT 'Gói 3 tháng', 3, 1350000
WHERE NOT EXISTS (
    SELECT 1 FROM packages WHERE package_name = 'Gói 3 tháng'
);

INSERT INTO trainers (full_name, phone, specialty)
SELECT 'Nguyễn Minh Anh', '0901234567', 'Fitness'
WHERE NOT EXISTS (
    SELECT 1 FROM trainers WHERE full_name = 'Nguyễn Minh Anh'
);
