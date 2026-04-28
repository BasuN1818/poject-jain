# SkillBridge AI – Setup Guide

## Quick Start (5 Steps)

### Step 1: Start XAMPP
- Open **XAMPP Control Panel** (from Desktop or Start Menu)
- Click **Start** for **Apache** → Wait for green status
- Click **Start** for **MySQL** → Wait for green status

### Step 2: Copy Project to htdocs
Open **Command Prompt** and run:
```
xcopy "C:\Users\BASANAGOUD\OneDrive\Desktop\1st Project" "C:\xampp\htdocs\skillbridge" /E /I /Y
```
Or manually copy the entire `1st Project` folder to `C:\xampp\htdocs\` and rename it to `skillbridge`.

### Step 3: Create the Database
1. Open browser → go to: **http://localhost/phpmyadmin**
2. Click **"New"** in the left sidebar
3. OR click the **SQL** tab and paste the contents of `database.sql`
4. Click **Go** to execute

### Step 4: Open the App
Go to: **http://localhost/skillbridge/index.php**

### Step 5: Login Credentials

| Role  | Employee ID | Password  | Email                          |
|-------|-------------|-----------|-------------------------------|
| Admin | ADMIN001    | password  | admin@skillbridge.com          |
| Emp 1 | EMP001      | password  | alice.johnson@skillbridge.com  |
| Emp 2 | EMP002      | password  | bob.smith@skillbridge.com      |
| Emp 3 | EMP003      | password  | carol.white@skillbridge.com    |
| Emp 4 | EMP004      | password  | david.lee@skillbridge.com      |

> ⚠️ **Note on 2FA Email:** The system sends OTP via `php mail()`. On localhost with XAMPP, emails may not send without SMTP configuration. For testing, the OTP is **logged to the PHP error log**.
> To find the OTP: Open XAMPP → Apache → `Logs` → `error.log` and search for "2FA OTP for..."

---

## Project Structure

```
skillbridge/
├── index.php              ← Login Page
├── verify_2fa.php         ← OTP Verification Page
├── database.sql           ← Run this in phpMyAdmin
├── config/
│   └── db.php             ← Database connection & helpers
├── api/
│   └── auth.php           ← Auth API (login, 2FA, logout)
├── admin/
│   ├── dashboard.php      ← Admin Command Center
│   ├── employees.php      ← Employee CRUD
│   ├── leaves.php         ← Leave Management
│   ├── attendance.php     ← Attendance Tracking
│   ├── salary.php         ← Salary & Payroll
│   ├── ai_recommendations.php  ← AI IDP Engine
│   ├── leaderboard.php    ← Rankings & Tiers
│   ├── profile.php        ← Admin Settings
│   └── sidebar.php        ← Shared sidebar component
└── employee/
    ├── dashboard.php      ← Employee Dashboard
    ├── profile.php        ← Read-only Profile
    ├── progress.php       ← Progress & Radar Chart
    ├── leave.php          ← Apply for Leave
    ├── leaderboard.php    ← View Rankings
    ├── history.php        ← Attendance & Salary History
    └── sidebar_emp.php    ← Employee sidebar component
```

## Configuring Email (OTP) for Production

Edit `config/db.php`:
```php
// For production, use PHPMailer with SMTP:
// Install: composer require phpmailer/phpmailer
// Then update sendOTPEmail() function
```

Or configure XAMPP's `php.ini` to use an SMTP relay (e.g., Gmail via SendGrid).

## Technology Stack
- **Frontend:** HTML5, Tailwind CSS (CDN), Vanilla JS, Chart.js
- **Backend:** PHP 8.x (RESTful pattern)
- **Database:** MySQL 8.x via XAMPP
- **Charts:** Chart.js (radar, line, doughnut)
- **Icons:** Inline SVG
- **Fonts:** Inter (Google Fonts)
