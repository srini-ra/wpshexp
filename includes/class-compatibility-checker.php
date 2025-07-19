<?php
/**
 * Compatibility Checker Class
 * 
 * Handles pre-import compatibility checks
 */

if (!defined('ABSPATH')) {
    exit;
}

class TKP_Compatibility_Checker {
    
    private $requirements = array();
    private $warnings = array();
    private $errors = array();
    
    public function __construct() {
        $this->init_requirements();
    }
    
    /**
     * Initialize system requirements
     */
    private function init_requirements() {
        $this->requirements = array(
            'php_version' => array(
                'min' => '7.4',
                'recommended' => '8.0',
                'current' => PHP_VERSION
            ),
            'wordpress_version' => array(
                'min' => '5.0',
                'recommended' => '6.0',
                'current' => get_bloginfo('version')
            ),
            'memory_limit' => array(
                'min' => '256M',
                'recommended' => '512M',
                'current' => ini_get('memory_limit')
            ),
            'max_execution_time' => array(
                'min' => 120,
                'recommended' => 300,
                'current' => ini_get('max_execution_time')
            ),
            'upload_max_filesize' => array(
                'min' => '64M',
                'recommended' => '128M',
                'current' => ini_get('upload_max_filesize')
            ),
            'extensions' => array(
                'required' => array('zip', 'curl', 'json', 'gd'),
                'recommended' => array('imagick', 'mbstring', 'xml')
            )
        );
    }
    
    /**
     * Run comprehensive compatibility check
     */
    public function run_check($check_type = 'general', $context = array()) {
        $this->warnings = array();
        $this->errors = array();
        
        switch ($check_type) {
            case 'import':
                return $this->check_import_compatibility($context['files'] ?? array(), $context['options'] ?? array());
            case 'export':
                return $this->check_export_compatibility($context);
            case 'elementor':
                return $this->check_elementor_compatibility();
            case 'gutenberg':
                return $this->check_gutenberg_compatibility();
            case 'woocommerce':
                return $this->check_woocommerce_compatibility();
            default:
                return $this->check_general_compatibility();
        }
    }
    
    /**
     * Check general system compatibility
     */
    public function check_general_compatibility() {
        $results = array(
            'compatible' => true,
            'score' => 0,
            'checks' => array(),
            'warnings' => array(),
            'errors' => array()
        );
        
        // Check PHP version
        $php_check = $this->check_php_version();
        $results['checks']['php'] = $php_check;
        if (!$php_check['passed']) {
            $results['compatible'] = false;
            $results['errors'][] = $php_check['message'];
        } elseif ($php_check['warning']) {
            $results['warnings'][] = $php_check['message'];
        }
        
        // Check WordPress version
        $wp_check = $this->check_wordpress_version();
        $results['checks']['wordpress'] = $wp_check;
        if (!$wp_check['passed']) {
            $results['compatible'] = false;
            $results['errors'][] = $wp_check['message'];
        } elseif ($wp_check['warning']) {
            $results['warnings'][] = $wp_check['message'];
        }
        
        // Check memory limit
        $memory_check = $this->check_memory_limit();
        $results['checks']['memory'] = $memory_check;
        if (!$memory_check['passed']) {
            $results['warnings'][] = $memory_check['message'];
        }
        
        // Check execution time
        $time_check = $this->check_execution_time();
        $results['checks']['execution_time'] = $time_check;
        if (!$time_check['passed']) {
            $results['warnings'][] = $time_check['message'];
        }
        
        // Check file upload limits
        $upload_check = $this->check_upload_limits();
        $results['checks']['upload'] = $upload_check;
        if (!$upload_check['passed']) {
            $results['warnings'][] = $upload_check['message'];
        }
        
        // Check required extensions
        $ext_check = $this->check_extensions();
        $results['checks']['extensions'] = $ext_check;
        if (!$ext_check['passed']) {
            $results['compatible'] = false;
            $results['errors'] = array_merge($results['errors'], $ext_check['missing']);
        }
        
        // Check disk space
        $disk_check = $this->check_disk_space();
        $results['checks']['disk_space'] = $disk_check;
        if (!$disk_check['passed']) {
            $results['warnings'][] = $disk_check['message'];
        }
        
        // Check file permissions
        $perm_check = $this->check_file_permissions();
        $results['checks']['permissions'] = $perm_check;
        if (!$perm_check['passed']) {
            $results['compatible'] = false;
            $results['errors'][] = $perm_check['message'];
        }
        
        // Calculate compatibility score
        $total_checks = count($results['checks']);
        $passed_checks = count(array_filter($results['checks'], function($check) {
            return $check['passed'];
        }));
        
        $results['score'] = $total_checks > 0 ? round(($passed_checks / $total_checks) * 100) : 0;
        
        return $results;
    }
    
