# Quick Start Guide

Get your Event Ticketing System up and running in minutes!

## For Local Development (Windows)

### Step 1: Install Prerequisites (10 minutes)

1. **Install XAMPP** (includes PHP & MySQL)
   - Download from: https://www.apachefriends.org/
   - Install to default location (C:\xampp)
   - Start Apache and MySQL from XAMPP Control Panel

2. **Install Node.js**
   - Download from: https://nodejs.org/
   - Install LTS version
   - Verify: Open Command Prompt and run `node --version`

### Step 2: Setup Database (5 minutes)

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create new database: `event_ticketing`
3. Click "Import" tab
4. Choose file: `database/schema.sql`
5. Click "Go"

### Step 3: Configure Backend (3 minutes)

1. Copy the `backend` folder from your project to XAMPP:
   - Source: `C:\Users\user\Ticketing Software\backend`
   - Destination: `C:\xampp\htdocs\ticketing-backend`
   - (You can name it anything, but we'll use "ticketing-backend")

2. Create `.env` file (copy from `.env.example`):
   - Open Command Prompt in `C:\xampp\htdocs\ticketing-backend`
   - Run: `copy .env.example .env`
   - (This keeps .env.example as reference and creates your actual .env)

3. Generate a secure JWT secret (choose one method):
   
   **Method A - Use XAMPP PHP:**
   ```powershell
   C:\xampp\php\php.exe generate-jwt-secret.php
   ```
   
   **Method B - Generate manually:**
   - Use any 32+ character random string
   - Example: `k9mP#vL2$nR8@wQ4&yT6^zA1!xC3%bV5*hN7-jM0+gF9=dS2~eW4`
   - Or visit: https://generate-secret.now.sh/64

4. Edit your new `.env` file with these settings:
```env
DB_HOST=localhost
DB_NAME=event_ticketing
DB_USER=root
DB_PASS=

JWT_SECRET=paste-your-generated-secret-here
ALLOWED_ORIGINS=http://localhost:3000
```

5. Test backend: Visit http://localhost/ticketing-backend/events
   - You should see JSON with sample events

### Step 4: Setup Frontend (5 minutes)

1. Open Command Prompt in `frontend` folder

2. Install dependencies:
```bash
npm install
```

3. Create `.env` file:
```env
REACT_APP_API_URL=http://localhost/ticketing-backend
```

4. Start development server:
```bash
npm start
```

5. Browser opens automatically at http://localhost:3000

### Step 5: Test the System (5 minutes)

**Try these demo accounts:**

Attendee:
- Email: `attendee@example.com`
- Password: `password`

Organizer:
- Email: `organizer@example.com`
- Password: `password`

**What to test:**
1. ✅ Login with attendee account
2. ✅ Browse events on homepage
3. ✅ Click an event to view details
4. ✅ Purchase tickets (simulated payment)
5. ✅ View tickets in "My Tickets"
6. ✅ Logout and login as organizer
7. ✅ Create a new event
8. ✅ View organizer dashboard

---

## For cPanel Deployment (15 minutes)

### Prerequisites
- cPanel hosting account
- Domain or subdomain
- Access to cPanel

### Quick Deploy Steps

**1. Database (3 min)**
- cPanel → MySQL Databases
- Create database & user
- Import `database/schema.sql` via phpMyAdmin

**2. Backend (5 min)**
- Upload `backend` folder to `public_html/api`
- Rename `.env.example` to `.env`
- Edit `.env` with your database credentials

**3. Frontend (7 min)**
- On your computer: `cd frontend`
- Edit `.env`: `REACT_APP_API_URL=https://yourdomain.com/api`
- Run: `npm install` (first time only)
- Run: `npm run build`
- Upload contents of `build` folder to `public_html`

**4. Test**
- Visit: https://yourdomain.com
- Login with demo credentials

---

## Troubleshooting

### Backend Issues

**Problem: "Database connection failed"**
- Check database credentials in `.env`
- Verify database exists
- Check if MySQL is running

**Problem: "404 on /api/events"**
- Verify `.htaccess` file exists in backend
- Check if mod_rewrite is enabled
- Verify backend folder location

### Frontend Issues

**Problem: "Network Error" in browser**
- Check `REACT_APP_API_URL` in `.env`
- Verify backend is running
- Check browser console for CORS errors

**Problem: White screen**
- Check browser console for errors
- Verify all files built correctly
- Try: `npm run build` again

**Problem: "Cannot find module"**
- Run: `npm install`
- Delete `node_modules` and run `npm install` again

---

## Common Commands

### Frontend Development
```bash
# Install dependencies
npm install

# Start development server
npm start

# Build for production
npm run build

# Check for errors
npm run build
```

### Backend (No build needed!)
- Just edit PHP files
- Changes take effect immediately
- Check PHP error logs if issues

---

## File Locations Reminder

**Local Development:**
- Backend: `C:\xampp\htdocs\ticketing-backend`
- Frontend: Run from project folder with `npm start`
- Database: Access via http://localhost/phpmyadmin

**cPanel Production:**
- Backend: `public_html/api` (or `/backend`)
- Frontend: `public_html` (root)
- Database: Access via cPanel phpMyAdmin

---

## Next Steps

After everything is working:

1. **Customize Branding**
   - Update colors in `frontend/src/index.css`
   - Change site name in components
   - Add your logo

2. **Security Hardening**
   - Change JWT_SECRET to random string
   - Use strong database passwords
   - Enable HTTPS

3. **Add Features**
   - Integrate real payment gateway
   - Add email notifications
   - Implement QR code library
   - Add event categories

4. **Optimize**
   - Enable caching
   - Compress images
   - Use CDN for assets

---

## Getting Help

**Check these first:**
1. Browser console (F12) for frontend errors
2. PHP error logs for backend issues
3. Network tab to see API requests
4. README.md for detailed documentation
5. API_DOCUMENTATION.md for endpoint reference

**Still stuck?**
- Review DEPLOYMENT.md for detailed steps
- Check PROJECT_STRUCTURE.md to understand architecture
- Verify all prerequisites are installed

---

## Success Checklist

- [ ] XAMPP installed and running (local)
- [ ] Database created and imported
- [ ] Backend accessible and returning JSON
- [ ] Frontend installed (`npm install` complete)
- [ ] Frontend running on http://localhost:3000
- [ ] Can login with demo credentials
- [ ] Can view events
- [ ] Can purchase tickets
- [ ] Can create events as organizer

**All checked?** Congratulations! 🎉 Your ticketing system is ready!

---

## Quick Reference - Default Ports

- Frontend Dev Server: http://localhost:3000
- Backend (XAMPP): http://localhost/ticketing-backend
- phpMyAdmin: http://localhost/phpmyadmin
- MySQL Port: 3306

---

**Time to complete:** ~30 minutes for local setup, ~20 minutes for cPanel deployment

Ready to build something amazing! 🚀
