=== IPSIT Invoice Generator ===
Contributors: Ipsit Technologies
Tags: invoice, invoice generator, pdf, billing, client management
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional invoice generator for WordPress with PDF generation, email sending, client management, and customizable templates.

== Description ==

IPSIT Invoice Generator is a comprehensive invoicing solution for WordPress that helps you create, manage, and send professional invoices directly from your WordPress admin panel.

= Key Features =

* **Professional Invoice Generation** - Create beautiful invoices with customizable templates
* **PDF Generation** - High-quality PDF invoices using Dompdf library
* **Email Integration** - Send invoices directly to clients via email
* **Client Management** - Manage client information with custom fields support
* **Multiple Templates** - 4 pre-built professional templates included
* **Custom Template Builder** - Create your own templates with HTML/CSS
* **Company Branding** - Add your logo and company information
* **Payment Details** - Include bank account and payment information
* **Tax Calculations** - Automatic tax calculations
* **Multiple Statuses** - Track invoices as Draft, Sent, Paid, Overdue, or Cancelled
* **Reporting Dashboard** - View revenue statistics and invoice counts
* **Customizable Numbering** - Configure invoice number format with prefix, suffix, and padding
* **Multi-Currency Support** - Support for different currencies and symbols

= Security Features =

* **SQL Injection Protection** - All database queries use prepared statements
* **Rate Limiting** - Prevents abuse with configurable request limits
* **Input Validation** - Comprehensive validation for all data types
* **Custom Capabilities** - Granular permission control
* **File Upload Security** - Validated logo uploads with size and type restrictions
* **Audit Trail** - Track who created and modified records

= Performance Optimizations =

* **Smart Caching** - Transient caching for company data, templates, and settings
* **Optimized Queries** - JOIN queries eliminate N+1 query problems
* **Database Indexes** - Fast queries on large datasets
* **Configurable Limits** - Enforced min/max on all queries

= Developer Friendly =

* **Well Documented** - Comprehensive documentation included
* **Modern PHP** - Type hints throughout (PHP 7.4+)
* **Logging System** - Multi-level logging for debugging
* **Clean Code** - Follows WordPress coding standards
* **Extensible** - Easy to customize and extend
* **Helper Functions** - Validation, caching, and utility functions

= Use Cases =

* Freelancers sending invoices to clients
* Small businesses managing billing
* Agencies tracking project invoices
* Consultants managing client payments
* Service providers creating professional invoices

= What's New in Version 1.2.0 =

* Complete security overhaul with SQL injection protection
* Performance improvements with caching system
* Custom capabilities for granular permissions
* Rate limiting to prevent abuse
* Comprehensive logging system
* Input validation layer
* Transaction support for data integrity
* Optimized database queries with JOINs
* Modern code with type hints

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for "IPSIT Invoice Generator"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Go to Plugins → Add New → Upload Plugin
4. Choose the downloaded zip file and click "Install Now"
5. Activate the plugin through the 'Plugins' menu

= After Activation =

1. Go to IPSIT Invoice Generator → Settings
2. Configure your invoice numbering, currency, and email settings
3. Go to Company Info and add your company details and logo
4. Go to Clients and add your first client
5. Go to Add Invoice to create your first invoice

== Frequently Asked Questions ==

= Can I customize the invoice templates? =

Yes! The plugin includes 4 pre-built templates, and you can create custom templates using HTML and CSS. Go to Templates → Add Template to create your own design.

= Can I send invoices via email? =

Absolutely! Each invoice has a "Send Email" button that allows you to send the invoice PDF directly to your client's email address.

= Does it support multiple currencies? =

Yes, you can configure your currency and currency symbol in the Settings page.

= Can I add my company logo? =

Yes, you can upload your company logo in the Company Info section. Supported formats: JPG, PNG, GIF (max 2MB).

= How do I customize the invoice number format? =

Go to Settings and configure the Invoice Number Prefix, Suffix, and Padding. For example: INV-00001, INVOICE-2024-0001, etc.

= Can I track invoice status? =

