<?php
/**
 * Theme Exporter Class
 * 
 * Handles the export functionality for theme packages
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_Exporter {
    
    private $temp_dir;
    private $upload_dir;
    private $export_data;
    
    public function __construct() {
        $this->upload_dir = wp_upload_dir();
        $this->temp_dir = $this->upload_dir['basedir'] . '/theme-exporter-pro/temp';
        $this->export_data = array();
    }
    
    /**
     * Export theme package
     */
    public function export_theme_package($options) {
        try {
            // Validate input
            if (!$this->validate_export_options($options)) {
                return array('success' => false, 'message' => __('Invalid export options', 'theme-exporter-pro'));
            }
            
            // Create temporary directory
            $this->create_temp_directory();
            
            // Initialize export data
            $this->init_export_data($options);
            
            // Export theme files
            if (isset($options['export_theme']) && $options['export_theme']) {
                $this->export_theme_files();
            }
            
            // Export templates
            if (isset($options['export_templates']) && $options['export_templates']) {
                $this->export_templates($options['builder_type']);
            }
            
            // Export customizer settings
            if (isset($options['export_customizer']) && $options['export_customizer']) {
                $this->export_customizer_settings();
            }
            
            // Export demo content
            if (isset($options['export_content']) && $options['export_content']) {
                $this->export_demo_content();
            }
            
            // Export plugin list
            if (isset($options['export_plugins']) && $options['export_plugins']) {
                $this->export_plugin_list();
            }
            
            // Export widgets
            if (isset($options['export_widgets']) && $options['export_widgets']) {
                $this->export_widgets();
            }
            
            // Create package manifest
            $this->create_package_manifest();
            
            // Create ZIP package
            $zip_path = $this->create_zip_package($options['package_name']);
            
            // Cleanup temporary files
            $this->cleanup_temp_files();
            
            return array(
                'success' => true, 
                'message' => __('Theme package exported successfully!', 'theme-exporter-pro'),
                'download_url' => $this->upload_dir['baseurl'] . '/theme-exporter-pro/' . basename($zip_path)
            );
            
        } catch (Exception $e) {
            $this->cleanup_temp_files();
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    /**
     * Validate export options
     */
    private function validate_export_options($options) {
        if (empty($options['package_name'])) {
            return false;
        }
        
        if (empty($options['builder_type']) || !in_array($options['builder_type'], array('elementor', 'gutenberg', 'both'))) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Create temporary directory
     */
    private function create_temp_directory() {
        if (!file_exists($this->temp_dir)) {
            wp_mkdir_p($this->temp_dir);
        }
        
        // Create unique export directory
        $this->temp_dir .= '/' . uniqid('export_');
        wp_mkdir_p($this->temp_dir);
    }
    
    /**
     * Initialize export data
     */
    private function init_export_data($options) {
        $this->export_data = array(
            'package_name' => sanitize_text_field($options['package_name']),
            'package_description' => sanitize_textarea_field($options['package_description'] ?? ''),
            'builder_type' => sanitize_text_field($options['builder_type']),
            'theme_name' => get_stylesheet(),
            'theme_version' => wp_get_theme()->get('Version'),
            'wordpress_version' => get_bloginfo('version'),
            'export_date' => current_time('mysql'),
            'export_options' => $options
        );
    }
    
    /**
     * Export theme files
     */
    private function export_theme_files() {
        $theme_dir = get_stylesheet_directory();
        $export_theme_dir = $this->temp_dir . '/theme';
        
        wp_mkdir_p($export_theme_dir);
        
        // Copy theme files
        $this->copy_directory($theme_dir, $export_theme_dir);
        
        // Add theme info to export data
        $this->export_data['theme_files'] = true;
        $this->export_data['theme_info'] = array(
            'name' => wp_get_theme()->get('Name'),
            'description' => wp_get_theme()->get('Description'),
            'version' => wp_get_theme()->get('Version'),
            'author' => wp_get_theme()->get('Author'),
            'template' => wp_get_theme()->get('Template')
        );
    }
    
    /**
     * Export templates based on builder type
     */
    private function export_templates($builder_type) {
        if ($builder_type === 'elementor' || $builder_type === 'both') {
            $this->export_elementor_templates();
        }
        
        if ($builder_type === 'gutenberg' || $builder_type === 'both') {
            $this->export_gutenberg_templates();
        }
    }
    
    /**
     * Export Elementor templates
     */
    private function export_elementor_templates() {
        if (!class_exists('Elementor\Plugin')) {
            return;
        }
        
        $elementor_handler = new TEP_Elementor_Handler();
        $templates = $elementor_handler->export_templates();
        
        if (!empty($templates)) {
            file_put_contents($this->temp_dir . '/elementor-templates.json', json_encode($templates));
            $this->export_data['elementor_templates'] = count($templates);
        }
    }
    
    /**
     * Export Gutenberg templates
     */
    private function export_gutenberg_templates() {
        $gutenberg_handler = new TEP_Gutenberg_Handler();
        $templates = $gutenberg_handler->export_templates();
        
        if (!empty($templates)) {
            file_put_contents($this->temp_dir . '/gutenberg-templates.json', json_encode($templates));
            $this->export_data['gutenberg_templates'] = count($templates);
        }
    }
    
    /**
     * Export customizer settings
     */
    private function export_customizer_settings() {
        $customizer_data = array();
        
        // Get all theme mods
        $theme_mods = get_theme_mods();
        if (!empty($theme_mods)) {
            $customizer_data['theme_mods'] = $theme_mods;
        }
        
        // Get customizer options
        $customizer_options = array();
        $options = wp_load_alloptions();
        foreach ($options as $key => $value) {
            if (strpos($key, 'theme_') === 0 || strpos($key, get_stylesheet()) === 0) {
                $customizer_options[$key] = $value;
            }
        }
        
        if (!empty($customizer_options)) {
            $customizer_data['options'] = $customizer_options;
        }
        
        if (!empty($customizer_data)) {
            file_put_contents($this->temp_dir . '/customizer-settings.json', json_encode($customizer_data));
            $this->export_data['customizer_settings'] = true;
        }
    }
    
    /**
     * Export demo content
     */
    private function export_demo_content() {
        // Export posts
        $posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $demo_content = array();
        
        foreach ($posts as $post) {
            $post_data = array(
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
                'post_type' => $post->post_type,
                'post_status' => $post->post_status,
                'post_meta' => get_post_meta($post->ID)
            );
            
            // Export featured image
            if (has_post_thumbnail($post->ID)) {
                $post_data['featured_image'] = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
            }
            
            $demo_content[] = $post_data;
        }
        
        if (!empty($demo_content)) {
            file_put_contents($this->temp_dir . '/demo-content.json', json_encode($demo_content));
            $this->export_data['demo_content'] = count($demo_content);
        }
    }
    
    /**
     * Export plugin list
     */
    private function export_plugin_list() {
        $active_plugins = get_option('active_plugins', array());
        $all_plugins = get_plugins();
        
        $plugin_list = array();
        
        foreach ($active_plugins as $plugin_file) {
            if (isset($all_plugins[$plugin_file])) {
                $plugin_data = $all_plugins[$plugin_file];
                $plugin_list[] = array(
                    'name' => $plugin_data['Name'],
                    'version' => $plugin_data['Version'],
                    'plugin_file' => $plugin_file,
                    'required' => true
                );
            }
        }
        
        if (!empty($plugin_list)) {
            file_put_contents($this->temp_dir . '/required-plugins.json', json_encode($plugin_list));
            $this->export_data['required_plugins'] = count($plugin_list);
        }
    }
    
    /**
     * Export widgets
     */
    private function export_widgets() {
        $widgets_data = array();
        
        // Get sidebars and their widgets
        $sidebars_widgets = wp_get_sidebars_widgets();
        
        foreach ($sidebars_widgets as $sidebar_id => $widgets) {
            if (!empty($widgets) && is_array($widgets)) {
                $widgets_data[$sidebar_id] = $widgets;
            }
        }
        
        // Get widget settings
        $widget_settings = array();
        $widget_types = array();
        
        foreach ($widgets_data as $sidebar_id => $widgets) {
            foreach ($widgets as $widget_id) {
                $widget_type = preg_replace('/-[0-9]+$/', '', $widget_id);
                $widget_types[] = $widget_type;
            }
        }
        
        $widget_types = array_unique($widget_types);
        
        foreach ($widget_types as $widget_type) {
            $widget_settings[$widget_type] = get_option('widget_' . $widget_type, array());
        }
        
        $export_widgets = array(
            'sidebars' => $widgets_data,
            'settings' => $widget_settings
        );
        
        if (!empty($export_widgets)) {
            file_put_contents($this->temp_dir . '/widgets.json', json_encode($export_widgets));
            $this->export_data['widgets'] = true;
        }
    }
    
    /**
     * Create package manifest
     */
    private function create_package_manifest() {
        $manifest = array(
            'package_info' => $this->export_data,
            'installation_steps' => array(
                'install_theme' => isset($this->export_data['theme_files']),
                'install_plugins' => isset($this->export_data['required_plugins']),
                'import_customizer' => isset($this->export_data['customizer_settings']),
                'import_content' => isset($this->export_data['demo_content']),
                'import_widgets' => isset($this->export_data['widgets']),
                'import_templates' => isset($this->export_data['elementor_templates']) || isset($this->export_data['gutenberg_templates'])
            )
        );
        
        file_put_contents($this->temp_dir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create ZIP package
     */
    private function create_zip_package($package_name) {
        $zip_filename = sanitize_file_name($package_name) . '-' . date('Y-m-d-H-i-s') . '.zip';
        $zip_path = $this->upload_dir['basedir'] . '/theme-exporter-pro/' . $zip_filename;
        
        // Ensure directory exists
        wp_mkdir_p(dirname($zip_path));
        
        $zip = new ZipArchive();
        
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception(__('Cannot create ZIP file', 'theme-exporter-pro'));
        }
        
        $this->add_directory_to_zip($zip, $this->temp_dir, '');
        $zip->close();
        
        return $zip_path;
    }
    
    /**
     * Add directory to ZIP
     */
    private function add_directory_to_zip($zip, $dir, $zip_path) {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $file_path = $dir . '/' . $file;
            $zip_file_path = $zip_path . $file;
            
            if (is_dir($file_path)) {
                $zip->addEmptyDir($zip_file_path);
                $this->add_directory_to_zip($zip, $file_path, $zip_file_path . '/');
            } else {
                $zip->addFile($file_path, $zip_file_path);
            }
        }
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