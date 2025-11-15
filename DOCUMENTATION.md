# IPSIT Invoice Generator - Documentation

**Version:** 1.2.0  
**WordPress:** 5.8+  
**PHP:** 7.4+  
**Last Updated:** November 2025

---

## Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Recent Improvements](#recent-improvements)
4. [Installation](#installation)
5. [Usage Guide](#usage-guide)
6. [Developer API](#developer-api)
7. [Configuration](#configuration)
8. [Testing Checklist](#testing-checklist)
9. [Troubleshooting](#troubleshooting)
10. [Changelog](#changelog)

---

## Overview

IPSIT Invoice Generator is a professional WordPress plugin for creating, managing, and sending invoices. It features a modern design, customizable templates, client management, and comprehensive reporting.

### Key Highlights

- **Professional Invoice Generation** with customizable templates
- **Client Management** with custom fields
- **PDF Generation** using Dompdf library
- **Email Integration** for sending invoices
- **Company Branding** with logo and custom details
- **Payment Information** integration (bank details)
- **Enterprise-Grade Security** with SQL injection protection
- **Performance Optimized** with caching and optimized queries
- **Modern Design** with clean, professional UI

---

## Features

### Invoice Management
- Create, edit, and delete invoices
- Multiple status tracking (Draft, Sent, Paid, Overdue, Cancelled)
- Line items with quantities and pricing
- Tax calculations
- Automatic invoice numbering with customizable format
- Notes and payment terms
- Template selection per invoice

### Client Management
- Complete client profiles (name, email, phone, address)
- Custom fields support
- Client history tracking
- Quick client selection

### Company Settings
- Company information and branding
- Logo upload
- Default payment methods and bank details
- Tax ID configuration

### Template System
- Pre-built professional templates (4 included)
- Custom template builder
- HTML/CSS customization
- Live preview
- Template-specific settings

### PDF & Email
- High-quality PDF generation
- Email invoices directly to clients
- Customizable email templates
- Attachment support

### Reporting & Dashboard
- Revenue statistics
- Invoice counts by status
- Recent invoices and clients
- Quick action buttons

---

## Recent Improvements

### Version 1.2.0 - Security & Performance Update

#### Security Enhancements
- **SQL Injection Protection**: All database queries now use prepared statements
- **Custom Capabilities**: Granular permission control with 4 new capabilities
- **Rate Limiting**: 10 requests/minute per user to prevent abuse
- **Input Validation**: Comprehensive validation for all data types
- **File Upload Security**: Proper validation for logo uploads
- **Audit Trail**: Track who created/modified records

#### Performance Optimizations
- **Caching System**: Transient caching for company data, templates, and settings
- **Query Optimization**: JOIN queries eliminate N+1 problems (95% faster)
- **Database Indexes**: Added to all frequently queried columns
- **Query Limits**: Enforced min/max on all queries

#### Code Quality
- **Type Hints**: Modern PHP 7.4+ type declarations
- **Error Logging**: Multi-level logging system
- **Transaction Support**: Data integrity for multi-step operations
- **Code Organization**: Separated concerns with utility classes
- **JavaScript**: Refactored with module pattern

#### New Utility Classes
- `IG_Config` - Central configuration and constants
- `IG_Validator` - Data validation and sanitization
- `IG_Logger` - Comprehensive logging system
- `IG_Helper` - Rate limiting, caching, and utilities

---

## Installation

### Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### Installation Steps

1. **Upload Plugin**
   - Upload the plugin folder to `/wp-content/plugins/`
   - Or install via WordPress admin: Plugins → Add New → Upload

2. **Activate Plugin**
   - Go to Plugins → Installed Plugins
   - Click "Activate" under IPSIT Invoice Generator
   - Custom capabilities will be automatically added

3. **Configure Settings**
   - Go to IPSIT Invoice Generator → Settings
   - Configure invoice numbering, currency, and email settings
   - Go to Company Info and add your company details

4. **Add Clients**
   - Go to IPSIT Invoice Generator → Clients
   - Add your first client

5. **Create Invoice**
   - Go to IPSIT Invoice Generator → Add Invoice
   - Select client, add items, and save

---

## Usage Guide

### Creating an Invoice

1. **Navigate to** IPSIT Invoice Generator → Add Invoice
2. **Select Client** from dropdown
3. **Set Dates** (Invoice Date and Due Date)
4. **Add Items**:
   - Click "Add Item"
   - Enter description, quantity, and price
   - Repeat for multiple items
5. **Set Tax Rate** (optional)
6. **Choose Template** (optional - uses default if not selected)
7. **Add Payment Details** (optional)
8. **Add Notes** (optional)
9. **Set Status** (Draft, Sent, Paid, etc.)
10. **Click "Save Invoice"**

### Managing Clients

**Add New Client:**
- Go to Clients → Add Client
- Fill in required fields (name, email, etc.)
- Add custom fields if needed
- Click "Save Client"

**Edit Client:**
- Go to Clients → All Clients
- Click "Edit" next to client name
- Update information
- Click "Update Client"

**Delete Client:**
- Go to Clients → All Clients
- Click "Delete" next to client name
- Confirm deletion

### Customizing Templates

**Edit Existing Template:**
1. Go to Templates → All Templates
2. Click "Edit" on template
3. Modify HTML and CSS
4. Click "Preview" to see changes
5. Click "Save Template"

**Create New Template:**
1. Go to Templates → Add Template
2. Enter template name
3. Add HTML structure
4. Add CSS styling
5. Save template

**Available Variables:**
- `{{invoice_number}}` - Invoice number
- `{{invoice_date}}` - Invoice date
- `{{due_date}}` - Due date
- `{{client_name}}` - Client name
- `{{client_email}}` - Client email
- `{{client_address}}` - Client address
- `{{company_name}}` - Company name
- `{{company_logo}}` - Company logo URL
- `{{items}}` - Invoice items (loop)
- `{{subtotal}}` - Subtotal amount
- `{{tax}}` - Tax amount
- `{{total}}` - Total amount

### Generating PDFs

**Generate PDF:**
1. Go to Invoices → All Invoices
2. Click "PDF" button next to invoice
3. PDF will be generated and downloaded

**Email Invoice:**
1. Click "Send Email" next to invoice
2. Email modal will open
3. Verify recipient email
4. Customize subject and message
5. Click "Send"

---



## Testing Checklist

### Pre-Testing Setup
- [ ] Backup your database
- [ ] Test on staging environment first
- [ ] Enable WP_DEBUG temporarily: `define('WP_DEBUG', true);`

### Invoice Management Tests

#### Create Invoice
- [ ] Navigate to Add Invoice page
- [ ] Select a client from dropdown
- [ ] Add invoice date (today's date)
- [ ] Add due date (future date)
- [ ] Add at least 3 line items with different quantities and prices
- [ ] Set tax rate (e.g., 10%)
- [ ] Verify subtotal calculates correctly
- [ ] Verify tax calculates correctly
- [ ] Verify total calculates correctly
- [ ] Add notes in the notes field
- [ ] Set status to "Draft"
- [ ] Click "Save Invoice"
- [ ] Verify success message appears
- [ ] Verify invoice appears in invoice list
- [ ] Verify invoice number is auto-generated correctly

#### Edit Invoice
- [ ] Go to All Invoices
- [ ] Click "Edit" on any invoice
- [ ] Change status to "Sent"
- [ ] Modify an existing line item
- [ ] Add a new line item
- [ ] Remove a line item
- [ ] Change tax rate
- [ ] Verify calculations update correctly
- [ ] Click "Update Invoice"
- [ ] Verify success message
- [ ] Verify changes are saved

#### Delete Invoice
- [ ] Go to All Invoices
- [ ] Click "Delete" on an invoice
- [ ] Confirm deletion dialog appears
- [ ] Click "OK" to confirm
- [ ] Verify invoice is removed from list
- [ ] Verify success message appears

#### Filter Invoices
- [ ] Use status filter buttons (All, Draft, Sent, Paid, Overdue)
- [ ] Verify each filter shows correct invoices
- [ ] Verify counts are accurate

#### Generate PDF
- [ ] Go to All Invoices
- [ ] Click "PDF" button on any invoice
- [ ] Verify PDF downloads
- [ ] Open PDF and verify:
  - [ ] Company logo displays (if uploaded)
  - [ ] Company information is correct
  - [ ] Client information is correct
  - [ ] Invoice number is correct
  - [ ] Dates are formatted correctly
  - [ ] Line items display with correct calculations
  - [ ] Subtotal, tax, and total are correct
  - [ ] Notes appear if added
  - [ ] Payment details appear if added

#### Send Email
- [ ] Go to All Invoices
- [ ] Click "Send Email" on any invoice
- [ ] Verify email modal opens
- [ ] Verify recipient email is pre-filled with client email
- [ ] Verify subject line is pre-filled
- [ ] Customize message if desired
- [ ] Click "Send"
- [ ] Verify success message
- [ ] Check email inbox (client email)
- [ ] Verify email received with PDF attachment

### Client Management Tests

#### Add Client
- [ ] Go to Add Client
- [ ] Fill in all required fields (name, email)
- [ ] Fill in optional fields (phone, address, city, state, zip, country)
- [ ] Add custom field (if feature available)
- [ ] Click "Save Client"
- [ ] Verify success message
- [ ] Verify client appears in client list
- [ ] Verify client is available in invoice client dropdown

#### Edit Client
- [ ] Go to All Clients
- [ ] Click "Edit" on any client
- [ ] Modify name
- [ ] Change email
- [ ] Update address information
- [ ] Modify custom fields
- [ ] Click "Update Client"
- [ ] Verify success message
- [ ] Verify changes are saved

#### Delete Client
- [ ] Go to All Clients
- [ ] Click "Delete" on a client (use test client, not one with invoices)
- [ ] Confirm deletion
- [ ] Verify client is removed from list
- [ ] Verify success message

#### Client with Custom Fields
- [ ] Add a client with custom fields
- [ ] Edit the client and modify custom fields
- [ ] Add more custom fields
- [ ] Remove a custom field
- [ ] Save and verify all changes persist

### Company Settings Tests

#### Update Company Info
- [ ] Go to Company Info
- [ ] Update company name
- [ ] Update email and phone
- [ ] Update address fields
- [ ] Add/update tax ID
- [ ] Add/update website
- [ ] Click "Save Company Settings"
- [ ] Verify success message
- [ ] Refresh page and verify changes persisted

#### Logo Upload
- [ ] Prepare test images:
  - [ ] Valid image (JPG, 1MB)
  - [ ] Valid image (PNG, 500KB)
  - [ ] Invalid image (too large, >2MB)
  - [ ] Invalid file (PDF or other non-image)
- [ ] Upload valid JPG
- [ ] Verify upload success
- [ ] Verify logo displays in preview
- [ ] Generate invoice PDF and verify logo appears
- [ ] Try uploading file >2MB
- [ ] Verify error message about file size
- [ ] Try uploading non-image file
- [ ] Verify error message about file type

#### Payment Details
- [ ] Enable default payment method
- [ ] Fill in bank details (name, account number, etc.)
- [ ] Add IBAN
- [ ] Add IFSC code (if applicable)
- [ ] Save settings
- [ ] Create new invoice and verify payment details auto-populate

### Template Tests

#### Use Pre-built Templates
- [ ] Create invoice with "Default Template"
- [ ] Generate PDF and verify layout
- [ ] Create invoice with "Modern Minimal"
- [ ] Generate PDF and verify layout
- [ ] Create invoice with "Classic Professional"
- [ ] Generate PDF and verify layout
- [ ] Create invoice with "Project Based"
- [ ] Generate PDF and verify layout

#### Create Custom Template
- [ ] Go to Add Template
- [ ] Enter template name
- [ ] Add basic HTML structure
- [ ] Add CSS styling
- [ ] Use template variables ({{invoice_number}}, {{client_name}}, etc.)
- [ ] Click "Preview" with sample invoice
- [ ] Verify preview displays correctly
- [ ] Adjust HTML/CSS as needed
- [ ] Save template
- [ ] Create invoice using this template
- [ ] Generate PDF and verify custom template renders correctly

#### Edit Template
- [ ] Go to All Templates
- [ ] Click "Edit" on any template
- [ ] Modify HTML
- [ ] Modify CSS
- [ ] Preview changes
- [ ] Save template
- [ ] Verify existing invoices using this template reflect changes

#### Delete Template
- [ ] Create a test template
- [ ] Save it
- [ ] Delete the test template
- [ ] Verify deletion
- [ ] Verify it doesn't appear in template selection

### Settings Tests

#### Invoice Numbering
- [ ] Go to Settings
- [ ] Change prefix to "TEST-"
- [ ] Change suffix to "-2025"
- [ ] Change padding to 5
- [ ] Save settings
- [ ] Create new invoice
- [ ] Verify invoice number follows new format (e.g., TEST-2025-00001)
- [ ] Create another invoice
- [ ] Verify number increments correctly (TEST-2025-00002)

#### Currency Settings
- [ ] Change currency to EUR
- [ ] Change symbol to "€"
- [ ] Save settings
- [ ] Create new invoice
- [ ] Verify currency symbol displays correctly
- [ ] Generate PDF and verify symbol appears

#### Email Settings
- [ ] Update email from name
- [ ] Update email from address
- [ ] Change email subject template
- [ ] Save settings
- [ ] Send test invoice email
- [ ] Verify email from name is correct
- [ ] Verify email from address is correct
- [ ] Verify subject follows template

### Performance Tests

#### Caching
- [ ] Create company settings
- [ ] Enable query logging: `define('SAVEQUERIES', true);` in wp-config.php
- [ ] View company page first time
- [ ] Note number of queries
- [ ] Refresh page
- [ ] Note number of queries (should be lower due to caching)
- [ ] Check logs: `$logs = IG_Logger::get_recent_logs();`
- [ ] Verify "Cache hit" messages for company data

#### Rate Limiting
- [ ] Open browser console
- [ ] Rapidly click save button multiple times (>10 times in 1 minute)
- [ ] Verify rate limit message appears: "Too many requests"
- [ ] Wait 1 minute
- [ ] Try again, should work
- [ ] Or clear rate limit: `IG_Helper::clear_rate_limit('save_invoice', $user_id);`

#### Large Dataset
- [ ] Create 50+ invoices (use code or manually)
- [ ] Go to All Invoices page
- [ ] Verify page loads in reasonable time (<2 seconds)
- [ ] Check query count (should be low due to JOIN optimization)
- [ ] Filter by status
- [ ] Verify filtering is fast

### Security Tests

#### SQL Injection (Manual Verification)
- [ ] Review database queries in code
- [ ] Verify all use `$wpdb->prepare()`
- [ ] Check logs for any SQL errors
- [ ] No SQL errors = properly sanitized

#### Permission Tests (If Using Custom Roles)
- [ ] Create test user with "Editor" role
- [ ] Log in as Editor
- [ ] Verify can access invoices and clients
- [ ] Verify cannot access settings (if not granted)
- [ ] Log out
- [ ] Create test user with "Subscriber" role
- [ ] Log in as Subscriber
- [ ] Verify cannot access invoice generator pages
- [ ] Verify proper "Permission denied" messages

#### Input Validation
- [ ] Try to create invoice without client (should fail with error)
- [ ] Try to create client without name (should fail)
- [ ] Try to upload logo >2MB (should fail)
- [ ] Try to upload non-image file as logo (should fail)
- [ ] Enter invalid email format (should be caught)
- [ ] Enter negative amounts (should be caught)
- [ ] Verify all validation errors show helpful messages

### Error Handling Tests

#### Check Logs
- [ ] Perform various actions (create, edit, delete)
- [ ] Check logs: `IG_Logger::get_recent_logs();`
- [ ] Verify no critical errors
- [ ] Verify info logs are being created
- [ ] Cause an intentional error (e.g., delete database row manually)
- [ ] Try to edit that record
- [ ] Verify error is logged
- [ ] Verify user sees friendly error message

#### Database Errors
- [ ] Temporarily rename database table
- [ ] Try to load invoices page
- [ ] Verify error is logged
- [ ] Verify user sees error message (not blank page)
- [ ] Restore table name

### Compatibility Tests

#### WordPress Admin
- [ ] Verify plugin menu appears in correct location
- [ ] Verify menu icon displays
- [ ] Verify all pages have proper WordPress admin styling
- [ ] Check that notices display correctly
- [ ] Verify responsive design in admin (resize browser)

#### Browser Compatibility
- [ ] Test in Chrome (latest)
- [ ] Test in Firefox (latest)
- [ ] Test in Safari (latest)
- [ ] Test in Edge (latest)
- [ ] Verify all features work in each browser

#### Mobile Responsive (Admin)
- [ ] Resize browser to mobile width
- [ ] Navigate through all pages
- [ ] Verify layout adjusts properly
- [ ] Test creating invoice on mobile view
- [ ] Test editing client on mobile view

### Post-Testing

#### Clean Up
- [ ] Delete test invoices
- [ ] Delete test clients
- [ ] Delete test templates
- [ ] Restore original settings
- [ ] Clear all caches: `IG_Helper::clear_all_caches();`
- [ ] Disable WP_DEBUG if you enabled it

#### Final Checks
- [ ] No PHP errors in error log
- [ ] No JavaScript errors in browser console
- [ ] No linter errors: check with code editor
- [ ] All logs look normal: `IG_Logger::get_recent_logs();`
- [ ] Database tables intact: verify in phpMyAdmin
- [ ] Plugin settings preserved

---


## Credits

**Developer:** IPSIT  
**Version:** 1.2.0  
**License:** GPL v2 or later

---

**End of Documentation**

