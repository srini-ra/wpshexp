=== Theme Kit Pro ===
Contributors: wpelance
Tags: theme, export, import, elementor, gutenberg, templates, demo-content, woocommerce, marketplace
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Author URI: https://wpelance.com

Professional theme export/import solution with full Elementor & Gutenberg support, WooCommerce integration, marketplace distribution, and cloud storage options.

== Security Features ==

* **Input Sanitization**: All user inputs are properly sanitized and validated
* **Nonce Verification**: CSRF protection for all AJAX requests
* **Capability Checks**: User permission verification for all operations
* **File Security**: Comprehensive file type validation and content scanning
* **SQL Injection Prevention**: Prepared statements and input validation
* **XSS Protection**: Output escaping and input filtering
* **Path Traversal Protection**: Secure file path handling
* **Malware Scanning**: Automatic scanning of uploaded packages
* **Secure File Handling**: Safe file operations with proper validation

== Description ==

Theme Kit Pro is the ultimate WordPress plugin for theme developers, agencies, and freelancers who want to create and distribute professional theme packages. Export complete websites as installable kits with templates, demo content, plugins, and settings.

= Features =

**🚀 Core Features:**
* Complete theme export with all files, templates, and settings
* Selective content export - choose exactly what to include
* One-click import with progress tracking and error handling
* Automatic image import with URL replacement
* Pre-import compatibility checks (Elementor version, PHP, server limits)
* Robust error handling with detailed logs
* Support for localhost development (bypasses remote checks)

**🎨 Page Builder Support:**
* **Elementor**: Full support for templates, global settings, and custom widgets
* **Gutenberg**: Complete support for blocks, templates, and global styles
* **Both**: Export packages that work with both builders

**🛒 WooCommerce Integration:**
* Export products with variations, attributes, and images
* Product categories, tags, and attributes
* WooCommerce settings and configuration
* Payment gateways and shipping methods
* Tax settings and rates

**🌐 Marketplace Integration:**
* **Envato Market** (ThemeForest/CodeCanyon) integration
* **TemplateMonster** marketplace support
* **Creative Market** integration
* **Custom marketplace** API support
* Automatic package distribution
* Sales analytics and tracking

**☁️ Cloud Storage:**
* **Google Drive** integration for package storage
* **Automatic uploads** after export
* **Shareable links** for easy distribution
* **Secure authentication** with OAuth2

**🔧 Advanced Features:**
* Kit preview before import
* Batch processing for large imports
* Custom kit creation tools
* Responsive design validation
* Image optimization during import
* Comprehensive logging and debugging
* In-plugin support and documentation
* **Cloud Storage Integration**: Upload packages to Google Drive
* **Security Scanning**: Automatic malware and vulnerability detection
* **Download Options**: Local download and cloud storage options

= Use Cases =

* **Theme Developers**: Create professional theme packages for sale
* **Agencies**: Deliver complete website setups to clients
* **Freelancers**: Package custom solutions for easy deployment
* **Template Marketplaces**: Distribute themes on multiple platforms
* **Site Migration**: Move complete websites between servers
* **Backup & Restore**: Create complete site backups as installable packages

**Why Choose Theme Kit Pro?**

✅ **Professional Grade**: Built for developers and agencies
✅ **Error Handling**: Robust error handling with detailed logs
✅ **Compatibility**: Pre-import checks prevent issues
✅ **Image Handling**: Automatic image import and URL replacement
✅ **Localhost Support**: Perfect for development environments
✅ **Marketplace Ready**: Direct integration with major marketplaces
✅ **Support**: Comprehensive documentation and support

= Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* ZIP extension enabled
* Writable upload directory
* cURL extension for image imports
* SSL/TLS support for cloud storage (recommended)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/theme-exporter-pro` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to 'Theme Kit Pro' in the WordPress admin menu.
4. Start exporting your theme packages!

= Cloud Storage Setup =

1. Go to Theme Kit Pro > Settings
2. Configure Google Drive integration (optional)
3. Authorize the plugin to access your Google Drive
4. Choose automatic upload options

== Frequently Asked Questions ==

= What gets exported in a theme package? =

A theme package can include:
- Complete theme files (style.css, functions.php, templates)
- WooCommerce products, categories, and settings
- Elementor templates and settings
- Gutenberg blocks and templates
- Demo content (posts, pages, media)
- Required plugins list
- Customizer settings
- Widget configurations
- Navigation menus
- User accounts (optional)
- Custom post types and fields

= Does it handle missing images during import? =

Yes! Theme Kit Pro automatically downloads and imports images, replacing URLs as needed. If an image fails to import, it uses a placeholder and logs the error for review.

= Can I export both Elementor and Gutenberg templates? =

Yes! The plugin supports exporting packages that work with both Elementor and Gutenberg, or you can choose to export for a specific builder.

= Is the exported package compatible with other sites? =

Yes, exported packages are designed to be portable and can be imported on any WordPress site that meets the system requirements. The plugin runs compatibility checks before import to prevent issues.

= Can I sell my theme packages on marketplaces? =

Absolutely! Theme Kit Pro includes direct integration with major marketplaces like Envato Market, TemplateMonster, and Creative Market. You can also distribute through custom marketplaces.

= What happens to existing content during import? =

The import process adds new content alongside existing content. It doesn't replace or delete existing posts, pages, or settings.

= Can I import packages created with other plugins? =

This plugin creates its own package format optimized for reliability and compatibility. For best results, use packages created with Theme Kit Pro.

= Does it work on localhost? =

Yes! Theme Kit Pro includes a localhost mode that bypasses certain remote checks, making it perfect for development environments.

= What if something goes wrong during import? =

Theme Kit Pro includes comprehensive error handling and logging. If an import fails, you'll get detailed information about what went wrong and how to fix it. The plugin also includes troubleshooting guides and support resources.

= Is the plugin secure? =

Yes! Theme Kit Pro includes comprehensive security features:
* Automatic malware scanning of uploaded packages
* Input validation and sanitization
* CSRF protection with nonces
* User capability checks
* Secure file handling
* SQL injection prevention
* XSS protection

= Can I upload packages to cloud storage? =

Yes! Theme Kit Pro supports Google Drive integration. You can automatically upload exported packages to your Google Drive and get shareable links for easy distribution.

== Screenshots ==

1. Export page - Configure your theme package export settings
2. Import page - Import theme packages with progress tracking
3. System check - Verify system requirements before export/import
4. Package information - View details about theme packages
5. Selective export - Choose specific content to include
6. Kit preview - Preview theme packages before import
7. Marketplace integration - Distribute to multiple platforms
8. Logs and support - Comprehensive debugging and support tools
9. WooCommerce integration - Export complete online stores
10. Batch processing - Handle large imports efficiently
11. Security scanner - Automatic threat detection
12. Cloud storage - Google Drive integration

== Changelog ==

= 1.0.0 =
* Initial release
* **Security Features:**
  * Comprehensive input validation and sanitization
  * Automatic malware scanning of uploaded packages
  * CSRF protection with WordPress nonces
  * User capability verification
  * Secure file handling with path validation
  * SQL injection prevention
  * XSS protection with output escaping
* **Cloud Storage:**
  * Google Drive integration with OAuth2 authentication
  * Automatic package uploads to cloud storage
  * Shareable links for easy distribution
  * Secure credential storage
* **Download Options:**
  * Local download with secure file serving
  * Cloud storage upload options
  * Progress tracking for uploads
* Export child themes as complete packages
* Support for Elementor and Gutenberg
* WooCommerce data export/import
* Selective content export options
* Theme preview functionality
* Marketplace integration (Envato, TemplateMonster, Creative Market)
* Robust error handling with detailed logs
* Automatic image import and URL replacement
* Pre-import compatibility checks
* Responsive design validation
* Localhost support for development
* Batch processing for large imports
* Kit preview before import
* Custom kit creation tools
* Demo content export/import
* Plugin list export/import
* Customizer settings export/import
* Widget configurations export/import
* Progress tracking for import/export operations
* System requirements checking

== Credits and Attribution ==

Theme Kit Pro was inspired by and builds upon concepts from several existing plugins:

* **Template Kit Import/Export** - Basic template import/export concepts
* **Elementor** - Template structure and handling methods
* **WordPress Importer** - Content import methodologies
* **WooCommerce** - Product data handling approaches

We acknowledge and thank the developers of these plugins for their contributions to the WordPress ecosystem. Theme Kit Pro extends these concepts with enhanced security, cloud storage, marketplace integration, and comprehensive error handling.

**Original Concepts:**
* Template export/import workflow
* WordPress content handling
* Plugin dependency management
* Theme file organization

**Our Enhancements:**
* Advanced security scanning and validation
* Cloud storage integration (Google Drive)
* Marketplace distribution automation
* Comprehensive error handling and logging
* Responsive design validation
* Localhost development support
* WooCommerce deep integration
* Selective content export
* Real-time progress tracking
* Professional support system

== Upgrade Notice ==

= 1.0.0 =
Initial release of Theme Kit Pro - The ultimate secure WordPress theme export/import solution with cloud storage and marketplace integration.

== Development ==

= For Developers =

The plugin provides several hooks for customization:

* `tkp_before_export` - Action fired before export starts
* `tkp_after_export` - Action fired after export completes
* `tkp_before_import` - Action fired before import starts
* `tkp_after_import` - Action fired after import completes
* `tkp_export_data` - Filter to modify export data
* `tkp_import_data` - Filter to modify import data

= File Structure =

```
theme-exporter-pro/
├── theme-kit-pro.php           # Main plugin file
├── includes/
│   ├── class-exporter.php      # Export functionality
│   ├── class-importer.php      # Import functionality
│   ├── class-elementor-handler.php  # Elementor integration
│   ├── class-gutenberg-handler.php  # Gutenberg integration
│   └── class-file-handler.php      # File utilities
│   ├── class-woocommerce-handler.php   # WooCommerce integration
│   ├── class-selective-exporter.php    # Selective export functionality
│   ├── class-theme-preview.php         # Theme preview generation
│   ├── class-marketplace-integration.php # Marketplace APIs
│   ├── class-image-processor.php       # Image handling
│   ├── class-compatibility-checker.php # System compatibility
│   ├── class-error-handler.php         # Error handling
│   ├── class-logger.php               # Logging system
│   └── class-batch-processor.php      # Batch processing
├── templates/
│   ├── admin-export.php        # Export page template
│   └── admin-import.php        # Import page template
├── assets/
│   ├── css/
│   │   └── admin.css          # Admin styles
│   └── js/
│       └── admin.js           # Admin scripts
└── readme.txt                 # Plugin information
```

= API Documentation =

Full API documentation is available at: https://wpelance.com/docs/theme-kit-pro/api

== Support ==

For support, feature requests, or bug reports, please visit: https://wpelance.com/support

Documentation: https://wpelance.com/docs/theme-kit-pro

== License ==

This plugin is licensed under the GPLv2 or later.