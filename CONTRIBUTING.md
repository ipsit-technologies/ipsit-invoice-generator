# Contributing to Ipsit Invoice Generator

Thank you for your interest in contributing to Ipsit Invoice Generator! We welcome contributions from the community and are grateful for your support.

## 🤝 How to Contribute

### Reporting Bugs

Before creating a bug report, please check the [existing issues](https://github.com/ipsit-technologies/ipsit-invoice-generator/issues) to avoid duplicates.

When reporting a bug, include:
- **Clear title and description**
- **Steps to reproduce** the issue
- **Expected behavior** vs **actual behavior**
- **WordPress version**
- **PHP version**
- **Plugin version**
- **Screenshots** (if applicable)
- **Error messages** from debug log

### Suggesting Features

We love feature suggestions! Please open an issue with:
- **Clear description** of the feature
- **Use case** - why would this be useful?
- **Examples** from other tools (if applicable)
- **Proposed implementation** (optional)

### Pull Requests

1. **Fork** the repository
2. **Create a branch** from `main`:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Make your changes**
4. **Test thoroughly**
5. **Commit** with clear messages:
   ```bash
   git commit -m "Add feature: description of what you added"
   ```
6. **Push** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```
7. **Open a Pull Request** against the `main` branch

## 💻 Development Guidelines

### Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use tabs for indentation (WordPress standard)
- Comment complex code sections
- Use meaningful variable and function names
- Write PHP DocBlocks for all functions and classes

### Security

- Always sanitize input data
- Validate data before processing
- Escape output data
- Use nonces for form submissions
- Check user capabilities before actions
- Never trust user input

### Testing

Before submitting a PR:
- Test on a fresh WordPress installation
- Test with PHP 7.4, 8.0, and 8.1
- Test with WordPress 5.8+
- Ensure no JavaScript console errors
- Verify database queries are optimized
- Check for PHP errors/warnings

### Commit Messages

Use clear, descriptive commit messages:

```
✅ Good:
- "Add email validation for client creation"
- "Fix: Invoice PDF generation for special characters"
- "Improve: Database query performance for invoice list"

❌ Bad:
- "Update"
- "Fix bug"
- "Changes"
```

## 🏗️ Code Structure

### Adding New Features

1. **Database changes**: Add to `class-ig-database.php`
2. **AJAX handlers**: Add to `class-ig-ajax.php`
3. **Admin pages**: Create view in `admin/views/`
4. **Validation**: Add to `class-ig-validator.php`
5. **Helper functions**: Add to `class-ig-helper.php`

### Hooks and Filters

Use WordPress hooks where appropriate:
- Actions: `do_action('ipsit_ig_action_name', $param)`
- Filters: `apply_filters('ipsit_ig_filter_name', $value, $param)`

Prefix all hooks with `ipsit_ig_` to avoid conflicts.

## 📝 Documentation

- Update `DOCUMENTATION.md` for new features
- Add inline code comments for complex logic
- Update `readme.txt` changelog
- Update `README.md` if needed

## 🎨 CSS Guidelines

- Use CSS variables from `variables.css`
- Follow BEM naming convention where applicable
- Ensure responsive design (mobile-first)
- Test on major browsers (Chrome, Firefox, Safari, Edge)

## 🔍 Review Process

1. All PRs require review before merging
2. Address review comments promptly
3. Keep PRs focused (one feature/fix per PR)
4. Update PR if `main` branch changes
5. Ensure CI checks pass (when implemented)

## ⚖️ License

By contributing, you agree that your contributions will be licensed under the GPL v2.0 or later license.

## 🙋 Questions?

If you have questions about contributing:
- Open a [GitHub Discussion](https://github.com/ipsit-technologies/ipsit-invoice-generator/discussions)
- Ask in the [WordPress.org support forum](https://wordpress.org/support/plugin/ipsit-invoice-generator/)

## 🙏 Thank You!

Every contribution, no matter how small, helps make this plugin better. We appreciate your time and effort!

---

**Happy Coding! 🚀**

*— The Ipsit Technologies Team*

