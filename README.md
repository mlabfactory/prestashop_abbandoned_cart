# PrestaShop Abandoned Cart Recovery

Recovery a customer abandoned cart by sending an email.

## Module

This repository contains the `pe_abandonedcart` PrestaShop module that implements abandoned cart recovery functionality.

### Features

- **MVC Pattern**: Clean architecture with Model-View-Controller separation
- **Composer Support**: PSR-4 autoloading with `MLAB\PE\` namespace
- **Cron Job**: Automated abandoned cart detection and email sending
- **Recovery Emails**: Beautiful HTML emails with product list and CTA button
- **One-Click Recovery**: Secure token-based cart restoration

### Installation

1. Copy the `pe_abandonedcart` folder to your PrestaShop `modules` directory
2. Run `composer install` in the module directory
3. Install the module from PrestaShop back office
4. Configure settings and set up the cron job

For detailed documentation, see [pe_abandonedcart/README.md](pe_abandonedcart/README.md)
