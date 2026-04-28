@echo off
set REPO_URL=https://github.com/BasuN1818/poject-jain.git

echo [1/4] Setting remote URL to %REPO_URL%...
git remote set-url origin %REPO_URL%
if %ERRORLEVEL% neq 0 (
    echo FAILED to set remote URL. Make sure git is installed.
    pause
    exit /b
)

echo [2/4] Adding changes...
git add .

echo [3/4] Committing changes...
git commit -m "Initial Docker Setup"

echo [4/4] Pushing to GitHub...
git push origin main
if %ERRORLEVEL% neq 0 (
    echo.
    echo PUSH FAILED! Possible reasons:
    echo 1. You are not logged in to GitHub in this terminal.
    echo 2. The repository does not exist or you don't have access.
    echo 3. There are remote changes you need to pull first.
    echo.
    pause
    exit /b
)

echo.
echo SUCCESS! Your project has been pushed to %REPO_URL%
pause

