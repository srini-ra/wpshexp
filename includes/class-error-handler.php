<?php
/**
 * Error Handler Class
 * 
 * Handles errors with detailed logging and user-friendly messages
 */

if (!defined('ABSPATH')) {
    exit;
}

class TKP_Error_Handler {
    
    private $logger;
    
    public function __construct($logger) {
        $this->logger = $logger;
        
        // Set custom error handler
        set_error_handler(array($this, 'handle_php_error'));
        set_exception_handler(array($this, 'handle_uncaught_exception'));
    }
    
    /**
     * Handle PHP errors
     */
    public function handle_php_error($severity, $message, $file, $line) {
        // Don't handle suppressed errors
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error_types = array(
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        );
        
        $error_type = $error_types[$severity] ?? 'Unknown Error';
        
        $this->logger->log('error', "PHP {$error_type}: {$message}", array(
            'file' => $file,
            'line' => $line,
            'severity' => $severity
        ));
        
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public function handle_uncaught_exception($exception) {
        $this->logger->log('critical', 'Uncaught Exception: ' . $exception->getMessage(), array(
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ));
    }
    
    /**
     * Handle AJAX errors
     */
    public function handle_ajax_error($exception, $action) {
        $error_message = $exception->getMessage();
        $error_code = $exception->getCode();
        
        // Log the error
        $this->logger->log('error', "AJAX Error in {$action}: {$error_message}", array(
            'action' => $action,
            'code' => $error_code,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => get_current_user_id(),
            'request_data' => $_POST
        ));
        
        // Return user-friendly error
        wp_send_json(array(
            'success' => false,
            'message' => $this->get_user_friendly_message($error_message, $action),
            'error_code' => $error_code,
            'debug_info' => WP_DEBUG ? array(
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $error_message
            ) : null
        ));
    }
    
    /**
     * Get user-friendly error message
     */
    private function get_user_friendly_message($error_message, $action) {
        $friendly_messages = array(
            'export_theme' => array(
                'memory' => __('Export failed due to insufficient memory. Try exporting smaller content or contact your host to increase memory limit.', 'theme-kit-pro'),
                'timeout' => __('Export timed out. Try exporting in smaller batches or increase server timeout limits.', 'theme-kit-pro'),
                'disk_space' => __('Export failed due to insufficient disk space. Please free up space and try again.', 'theme-kit-pro'),
                'permission' => __('Export failed due to file permission issues. Please check directory permissions.', 'theme-kit-pro'),
                'zip' => __('Failed to create ZIP file. Please ensure ZIP extension is installed and working.', 'theme-kit-pro'),
                'default' => __('Export failed. Please check the logs for more details.', 'theme-kit-pro')
            ),
            'import_theme' => array(
                'invalid_file' => __('Invalid kit file. Please ensure you\'re uploading a valid Theme Kit Pro package.', 'theme-kit-pro'),
                'corrupted' => __('Kit file appears to be corrupted. Please try downloading and uploading again.', 'theme-kit-pro'),
                'compatibility' => __('Kit is not compatible with your current setup. Please check compatibility requirements.', 'theme-kit-pro'),
                'permission' => __('Import failed due to file permission issues. Please check directory permissions.', 'theme-kit-pro'),
                'plugin_missing' => __('Required plugins are missing. Please install required plugins first.', 'theme-kit-pro'),
                'default' => __('Import failed. Please check the logs for more details.', 'theme-kit-pro')
            )
        );
        
        // Check for specific error patterns
        $error_lower = strtolower($error_message);
        
        if (strpos($error_lower, 'memory') !== false || strpos($error_lower, 'allowed memory size') !== false) {
            return $friendly_messages[$action]['memory'] ?? __('Memory limit exceeded. Please increase memory limit or reduce content size.', 'theme-kit-pro');
        }
        
        if (strpos($error_lower, 'timeout') !== false || strpos($error_lower, 'maximum execution time') !== false) {
            return $friendly_messages[$action]['timeout'] ?? __('Operation timed out. Please try again or increase timeout limits.', 'theme-kit-pro');
        }
        
        if (strpos($error_lower, 'disk') !== false || strpos($error_lower, 'space') !== false) {
            return $friendly_messages[$action]['disk_space'] ?? __('Insufficient disk space. Please free up space and try again.', 'theme-kit-pro');
        }
        
        if (strpos($error_lower, 'permission') !== false || strpos($error_lower, 'denied') !== false) {
            return $friendly_messages[$action]['permission'] ?? __('Permission denied. Please check file and directory permissions.', 'theme-kit-pro');
        }
        
        if (strpos($error_lower, 'zip') !== false) {
            return $friendly_messages[$action]['zip'] ?? __('ZIP operation failed. Please ensure ZIP extension is available.', 'theme-kit-pro');
        }
        
        if (strpos($error_lower, 'invalid') !== false || strpos($error_lower, 'corrupt') !== false) {
            return $friendly_messages[$action]['invalid_file'] ?? __('Invalid or corrupted file. Please check the file and try again.', 'theme-kit-pro');
        }
        
        // Return default message
        return $friendly_messages[$action]['default'] ?? __('An error occurred. Please check the logs for more details.', 'theme-kit-pro');
    }
    
    /**
     * Handle image import errors
     */
    public function handle_image_error($image_url, $error_message, $context = array()) {
        $this->logger->log('warning', "Image import failed: {$image_url}", array_merge($context, array(
            'error' => $error_message,
            'url' => $image_url
        )));
        
        return array(
            'success' => false,
            'message' => sprintf(__('Failed to import image: %s. Reason: %s', 'theme-kit-pro'), basename($image_url), $error_message),
            'url' => $image_url,
            'fallback_available' => $this->has_fallback_image($image_url)
        );
    }
    
    /**
     * Check if fallback image is available
     */
    private function has_fallback_image($image_url) {
        // Check if we have a placeholder or similar image
        $upload_dir = wp_upload_dir();
        $placeholder_path = $upload_dir['basedir'] . '/theme-kit-pro/placeholder.jpg';
        
        return file_exists($placeholder_path);
    }
    
    /**
     * Get error statistics
     */
    public function get_error_stats($days = 7) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tkp_logs';
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_results($wpdb->prepare("
            SELECT 
                level,
                COUNT(*) as count,
                DATE(timestamp) as date
            FROM {$table_name} 
            WHERE timestamp >= %s 
            GROUP BY level, DATE(timestamp)
            ORDER BY timestamp DESC
        ", $date_from));
        
        return $stats;
    }
    
    /**
     * Clear old error logs
     */
    public function cleanup_old_logs($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tkp_logs';
        $date_threshold = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $deleted = $wpdb->query($wpdb->prepare("
            DELETE FROM {$table_name} 
            WHERE timestamp < %s
        ", $date_threshold));
        
        $this->logger->log('info', "Cleaned up {$deleted} old log entries");
        
        return $deleted;
    }
}