-- ============================================================
-- SkillBridge AI Corporate Platform - Database Schema
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS skillbridge_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skillbridge_db;

-- ============================================================
-- USERS TABLE (Core Authentication)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    two_fa_code VARCHAR(6) DEFAULT NULL,
    two_fa_expires DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- EMPLOYEE PROFILES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS employee_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    uid VARCHAR(20) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    department VARCHAR(100) DEFAULT NULL,
    designation VARCHAR(100) DEFAULT NULL,
    target_role VARCHAR(100) DEFAULT NULL,
    current_skills TEXT DEFAULT NULL,
    target_skills TEXT DEFAULT NULL,
    knowledge_score INT DEFAULT 0,
    tier ENUM('Bronze', 'Silver', 'Gold', 'Diamond') DEFAULT 'Bronze',
    date_joined DATE DEFAULT NULL,
    profile_photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- ATTENDANCE TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME DEFAULT NULL,
    check_out TIME DEFAULT NULL,
    status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'present',
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (user_id, date)
) ENGINE=InnoDB;

-- ============================================================
-- LEAVES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS leaves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    leave_type ENUM('sick', 'casual', 'earned', 'maternity', 'emergency') DEFAULT 'casual',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT DEFAULT 1,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_comment VARCHAR(255) DEFAULT NULL,
    applied_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actioned_on DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SALARY TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS salary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    month VARCHAR(20) NOT NULL,
    year INT NOT NULL,
    basic_salary DECIMAL(10,2) DEFAULT 0.00,
    allowances DECIMAL(10,2) DEFAULT 0.00,
    deductions DECIMAL(10,2) DEFAULT 0.00,
    net_salary DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    paid_on DATETIME DEFAULT NULL,
    transaction_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- IDP RECOMMENDATIONS TABLE (AI Engine)
-- ============================================================
CREATE TABLE IF NOT EXISTS idp_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('training', 'mentorship', 'job_rotation', 'certification', 'workshop') NOT NULL,
    title VARCHAR(255) NOT NULL,
    ai_suggestion TEXT NOT NULL,
    skill_gap VARCHAR(255) DEFAULT NULL,
    priority ENUM('high', 'medium', 'low') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'approved', 'rejected') DEFAULT 'pending',
    assigned_by INT DEFAULT NULL,
    progress_percent INT DEFAULT 0,
    due_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SESSIONS TABLE (Secure Token Management)
