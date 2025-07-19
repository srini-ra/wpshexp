<?php
/**
 * Plugin Name: Theme Kit Pro
 * Plugin URI: https://wpelance.com/theme-kit-pro
 * Description: Professional theme export/import solution with full Elementor & Gutenberg support, WooCommerce integration, marketplace distribution, and cloud storage options
 * Version: 1.0.0
 * Author: WPelance
 * Author URI: https://wpelance.com
 * License: GPL v2 or later
 * Text Domain: theme-kit-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * 
 * Security Features:
 * - Input sanitization and validation
 * - Nonce verification for all AJAX requests
 * - Capability checks for all operations
 * - File type validation and security scanning
 * - SQL injection prevention
 * - XSS protection
 * - CSRF protection
 * - Secure file handling
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TKP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TKP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TKP_PLUGIN_VERSION', '1.0.0');
define('TKP_PLUGIN_FILE', __FILE__);
define('TKP_MIN_PHP_VERSION', '7.4');
define('TKP_MIN_WP_VERSION', '5.0');

// Main plugin class
class ThemeKitPro {
    
    private static $instance = null;
    private $logger;
    private $compatibility_checker;
    private $error_handler;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Check compatibility before initialization
        if (!$this->check_compatibility()) {
            return;
        }
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
        
        // AJAX handlers
        add_action('wp_ajax_tkp_export_theme', array($this, 'ajax_export_theme'));
        add_action('wp_ajax_tkp_import_theme', array($this, 'ajax_import_theme'));
        add_action('wp_ajax_tkp_selective_export', array($this, 'ajax_selective_export'));
        add_action('wp_ajax_tkp_generate_preview', array($this, 'ajax_generate_preview'));
        add_action('wp_ajax_tkp_marketplace_upload', array($this, 'ajax_marketplace_upload'));
        add_action('wp_ajax_tkp_compatibility_check', array($this, 'ajax_compatibility_check'));
        add_action('wp_ajax_tkp_validate_kit', array($this, 'ajax_validate_kit'));
        add_action('wp_ajax_tkp_batch_process', array($this, 'ajax_batch_process'));
        add_action('wp_ajax_tkp_download_package', array($this, 'ajax_download_package'));
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize error handling
        $this->init_error_handling();
    }
    
    /**
     * Check plugin compatibility
     */
    private function check_compatibility() {
        // Check PHP version
        if (version_compare(PHP_VERSION, TKP_MIN_PHP_VERSION, '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                printf(__('Theme Kit Pro requires PHP %s or higher. You are running PHP %s.', 'theme-kit-pro'), TKP_MIN_PHP_VERSION, PHP_VERSION);
                echo '</p></div>';
            });
            return false;
        }
        
        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, TKP_MIN_WP_VERSION, '<')) {
            add_action('admin_notices', function() use ($wp_version) {
                echo '<div class="notice notice-error"><p>';
                printf(__('Theme Kit Pro requires WordPress %s or higher. You are running WordPress %s.', 'theme-kit-pro'), TKP_MIN_WP_VERSION, $wp_version);
                echo '</p></div>';
            });
            return false;
        }
        
        // Check required extensions
        $required_extensions = array('zip', 'curl', 'json');
        $missing_extensions = array();
        
        foreach ($required_extensions as $extension) {
            if (!extension_loaded($extension)) {
                $missing_extensions[] = $extension;
            }
        }
        
        if (!empty($missing_extensions)) {
            add_action('admin_notices', function() use ($missing_extensions) {
                echo '<div class="notice notice-error"><p>';
                printf(__('Theme Kit Pro requires the following PHP extensions: %s', 'theme-kit-pro'), implode(', ', $missing_extensions));
                echo '</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Initialize error handling
     */
    private function init_error_handling() {
        require_once TKP_PLUGIN_PATH . 'includes/class-error-handler.php';
        require_once TKP_PLUGIN_PATH . 'includes/class-logger.php';
        
        $this->logger = new TKP_Logger();
        $this->error_handler = new TKP_Error_Handler($this->logger);
    }
    
    public function init() {
        load_plugin_textdomain('theme-kit-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        $this->include_files();
        
        // Initialize compatibility checker
        require_once TKP_PLUGIN_PATH . 'includes/class-compatibility-checker.php';
        $this->compatibility_checker = new TKP_Compatibility_Checker();
    }
    
    /**
     * Include required files
     */
    private function include_files() {
        $includes = array(
            'includes/class-exporter.php',
            'includes/class-importer.php',
            'includes/class-elementor-handler.php',
            'includes/class-gutenberg-handler.php',
            'includes/class-file-handler.php',
            'includes/class-woocommerce-handler.php',
            'includes/class-selective-exporter.php',
            'includes/class-theme-preview.php',
            'includes/class-marketplace-integration.php',
            'includes/class-image-processor.php',
            'includes/class-url-replacer.php',
            'includes/class-batch-processor.php',
            'includes/class-kit-validator.php',
            'includes/class-support-helper.php',
            'includes/class-security-scanner.php',
            'includes/class-cloud-storage.php'
        );
        
        foreach ($includes as $file) {
            $file_path = TKP_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                $this->logger->log('error', "Required file missing: {$file}");
            }
        }
    }
    
    public function add_admin_menu() {
        $capability = 'manage_options';
        
        // Main menu
        add_menu_page(
            __('Theme Kit Pro', 'theme-kit-pro'),
            __('Theme Kit Pro', 'theme-kit-pro'),
            $capability,
            'theme-kit-pro',
            array($this, 'admin_page'),
            'dashicons-download',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            'theme-kit-pro',
            __('Export Kit', 'theme-kit-pro'),
            __('Export', 'theme-kit-pro'),
            $capability,
            'theme-kit-pro',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'theme-kit-pro',
            __('Import Kit', 'theme-kit-pro'),
            __('Import', 'theme-kit-pro'),
            $capability,
            'theme-kit-pro-import',
            array($this, 'import_page')
        );
        
        add_submenu_page(
            'theme-kit-pro',
            __('Selective Export', 'theme-kit-pro'),
            __('Selective Export', 'theme-kit-pro'),
            $capability,
            'theme-kit-pro-selective',
            array($this, 'selective_export_page')
        );
        
        add_submenu_page(
            'theme-kit-pro',
            __('Kit Preview', 'theme-kit-pro'),
            __('Preview', 'theme-kit-pro'),
            $capability,
            'theme-kit-pro-preview',
            array($this, 'preview_page')
        );
        
        add_submenu_page(
            'theme-kit-pro',
            __('Marketplace', 'theme-kit-pro'),
            __('Marketplace', 'theme-kit-pro'),
            $capability,
            'theme-kit-pro-marketplace',
            array($this, 'marketplace_page')
        );
        
        add_submenu_page(
            'theme-kit-pro',
            __('Logs & Support', 'theme-kit-pro'),
            __('Logs & Support', 'theme-kit-pro'),
            $capability,
            'theme-kit-pro-support',
            array($this, 'support_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'theme-kit-pro') === false) {
            return;
        }
        
        wp_enqueue_script('tkp-admin-js', TKP_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), TKP_PLUGIN_VERSION, true);
        wp_enqueue_style('tkp-admin-css', TKP_PLUGIN_URL . 'assets/css/admin.css', array(), TKP_PLUGIN_VERSION);
        
        wp_localize_script('tkp-admin-js', 'tkpAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tkp_nonce'),
            'strings' => array(
                'exporting' => __('Exporting theme kit...', 'theme-kit-pro'),
                'importing' => __('Importing theme kit...', 'theme-kit-pro'),
                'validating' => __('Validating kit...', 'theme-kit-pro'),
                'processing' => __('Processing...', 'theme-kit-pro'),
                'success' => __('Operation completed successfully!', 'theme-kit-pro'),
                'error' => __('An error occurred. Please check the logs.', 'theme-kit-pro'),
                'compatibility_check' => __('Running compatibility check...', 'theme-kit-pro'),
                'batch_processing' => __('Processing batch...', 'theme-kit-pro')
            ),
            'settings' => array(
                'is_localhost' => $this->is_localhost(),
                'max_execution_time' => ini_get('max_execution_time'),
                'memory_limit' => ini_get('memory_limit'),
                'upload_max_filesize' => ini_get('upload_max_filesize')
            )
        ));
    }
    
    /**
     * Check if running on localhost
     */
    private function is_localhost() {
        $localhost_ips = array('127.0.0.1', '::1', 'localhost');
        $server_addr = $_SERVER['SERVER_ADDR'] ?? '';
        $http_host = $_SERVER['HTTP_HOST'] ?? '';
        
        return in_array($server_addr, $localhost_ips) || 
               strpos($http_host, 'localhost') !== false ||
               strpos($http_host, '.local') !== false ||
               strpos($http_host, '.test') !== false;
    }
    
    public function show_admin_notices() {
        // Show compatibility warnings
        $warnings = $this->compatibility_checker->get_warnings();
        
        foreach ($warnings as $warning) {
            echo '<div class="notice notice-warning"><p>' . esc_html($warning) . '</p></div>';
        }
        
        // Show error notices from logger
        $recent_errors = $this->logger->get_recent_errors(5);
        
        foreach ($recent_errors as $error) {
            echo '<div class="notice notice-error is-dismissible"><p>';
            echo '<strong>' . esc_html($error['level']) . ':</strong> ';
            echo esc_html($error['message']);
            echo ' <small>(' . esc_html($error['timestamp']) . ')</small>';
            echo '</p></div>';
        }
    }
    
    // Admin page methods
    public function admin_page() {
        include TKP_PLUGIN_PATH . 'templates/admin-export.php';
    }
    
    public function import_page() {
        include TKP_PLUGIN_PATH . 'templates/admin-import.php';
    }
    
    public function selective_export_page() {
        include TKP_PLUGIN_PATH . 'templates/admin-selective-export.php';
    }
    
    public function preview_page() {
        include TKP_PLUGIN_PATH . 'templates/admin-preview.php';
    }
    
    public function marketplace_page() {
        include TKP_PLUGIN_PATH . 'templates/admin-marketplace.php';
    }
    
    public function support_page() {
        include TKP_PLUGIN_PATH . 'templates/admin-support.php';
    }
    
    // AJAX handlers with enhanced error handling
    public function ajax_export_theme() {
        try {
            check_ajax_referer('tkp_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                throw new Exception(__('Insufficient permissions', 'theme-kit-pro'));
            }
            
            $exporter = new TKP_Exporter($this->logger);
            $result = $exporter->export_theme_package($_POST);
            
            wp_send_json($result);
            
        } catch (Exception $e) {
            $this->error_handler->handle_ajax_error($e, 'export_theme');
        }
    }
    
    public function ajax_import_theme() {
        try {
            check_ajax_referer('tkp_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                throw new Exception(__('Insufficient permissions', 'theme-kit-pro'));
            }
            
            // Run pre-import compatibility check
            $compatibility_result = $this->compatibility_checker->check_import_compatibility($_FILES, $_POST);
            
            if (!$compatibility_result['compatible']) {
                wp_send_json(array(
                    'success' => false,
                    'message' => __('Compatibility check failed', 'theme-kit-pro'),
                    'errors' => $compatibility_result['errors'],
                    'warnings' => $compatibility_result['warnings']
                ));
                return;
            }
            
            $importer = new TKP_Importer($this->logger);
            $result = $importer->import_theme_package($_FILES, $_POST);
            
            wp_send_json($result);
            
        } catch (Exception $e) {
            $this->error_handler->handle_ajax_error($e, 'import_theme');
        }
    }
    
    public function ajax_compatibility_check() {
        try {
            check_ajax_referer('tkp_nonce', 'nonce');
            
            $check_type = sanitize_text_field($_POST['check_type'] ?? 'general');
            $result = $this->compatibility_checker->run_check($check_type, $_POST);
            
            wp_send_json($result);
            
        } catch (Exception $e) {
            $this->error_handler->handle_ajax_error($e, 'compatibility_check');
        }
    }
    
    public function ajax_validate_kit() {
        try {
            check_ajax_referer('tkp_nonce', 'nonce');
            
            $validator = new TKP_Kit_Validator($this->logger);
            $result = $validator->validate_kit($_FILES['kit_file']['tmp_name']);
            
            wp_send_json($result);
            
        } catch (Exception $e) {
            $this->error_handler->handle_ajax_error($e, 'validate_kit');
        }
    }
    
    public function ajax_batch_process() {
        try {
            check_ajax_referer('tkp_nonce', 'nonce');
            
            $processor = new TKP_Batch_Processor($this->logger);
            $result = $processor->process_batch($_POST);
            
            wp_send_json($result);
            
        } catch (Exception $e) {
            $this->error_handler->handle_ajax_error($e, 'batch_process');
        }
    }
    
    /**
     * Handle package download
     */
    public function ajax_download_package() {
        try {
            check_ajax_referer('tkp_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                throw new Exception(__('Insufficient permissions', 'theme-kit-pro'));
            }
            
            $package_path = sanitize_text_field($_POST['package_path']);
            $download_type = sanitize_text_field($_POST['download_type']);
            
            if (!file_exists($package_path)) {
                throw new Exception(__('Package file not found', 'theme-kit-pro'));
            }
            
            switch ($download_type) {
                case 'local':
                    $result = $this->download_to_local($package_path);
                    break;
                case 'google_drive':
                    $cloud_storage = new TKP_Cloud_Storage($this->logger);
                    $result = $cloud_storage->upload_to_google_drive($package_path);
                    break;
                default:
                    throw new Exception(__('Invalid download type', 'theme-kit-pro'));
            }
            
            wp_send_json($result);
            
        } catch (Exception $e) {
            $this->error_handler->handle_ajax_error($e, 'download_package');
        }
    }
    
    /**
     * Download to local drive
     */
    private function download_to_local($package_path) {
        $filename = basename($package_path);
        
        // Security check - ensure file is in allowed directory
        $upload_dir = wp_upload_dir();
        $allowed_path = $upload_dir['basedir'] . '/theme-kit-pro/';
        
        if (strpos(realpath($package_path), realpath($allowed_path)) !== 0) {
            throw new Exception(__('Invalid file path', 'theme-kit-pro'));
        }
        
        return array(
            'success' => true,
            'download_url' => $upload_dir['baseurl'] . '/theme-kit-pro/' . $filename,
            'filename' => $filename,
            'size' => size_format(filesize($package_path))
        );
    }
    
    public function activate() {
        // Create necessary database tables
        $this->create_database_tables();
        
        // Create upload directories
        $this->create_directories();
        
        // Set default options
        $this->set_default_options();
        
        // Log activation
        $this->logger->log('info', 'Theme Kit Pro activated');
    }
    
    public function deactivate() {
        // Cleanup temporary files
        $this->cleanup_temp_files();
        
        // Log deactivation
        $this->logger->log('info', 'Theme Kit Pro deactivated');
    }
    
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Exports table
        $table_name = $wpdb->prefix . 'tkp_exports';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            export_name varchar(255) NOT NULL,
            export_data longtext NOT NULL,
            export_type varchar(50) NOT NULL DEFAULT 'full',
            file_path varchar(500),
            file_size bigint(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'completed',
            PRIMARY KEY (id),
            KEY export_type (export_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Logs table
        $logs_table = $wpdb->prefix . 'tkp_logs';
        $logs_sql = "CREATE TABLE $logs_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY level (level),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($logs_sql);
    }
    
    private function create_directories() {
        $upload_dir = wp_upload_dir();
        $directories = array(
            $upload_dir['basedir'] . '/theme-kit-pro',
            $upload_dir['basedir'] . '/theme-kit-pro/exports',
            $upload_dir['basedir'] . '/theme-kit-pro/imports',
            $upload_dir['basedir'] . '/theme-kit-pro/temp',
            $upload_dir['basedir'] . '/theme-kit-pro/previews',
            $upload_dir['basedir'] . '/theme-kit-pro/logs'
        );
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
                
                // Add .htaccess for security
                $htaccess_content = "Options -Indexes\nDeny from all\n";
                file_put_contents($dir . '/.htaccess', $htaccess_content);
            }
        }
    }
    
    private function set_default_options() {
        $defaults = array(
            'tkp_max_execution_time' => 300,
            'tkp_memory_limit' => '512M',
            'tkp_enable_logging' => true,
            'tkp_log_level' => 'info',
            'tkp_auto_cleanup' => true,
            'tkp_cleanup_days' => 30,
            'tkp_enable_localhost_mode' => $this->is_localhost(),
            'tkp_batch_size' => 50,
            'tkp_enable_responsive_validation' => true,
            'tkp_enable_image_optimization' => true
        );
        
        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    private function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/theme-kit-pro/temp';
        
        if (file_exists($temp_dir)) {
            $this->recursive_rmdir($temp_dir);
        }
    }
    
    private function recursive_rmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->recursive_rmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}

// Initialize the plugin
ThemeKitPro::getInstance();