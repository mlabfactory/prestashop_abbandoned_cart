# Installation Guide - PrestaShop Abandoned Cart Recovery Module

## Prerequisites

Before installing the module, ensure you have:

- PrestaShop 1.7.0 or higher installed
- PHP 7.2 or higher
- Composer installed on your server
- Access to PrestaShop back office
- Ability to set up cron jobs

## Installation Steps

### Step 1: Upload Module

1. Download or clone this repository
2. Upload the `pe_abandonedcart` folder to your PrestaShop installation directory:
   ```
   /modules/pe_abandonedcart/
   ```

### Step 2: Install Composer Dependencies

Navigate to the module directory and install dependencies:

```bash
cd /path/to/prestashop/modules/pe_abandonedcart
composer install
```

This will install the autoloader and set up the `MLAB\PE\` namespace.

### Step 3: Install Module in PrestaShop

1. Log in to your PrestaShop back office
2. Go to **Modules > Module Manager**
3. Search for "Abandoned Cart Recovery"
4. Click the **Install** button
5. Click **Configure** when prompted

### Step 4: Configure Module Settings

After installation, configure the module:

1. **Enable abandoned cart recovery**: Set to "Yes" to activate the feature
2. **Delay before sending email**: Set the time (in minutes) to wait before considering a cart abandoned
   - Default: 60 minutes
   - Recommended: 30-120 minutes
3. **Copy the Cron URL**: You'll need this for the next step

### Step 5: Set Up Cron Job

The module requires a cron job to automatically process abandoned carts and send emails.

#### Option A: Using cPanel

1. Log in to your cPanel
2. Go to **Advanced > Cron Jobs**
3. Add a new cron job:
   - **Minute**: */15 (every 15 minutes)
   - **Hour**: * (every hour)
   - **Day**: * (every day)
   - **Month**: * (every month)
   - **Weekday**: * (every weekday)
   - **Command**: 
     ```
     curl "https://yourstore.com/module/pe_abandonedcart/cron?token=YOUR_TOKEN"
     ```

#### Option B: Using Command Line

Add to your crontab:

```bash
crontab -e
```

Add this line:

```
*/15 * * * * curl "https://yourstore.com/module/pe_abandonedcart/cron?token=YOUR_TOKEN" > /dev/null 2>&1
```

#### Option C: Using wget

If curl is not available:

```
*/15 * * * * wget -q -O - "https://yourstore.com/module/pe_abandonedcart/cron?token=YOUR_TOKEN" > /dev/null 2>&1
```

**Important Notes:**
- Replace `yourstore.com` with your actual domain
- Replace `YOUR_TOKEN` with the token shown in the module configuration
- The cron job should run every 15-30 minutes for optimal results
- More frequent runs won't cause issues but may increase server load

### Step 6: Test the Installation

To verify the module is working:

1. **Test Cart Tracking**:
   - Log in as a customer
   - Add items to cart
   - Leave the site without completing checkout
   - Wait for the configured delay period

2. **Check Admin Panel**:
   - Go to **Orders > Abandoned Carts** in the back office
   - You should see your abandoned cart listed

3. **Test Cron Manually**:
   - Visit the cron URL in your browser (with the token)
   - You should see a JSON response: `{"success":true,"emails_sent":N,"timestamp":"..."}`

4. **Check Email**:
   - Wait for the cron job to run (or trigger it manually)
   - Check the customer's email inbox
   - Verify the recovery email was received

5. **Test Recovery Link**:
   - Click the "Complete Your Purchase" button in the email
   - Verify you're redirected to the cart with items restored

## File Permissions

Ensure proper permissions for:

```bash
chmod 755 /path/to/prestashop/modules/pe_abandonedcart/
chmod 644 /path/to/prestashop/modules/pe_abandonedcart/*.php
chmod 755 /path/to/prestashop/modules/pe_abandonedcart/vendor/
```

## Troubleshooting

### Module Won't Install

**Problem**: Module installation fails  
**Solutions**:
- Ensure Composer dependencies are installed (`composer install`)
- Check file permissions
- Verify PHP version (7.2+)
- Check PrestaShop error logs

### Cron Job Not Working

**Problem**: Emails are not being sent  
**Solutions**:
- Verify the cron URL is correct with the proper token
- Check if the cron job is running: `grep CRON /var/log/syslog`
- Manually visit the cron URL to test
- Check email settings in PrestaShop
- Verify the module is enabled in configuration

### Emails Not Sending

**Problem**: Cron runs but no emails are sent  
**Solutions**:
- Check PrestaShop email configuration (SMTP settings)
- Verify customers have valid email addresses
- Check the delay setting (carts must exceed delay time)
- Look in **Orders > Abandoned Carts** to see if carts are tracked
- Check PrestaShop error logs

### Cart Not Recovering

**Problem**: Recovery link doesn't restore cart  
**Solutions**:
- Verify the token in the URL is valid
- Check if the cart still exists in the database
- Ensure the cart hasn't already been recovered
- Check browser cookies are enabled

### Database Issues

**Problem**: Database tables not created  
**Solutions**:
- Manually run the SQL from the module file
- Check database permissions
- Verify database prefix matches PrestaShop configuration

## Uninstallation

To uninstall the module:

1. Go to **Modules > Module Manager**
2. Find "Abandoned Cart Recovery"
3. Click **Uninstall**
4. Confirm the uninstallation

**Note**: Uninstalling will:
- Remove the database table (`ps_abandoned_cart`)
- Delete all abandoned cart data
- Remove the admin tab
- Remove configuration values

## Support

For issues or questions:
- Check the [README.md](pe_abandonedcart/README.md) documentation
- Review PrestaShop error logs
- Contact MLAB Factory support

## Security Notes

- The cron endpoint is protected by a secure token
- Recovery links use unique, one-time tokens
- All user inputs are sanitized
- Database queries use prepared statements
- Module follows PrestaShop security best practices
