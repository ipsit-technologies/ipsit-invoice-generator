# Translation Guide for IPSIT Invoice Generator

Thank you for your interest in translating IPSIT Invoice Generator!

## How to Translate

### Using Poedit (Recommended for Beginners)

1. **Download Poedit**: https://poedit.net/ (Free)
2. **Open the POT file**: `ipsit-invoice-generator.pot`
3. **Create a new translation**:
   - Click "Create New Translation"
   - Select your language
4. **Translate strings**: Fill in the translations for each string
5. **Save**: Save as `ipsit-invoice-generator-{locale}.po`
   - Example: `ipsit-invoice-generator-es_ES.po` for Spanish (Spain)
   - Example: `ipsit-invoice-generator-fr_FR.po` for French (France)
6. Poedit will automatically create the `.mo` file

### Language Codes

Common locale codes:
- Spanish (Spain): `es_ES`
- French (France): `fr_FR`
- German: `de_DE`
- Italian: `it_IT`
- Portuguese (Brazil): `pt_BR`
- Dutch: `nl_NL`
- Russian: `ru_RU`
- Chinese (Simplified): `zh_CN`
- Japanese: `ja`

Full list: https://make.wordpress.org/polyglots/teams/

### Using translate.wordpress.org (Preferred Method)

Once the plugin is approved on WordPress.org, you can contribute translations directly:

1. Visit: https://translate.wordpress.org/projects/wp-plugins/ipsit-invoice-generator/
2. Select your language
3. Translate strings online
4. Translations will be automatically distributed to users

## File Structure

After translation, your language files should be:
```
languages/
├── ipsit-invoice-generator.pot          (Template - Don't modify)
├── ipsit-invoice-generator-{locale}.po  (Your translation source)
└── ipsit-invoice-generator-{locale}.mo  (Compiled translation - auto-generated)
```

## Translator Notes

Some strings contain placeholders:
- `%s` - Will be replaced with text (name, date, etc.)
- `%d` - Will be replaced with a number
- Keep placeholders in your translation!

Example:
- English: `Dear %s,`
- Spanish: `Estimado %s,`

## Testing Your Translation

1. Place your `.po` and `.mo` files in the `languages/` folder
2. Change your WordPress language in **Settings → General → Site Language**
3. Visit the plugin pages to verify translations

## Contributing

### Submit via GitHub
1. Fork the repository
2. Add your translation files to the `languages/` folder
3. Submit a Pull Request

### Submit via Email
Send your `.po` and `.mo` files to: support@ipsittechnologies.com

## Questions?

- Plugin Website: https://ipsittechnologies.com/
- Support Forum: https://wordpress.org/support/plugin/ipsit-invoice-generator/

---

**Thank you for helping make IPSIT Invoice Generator accessible to more people worldwide!** 🌍

