# PrestaShop Abandoned Cart Recovery Module

## Description

This PrestaShop module helps recover abandoned carts by automatically sending reminder emails to customers who left items in their shopping cart. The module follows the MVC pattern and uses Composer for autoloading with the `MLAB\PE\` namespace.

## Features

- **Automatic Cart Tracking**: Tracks customer carts and identifies abandoned ones
- **Email Notifications**: Sends beautiful HTML emails with product lists and cart totals
- **Recovery Links**: Includes a secure one-click link to restore the cart
- **Cron Job Support**: Process abandoned carts via scheduled cron job
- **Configurable Delay**: Set the time delay before considering a cart abandoned
- **MVC Architecture**: Clean separation of concerns with Model-View-Controller pattern
- **PSR-4 Autoloading**: Uses Composer with `MLAB\PE\` namespace

## Installation

1. Copy the `pe_abandonedcart` folder to your PrestaShop `modules` directory
2. Run `composer install` in the module directory to install dependencies
3. Go to **Modules > Module Manager** in your PrestaShop back office
4. Find "Abandoned Cart Recovery" and click **Install**
5. Configure the module settings

## Configuration

After installation, configure the module:

1. Go to **Modules > Module Manager**
2. Find "Abandoned Cart Recovery" and click **Configure**
3. Set the following options:
   - **Enable abandoned cart recovery**: Turn the feature on/off
   - **Delay before sending email**: Time in minutes to wait before sending the reminder (default: 60 minutes)
   - **Cron URL**: Copy this URL to set up your cron job

## Cron Job Setup

To automatically process abandoned carts, set up a cron job:

```bash
*/15 * * * * curl "https://yourstore.com/module/pe_abandonedcart/cron?token=YOUR_TOKEN"
```

Replace:
- `yourstore.com` with your actual domain
- `YOUR_TOKEN` with the token shown in the module configuration

The cron job should run every 15-30 minutes for best results.

## Email Template

The module includes a professional email template with:
- Personalized greeting
- List of products with images
- Cart total
- Call-to-action button to complete the purchase
- Responsive design for mobile devices

## How It Works

1. **Cart Tracking**: When a customer adds items to their cart, the module tracks it
2. **Abandonment Detection**: If the customer doesn't complete checkout within the configured delay, the cart is marked as abandoned
3. **Email Sending**: The cron job processes abandoned carts and sends recovery emails
4. **Cart Recovery**: When the customer clicks the recovery link, their cart is restored and they're redirected to checkout

## Database Structure

The module creates a table `ps_abandoned_cart` with the following fields:
- `id_abandoned_cart`: Primary key
- `id_cart`: Cart ID
- `id_customer`: Customer ID
- `email`: Customer email
- `cart_data`: JSON data of cart contents
- `recovery_token`: Unique token for recovery link
- `date_add`: Creation date
- `date_upd`: Last update date
- `email_sent`: Whether email was sent
- `date_email_sent`: When email was sent
- `recovered`: Whether cart was recovered
- `date_recovered`: When cart was recovered

## Architecture

The module follows MVC pattern with the following structure:

```
pe_abandonedcart/
├── src/
│   ├── Controller/       # Controllers (Cron, Recovery)
│   ├── Model/           # Data models (AbandonedCart)
│   └── Service/         # Business logic (AbandonedCartService)
├── views/
│   └── templates/
│       ├── email/       # Email templates
│       └── front/       # Frontend templates
├── config/              # Configuration files
├── controllers/         # PrestaShop controllers
│   └── front/          # Frontend controllers
├── composer.json        # Composer configuration
└── pe_abandonedcart.php # Main module file
```

## Namespace

All classes use the `MLAB\PE\` namespace as specified in the requirements:
- `MLAB\PE\Model\AbandonedCart`
- `MLAB\PE\Service\AbandonedCartService`

## Requirements

- PrestaShop 1.7.0 or higher
- PHP 7.2 or higher
- Composer

## Support

For support, please contact MLAB Factory at info@mlabfactory.com

## License

MIT License

Copyright (c) 2025 MLAB Factory

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