Yes, invoices can be marked as Draft, Sent, Paid, Overdue, or Cancelled. You can filter invoices by status on the All Invoices page.

= Is it compatible with my theme? =

Yes, the plugin works with any WordPress theme as it operates entirely within the WordPress admin area.

= Can I add custom fields to clients? =

Yes, the client management system supports custom fields, allowing you to store additional information specific to your needs.

= Does it calculate taxes automatically? =

Yes, you can set a tax rate percentage and the plugin will automatically calculate the tax and total amount.

= Can I include payment/bank details on invoices? =

Yes, you can add default bank account information in Company Settings, and override it per invoice if needed.

= Is the plugin translation ready? =

Yes, the plugin is fully internationalized and ready for translation.

= What PDF library does it use? =

The plugin uses Dompdf, a popular and reliable PHP library for generating PDFs from HTML.

= Does it work on multisite? =

The plugin is designed for single-site installations. Multisite compatibility has not been tested.

= How do I get support? =

Please use the WordPress.org support forum for this plugin. Check the documentation included in the plugin folder for detailed information.

== Screenshots ==

1. Dashboard - Overview of invoices, revenue, and quick stats
2. Invoice List - Manage all invoices with filtering and quick actions
3. Create Invoice - Easy-to-use form for creating professional invoices
4. Client Management - Add and manage client information
5. Company Settings - Configure your company details and branding
6. Template Builder - Create custom invoice templates
7. PDF Preview - High-quality PDF invoices ready to send
8. Email Invoice - Send invoices directly to clients

== Changelog ==

= 1.0.0 - 2025-11-15 =
**Initial Release**

* Complete invoice management system
* Professional PDF generation with 4 pre-built templates
* Custom template builder with HTML/CSS support
* Client management with custom fields
* Email integration for sending invoices
* Company branding with logo upload
* Payment details and bank information
* Multiple invoice statuses (Draft, Sent, Paid, Overdue, Cancelled)
* Customizable invoice numbering
* Multi-currency support
* Tax calculations
* Revenue dashboard and reporting
* Enterprise-grade security features:
  - SQL injection protection (all queries use prepared statements)
  - Custom capabilities for granular permissions
  - Rate limiting (10 requests/minute per user)
  - Comprehensive input validation
  - Secure file uploads
  - Audit trail (created_by, modified_by tracking)
* Performance optimizations:
  - Smart caching (company data, templates, settings)
  - Optimized database queries with JOINs
  - Database indexes on key columns
* Developer-friendly:
  - Modern PHP with type hints
  - Comprehensive logging system
  - Clean, well-documented code
  - WordPress coding standards
  - Extensible architecture

== Upgrade Notice ==

= 1.0.0 =
Initial release of IPSIT Invoice Generator - A complete professional invoicing solution with enterprise-grade security, performance optimizations, and beautiful templates.

== Privacy & Data ==

This plugin stores the following data in your WordPress database:
* Invoice information (numbers, dates, amounts, items, notes)
* Client information (names, emails, addresses, phone numbers)
* Company information (name, logo, contact details, bank information)
* Custom invoice templates (HTML and CSS)
* Plugin settings and preferences

The plugin does NOT:
* Send data to external servers (except for email sending through WordPress mail system)
* Track users or collect analytics
* Use cookies
* Store credit card or payment information

All data is stored locally in your WordPress database and is only accessible to authorized users based on their WordPress roles and capabilities.

When you send invoice emails, the email is sent through your WordPress installation's mail system (wp_mail function), which may use your server's mail system or a configured SMTP plugin.

== Support ==

For support, please visit the WordPress.org support forum for this plugin.

For detailed documentation, see the DOCUMENTATION.md file included in the plugin folder.

== Credits ==

* Developed by Ipsit Technologies
* Uses Dompdf library for PDF generation (https://github.com/dompdf/dompdf)
* Icons from WordPress Dashicons

== Contribute ==

If you find this plugin useful and want to contribute, please consider:
* Reporting bugs
* Suggesting new features
* Contributing code improvements
* Translating to your language
* Rating the plugin on WordPress.org