    /**
     * Check import compatibility
     */
    public function check_import_compatibility($files, $options) {
        $results = $this->check_general_compatibility();
        
        // Additional import-specific checks
        if (isset($files['package_file'])) {
            $file_check = $this->check_import_file($files['package_file']);
            $results['checks']['import_file'] = $file_check;
            
            if (!$file_check['passed']) {
                $results['compatible'] = false;
                $results['errors'][] = $file_check['message'];
            }
        }
        
        // Check if importing Elementor content
        if (isset($options['import_templates']) && $options['import_templates']) {
            $elementor_check = $this->check_elementor_compatibility();
            $results['checks']['elementor'] = $elementor_check;
            
            if (!$elementor_check['passed']) {
                $results['warnings'][] = $elementor_check['message'];
            }
        }
        
        // Check if importing WooCommerce data
        if (isset($options['import_woocommerce']) && $options['import_woocommerce']) {
            $wc_check = $this->check_woocommerce_compatibility();
            $results['checks']['woocommerce'] = $wc_check;
            
            if (!$wc_check['passed']) {
                $results['warnings'][] = $wc_check['message'];
            }
        }
        
        return $results;
    }
    
    /**
     * Check export compatibility
     */
    public function check_export_compatibility($context) {
        $results = $this->check_general_compatibility();
        
        // Check available content
        $content_check = $this->check_exportable_content();
        $results['checks']['content'] = $content_check;
        
        if (!$content_check['passed']) {
            $results['warnings'][] = $content_check['message'];
        }
        
        return $results;
    }
    
    /**
     * Check PHP version
     */
    private function check_php_version() {
        $current = $this->requirements['php_version']['current'];
        $min = $this->requirements['php_version']['min'];
        $recommended = $this->requirements['php_version']['recommended'];
        
        $passed = version_compare($current, $min, '>=');
        $warning = version_compare($current, $recommended, '<');
        
        return array(
            'passed' => $passed,
            'warning' => $warning && $passed,
            'message' => $passed 
                ? ($warning ? sprintf(__('PHP %s is supported but %s+ is recommended for better performance', 'theme-kit-pro'), $current, $recommended) : sprintf(__('PHP %s is compatible', 'theme-kit-pro'), $current))
                : sprintf(__('PHP %s+ is required. You are running %s', 'theme-kit-pro'), $min, $current),
            'current' => $current,
            'required' => $min,
            'recommended' => $recommended
        );
    }
    
    /**
     * Check WordPress version
     */
    private function check_wordpress_version() {
        $current = $this->requirements['wordpress_version']['current'];
        $min = $this->requirements['wordpress_version']['min'];
        $recommended = $this->requirements['wordpress_version']['recommended'];
        
        $passed = version_compare($current, $min, '>=');
        $warning = version_compare($current, $recommended, '<');
        
        return array(
            'passed' => $passed,
            'warning' => $warning && $passed,
            'message' => $passed 
                ? ($warning ? sprintf(__('WordPress %s is supported but %s+ is recommended', 'theme-kit-pro'), $current, $recommended) : sprintf(__('WordPress %s is compatible', 'theme-kit-pro'), $current))
                : sprintf(__('WordPress %s+ is required. You are running %s', 'theme-kit-pro'), $min, $current),
            'current' => $current,
            'required' => $min,
            'recommended' => $recommended
        );
    }
    
    /**
     * Check memory limit
     */
    private function check_memory_limit() {
        $current = ini_get('memory_limit');
        $min_bytes = $this->convert_to_bytes($this->requirements['memory_limit']['min']);
        $current_bytes = $this->convert_to_bytes($current);
        
        $passed = $current_bytes >= $min_bytes;
        
        return array(
            'passed' => $passed,
            'message' => $passed 
                ? sprintf(__('Memory limit %s is sufficient', 'theme-kit-pro'), $current)
                : sprintf(__('Memory limit %s is too low. Minimum %s required', 'theme-kit-pro'), $current, $this->requirements['memory_limit']['min']),
            'current' => $current,
            'required' => $this->requirements['memory_limit']['min']
        );
    }
    
    /**
     * Check execution time
     */
    private function check_execution_time() {
        $current = (int) ini_get('max_execution_time');
        $min = $this->requirements['max_execution_time']['min'];
        
        // 0 means unlimited
        $passed = $current === 0 || $current >= $min;
        
        return array(
            'passed' => $passed,
            'message' => $passed 
                ? sprintf(__('Execution time limit is sufficient (%s)', 'theme-kit-pro'), $current === 0 ? 'unlimited' : $current . 's')
                : sprintf(__('Execution time limit %ss is too low. Minimum %ss required', 'theme-kit-pro'), $current, $min),
            'current' => $current,
            'required' => $min
        );
    }
    
