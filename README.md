# Ipsit Invoice Generator

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://wordpress.org/plugins/ipsit-invoice-generator/)
[![WordPress Compatibility](https://img.shields.io/badge/wordpress-5.8%2B-brightgreen.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/php-7.4%2B-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)](LICENSE)

> A professional, feature-rich WordPress plugin for creating, managing, and sending invoices directly from your WordPress dashboard.

## 📋 Description

**Ipsit Invoice Generator** is a comprehensive invoicing solution for WordPress that enables businesses to create professional invoices, manage clients, customize templates, and send invoices via email - all without leaving the WordPress admin area.

Perfect for freelancers, small businesses, agencies, and anyone who needs a simple yet powerful invoicing system integrated into their WordPress site.

### 🌟 Key Highlights

- **Clean & Professional UI** - Modern, intuitive interface that feels native to WordPress
- **Fully Responsive** - Works seamlessly on desktop, tablet, and mobile devices
- **Secure & Validated** - Built with WordPress security best practices
- **Performance Optimized** - Efficient database queries with caching
- **Developer Friendly** - Well-documented, extensible code architecture

---

## ✨ Features

### 📊 Invoice Management
- ✅ Create unlimited invoices with line items
- ✅ Multiple status tracking (Draft, Sent, Paid, Overdue)
- ✅ Automatic invoice numbering with custom prefixes
- ✅ Tax calculation support
- ✅ Notes and terms section
- ✅ PDF generation with custom branding
- ✅ Send invoices via email directly from WordPress

### 👥 Client Management
- ✅ Unlimited client records
- ✅ Store client contact information
- ✅ Custom fields for additional client data
- ✅ Quick client selection during invoice creation

### 🏢 Company Settings
- ✅ Configure company information and branding
- ✅ Custom logo upload
- ✅ Accent color customization
- ✅ Default payment method settings (Bank Transfer)
- ✅ Banking details (Account number, IBAN, IFSC, etc.)

### 🎨 Template Customization
- ✅ Visual template builder
- ✅ Multiple pre-built professional templates
- ✅ Custom template creation
- ✅ Live preview functionality
- ✅ Custom CSS support for advanced styling

### ⚙️ General Settings
- ✅ Custom currency symbol
- ✅ Invoice number prefix
- ✅ Date format settings
- ✅ Default tax rates

### 📈 Dashboard
- ✅ Quick statistics overview
- ✅ Recent invoices widget
- ✅ Recent clients widget
- ✅ Quick action buttons

---

## 🚀 Installation

### From WordPress.org (Recommended)

1. Go to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Search for "Ipsit Invoice Generator"
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin ZIP file
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the downloaded ZIP file and click **Install Now**
4. Activate the plugin

### From GitHub

```bash
cd wp-content/plugins/
git clone https://github.com/ipsit-technologies/ipsit-invoice-generator.git
cd ipsit-invoice-generator
composer install --no-dev
```

Then activate the plugin from WordPress admin.

---

## 📖 Quick Start Guide

### 1️⃣ Configure Company Settings
After activation, go to **Invoices → Company** and set up:
- Company name and contact information
- Upload your logo
- Set accent color for branding
- Configure default banking details

### 2️⃣ Add Clients
Navigate to **Invoices → Clients** and add your clients with their contact information.

### 3️⃣ Create Your First Invoice
1. Go to **Invoices → Create Invoice**
2. Select a client
3. Add invoice items (description, quantity, price)
4. Set tax rate if applicable
5. Choose payment method (optional)
6. Click **Save Invoice**

### 4️⃣ Send Invoice
After saving, you can:
- Download PDF version
- Send via email using the "Send Invoice via Email" section
- Print directly from browser

---

## 🎨 Screenshots

1. **Dashboard** - Overview with recent invoices and clients
2. **Invoice List** - Manage all your invoices with status tracking
3. **Create/Edit Invoice** - Intuitive invoice creation interface
4. **PDF Invoice** - Professional PDF output
5. **Template Builder** - Customize invoice templates
6. **Client Management** - Store and manage client information
7. **Settings Panel** - Configure plugin options

---

## 🛠️ Development

### Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher (8.0+ recommended)
- MySQL 5.6 or higher / MariaDB 10.1 or higher

### Development Setup

```bash
# Clone the repository
git clone https://github.com/ipsit-technologies/ipsit-invoice-generator.git
cd ipsit-invoice-generator

# Install dependencies
composer install

# For production build
composer install --no-dev --optimize-autoloader
```

### Project Structure

```
ipsit-invoice-generator/
├── admin/
│   └── views/          # Admin page templates
├── assets/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── images/         # Image assets
├── includes/
│   ├── class-ig-admin.php       # Admin menu & pages
│   ├── class-ig-ajax.php        # AJAX handlers
│   ├── class-ig-database.php    # Database operations
│   ├── class-ig-pdf.php         # PDF generation
│   ├── class-ig-email.php       # Email functionality
│   ├── class-ig-validator.php   # Data validation
│   ├── class-ig-helper.php      # Utility functions
│   └── class-ig-logger.php      # Logging system
├── templates/          # Invoice templates
├── vendor/            # Composer dependencies
├── DOCUMENTATION.md   # Comprehensive documentation
├── readme.txt         # WordPress.org readme
└── ipsit-invoice-generator.php  # Main plugin file
```

### Coding Standards

This plugin follows:
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WordPress Plugin Development Best Practices](https://developer.wordpress.org/plugins/plugin-basics/best-practices/)
- PSR-4 Autoloading for classes

---

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Reporting Bugs

1. Check if the bug has already been reported in [Issues](https://github.com/ipsit-technologies/ipsit-invoice-generator/issues)
2. If not, create a new issue with:
   - Clear title and description
   - Steps to reproduce
   - Expected vs actual behavior
   - WordPress & PHP versions
   - Screenshots if applicable

### Suggesting Features

Open an issue with the `enhancement` label describing:
- The feature you'd like to see
- Why it would be useful
- Any examples from other tools

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Test thoroughly
5. Commit with clear messages (`git commit -m 'Add amazing feature'`)
6. Push to your fork (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Code Contributions Guidelines

- Follow WordPress coding standards
- Comment your code where necessary
- Update documentation if needed
- Test on multiple PHP versions (7.4, 8.0, 8.1)
- Ensure backward compatibility

---

## 📚 Documentation

- **[Full Documentation](DOCUMENTATION.md)** - Comprehensive guide covering all features
- **[WordPress.org Plugin Page](https://wordpress.org/plugins/ipsit-invoice-generator/)** - Official plugin listing
- **[Support Forum](https://wordpress.org/support/plugin/ipsit-invoice-generator/)** - Community support

---

## 🔧 Frequently Asked Questions

### Can I customize the invoice templates?
Yes! Use the built-in Template Builder to create custom templates or modify existing ones.

### Does it support multiple currencies?
Currently, you can set one currency symbol in settings. Multi-currency support is planned for future releases.

### Can I export invoices?
Yes, you can download invoices as PDF files with your company branding.

### Is it compatible with multisite?
Yes, the plugin works with WordPress multisite installations.

### Can I use custom fields for clients?
Yes, you can add unlimited custom fields to store additional client information.

---

## 🗺️ Roadmap

### Version 1.1 (Planned)
- [ ] Recurring invoices
- [ ] Email templates customization
- [ ] Invoice reminders
- [ ] Payment gateway integration

### Version 1.2 (Future)
- [ ] Multi-currency support
- [ ] Expense tracking
- [ ] Reports and analytics
- [ ] Client portal

### Version 2.0 (Long-term)
- [ ] Quotes/Estimates
- [ ] Time tracking
- [ ] Multi-user/team support
- [ ] REST API

---

## 🐛 Support

### Free Support
- [WordPress.org Support Forum](https://wordpress.org/support/plugin/ipsit-invoice-generator/)
- [GitHub Issues](https://github.com/ipsit-technologies/ipsit-invoice-generator/issues)

### Premium Support
For priority support and custom development, visit [Ipsit Technologies](https://ipsittechnologies.com/)

---

## 📄 License

This plugin is licensed under the **GNU General Public License v2.0 or later**.

```
Ipsit Invoice Generator - WordPress invoicing plugin
Copyright (C) 2024 Ipsit Technologies

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

See [LICENSE](LICENSE) file for full license text.

---

## 👨‍💻 Authors & Credits

**Developed by [Ipsit Technologies](https://ipsittechnologies.com/)**

### Third-Party Libraries
- [Dompdf](https://github.com/dompdf/dompdf) - PDF generation (LGPL 2.1)

---

## 🌟 Show Your Support

If you find this plugin helpful:
- ⭐ Star this repository on GitHub
- ✍️ Leave a review on [WordPress.org](https://wordpress.org/support/plugin/ipsit-invoice-generator/reviews/)
- 🐦 Share with others who might benefit
- 🤝 Contribute to the project

---

## 📞 Connect With Us

- **Website:** [https://ipsittechnologies.com/](https://ipsittechnologies.com/)
- **GitHub:** [@ipsit-technologies](https://github.com/ipsit-technologies)
- **WordPress.org:** [Plugin Profile](https://wordpress.org/plugins/ipsit-invoice-generator/)

---

<p align="center">Made with ❤️ by Ipsit Technologies</p>
<p align="center">© 2024 Ipsit Technologies. All rights reserved.</p>