-- ============================================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA: Default Admin Account
-- Password: Admin@123 (bcrypt hashed)
-- ============================================================
INSERT INTO users (uid, email, password_hash, role) VALUES
('ADMIN001', 'admin@skillbridge.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Admin profile
INSERT INTO employee_profiles (user_id, uid, first_name, last_name, department, designation, knowledge_score, tier, date_joined) VALUES
(1, 'ADMIN001', 'System', 'Administrator', 'HR & Administration', 'Platform Admin', 100, 'Diamond', CURDATE());

-- ============================================================
-- SEED DATA: Sample Employees
-- ============================================================
INSERT INTO users (uid, email, password_hash, role) VALUES
('EMP001', 'alice.johnson@skillbridge.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('EMP002', 'bob.smith@skillbridge.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('EMP003', 'carol.white@skillbridge.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee'),
('EMP004', 'david.lee@skillbridge.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee');

INSERT INTO employee_profiles (user_id, uid, first_name, last_name, phone, department, designation, target_role, current_skills, target_skills, knowledge_score, tier, date_joined) VALUES
(2, 'EMP001', 'Alice', 'Johnson', '+1-555-0101', 'Engineering', 'Junior Developer', 'Senior Full-Stack Developer', 'JavaScript,HTML,CSS,React', 'Node.js,AWS,Docker,GraphQL', 72, 'Gold', '2024-01-15'),
(3, 'EMP002', 'Bob', 'Smith', '+1-555-0102', 'Data Science', 'Data Analyst', 'ML Engineer', 'Python,SQL,Excel,Tableau', 'Machine Learning,TensorFlow,Spark,Kubernetes', 55, 'Silver', '2024-03-01'),
(4, 'EMP003', 'Carol', 'White', '+1-555-0103', 'Design', 'UI Designer', 'Product Designer', 'Figma,Adobe XD,CSS,Sketch', 'User Research,Prototyping,Motion Design,Accessibility', 88, 'Diamond', '2023-11-20'),
(5, 'EMP004', 'David', 'Lee', '+1-555-0104', 'Engineering', 'Backend Developer', 'DevOps Engineer', 'PHP,MySQL,Linux,Git', 'Docker,Kubernetes,CI/CD,Terraform', 41, 'Bronze', '2025-02-10');

-- Sample Salary Records
INSERT INTO salary (user_id, month, year, basic_salary, allowances, deductions, net_salary, status, paid_on) VALUES
(2, 'March', 2026, 75000.00, 15000.00, 5000.00, 85000.00, 'paid', '2026-04-01 10:00:00'),
(3, 'March', 2026, 65000.00, 12000.00, 4000.00, 73000.00, 'paid', '2026-04-01 10:00:00'),
(4, 'March', 2026, 90000.00, 18000.00, 6000.00, 102000.00, 'paid', '2026-04-01 10:00:00'),
(5, 'March', 2026, 60000.00, 10000.00, 3500.00, 66500.00, 'paid', '2026-04-01 10:00:00'),
(2, 'April', 2026, 75000.00, 15000.00, 5000.00, 85000.00, 'pending', NULL),
(3, 'April', 2026, 65000.00, 12000.00, 4000.00, 73000.00, 'pending', NULL),
(4, 'April', 2026, 90000.00, 18000.00, 6000.00, 102000.00, 'pending', NULL),
(5, 'April', 2026, 60000.00, 10000.00, 3500.00, 66500.00, 'pending', NULL);

-- Sample Leaves
INSERT INTO leaves (user_id, leave_type, start_date, end_date, total_days, reason, status) VALUES
(2, 'sick', '2026-04-28', '2026-04-29', 2, 'Fever and flu symptoms', 'pending'),
(3, 'casual', '2026-05-02', '2026-05-02', 1, 'Personal work', 'pending'),
(4, 'earned', '2026-04-30', '2026-05-01', 2, 'Family function', 'approved'),
(5, 'emergency', '2026-04-27', '2026-04-27', 1, 'Family emergency', 'approved');

-- Sample Attendance (last 5 days)
INSERT INTO attendance (user_id, date, check_in, check_out, status) VALUES
(2, CURDATE() - INTERVAL 4 DAY, '09:02:00', '18:05:00', 'present'),
(2, CURDATE() - INTERVAL 3 DAY, '09:15:00', '18:00:00', 'present'),
(2, CURDATE() - INTERVAL 2 DAY, '10:30:00', '18:00:00', 'late'),
(2, CURDATE() - INTERVAL 1 DAY, '09:00:00', '18:00:00', 'present'),
(3, CURDATE() - INTERVAL 4 DAY, '08:55:00', '17:55:00', 'present'),
(3, CURDATE() - INTERVAL 3 DAY, NULL, NULL, 'absent'),
(3, CURDATE() - INTERVAL 2 DAY, '09:10:00', '18:10:00', 'present'),
(4, CURDATE() - INTERVAL 4 DAY, '08:45:00', '17:45:00', 'present'),
(4, CURDATE() - INTERVAL 3 DAY, '09:00:00', '18:00:00', 'present'),
(5, CURDATE() - INTERVAL 4 DAY, '09:30:00', '18:30:00', 'present'),
(5, CURDATE() - INTERVAL 3 DAY, '11:00:00', '18:00:00', 'late');

-- Sample IDP Recommendations
INSERT INTO idp_recommendations (user_id, type, title, ai_suggestion, skill_gap, priority, status, progress_percent, due_date) VALUES
(2, 'training', 'Advanced Node.js & Microservices', 'Based on your current JavaScript proficiency, an intensive Node.js training focusing on microservices architecture will bridge your gap to Senior Full-Stack Developer role.', 'Node.js, Microservices', 'high', 'in_progress', 45, '2026-06-30'),
(2, 'certification', 'AWS Cloud Practitioner Certification', 'Cloud proficiency is critical for your target role. AWS CP certification provides foundational cloud knowledge required for senior positions.', 'AWS, Cloud', 'high', 'approved', 20, '2026-07-31'),
(3, 'training', 'Machine Learning Fundamentals with Python', 'Your strong Python and SQL base makes you an excellent candidate for ML upskilling. This course will accelerate your transition to ML Engineer.', 'Machine Learning, TensorFlow', 'high', 'in_progress', 60, '2026-05-31'),
(3, 'mentorship', 'Mentorship with Senior ML Engineer', 'A 3-month mentorship program with a senior ML Engineer will provide hands-on project experience and industry insights.', 'Practical ML Experience', 'medium', 'pending', 0, '2026-08-31'),
(4, 'workshop', 'Advanced Figma Prototyping Workshop', 'Your design skills are exceptional. This advanced prototyping workshop will elevate your capabilities to lead product design initiatives.', 'Advanced Prototyping', 'medium', 'completed', 100, '2026-03-31'),
(5, 'job_rotation', 'DevOps Team Rotation (3 months)', 'A structured rotation in the DevOps team will provide hands-on experience with CI/CD pipelines and container orchestration.', 'Docker, CI/CD, Kubernetes', 'high', 'approved', 10, '2026-07-31'),
(5, 'training', 'Docker & Kubernetes Masterclass', 'Container technologies are the foundation of your target DevOps role. This course provides comprehensive hands-on training.', 'Docker, Kubernetes', 'high', 'in_progress', 30, '2026-06-15');
