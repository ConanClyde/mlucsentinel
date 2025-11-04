# Railway Deployment Setup Guide

## Required Environment Variables

### Application Settings
```env
APP_NAME=MLUCSentinel
APP_ENV=production
APP_KEY=base64:jfCJSCalfyH+dGU3/g/uO9S7qROFt4dmN2REPR5KhbQ=
APP_DEBUG=false
APP_URL=https://mluc-sentinel.com
ASSET_URL=https://mluc-sentinel.com
```

### Database (Use Railway MySQL Service Variables)
```env
DB_CONNECTION=mysql
DB_HOST=${{RAILWAY_PRIVATE_DOMAIN}}
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=${{MYSQL_ROOT_PASSWORD}}
```

### Session & Cache
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

### Broadcasting (Reverb)
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=338257
REVERB_APP_KEY=xjybg4ttpkkoazcqudlb
REVERB_APP_SECRET=wz3y3d82d2g8s9ygz74z
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https
```

### Vite (Frontend)
```env
VITE_APP_NAME="${APP_NAME}"
VITE_REVERB_APP_KEY=xjybg4ttpkkoazcqudlb
VITE_REVERB_HOST=mluc-sentinel.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mlucsentinel@gmail.com
MAIL_PASSWORD=fklkmfoettidswha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=mlucsentinel@gmail.com
MAIL_FROM_NAME="MLUC Sentinel"
```

### Logging
```env
LOG_CHANNEL=stack
LOG_LEVEL=info
```

## Setup Steps

### 1. Add MySQL Database
1. In Railway dashboard, click **+ New**
2. Select **Database** → **MySQL**
3. Railway will auto-create connection variables

### 2. Configure Environment Variables
1. Go to your service → **Variables** tab
2. Add all variables listed above
3. Use Railway's reference syntax for database: `${{MYSQLHOST}}`

### 3. Enable Custom Domain
1. Go to **Settings** → **Networking**
2. Click **Generate Domain** for Railway subdomain
3. Add custom domain: `mluc-sentinel.com`
4. Configure DNS CNAME record at Namecheap

### 4. Deploy
Railway will automatically deploy when you push to GitHub.

### 5. Run Seeders (First Time Only)
```bash
# Install Railway CLI
npm i -g @railway/cli

# Login and link
railway login
railway link

# Run seeders
railway run php artisan db:seed --force
railway run php artisan db:seed --class=UsersSeeder --force
```

## Services Running

The deployment runs these services:
- ✅ **Web Server** (port 8000)
- ✅ **Reverb WebSocket** (port 8080)
- ✅ **Queue Worker** (background)
- ✅ **Storage Link**
- ✅ **Cron Jobs** (if configured)

## Monitoring

Check logs in Railway dashboard:
- **Deploy logs** - Build and deployment process
- **Application logs** - Runtime logs from your app
- **Database logs** - MySQL query logs

## Troubleshooting

### Mixed Content Errors
- Ensure `APP_URL` and `ASSET_URL` use `https://`
- Check `APP_ENV=production`

### Database Connection Failed
- Verify MySQL service is running
- Check database variables are using `${{MYSQLHOST}}` syntax

### Assets Not Loading
- Run `npm run build` locally and commit
- Check `public/build` directory exists in repo

### Reverb Not Connecting
- Verify `VITE_REVERB_HOST` matches your domain
- Check `VITE_REVERB_PORT=443` (not 8080)
- Ensure `REVERB_SCHEME=https`

## Post-Deployment Commands

Run via Railway CLI:
```bash
# Clear caches
railway run php artisan cache:clear

# View logs
railway logs

# Run migrations
railway run php artisan migrate --force

# Access shell
railway run bash
```

