# Database Setup - Fix Issues

## Problem: Database Already Exists

You have 2 options:

### Option 1: Use Fresh Schema (RECOMMENDED)

1. Go to phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Select file: `database/schema-fresh.sql` (NEW FILE)
4. Click "Go"

This will DROP the old database and create a fresh one with all sample data.

### Option 2: Manual Reset

1. Go to phpMyAdmin
2. Click on `event_ticketing` database (if it exists)
3. Click "Operations" tab
4. Scroll down and click "Drop the database"
5. Then import `database/schema.sql`

---

## Login Issues - FIXED! ✅

The password hash in the database was incorrect. The new schema has working credentials:

**Attendee:**
- Email: `attendee@example.com`
- Password: `password`

**Organizer:**
- Email: `organizer@example.com`
- Password: `password`

---

## Sample Events - ADDED! ✅

The new schema includes **6 sample events**:

1. ✅ Tech Conference 2026
2. ✅ Summer Music Festival
3. ✅ Business Networking Gala
4. ✅ Food & Wine Expo 2026
5. ✅ Marathon Challenge 2026
6. ✅ Art Exhibition: Modern Masters

All with multiple ticket types and proper pricing!

---

## Registration Failed - Check These:

1. **Database Connection:**
   - Verify `.env` file in backend has correct credentials
   - Test by visiting: http://localhost/ticketing-backend/events
   - Should return JSON with events

2. **CORS Issues:**
   - Check browser console (F12) for errors
   - Verify `ALLOWED_ORIGINS` in backend `.env` is `http://localhost:3000`

3. **Backend .htaccess:**
   - Make sure `.htaccess` file exists in backend folder
   - Should be copied automatically

---

## Quick Fix Steps:

```bash
# 1. Import fresh database
# Go to phpMyAdmin and import: database/schema-fresh.sql

# 2. Verify backend .env
# Check that backend/.env has:
DB_HOST=localhost
DB_NAME=event_ticketing
DB_USER=root
DB_PASS=

# 3. Test backend
# Visit: http://localhost/ticketing-backend/events
# Should show JSON with 6 events

# 4. Test frontend
# Visit: http://localhost:3000
# Should show 6 events on homepage

# 5. Try login
# Email: attendee@example.com
# Password: password
```

---

## Still Having Issues?

**Check backend is working:**
```
http://localhost/ticketing-backend/events
```
Should return:
```json
{
  "success": true,
  "data": {
    "events": [...]
  }
}
```

**Check frontend API URL:**
Open `frontend/.env` - should have:
```
REACT_APP_API_URL=http://localhost/ticketing-backend
```

**Check browser console (F12):**
- Look for CORS errors
- Look for network errors
- Check if API requests are being made

---

## All Fixed? ✅

After importing `schema-fresh.sql`, you should have:
- ✅ Working login credentials
- ✅ 6 sample events visible
- ✅ Multiple ticket types per event
- ✅ Registration working
- ✅ All features functional
