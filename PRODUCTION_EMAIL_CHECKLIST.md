# Production Email Setup Checklist

## ✅ Current Status: Production Ready

Your email system is ready for production with minimal setup required.

## 🔧 Required Production Setup

### 1. Environment Configuration

Ensure your `.env` file has:
```env
QUEUE_CONNECTION=database
```

### 2. Run Database Migrations

Make sure queue tables exist:
```bash
php artisan migrate
```

This creates:
- `jobs` table (for queued jobs)
- `failed_jobs` table (for failed jobs)
- `job_batches` table (for batch jobs)

### 3. Set Up Queue Worker (CRITICAL)

**For Production, you MUST run a queue worker.** Choose one option:

#### Option A: Supervisor (Recommended for Production)

1. **Install Supervisor** (if not installed):
```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor
```

2. **Create config file** `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

3. **Update paths** in the config:
   - Replace `/path/to/your/project` with your actual project path
   - Replace `www-data` with your web server user

4. **Start Supervisor**:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

5. **Check status**:
```bash
sudo supervisorctl status
```

#### Option B: Systemd Service (Alternative)

Create `/etc/systemd/system/laravel-worker.service`:
```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /path/to/your/project/artisan queue:work database --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
sudo systemctl status laravel-worker
```

#### Option C: Cron Job (Simple but less reliable)

Add to crontab (`crontab -e`):
```bash
* * * * * cd /path/to/your/project && php artisan queue:work database --stop-when-empty
```

### 4. Verify SMTP Configuration

1. Go to `/erp/settings` → SMTP Email tab
2. Configure your production SMTP settings
3. Test SMTP configuration using the test button
4. Verify emails are received

### 5. Monitor Queue Status

**Check queued jobs:**
```bash
php artisan tinker
DB::table('jobs')->count();
```

**Check failed jobs:**
```bash
php artisan queue:failed
```

**Retry failed jobs:**
```bash
php artisan queue:retry all
```

**View worker logs:**
```bash
tail -f storage/logs/worker.log
```

## 📊 Production Monitoring

### Daily Checks

1. **Monitor failed jobs:**
   ```bash
   php artisan queue:failed
   ```

2. **Check worker status:**
   ```bash
   sudo supervisorctl status
   # or
   sudo systemctl status laravel-worker
   ```

3. **View application logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i email
   ```

### Set Up Alerts (Optional but Recommended)

Monitor for:
- High number of failed jobs
- Queue worker not running
- SMTP connection failures

## 🔒 Security Checklist

- ✅ SMTP credentials stored in database (not hardcoded)
- ✅ Queue worker runs as non-root user
- ✅ Failed jobs logged for debugging
- ✅ No sensitive data in logs

## ⚡ Performance Optimization

### For High Traffic Sites

1. **Increase queue workers:**
   ```ini
   numprocs=4  # In supervisor config
   ```

2. **Use Redis queue** (faster than database):
   ```env
   QUEUE_CONNECTION=redis
   ```
   Then update job connection in `SendOrderEmails.php`

3. **Monitor queue size** and scale workers as needed

## 🧪 Pre-Launch Testing

1. **Test order placement:**
   - Place a test order
   - Verify order response is instant
   - Check email inbox (and spam folder)
   - Verify owner notification email

2. **Test queue processing:**
   ```bash
   php artisan queue:work --once
   ```

3. **Test failed job handling:**
   - Temporarily break SMTP config
   - Place order
   - Check failed_jobs table
   - Fix SMTP
   - Retry: `php artisan queue:retry all`

## ✅ Production Readiness Checklist

- [ ] Queue worker running (Supervisor/Systemd/Cron)
- [ ] SMTP configured and tested
- [ ] Database migrations run
- [ ] Queue tables exist
- [ ] Test order placed successfully
- [ ] Emails received
- [ ] Monitoring set up
- [ ] Failed job handling tested

## 🚨 Troubleshooting

### Emails Not Sending

1. **Check queue worker is running:**
   ```bash
   sudo supervisorctl status
   ```

2. **Check queued jobs:**
   ```bash
   php artisan tinker
   DB::table('jobs')->count();
   ```

3. **Process queue manually:**
   ```bash
   php artisan queue:work --once
   ```

4. **Check SMTP configuration:**
   - Go to `/erp/settings` → Test SMTP

### Queue Worker Not Running

1. **Restart worker:**
   ```bash
   sudo supervisorctl restart laravel-worker:*
   ```

2. **Check logs:**
   ```bash
   tail -f storage/logs/worker.log
   ```

3. **Verify permissions:**
   - Worker user has access to project files
   - Storage directory is writable

## 📝 Summary

**Your email system is production-ready!** Just ensure:

1. ✅ Queue worker is running (Supervisor recommended)
2. ✅ SMTP is configured
3. ✅ Monitor failed jobs regularly

That's it! Your emails will process automatically in the background.

