<?php
/**
 * Theme Importer Class
 * 
 * Handles the import functionality for theme packages
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_Importer {
    
    private $temp_dir;
    private $upload_dir;
    private $manifest;
    
    public function __construct() {
        $this->upload_dir = wp_upload_dir();
        $this->temp_dir = $this->upload_dir['basedir'] . '/theme-exporter-pro/import';
    }
    
    /**
     * Import theme package
     */
    public function import_theme_package($files, $options) {
        try {
            // Validate upload
            if (!isset($files['package_file']) || $files['package_file']['error'] !== UPLOAD_ERR_OK) {
                return array('success' => false, 'message' => __('Invalid file upload', 'theme-exporter-pro'));
            }
            
            // Create temporary directory
            $this->create_temp_directory();
            
            // Extract package
            $this->extract_package($files['package_file']['tmp_name']);
            
            // Load manifest
            $this->load_manifest();
            
            // Validate package
            if (!$this->validate_package()) {
                return array('success' => false, 'message' => __('Invalid package format', 'theme-exporter-pro'));
            }
            
            $results = array();
            
            // Import theme
            if (isset($options['import_theme']) && $options['import_theme'] && $this->manifest['installation_steps']['install_theme']) {
                $results['theme'] = $this->import_theme();
            }
            
            // Install plugins
            if (isset($options['install_plugins']) && $options['install_plugins'] && $this->manifest['installation_steps']['install_plugins']) {
                $results['plugins'] = $this->install_required_plugins();
            }
            
            // Import customizer settings
            if (isset($options['import_customizer']) && $options['import_customizer'] && $this->manifest['installation_steps']['import_customizer']) {
                $results['customizer'] = $this->import_customizer_settings();
            }
            
            // Import demo content
            if (isset($options['import_content']) && $options['import_content'] && $this->manifest['installation_steps']['import_content']) {
                $results['content'] = $this->import_demo_content();
            }
            
            // Import widgets
            if (isset($options['import_widgets']) && $options['import_widgets'] && $this->manifest['installation_steps']['import_widgets']) {
                $results['widgets'] = $this->import_widgets();
            }
            
            // Import templates
            if (isset($options['import_templates']) && $options['import_templates'] && $this->manifest['installation_steps']['import_templates']) {
                $results['templates'] = $this->import_templates();
            }
            
            // Cleanup
            $this->cleanup_temp_files();
            
            return array(
                'success' => true,
                'message' => __('Theme package imported successfully!', 'theme-exporter-pro'),
                'results' => $results
            );
            
        } catch (Exception $e) {
            $this->cleanup_temp_files();
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    /**
     * Create temporary directory
     */
    private function create_temp_directory() {
        if (!file_exists($this->temp_dir)) {
            wp_mkdir_p($this->temp_dir);
        }
        
        // Create unique import directory
        $this->temp_dir .= '/' . uniqid('import_');
        wp_mkdir_p($this->temp_dir);
    }
    
    /**
     * Extract package
     */
    private function extract_package($zip_file) {
        $zip = new ZipArchive();
        
        if ($zip->open($zip_file) !== TRUE) {
            throw new Exception(__('Cannot open package file', 'theme-exporter-pro'));
        }
        
        $zip->extractTo($this->temp_dir);
        $zip->close();
    }
    
    /**
     * Load manifest
     */
    private function load_manifest() {
        $manifest_file = $this->temp_dir . '/manifest.json';
        
        if (!file_exists($manifest_file)) {
            throw new Exception(__('Package manifest not found', 'theme-exporter-pro'));
        }
        
        $this->manifest = json_decode(file_get_contents($manifest_file), true);
        
        if (!$this->manifest) {
            throw new Exception(__('Invalid package manifest', 'theme-exporter-pro'));
        }
    }
    
    /**
     * Validate package
     */
    private function validate_package() {
        if (!isset($this->manifest['package_info']) || !isset($this->manifest['installation_steps'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Import theme
     */
    private function import_theme() {
        $theme_dir = $this->temp_dir . '/theme';
        
        if (!file_exists($theme_dir)) {
            return array('success' => false, 'message' => __('Theme files not found', 'theme-exporter-pro'));
        }
        
        // Get theme destination
        $themes_dir = get_theme_root();
        $theme_name = $this->manifest['package_info']['package_name'];
        $theme_dest = $themes_dir . '/' . sanitize_file_name($theme_name);
        
        // Copy theme files
        $this->copy_directory($theme_dir, $theme_dest);
        
        // Activate theme
        switch_theme($theme_name);
        
        return array('success' => true, 'message' => __('Theme imported and activated', 'theme-exporter-pro'));
    }
    
    /**
     * Install required plugins
     */
    private function install_required_plugins() {
        $plugins_file = $this->temp_dir . '/required-plugins.json';
        
        if (!file_exists($plugins_file)) {
            return array('success' => false, 'message' => __('Plugin list not found', 'theme-exporter-pro'));
        }
        
        $plugins = json_decode(file_get_contents($plugins_file), true);
        $results = array();
        
        if (!function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        
        if (!class_exists('WP_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        
        foreach ($plugins as $plugin) {
            if (!$this->is_plugin_installed($plugin['plugin_file'])) {
                $result = $this->install_plugin($plugin['name']);
                $results[] = $result;
            } else {
                $results[] = array(
                    'name' => $plugin['name'],
                    'status' => 'already_installed'
                );
            }
        }
        
        return array('success' => true, 'plugins' => $results);
    }
    
    /**
     * Check if plugin is installed
     */
    private function is_plugin_installed($plugin_file) {
        $plugins = get_plugins();
        return isset($plugins[$plugin_file]);
    }
    
    /**
     * Install plugin
     */
    private function install_plugin($plugin_name) {
        $api = plugins_api('plugin_information', array(
            'slug' => sanitize_title($plugin_name),
            'fields' => array(
                'short_description' => false,
                'sections' => false,
                'requires' => false,
                'rating' => false,
                'ratings' => false,
                'downloaded' => false,
                'last_updated' => false,
                'added' => false,
                'tags' => false,
                'compatibility' => false,
                'homepage' => false,
                'donate_link' => false,
            ),
        ));
        
        if (is_wp_error($api)) {
            return array(
                'name' => $plugin_name,
                'status' => 'error',
                'message' => $api->get_error_message()
            );
        }
        
        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
        $result = $upgrader->install($api->download_link);
        
        if (is_wp_error($result)) {
            return array(
                'name' => $plugin_name,
                'status' => 'error',
                'message' => $result->get_error_message()
            );
        }
        
        return array(
            'name' => $plugin_name,
            'status' => 'installed'
        );
    }
    
    /**
     * Import customizer settings
     */
    private function import_customizer_settings() {
        $customizer_file = $this->temp_dir . '/customizer-settings.json';
        
        if (!file_exists($customizer_file)) {
            return array('success' => false, 'message' => __('Customizer settings not found', 'theme-exporter-pro'));
        }
        
        $customizer_data = json_decode(file_get_contents($customizer_file), true);
        
        // Import theme mods
        if (isset($customizer_data['theme_mods'])) {
            foreach ($customizer_data['theme_mods'] as $mod_name => $mod_value) {
                set_theme_mod($mod_name, $mod_value);
            }
        }
        
        // Import options
        if (isset($customizer_data['options'])) {
            foreach ($customizer_data['options'] as $option_name => $option_value) {
                update_option($option_name, $option_value);
            }
        }
        
        return array('success' => true, 'message' => __('Customizer settings imported', 'theme-exporter-pro'));
    }
    
    /**
     * Import demo content
     */
    private function import_demo_content() {
        $content_file = $this->temp_dir . '/demo-content.json';
        
        if (!file_exists($content_file)) {
            return array('success' => false, 'message' => __('Demo content not found', 'theme-exporter-pro'));
        }
        
        $content_data = json_decode(file_get_contents($content_file), true);
        $imported_posts = 0;
        
        foreach ($content_data as $post_data) {
            $post_id = wp_insert_post(array(
                'post_title' => $post_data['post_title'],
                'post_content' => $post_data['post_content'],
                'post_excerpt' => $post_data['post_excerpt'],
                'post_type' => $post_data['post_type'],
                'post_status' => 'publish'
            ));
            
            if ($post_id && !is_wp_error($post_id)) {
                // Import post meta
                if (isset($post_data['post_meta'])) {
                    foreach ($post_data['post_meta'] as $meta_key => $meta_values) {
                        foreach ($meta_values as $meta_value) {
                            add_post_meta($post_id, $meta_key, $meta_value);
                        }
                    }
                }
                
                $imported_posts++;
            }
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('%d posts imported', 'theme-exporter-pro'), $imported_posts)
        );
    }
    
    /**
     * Import widgets
     */
    private function import_widgets() {
        $widgets_file = $this->temp_dir . '/widgets.json';
        
        if (!file_exists($widgets_file)) {
            return array('success' => false, 'message' => __('Widgets not found', 'theme-exporter-pro'));
        }
        
        $widgets_data = json_decode(file_get_contents($widgets_file), true);
        
        // Import widget settings
        if (isset($widgets_data['settings'])) {
            foreach ($widgets_data['settings'] as $widget_type => $widget_settings) {
                update_option('widget_' . $widget_type, $widget_settings);
            }
        }
        
        // Import sidebar assignments
        if (isset($widgets_data['sidebars'])) {
            wp_set_sidebars_widgets($widgets_data['sidebars']);
        }
        
        return array('success' => true, 'message' => __('Widgets imported', 'theme-exporter-pro'));
    }
    
    /**
     * Import templates
     */
    private function import_templates() {
        $results = array();
        
        // Import Elementor templates
        if (file_exists($this->temp_dir . '/elementor-templates.json')) {
            $elementor_handler = new TEP_Elementor_Handler();
            $elementor_templates = json_decode(file_get_contents($this->temp_dir . '/elementor-templates.json'), true);
            $results['elementor'] = $elementor_handler->import_templates($elementor_templates);
        }
        
        // Import Gutenberg templates
        if (file_exists($this->temp_dir . '/gutenberg-templates.json')) {
            $gutenberg_handler = new TEP_Gutenberg_Handler();
            $gutenberg_templates = json_decode(file_get_contents($this->temp_dir . '/gutenberg-templates.json'), true);
            $results['gutenberg'] = $gutenberg_handler->import_templates($gutenberg_templates);
        }
        
        return array('success' => true, 'results' => $results);
    }
    
    /**
     * Copy directory recursively
     */
    private function copy_directory($source, $destination) {
        if (!file_exists($destination)) {
            wp_mkdir_p($destination);
        }
        
        $files = scandir($source);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $source_path = $source . '/' . $file;
            $dest_path = $destination . '/' . $file;
            
            if (is_dir($source_path)) {
                $this->copy_directory($source_path, $dest_path);
            } else {
                copy($source_path, $dest_path);
            }
        }
    }
    
    /**
     * Cleanup temporary files
     */
    private function cleanup_temp_files() {
        if (file_exists($this->temp_dir)) {
            $this->recursive_rmdir($this->temp_dir);
        }
    }
    
    /**
     * Remove directory recursively
     */
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