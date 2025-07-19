<?php
/**
 * Security Scanner Class
 * 
 * Handles security validation and scanning
 */

if (!defined('ABSPATH')) {
    exit;
}

class TKP_Security_Scanner {
    
    private $logger;
    private $dangerous_functions = array(
        'eval', 'exec', 'system', 'shell_exec', 'passthru', 
        'file_get_contents', 'file_put_contents', 'fopen', 'fwrite',
        'curl_exec', 'curl_init', 'base64_decode', 'base64_encode'
    );
    
    private $suspicious_patterns = array(
        '/\$_GET\s*\[\s*[\'"][^\'"]*[\'"]\s*\]/',
        '/\$_POST\s*\[\s*[\'"][^\'"]*[\'"]\s*\]/',
        '/\$_REQUEST\s*\[\s*[\'"][^\'"]*[\'"]\s*\]/',
        '/\$_COOKIE\s*\[\s*[\'"][^\'"]*[\'"]\s*\]/',
        '/\$_SERVER\s*\[\s*[\'"][^\'"]*[\'"]\s*\]/',
        '/eval\s*\(/',
        '/base64_decode\s*\(/',
        '/gzinflate\s*\(/',
        '/str_rot13\s*\(/',
        '/create_function\s*\(/',
        '/<\?php\s+@/',
        '/\$\$[a-zA-Z_]/',
        '/\${[^}]+}/',
        '/\\\x[0-9a-fA-F]{2}/',
        '/chr\s*\(\s*\d+\s*\)/',
        '/\\\[0-7]{3}/'
    );
    
    public function __construct($logger) {
        $this->logger = $logger;
    }
    
    /**
     * Scan uploaded file for security threats
     */
    public function scan_uploaded_file($file_path) {
        $results = array(
            'safe' => true,
            'threats' => array(),
            'warnings' => array(),
            'scanned_files' => 0
        );
        
        if (!file_exists($file_path)) {
            $results['safe'] = false;
            $results['threats'][] = 'File does not exist';
            return $results;
        }
        
        // Check file type
        $file_type = wp_check_filetype($file_path);
        if ($file_type['ext'] !== 'zip') {
            $results['safe'] = false;
            $results['threats'][] = 'Invalid file type. Only ZIP files are allowed.';
            return $results;
        }
        
        // Scan ZIP contents
        $zip = new ZipArchive();
        if ($zip->open($file_path) !== TRUE) {
            $results['safe'] = false;
            $results['threats'][] = 'Cannot open ZIP file for scanning';
            return $results;
        }
        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $file_info = $zip->statIndex($i);
            $filename = $file_info['name'];
            
            // Check for dangerous file types
            if ($this->is_dangerous_file($filename)) {
                $results['safe'] = false;
                $results['threats'][] = "Dangerous file type detected: {$filename}";
                continue;
            }
            
            // Check for suspicious filenames
            if ($this->is_suspicious_filename($filename)) {
                $results['warnings'][] = "Suspicious filename: {$filename}";
            }
            
            // Scan file contents for PHP files
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'php') {
                $content = $zip->getFromIndex($i);
                $scan_result = $this->scan_php_content($content, $filename);
                
                if (!$scan_result['safe']) {
                    $results['safe'] = false;
                    $results['threats'] = array_merge($results['threats'], $scan_result['threats']);
                }
                
                $results['warnings'] = array_merge($results['warnings'], $scan_result['warnings']);
            }
            
