<?php
/**
 * Logger Class
 * 
 * Handles detailed logging with different levels
 */

if (!defined('ABSPATH')) {
    exit;
}

class TKP_Logger {
    
    private $log_levels = array(
        'debug' => 0,
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5,
        'alert' => 6,
        'emergency' => 7
    );
    
    private $current_level;
    
    public function __construct() {
        $this->current_level = get_option('tkp_log_level', 'info');
    }
    
    /**
     * Log a message
     */
    public function log($level, $message, $context = array()) {
        // Check if logging is enabled
        if (!get_option('tkp_enable_logging', true)) {
            return false;
        }
        
        // Check log level
        if (!$this->should_log($level)) {
            return false;
        }
        
        // Prepare log entry
        $log_entry = array(
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'timestamp' => current_time('mysql')
        );
        
        // Save to database
        $this->save_to_database($log_entry);
        
        // Save to file if critical
        if (in_array($level, array('critical', 'alert', 'emergency'))) {
            $this->save_to_file($log_entry);
        }
        
        return true;
    }
    
    /**
     * Check if message should be logged based on level
     */
    private function should_log($level) {
        $message_level = $this->log_levels[$level] ?? 0;
        $current_level = $this->log_levels[$this->current_level] ?? 1;
        
        return $message_level >= $current_level;
    }
    
    /**
     * Save log entry to database
     */
    private function save_to_database($log_entry) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tkp_logs';
        
        $wpdb->insert(
            $table_name,
            $log_entry,
            array('%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Save critical logs to file
     */
    private function save_to_file($log_entry) {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/theme-kit-pro/logs';
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        $log_file = $log_dir . '/critical-' . date('Y-m-d') . '.log';
        
        $log_line = sprintf(
            "[%s] %s: %s %s\n",
            $log_entry['timestamp'],
            strtoupper($log_entry['level']),
            $log_entry['message'],
            $log_entry['context'] ? '| Context: ' . $log_entry['context'] : ''
        );
        
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get recent logs
     */
    public function get_recent_logs($limit = 50, $level = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tkp_logs';
        
        $where_clause = '';
        $params = array();
        
        if ($level) {
            $where_clause = 'WHERE level = %s';
            $params[] = $level;
        }
        
        $params[] = $limit;
        
        $logs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$table_name} 
            {$where_clause}
            ORDER BY timestamp DESC 
            LIMIT %d
        ", $params));
        
        // Decode context for each log
        foreach ($logs as &$log) {
            $log->context = json_decode($log->context, true);
        }
        
        return $logs;
    }
    
    /**
     * Get recent errors for admin notices
     */
    public function get_recent_errors($limit = 5) {
        return $this->get_recent_logs($limit, 'error');
    }
    
    /**
     * Get log statistics
     */
    public function get_log_stats($days = 7) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tkp_logs';
        $date_from = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = $wpdb->get_results($wpdb->prepare("
            SELECT 
                level,
                COUNT(*) as count
            FROM {$table_name} 
            WHERE timestamp >= %s 
            GROUP BY level
            ORDER BY count DESC
        ", $date_from));
        
        return $stats;
    }
    
    /**
     * Export logs
     */
    public function export_logs($date_from = null, $date_to = null, $level = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tkp_logs';
        
        $where_conditions = array();
        $params = array();
        
        if ($date_from) {
            $where_conditions[] = 'timestamp >= %s';
            $params[] = $date_from;
        }
        
        if ($date_to) {
            $where_conditions[] = 'timestamp <= %s';
            $params[] = $date_to;
        }
        
        if ($level) {
            $where_conditions[] = 'level = %s';
            $params[] = $level;
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $logs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$table_name} 
            {$where_clause}
            ORDER BY timestamp DESC
        ", $params));
        
        return $logs;
    }
    
    /**
     * Clear logs
     */
    public function clear_logs($level = null, $older_than_days = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tkp_logs';
        
        $where_conditions = array();
        $params = array();
        
        if ($level) {
            $where_conditions[] = 'level = %s';
            $params[] = $level;
        }
        
        if ($older_than_days) {
            $where_conditions[] = 'timestamp < %s';
            $params[] = date('Y-m-d H:i:s', strtotime("-{$older_than_days} days"));
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $deleted = $wpdb->query($wpdb->prepare("
            DELETE FROM {$table_name} 
            {$where_clause}
        ", $params));
        
        return $deleted;
    }
    
    /**
     * Debug helper methods
     */
    public function debug($message, $context = array()) {
        return $this->log('debug', $message, $context);
    }
    
    public function info($message, $context = array()) {
        return $this->log('info', $message, $context);
    }
    
    public function notice($message, $context = array()) {
        return $this->log('notice', $message, $context);
    }
    
    public function warning($message, $context = array()) {
        return $this->log('warning', $message, $context);
    }
    
    public function error($message, $context = array()) {
        return $this->log('error', $message, $context);
    }
    
    public function critical($message, $context = array()) {
        return $this->log('critical', $message, $context);
    }
}