    /**
     * Check upload limits
     */
    private function check_upload_limits() {
        $current = ini_get('upload_max_filesize');
        $min_bytes = $this->convert_to_bytes($this->requirements['upload_max_filesize']['min']);
        $current_bytes = $this->convert_to_bytes($current);
        
        $passed = $current_bytes >= $min_bytes;
        
        return array(
            'passed' => $passed,
            'message' => $passed 
                ? sprintf(__('Upload limit %s is sufficient', 'theme-kit-pro'), $current)
                : sprintf(__('Upload limit %s is too low. Minimum %s required', 'theme-kit-pro'), $current, $this->requirements['upload_max_filesize']['min']),
            'current' => $current,
            'required' => $this->requirements['upload_max_filesize']['min']
        );
    }
    
    /**
     * Check required extensions
     */
    private function check_extensions() {
        $required = $this->requirements['extensions']['required'];
        $recommended = $this->requirements['extensions']['recommended'];
        
        $missing_required = array();
        $missing_recommended = array();
        
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing_required[] = $ext;
            }
        }
        
        foreach ($recommended as $ext) {
            if (!extension_loaded($ext)) {
                $missing_recommended[] = $ext;
            }
        }
        
        $passed = empty($missing_required);
        
        $message = '';
        if (!$passed) {
            $message = sprintf(__('Missing required extensions: %s', 'theme-kit-pro'), implode(', ', $missing_required));
        } elseif (!empty($missing_recommended)) {
            $message = sprintf(__('Missing recommended extensions: %s', 'theme-kit-pro'), implode(', ', $missing_recommended));
        } else {
            $message = __('All required extensions are available', 'theme-kit-pro');
        }
        
        return array(
            'passed' => $passed,
            'message' => $message,
            'missing' => $missing_required,
            'missing_recommended' => $missing_recommended
        );
    }
    
    /**
     * Check disk space
     */
    private function check_disk_space($required_mb = 100) {
        $upload_dir = wp_upload_dir();
        $free_bytes = disk_free_space($upload_dir['basedir']);
        
        if ($free_bytes === false) {
            return array(
                'passed' => true,
                'message' => __('Cannot determine disk space', 'theme-kit-pro')
            );
        }
        
        $required_bytes = $required_mb * 1024 * 1024;
        $passed = $free_bytes >= $required_bytes;
        
        return array(
            'passed' => $passed,
            'message' => $passed 
                ? sprintf(__('Sufficient disk space available (%s)', 'theme-kit-pro'), size_format($free_bytes))
                : sprintf(__('Insufficient disk space. %s available, %s required', 'theme-kit-pro'), size_format($free_bytes), size_format($required_bytes)),
            'available' => $free_bytes,
            'required' => $required_bytes
        );
    }
    
    /**
     * Check file permissions
     */
    private function check_file_permissions() {
        $upload_dir = wp_upload_dir();
        $test_dir = $upload_dir['basedir'] . '/theme-kit-pro-test';
        
        // Try to create test directory
        if (!wp_mkdir_p($test_dir)) {
            return array(
                'passed' => false,
                'message' => __('Cannot create directories in upload folder', 'theme-kit-pro')
            );
        }
        
        // Try to create test file
        $test_file = $test_dir . '/test.txt';
        if (!file_put_contents($test_file, 'test')) {
            rmdir($test_dir);
            return array(
                'passed' => false,
                'message' => __('Cannot create files in upload folder', 'theme-kit-pro')
            );
        }
        
        // Cleanup
        unlink($test_file);
        rmdir($test_dir);
        
        return array(
            'passed' => true,
            'message' => __('File permissions are correct', 'theme-kit-pro')
        );
    }
    
    /**
     * Check import file
     */
    private function check_import_file($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array(
                'passed' => false,
                'message' => sprintf(__('File upload error: %s', 'theme-kit-pro'), $this->get_upload_error_message($file['error']))
            );
        }
        
        // Check file type
        $file_type = wp_check_filetype($file['name']);
        if ($file_type['ext'] !== 'zip') {
            return array(
                'passed' => false,
                'message' => __('Only ZIP files are supported', 'theme-kit-pro')
            );
        }
        
        // Check file size
        $max_size = $this->convert_to_bytes(ini_get('upload_max_filesize'));
        if ($file['size'] > $max_size) {
            return array(
                'passed' => false,
                'message' => sprintf(__('File too large. Maximum size: %s', 'theme-kit-pro'), size_format($max_size))
            );
        }
        
        // Try to open ZIP
        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== TRUE) {
            return array(
                'passed' => false,
                'message' => __('Invalid or corrupted ZIP file', 'theme-kit-pro')
            );
        }
        
        // Check for manifest file
        if ($zip->locateName('manifest.json') === false) {
            $zip->close();
            return array(
                'passed' => false,
                'message' => __('Invalid kit file. Missing manifest.json', 'theme-kit-pro')
            );
        }
        
        $zip->close();
        
        return array(
            'passed' => true,
            'message' => __('Kit file is valid', 'theme-kit-pro')
        );
    }
    
    /**
     * Check Elementor compatibility
     */
    public function check_elementor_compatibility() {
        if (!class_exists('Elementor\Plugin')) {
            return array(
                'passed' => false,
                'message' => __('Elementor plugin is not installed', 'theme-kit-pro'),
                'action' => 'install_elementor'
            );
        }
        
        $elementor_version = ELEMENTOR_VERSION;
        $min_version = '3.0.0';
        
        if (version_compare($elementor_version, $min_version, '<')) {
            return array(
                'passed' => false,
                'message' => sprintf(__('Elementor %s+ is required. You have %s', 'theme-kit-pro'), $min_version, $elementor_version),
                'action' => 'update_elementor'
            );
        }
        
        return array(
            'passed' => true,
            'message' => sprintf(__('Elementor %s is compatible', 'theme-kit-pro'), $elementor_version)
        );
    }
    
    /**
     * Check Gutenberg compatibility
     */
    public function check_gutenberg_compatibility() {
        global $wp_version;
        
        // Gutenberg is built into WordPress 5.0+
        if (version_compare($wp_version, '5.0', '>=')) {
            return array(
                'passed' => true,
                'message' => __('Gutenberg (Block Editor) is available', 'theme-kit-pro')
            );
        }
        
        // Check if Gutenberg plugin is installed
        if (class_exists('WP_Block_Type_Registry')) {
            return array(
                'passed' => true,
                'message' => __('Gutenberg plugin is installed', 'theme-kit-pro')
            );
        }
        
        return array(
            'passed' => false,
            'message' => __('Gutenberg support requires WordPress 5.0+ or Gutenberg plugin', 'theme-kit-pro'),
            'action' => 'update_wordpress'
        );
    }
    
    /**
     * Check WooCommerce compatibility
     */
    public function check_woocommerce_compatibility() {
        if (!class_exists('WooCommerce')) {
            return array(
                'passed' => false,
                'message' => __('WooCommerce plugin is not installed', 'theme-kit-pro'),
                'action' => 'install_woocommerce'
            );
        }
        
        $wc_version = WC()->version;
        $min_version = '5.0.0';
        
        if (version_compare($wc_version, $min_version, '<')) {
            return array(
                'passed' => false,
                'message' => sprintf(__('WooCommerce %s+ is required. You have %s', 'theme-kit-pro'), $min_version, $wc_version),
                'action' => 'update_woocommerce'
            );
        }
        
        return array(
            'passed' => true,
            'message' => sprintf(__('WooCommerce %s is compatible', 'theme-kit-pro'), $wc_version)
        );
    }
    
    /**
     * Check exportable content
     */
    private function check_exportable_content() {
        $post_count = wp_count_posts('post')->publish;
        $page_count = wp_count_posts('page')->publish;
        $media_count = wp_count_posts('attachment')->inherit;
        
        $total_content = $post_count + $page_count + $media_count;
        
        if ($total_content === 0) {
            return array(
                'passed' => false,
                'message' => __('No content available to export', 'theme-kit-pro')
            );
        }
        
        return array(
            'passed' => true,
            'message' => sprintf(__('%d items available for export', 'theme-kit-pro'), $total_content),
            'content_stats' => array(
                'posts' => $post_count,
                'pages' => $page_count,
                'media' => $media_count
            )
        );
    }
    
    /**
     * Get upload error message
     */
    private function get_upload_error_message($error_code) {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE => __('File exceeds upload_max_filesize directive', 'theme-kit-pro'),
            UPLOAD_ERR_FORM_SIZE => __('File exceeds MAX_FILE_SIZE directive', 'theme-kit-pro'),
            UPLOAD_ERR_PARTIAL => __('File was only partially uploaded', 'theme-kit-pro'),
            UPLOAD_ERR_NO_FILE => __('No file was uploaded', 'theme-kit-pro'),
            UPLOAD_ERR_NO_TMP_DIR => __('Missing temporary folder', 'theme-kit-pro'),
            UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk', 'theme-kit-pro'),
            UPLOAD_ERR_EXTENSION => __('File upload stopped by extension', 'theme-kit-pro')
        );
        
        return $upload_errors[$error_code] ?? __('Unknown upload error', 'theme-kit-pro');
    }
    
    /**
     * Convert size string to bytes
     */
    private function convert_to_bytes($size_str) {
        $size_str = trim($size_str);
        $last = strtolower($size_str[strlen($size_str) - 1]);
        $size = (int) $size_str;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }
    
    /**
     * Get warnings
     */
    public function get_warnings() {
        return $this->warnings;
    }
    
    /**
     * Get errors
     */
    public function get_errors() {
        return $this->errors;
    }
}