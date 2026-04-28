-- ============================================================
-- SkillBridge AI Corporate Platform - Database Schema
-- HOSTING COMPATIBLE VERSION (No CREATE DATABASE / USE)
-- ============================================================

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
-- Password: password
-- ============================================================
INSERT INTO users (uid, email, password_hash, role) VALUES
('ADMIN001', 'admin@skillbridge.com', '$2y$10$8K1p/mG7Xv4rYqL0X0q0u.e6u8V0eF7K1R0Y9lC7V8vV1R0Y9lC7V', 'admin');

-- Admin profile
INSERT INTO employee_profiles (user_id, uid, first_name, last_name, department, designation, knowledge_score, tier, date_joined) VALUES
(1, 'ADMIN001', 'System', 'Administrator', 'HR & Administration', 'Platform Admin', 100, 'Diamond', CURDATE());

-- Sample Employees
INSERT INTO users (uid, email, password_hash, role) VALUES
('EMP001', 'alice.johnson@skillbridge.com', '$2y$10$8K1p/mG7Xv4rYqL0X0q0u.e6u8V0eF7K1R0Y9lC7V8vV1R0Y9lC7V', 'employee'),
('EMP002', 'bob.smith@skillbridge.com', '$2y$10$8K1p/mG7Xv4rYqL0X0q0u.e6u8V0eF7K1R0Y9lC7V8vV1R0Y9lC7V', 'employee');

INSERT INTO employee_profiles (user_id, uid, first_name, last_name, department, designation, knowledge_score, tier, date_joined) VALUES
(2, 'EMP001', 'Alice', 'Johnson', 'Engineering', 'Junior Developer', 72, 'Gold', '2024-01-15'),
(3, 'EMP002', 'Bob', 'Smith', 'Data Science', 'Data Analyst', 55, 'Silver', '2024-03-01');
