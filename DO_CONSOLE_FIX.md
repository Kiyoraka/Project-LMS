# DigitalOcean Console Fix for Video Upload

## Method 1: Using DigitalOcean Web Console (Easiest)

1. **Login to DigitalOcean**
2. **Go to your Droplet**
3. **Click "Access" → "Launch Droplet Console"**
4. **Run these commands one by one:**

```bash
# Step 1: Create a custom PHP configuration
cd /var/www/html/public
echo "upload_max_filesize = 500M" > .user.ini
echo "post_max_size = 500M" >> .user.ini
echo "max_execution_time = 300" >> .user.ini
echo "max_input_time = 300" >> .user.ini
echo "memory_limit = 512M" >> .user.ini

# Step 2: Update Nginx site configuration
sudo nano /etc/nginx/sites-available/default
```

When nano opens, find the `server {` block and add this line inside:
```
client_max_body_size 500M;
```

Press `Ctrl+X`, then `Y`, then `Enter` to save.

```bash
# Step 3: Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## Method 2: Using DigitalOcean App Platform (If using App Platform)

If you're using DigitalOcean App Platform instead of a Droplet:

1. **Go to your App in DigitalOcean**
2. **Click Settings → App-Level Environment Variables**
3. **Add these:**
   - `PHP_UPLOAD_MAX_FILESIZE` = `500M`
   - `PHP_POST_MAX_SIZE` = `500M`
   - `PHP_MEMORY_LIMIT` = `512M`

4. **Click Settings → App Spec**
5. **Edit the YAML and add:**

```yaml
services:
  - environment_slug: php
    envs:
      - key: PHP_UPLOAD_MAX_FILESIZE
        value: "500M"
      - key: PHP_POST_MAX_SIZE
        value: "500M"
    run_command: |
      echo "upload_max_filesize = 500M" > /app/public/.user.ini
      echo "post_max_size = 500M" >> /app/public/.user.ini
      heroku-php-nginx -C nginx.conf public/
```

## Method 3: Quick Temporary Fix (Without SSH)

**I've already added `.user.ini` to your project!**

Just push this to production:
```bash
git add public/.user.ini
git commit -m "Add PHP upload limits configuration"
git push origin main
```

Then in DigitalOcean Console, just run:
```bash
sudo systemctl restart php8.2-fpm
```

## Method 4: Using RunCloud/ServerPilot/Forge (If you have it)

If you're using a server management panel:
1. Login to your panel
2. Go to PHP Settings
3. Update:
   - upload_max_filesize: 500M
   - post_max_size: 500M
4. Save and restart PHP

## Verify It Worked

After applying any method, check:
https://lmsprototype-app-yo8va.ondigitalocean.app/check-upload-limits.php?token=check-limits-2025

You should see all values in green!

## Emergency Workaround (Client-Side)

If server fix is not possible immediately, implement chunked uploads:

1. Install: `npm install resumablejs`
2. Split files client-side into 2MB chunks
3. Upload chunks separately
4. Reassemble server-side

## Note About Nginx

If you still get errors after PHP fix, you MUST update Nginx:

```bash
# Find your nginx config
ls /etc/nginx/sites-available/

# Edit it (replace 'default' with your site name if different)
sudo nano /etc/nginx/sites-available/default

# Add this inside server block:
client_max_body_size 500M;

# Test and restart
sudo nginx -t
sudo systemctl restart nginx
```