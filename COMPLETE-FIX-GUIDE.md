# COMPLETE FIX GUIDE - Step by Step

## Issue #1: Database Import Error

The problem is phpMyAdmin expects you to create the database first, then import tables.

### FIX - Follow These Steps EXACTLY:

#### Step 1: Create Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click **"SQL"** tab at the top
3. Copy and paste this:
   ```sql
   CREATE DATABASE IF NOT EXISTS event_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Click **"Go"**
5. You should see: "✓ Query executed successfully"

#### Step 2: Import Tables and Data
1. On the left sidebar, click **"event_ticketing"** database
2. Click **"Import"** tab
3. Click **"Choose File"**
4. Select: `C:\Users\user\Ticketing Software\database\import-tables.sql`
5. Click **"Go"**
6. Wait for success message

#### Step 3: Verify Data
1. Click **"event_ticketing"** in left sidebar
2. Click **"events"** table
3. Click **"Browse"** tab
4. You should see 6 events!

---

## Issue #2: Backend Not Responding

### Test Backend First:

Open in browser: http://localhost/ticketing-backend/test.php

**Expected Result:**
```json
{
  "success": true,
  "message": "Backend is working!",
  "php_version": "8.x.x",
  "time": "2026-01-23 ..."
}
```

**If you see error or blank page:**
- XAMPP Apache is not running
- Fix: Open XAMPP Control Panel → Start Apache

---

## Issue #3: Check Backend .env File

### Verify Configuration:

```powershell
# Run this in PowerShell:
Get-Content "C:\xampp\htdocs\ticketing-backend\.env"
```

**Should contain:**
```
DB_HOST=localhost
DB_NAME=event_ticketing
DB_USER=root
DB_PASS=
JWT_SECRET=your_generated_secret_here
ALLOWED_ORIGINS=http://localhost:3000
```

**If file is missing or wrong:**
```powershell
# Copy from workspace:
Copy-Item "C:\Users\user\Ticketing Software\backend\.env.example" "C:\xampp\htdocs\ticketing-backend\.env"
```

Then edit and fill in values.

---

## Issue #4: Test Backend API

After database is imported, test the events endpoint:

Open: http://localhost/ticketing-backend/events

**Expected Result:**
```json
{
  "success": true,
  "data": {
    "events": [
      {
        "id": "1",
        "title": "Tech Conference 2026",
        ...
      }
    ]
  }
}
```

**If you see errors:**
- Check database connection in .env
- Verify database has data (phpMyAdmin → event_ticketing → events → Browse)
- Check Apache error logs: C:\xampp\apache\logs\error.log

---

## Issue #5: Frontend Configuration

### Check frontend .env:

```powershell
Get-Content "C:\Users\user\Ticketing Software\frontend\.env"
```

**Should contain:**
```
REACT_APP_API_URL=http://localhost/ticketing-backend
```

---

## Complete Test Sequence:

### 1. Test Apache:
```
http://localhost
```
Should show XAMPP dashboard.

### 2. Test Backend:
```
http://localhost/ticketing-backend/test.php
```
Should show JSON with "Backend is working!"

### 3. Test Database Connection:
```
http://localhost/ticketing-backend/events
```
Should show JSON with 6 events.

### 4. Test Frontend:
```powershell
cd "C:\Users\user\Ticketing Software\frontend"
npm start
```
Then visit: http://localhost:3000

Should show 6 events on homepage!

### 5. Test Login:
- Email: `attendee@example.com`
- Password: `password`

Should successfully log in!

---

## Still Having Issues?

### Check Browser Console:
1. Open homepage: http://localhost:3000
2. Press **F12** to open Developer Tools
3. Click **Console** tab
4. Look for red errors
5. Tell me what errors you see

### Check Network Requests:
1. In Developer Tools, click **Network** tab
2. Refresh page
3. Look for request to `/events`
4. Click on it
5. Check **Response** tab
6. Tell me what it says

---

## Quick Commands to Copy/Paste:

```powershell
# 1. Check if backend exists
Test-Path "C:\xampp\htdocs\ticketing-backend"

# 2. Check if .env exists
Test-Path "C:\xampp\htdocs\ticketing-backend\.env"

# 3. View .env content
Get-Content "C:\xampp\htdocs\ticketing-backend\.env"

# 4. Start frontend
cd "C:\Users\user\Ticketing Software\frontend"
npm start
```

---

## After Following All Steps:

✅ Database imported successfully  
✅ Backend test.php works  
✅ Backend /events returns 6 events  
✅ Frontend shows 6 events  
✅ Login works  
✅ Registration works  

Then your system is fully working! 🎉
