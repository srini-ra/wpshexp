<?php
<?php
/**
 * Plugin Name: Theme Exporter Pro
 * Plugin URI: https://yourwebsite.com/theme-exporter-pro
 * Description: Export child themes as packages with templates, plugins, and demo data for Elementor and Gutenberg
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * Text Domain: theme-exporter-pro
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TEP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TEP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TEP_PLUGIN_VERSION', '1.0.0');

// Main plugin class
class ThemeExporterPro {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_tep_export_theme', array($this, 'ajax_export_theme'));
        add_action('wp_ajax_tep_import_theme', array($this, 'ajax_import_theme'));
        add_action('wp_ajax_tep_selective_export', array($this, 'ajax_selective_export'));
        add_action('wp_ajax_tep_generate_preview', array($this, 'ajax_generate_preview'));
        add_action('wp_ajax_tep_marketplace_upload', array($this, 'ajax_marketplace_upload'));
        
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        load_plugin_textdomain('theme-exporter-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Include required files
        require_once TEP_PLUGIN_PATH . 'includes/class-exporter.php';
        require_once TEP_PLUGIN_PATH . 'includes/class-importer.php';
        require_once TEP_PLUGIN_PATH . 'includes/class-elementor-handler.php';
        require_once TEP_PLUGIN_PATH . 'includes/class-gutenberg-handler.php';
        require_once TEP_PLUGIN_PATH . 'includes/class-file-handler.php';
        require_once TEP_PLUGIN_PATH . 'includes/class-woocommerce-handler.php';
        require_once TEP_PLUGIN_PATH . 'includes/class-selective-exporter.php';
        require_once TEP_PLUGIN_PATH . 'includes/class-theme-preview.php';
        require_once TEP_PLUGIN_PATH . 'includes/class-marketplace-integration.php';
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Theme Exporter Pro', 'theme-exporter-pro'),
            __('Theme Exporter', 'theme-exporter-pro'),
            'manage_options',
            'theme-exporter-pro',
            array($this, 'admin_page'),
            'dashicons-download',
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'theme-exporter-pro',
            __('Export Theme', 'theme-exporter-pro'),
            __('Export', 'theme-exporter-pro'),
            'manage_options',
            'theme-exporter-pro',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'theme-exporter-pro',
            __('Import Theme', 'theme-exporter-pro'),
            __('Import', 'theme-exporter-pro'),
            'manage_options',
            'theme-exporter-import',
            array($this, 'import_page')
        );
        
        add_submenu_page(
            'theme-exporter-pro',
            __('Selective Export', 'theme-exporter-pro'),
            __('Selective Export', 'theme-exporter-pro'),
            'manage_options',
            'theme-exporter-selective',
            array($this, 'selective_export_page')
        );
        
        add_submenu_page(
            'theme-exporter-pro',
            __('Preview', 'theme-exporter-pro'),
            __('Preview', 'theme-exporter-pro'),
            'manage_options',
            'theme-exporter-preview',
            array($this, 'preview_page')
        );
        
        add_submenu_page(
            'theme-exporter-pro',
            __('Marketplace', 'theme-exporter-pro'),
            __('Marketplace', 'theme-exporter-pro'),
            'manage_options',
            'theme-exporter-marketplace',
            array($this, 'marketplace_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'theme-exporter') === false) {
            return;
        }
        
        wp_enqueue_script('tep-admin-js', TEP_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), TEP_PLUGIN_VERSION, true);
        wp_enqueue_style('tep-admin-css', TEP_PLUGIN_URL . 'assets/css/admin.css', array(), TEP_PLUGIN_VERSION);
        
        wp_localize_script('tep-admin-js', 'tepAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tep_nonce'),
            'strings' => array(
                'exporting' => __('Exporting theme package...', 'theme-exporter-pro'),
                'importing' => __('Importing theme package...', 'theme-exporter-pro'),
                'success' => __('Operation completed successfully!', 'theme-exporter-pro'),
                'error' => __('An error occurred. Please try again.', 'theme-exporter-pro'),
            )
        ));
    }
    
    public function admin_page() {
        include TEP_PLUGIN_PATH . 'templates/admin-export.php';
    }
    
    public function import_page() {
        include TEP_PLUGIN_PATH . 'templates/admin-import.php';
    }
    
    public function selective_export_page() {
        include TEP_PLUGIN_PATH . 'templates/admin-selective-export.php';
    }
    
    public function preview_page() {
        include TEP_PLUGIN_PATH . 'templates/admin-preview.php';
    }
    
    public function marketplace_page() {
        include TEP_PLUGIN_PATH . 'templates/admin-marketplace.php';
    }
    
    public function ajax_export_theme() {
        check_ajax_referer('tep_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'theme-exporter-pro'));
        }
        
        $exporter = new TEP_Exporter();
        $result = $exporter->export_theme_package($_POST);
        
        wp_send_json($result);
    }
    
    public function ajax_import_theme() {
        check_ajax_referer('tep_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'theme-exporter-pro'));
        }
        
        $importer = new TEP_Importer();
        $result = $importer->import_theme_package($_FILES, $_POST);
        
        wp_send_json($result);
    }
    
    public function ajax_selective_export() {
        check_ajax_referer('tep_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'theme-exporter-pro'));
        }
        
        $selective_exporter = new TEP_Selective_Exporter();
        $result = $selective_exporter->export_selected_content($_POST);
        
        wp_send_json($result);
    }
    
    public function ajax_generate_preview() {
        check_ajax_referer('tep_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'theme-exporter-pro'));
        }
        
        $theme_preview = new TEP_Theme_Preview();
        $result = $theme_preview->generate_theme_preview($_POST['package_path']);
        
        wp_send_json($result);
    }
    
    public function ajax_marketplace_upload() {
        check_ajax_referer('tep_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'theme-exporter-pro'));
        }
        
        $marketplace = new TEP_Marketplace_Integration();
        $result = $marketplace->upload_to_marketplace($_POST['package_path'], $_POST['marketplace_id'], $_POST);
        
        wp_send_json($result);
    }
    
    public function activate() {
        // Create necessary database tables
        $this->create_database_tables();
        
        // Create upload directories
        $upload_dir = wp_upload_dir();
        $tep_dir = $upload_dir['basedir'] . '/theme-exporter-pro';
        
        if (!file_exists($tep_dir)) {
            wp_mkdir_p($tep_dir);
        }
    }
    
    public function deactivate() {
        // Cleanup temporary files
        $this->cleanup_temp_files();
    }
    
    private function create_database_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tep_exports';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            export_name varchar(255) NOT NULL,
            export_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    private function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $tep_dir = $upload_dir['basedir'] . '/theme-exporter-pro/temp';
        
        if (file_exists($tep_dir)) {
            $this->recursive_rmdir($tep_dir);
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
ThemeExporterPro::getInstance();