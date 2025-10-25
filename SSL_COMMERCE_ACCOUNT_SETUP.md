# SSL Commerce Account Setup Guide

## 🚀 Getting Started with SSL Commerce

### Step 1: Register for SSL Commerce Account

1. **Visit SSL Commerce Developer Portal:**
   - Go to: https://developer.sslcommerz.com/
   - Click "Register" to create a new account

2. **Complete Registration:**
   - Fill in your business details
   - Provide contact information
   - Verify your email address

### Step 2: Get Sandbox Credentials (For Testing)

1. **Login to Developer Portal:**
   - Use your registered credentials
   - Navigate to "My Stores" section

2. **Create Sandbox Store:**
   - Click "Create New Store"
   - Select "Sandbox" environment
   - Fill in store details

3. **Get Credentials:**
   - Store ID: `testbox`
   - Store Password: `qwerty`
   - API URL: `https://sandbox.sslcommerz.com/gwprocess/v4/api.php`

### Step 3: Configure Your Application

Update your `.env` file with sandbox credentials:

```env
SSL_COMMERCE_STORE_ID=testbox
SSL_COMMERCE_STORE_PASSWORD=qwerty
SSL_COMMERCE_API_URL=https://sandbox.sslcommerz.com/gwprocess/v4/api.php
SSL_COMMERCE_VALIDATION_URL=https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php
SSL_COMMERCE_ENVIRONMENT=sandbox
```

### Step 4: Test Payment Flow

1. **Start Your Application:**
   ```bash
   php artisan serve
   ```

2. **Test with Sandbox:**
   - Add items to cart
   - Proceed to checkout
   - Select "Online Payment"
   - Use test card: `4111111111111111`
   - Expiry: Any future date
   - CVV: Any 3 digits

### Step 5: Get Live Credentials (For Production)

1. **Complete Merchant Verification:**
   - Submit business documents
   - Complete KYC process
   - Wait for approval (1-3 business days)

2. **Get Live Credentials:**
   - Store ID: Your unique store ID
   - Store Password: Your secure password
   - API URL: `https://securepay.sslcommerz.com/gwprocess/v4/api.php`

3. **Update Production Environment:**
   ```env
   SSL_COMMERCE_STORE_ID=your_live_store_id
   SSL_COMMERCE_STORE_PASSWORD=your_live_password
   SSL_COMMERCE_API_URL=https://securepay.sslcommerz.com/gwprocess/v4/api.php
   SSL_COMMERCE_VALIDATION_URL=https://securepay.sslcommerz.com/validator/api/validationserverAPI.php
   SSL_COMMERCE_ENVIRONMENT=live
   ```

### Step 6: Configure Callback URLs

In your SSL Commerce merchant panel, set these callback URLs:

- **Success URL:** `https://yourdomain.com/payment/success`
- **Fail URL:** `https://yourdomain.com/payment/failed`
- **Cancel URL:** `https://yourdomain.com/payment/cancelled`
- **IPN URL:** `https://yourdomain.com/payment/ipn`

### Step 7: SSL Certificate Setup

#### For Development:
```bash
# Using Laravel Valet
valet secure your-site

# Using ngrok
ngrok http 8000 --host-header=rewrite
```

#### For Production:
1. Purchase SSL certificate from trusted CA
2. Install on your web server
3. Ensure all URLs use HTTPS
4. Test SSL configuration

### Step 8: Testing Checklist

- [ ] Sandbox credentials configured
- [ ] SSL certificate installed
- [ ] Callback URLs accessible
- [ ] Test payment successful
- [ ] Payment verification working
- [ ] Error handling tested
- [ ] Mobile responsiveness verified

### Step 9: Go Live Checklist

- [ ] Live credentials obtained
- [ ] Merchant verification completed
- [ ] Production SSL certificate installed
- [ ] Live callback URLs configured
- [ ] Payment flow tested in production
- [ ] Monitoring and logging setup
- [ ] Backup procedures in place

## 🔧 Troubleshooting

### Common Issues:

1. **"Invalid Store ID" Error:**
   - Check store credentials
   - Ensure correct environment (sandbox/live)

2. **"SSL Certificate Error":**
   - Install valid SSL certificate
   - Check certificate chain

3. **"Callback URL Not Accessible":**
   - Ensure URLs are HTTPS
   - Check server firewall settings

4. **"Payment Verification Failed":**
   - Verify validation URL
   - Check network connectivity

### Support Contacts:

- **SSL Commerce Support:** support@sslcommerz.com
- **Developer Documentation:** https://developer.sslcommerz.com/doc/
- **API Reference:** https://developer.sslcommerz.com/doc/

## 📱 Test Payment Methods

### Sandbox Test Cards:
- **Visa:** 4111111111111111
- **Mastercard:** 5555555555554444
- **Expiry:** Any future date (MM/YY)
- **CVV:** Any 3 digits

### Test Mobile Banking:
- Use sandbox mobile numbers
- No real money transactions
- All transactions are simulated

## 🚀 Ready to Go Live!

Once you've completed all steps and testing, your SSL Commerce integration will be ready for production use. Your customers can now make secure payments using:

- Credit/Debit Cards
- Mobile Banking (bKash, Rocket, Nagad)
- Internet Banking
- Digital Wallets

The integration provides a seamless, secure, and user-friendly payment experience for your e-commerce platform.
