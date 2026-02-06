@echo off
echo ========================================
echo Event Ticketing - Fix CORS Issue
echo ========================================
echo.

echo Step 1: Copying updated .env to XAMPP backend...
copy "C:\Users\user\Ticketing Software\backend\.env.example" "C:\xampp\htdocs\ticketing-backend\.env"

echo.
echo Step 2: Updating ALLOWED_ORIGINS to allow all...
powershell -Command "(Get-Content 'C:\xampp\htdocs\ticketing-backend\.env') -replace 'ALLOWED_ORIGINS=http://localhost:3000', 'ALLOWED_ORIGINS=*' | Set-Content 'C:\xampp\htdocs\ticketing-backend\.env'"

echo.
echo Step 3: Verifying configuration...
powershell -Command "Get-Content 'C:\xampp\htdocs\ticketing-backend\.env' | Select-String 'ALLOWED_ORIGINS'"

echo.
echo ========================================
echo DONE! CORS issue should be fixed.
echo ========================================
echo.
echo Now refresh your browser at http://localhost:3001
echo.
pause