            $results['scanned_files']++;
        }
        
        $zip->close();
        
        // Log scan results
        $this->logger->info('Security scan completed', array(
            'file' => basename($file_path),
            'safe' => $results['safe'],
            'threats_count' => count($results['threats']),
            'warnings_count' => count($results['warnings']),
            'scanned_files' => $results['scanned_files']
        ));
        
        return $results;
    }
    
    /**
     * Check if file type is dangerous
     */
    private function is_dangerous_file($filename) {
        $dangerous_extensions = array(
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
            'sh', 'py', 'pl', 'rb', 'asp', 'aspx', 'jsp', 'cfm'
        );
        
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $dangerous_extensions);
    }
    
    /**
     * Check if filename is suspicious
     */
    private function is_suspicious_filename($filename) {
        $suspicious_patterns = array(
            '/\.\./i',  // Directory traversal
            '/^\./',    // Hidden files
            '/__/',     // Double underscores
            '/\$/',     // Dollar signs
            '/\%/',     // Percent encoding
            '/\\\/',    // Backslashes
        );
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Scan PHP content for malicious code
     */
    private function scan_php_content($content, $filename) {
        $results = array(
            'safe' => true,
            'threats' => array(),
            'warnings' => array()
        );
        
        // Check for dangerous functions
        foreach ($this->dangerous_functions as $function) {
            if (strpos($content, $function) !== false) {
                // More detailed check to avoid false positives
                if (preg_match('/\b' . preg_quote($function, '/') . '\s*\(/', $content)) {
                    $results['warnings'][] = "Potentially dangerous function '{$function}' found in {$filename}";
                }
            }
        }
        
        // Check for suspicious patterns
        foreach ($this->suspicious_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $results['warnings'][] = "Suspicious code pattern found in {$filename}";
            }
        }
        
        // Check for obfuscated code
        if ($this->is_obfuscated($content)) {
            $results['safe'] = false;
            $results['threats'][] = "Obfuscated code detected in {$filename}";
        }
        
        // Check for backdoor patterns
        if ($this->has_backdoor_patterns($content)) {
            $results['safe'] = false;
            $results['threats'][] = "Potential backdoor detected in {$filename}";
        }
        
        return $results;
    }
    
    /**
     * Check if code appears to be obfuscated
     */
    private function is_obfuscated($content) {
        // Check for high ratio of encoded content
        $encoded_patterns = array(
            '/base64_decode/',
            '/gzinflate/',
            '/str_rot13/',
            '/eval\s*\(\s*[\'"][a-zA-Z0-9+\/=]+[\'"]\s*\)/',
            '/\\\x[0-9a-fA-F]{2}/',
        );
        
        $matches = 0;
        foreach ($encoded_patterns as $pattern) {
            $matches += preg_match_all($pattern, $content);
        }
        
        // If more than 3 encoding patterns, likely obfuscated
        return $matches > 3;
    }
    
    /**
     * Check for backdoor patterns
     */
    private function has_backdoor_patterns($content) {
        $backdoor_patterns = array(
            '/\$_GET\s*\[\s*[\'"][a-zA-Z0-9_]+[\'"]\s*\]\s*\(\s*\$_GET/',
            '/\$_POST\s*\[\s*[\'"][a-zA-Z0-9_]+[\'"]\s*\]\s*\(\s*\$_POST/',
            '/eval\s*\(\s*\$_/',
            '/system\s*\(\s*\$_/',
            '/exec\s*\(\s*\$_/',
            '/shell_exec\s*\(\s*\$_/',
            '/passthru\s*\(\s*\$_/',
            '/file_get_contents\s*\(\s*[\'"]https?:\/\//',
            '/curl_exec\s*\(\s*\$/',
            '/\$\$[a-zA-Z_]+\s*\(/',
        );
        
        foreach ($backdoor_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate input data
     */
    public function validate_input($data, $type = 'general') {
        switch ($type) {
            case 'filename':
                return $this->validate_filename($data);
            case 'path':
                return $this->validate_path($data);
            case 'url':
                return $this->validate_url($data);
            case 'email':
                return $this->validate_email($data);
            default:
                return $this->validate_general($data);
        }
    }
    
    /**
     * Validate filename
     */
    private function validate_filename($filename) {
        // Remove any path components
        $filename = basename($filename);
        
        // Check for dangerous characters
        if (preg_match('/[<>:"|?*\x00-\x1f]/', $filename)) {
            return false;
        }
        
        // Check for reserved names (Windows)
        $reserved = array('CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 'LPT1', 'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9');
        if (in_array(strtoupper(pathinfo($filename, PATHINFO_FILENAME)), $reserved)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate file path
     */
    private function validate_path($path) {
        // Check for directory traversal
        if (strpos($path, '..') !== false) {
            return false;
        }
        
        // Check for null bytes
        if (strpos($path, "\0") !== false) {
            return false;
        }
        
        // Ensure path is within WordPress directory
        $wp_path = ABSPATH;
        $real_path = realpath($path);
        
        if ($real_path && strpos($real_path, realpath($wp_path)) !== 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate URL
     */
    private function validate_url($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check for dangerous protocols
        $dangerous_protocols = array('javascript:', 'data:', 'vbscript:', 'file:');
        foreach ($dangerous_protocols as $protocol) {
            if (stripos($url, $protocol) === 0) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate email
     */
    private function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * General input validation
     */
    private function validate_general($data) {
        // Check for null bytes
        if (strpos($data, "\0") !== false) {
            return false;
        }
        
        // Check for script tags
        if (preg_match('/<script[^>]*>.*?<\/script>/is', $data)) {
            return false;
        }
        
        // Check for dangerous HTML
        if (preg_match('/<(iframe|object|embed|form)[^>]*>/i', $data)) {
            return false;
        }
        
        return true;
    }
}