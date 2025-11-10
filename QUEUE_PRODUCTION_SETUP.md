# Queue Production Setup - Simple Guide

## ✅ What's Already Configured

Your Laravel application is now configured to automatically process queued emails every minute using Laravel's task scheduler.

## 🚀 Production Server Setup (One-Time)

You only need to add **ONE cron job** on your production server. That's it!

### Step 1: Add Cron Job

SSH into your production server and run:

```bash
crontab -e
```

### Step 2: Add This Line

Add this single line to your crontab (replace `/path/to/your/project` with your actual project path):

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

**Example for common paths:**
```bash
# If your project is in /var/www/html/composer1
* * * * * cd /var/www/html/composer1 && php artisan schedule:run >> /dev/null 2>&1

# If your project is in /home/username/public_html
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### Step 3: Save and Exit

- Press `Ctrl+X` (if using nano)
- Press `Y` to confirm
- Press `Enter` to save

## ✅ That's It!

Now your queue will automatically process every minute. No supervisor, no manual commands needed!

## 🔍 How It Works

1. **Cron runs every minute** → Executes `php artisan schedule:run`
2. **Laravel scheduler** → Runs `queue:work --stop-when-empty` 
3. **Queue worker** → Processes all pending email jobs
4. **Emails sent** → Automatically in the background

## 📊 Verify It's Working

Check if jobs are being processed:

```bash
# Check queued jobs
php artisan queue:work --stop-when-empty

# Check failed jobs (if any)
php artisan queue:failed

# View logs
tail -f storage/logs/laravel.log
```

## 🛠️ Troubleshooting

### Cron not running?
```bash
# Check if cron is running
sudo service cron status

# Check your crontab
crontab -l

# Test the command manually
cd /path/to/your/project && php artisan schedule:run
```

### Jobs not processing?
- Make sure `QUEUE_CONNECTION=database` in your `.env` file
- Check that the `jobs` table exists: `php artisan migrate`
- Verify cron is running: `crontab -l`

### Need faster processing?
The queue processes every minute. If you need faster processing, you can change `everyMinute()` to `everyThirtySeconds()` in `bootstrap/app.php`, but this requires a more frequent cron (every 30 seconds).

## 📝 Notes

- **No supervisor needed** - Laravel's scheduler handles everything
- **Automatic retry** - Failed jobs retry up to 3 times
- **No overlap** - Jobs won't run simultaneously
- **Background processing** - Won't block your web server

## 🎯 Summary

**Local Development:** Run `php artisan queue:work` manually when needed

**Production:** Just add the cron job once, and everything works automatically!

