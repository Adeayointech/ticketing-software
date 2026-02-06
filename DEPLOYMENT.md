# Deployment Guide - Event Ticketing System

## Quick Deployment to cPanel

This guide will help you deploy the Event Ticketing System to any cPanel hosting.

## Prerequisites

- cPanel hosting account with PHP 7.4+
- MySQL database access
- SSH access (optional but recommended)
- Domain or subdomain pointed to your hosting

## Step-by-Step Deployment

### Part 1: Database Setup (5 minutes)

1. **Login to cPanel**
   - Navigate to your cPanel dashboard

2. **Create MySQL Database**
   - Go to "MySQL Databases"
   - Create new database: `yourusername_ticketing`
   - Create new user with strong password
   - Add user to database with ALL PRIVILEGES

3. **Import Database Schema**
   - Go to phpMyAdmin
   - Select your new database
   - Click "Import" tab
   - Choose `database/schema.sql` file
   - Click "Go"

### Part 2: Backend Deployment (10 minutes)

1. **Upload Backend Files**
   - Using File Manager or FTP, upload the `backend` folder
   - Recommended location: `public_html/api` or `public_html/backend`

2. **Configure Environment**
   - Navigate to the backend folder
   - Rename `.env.example` to `.env`
   - Edit `.env` file with your details:
   ```env
   DB_HOST=localhost
   DB_NAME=yourusername_ticketing
   DB_USER=yourusername_dbuser
   DB_PASS=your_db_password
   
   JWT_SECRET=generate-random-32-character-string-here
   
   ALLOWED_ORIGINS=https://yourdomain.com
   ```

3. **Set Folder Permissions**
   - Set `uploads` folder permission to 755
   - Set `uploads/qrcodes` folder permission to 755
   - Ensure `.htaccess` file exists and is readable

4. **Test Backend**
   - Visit: `https://yourdomain.com/api/events`
   - You should see a JSON response with sample events

### Part 3: Frontend Deployment (15 minutes)

1. **Build React Application Locally**
   ```bash
   cd frontend
   
   # Update .env with your API URL
   echo REACT_APP_API_URL=https://yourdomain.com/api > .env
   
   # Install dependencies (first time only)
   npm install
   
   # Build for production
   npm run build
   ```

2. **Upload Build Files**
   - Upload contents of `build` folder to `public_html`
   - Or to a subdirectory like `public_html/ticketing`

3. **Configure React Router**
   - Create `.htaccess` in the frontend directory:
   ```apache
   <IfModule mod_rewrite.c>
     RewriteEngine On
     RewriteBase /
     RewriteRule ^index\.html$ - [L]
     RewriteCond %{REQUEST_FILENAME} !-f
     RewriteCond %{REQUEST_FILENAME} !-d
     RewriteRule . /index.html [L]
   </IfModule>
   ```

4. **Test Frontend**
   - Visit: `https://yourdomain.com`
   - You should see the homepage with events

## Alternative: Direct cPanel Upload Without Local Build

If you can't run npm locally:

1. **Use the pre-built version** (if provided)
2. **Or request a build** from someone with Node.js
3. **Or use cPanel Node.js selector** if available:
   - Enable Node.js in cPanel
   - Upload frontend source
   - Run npm install and npm build via SSH

## Post-Deployment Checklist

- [ ] Database imported successfully
- [ ] Backend `.env` configured
- [ ] Backend accessible at `/api/events`
- [ ] Frontend shows events list
- [ ] Can register new account
- [ ] Can login with demo credentials
- [ ] Can browse and view events
- [ ] Tickets can be purchased (test mode)

## Test the System

1. **Test Registration:**
   - Go to Register page
   - Create attendee account
   - Verify redirect to dashboard

2. **Test Event Browsing:**
   - View events on homepage
   - Click event to see details
   - Check ticket types display

3. **Test Ticket Purchase:**
   - Login as attendee
   - Select tickets
   - Complete purchase
   - View tickets in "My Tickets"

4. **Test Organizer Features:**
   - Login with organizer account
   - Create new event
   - View in organizer dashboard

## Common Issues and Solutions

### Issue: 404 on API endpoints

**Solution:**
- Check `.htaccess` exists in backend folder
- Verify mod_rewrite is enabled
- Check file permissions

### Issue: Database connection error

**Solution:**
- Verify database credentials in `.env`
- Ensure database user has proper privileges
- Check if database exists

### Issue: CORS errors in browser console

**Solution:**
- Update `ALLOWED_ORIGINS` in backend `config/config.php`
- Check `.htaccess` CORS headers
- Verify API URL in frontend `.env`

### Issue: React Router 404 on page refresh

**Solution:**
- Add `.htaccess` to frontend directory
- Verify RewriteEngine is on
- Check RewriteBase matches your directory

### Issue: White screen after deployment

**Solution:**
- Check browser console for errors
- Verify API URL is correct
- Check if build files uploaded correctly

## Security Hardening

1. **Change JWT Secret:**
   - Generate strong random string
   - Update in `.env`

2. **Secure Database:**
   - Use strong password
   - Limit user privileges if possible

3. **Enable HTTPS:**
   - Use SSL certificate (Let's Encrypt free)
   - Force HTTPS redirect

4. **Protect .env File:**
   - Add to `.htaccess`:
   ```apache
   <Files .env>
     Order allow,deny
     Deny from all
   </Files>
   ```

## Performance Optimization

1. **Enable Caching:**
   - Add browser caching rules to `.htaccess`

2. **Compress Assets:**
   - Enable gzip compression in `.htaccess`

3. **Optimize Database:**
   - Add indexes (already in schema)
   - Regular cleanup of old data

4. **CDN (Optional):**
   - Use CDN for static assets
   - Serve images from CDN

## Updating the Application

### Backend Updates:
1. Upload new/modified PHP files
2. Run any new SQL migrations
3. Update `.env` if needed
4. Clear any PHP opcache

### Frontend Updates:
1. Update source code locally
2. Run `npm run build`
3. Upload new build files
4. Clear browser cache

## Backup Recommendations

1. **Database Backup:**
   - Use cPanel backup or phpMyAdmin export
   - Schedule automatic backups

2. **File Backup:**
   - Backup `backend` folder (especially `.env`)
   - Backup `frontend` build files

3. **Backup Schedule:**
   - Daily database backups
   - Weekly full file backups

## Need Help?

- Check browser console for errors
- Check PHP error logs in cPanel
- Review `README.md` for detailed documentation
- Verify all prerequisites are met

---

## Quick Reference - File Structure on Server

```
public_html/
├── api/                      # Backend files
│   ├── .env                  # Database config (SECURE THIS!)
│   ├── .htaccess             # URL routing
│   ├── index.php             # API entry point
│   ├── config/               # Configuration files
│   ├── controllers/          # API controllers
│   ├── models/               # Database models
│   ├── utils/                # Utility classes
│   ├── Firebase/             # JWT library
│   └── uploads/              # User uploads
│       └── qrcodes/          # Generated QR codes
│
├── index.html                # Frontend entry point
├── .htaccess                 # React Router config
├── static/                   # CSS, JS bundles
├── favicon.ico
└── asset-manifest.json
```

This completes your deployment! 🎉
