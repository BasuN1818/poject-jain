@echo off
title SkillBridge AI - Deploy to XAMPP
color 0A
echo.
echo  =============================================
echo   SkillBridge AI - XAMPP Deployment Script
echo  =============================================
echo.

REM Check if XAMPP htdocs exists
if not exist "C:\xampp\htdocs\" (
    echo  [ERROR] XAMPP not found at C:\xampp\
    echo  Please install XAMPP or check the path.
    echo.
    pause
    exit /b 1
)

echo  [1/3] Removing old deployment if exists...
if exist "C:\xampp\htdocs\skillbridge\" (
    rmdir /S /Q "C:\xampp\htdocs\skillbridge"
)

echo  [2/3] Copying project files to XAMPP htdocs...
xcopy "%~dp0" "C:\xampp\htdocs\skillbridge\" /E /I /Y /EXCLUDE:%~dp0exclude.txt >nul 2>&1

REM Create the exclude file inline if missing
echo DEPLOY_TO_XAMPP.bat > "%~dp0exclude.txt"
echo exclude.txt >> "%~dp0exclude.txt"
xcopy "%~dp0" "C:\xampp\htdocs\skillbridge\" /E /I /Y /EXCLUDE:%~dp0exclude.txt >nul

echo  [3/3] Done!
echo.
echo  =============================================
echo   SUCCESS! Your app is ready.
echo  =============================================
echo.
echo   Open this URL in your browser:
echo   http://localhost/skillbridge/index.php
echo.
echo   Admin Login:
echo   ID       : ADMIN001
echo   Password : password
echo.
echo   Employee Logins:
echo   EMP001 / EMP002 / EMP003 / EMP004
echo   Password: password
echo.
echo   NOTE: OTP is logged to:
echo   C:\xampp\apache\logs\error.log
echo   (Search for "2FA OTP for...")
echo.
echo   Opening browser now...
start "" "http://localhost/skillbridge/index.php"
echo.
pause
