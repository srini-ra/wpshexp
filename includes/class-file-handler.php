<?php
/**
 * File Handler Class
 * 
 * Handles file operations and utilities
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_File_Handler {
    
    /**
     * Download file from URL
     */
    public static function download_file($url, $destination) {
        $response = wp_remote_get($url, array(
            'timeout' => 300,
            'stream' => true,
            'filename' => $destination
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Upload file to media library
     */
    public static function upload_to_media_library($file_path, $filename = null) {
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        if (!$filename) {
            $filename = basename($file_path);
        }
        
        $upload_file = wp_upload_bits($filename, null, file_get_contents($file_path));
        
        if (!$upload_file['error']) {
            $attachment = array(
                'post_mime_type' => wp_check_filetype($filename, null)['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $upload_file['file']);
            
            if (!function_exists('wp_generate_attachment_metadata')) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
            }
            
            $attach_data = wp_generate_attachment_metadata($attach_id, $upload_file['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            return $attach_id;
        }
        
        return false;
    }
    
    /**
     * Get file size in human readable format
     */
    public static function format_file_size($bytes) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Validate file type
     */
    public static function validate_file_type($file_path, $allowed_types = array()) {
        $file_info = wp_check_filetype($file_path);
        
        if (empty($allowed_types)) {
            $allowed_types = array('zip', 'json', 'xml');
        }
        
        return in_array($file_info['ext'], $allowed_types);
    }
    
    /**
     * Sanitize filename
     */
    public static function sanitize_filename($filename) {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
        
        // Remove multiple consecutive periods
        $filename = preg_replace('/\.+/', '.', $filename);
        
        // Remove leading/trailing periods and hyphens
        $filename = trim($filename, '.-');
        
        return $filename;
    }
    
    /**
     * Create directory if it doesn't exist
     */
    public static function ensure_directory_exists($path) {
        if (!file_exists($path)) {
            wp_mkdir_p($path);
        }
        
        return file_exists($path);
    }
    
    /**
     * Get directory size
     */
    public static function get_directory_size($path) {
        $size = 0;
        
        if (is_dir($path)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Clean temporary files older than specified time
     */
    public static function cleanup_old_files($directory, $max_age = 86400) {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = scandir($directory);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $file_path = $directory . '/' . $file;
            
            if (is_file($file_path) && (time() - filemtime($file_path)) > $max_age) {
                unlink($file_path);
            } elseif (is_dir($file_path)) {
                self::cleanup_old_files($file_path, $max_age);
                
                // Remove empty directories
                $dir_files = scandir($file_path);
                if (count($dir_files) <= 2) { // Only . and .. entries
                    rmdir($file_path);
                }
            }
        }
    }
    
    /**
     * Check if ZIP extension is available
     */
    public static function is_zip_available() {
        return class_exists('ZipArchive');
    }
    
    /**
     * Check available disk space
     */
    public static function check_disk_space($required_space = 0) {
        $upload_dir = wp_upload_dir();
        $free_space = disk_free_space($upload_dir['basedir']);
        
        if ($free_space === false) {
            return true; // Cannot determine, assume it's okay
        }
        
        return $free_space >= $required_space;
    }
    
    /**
     * Get MIME type of file
     */
    public static function get_mime_type($file_path) {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_path);
            finfo_close($finfo);
            return $mime_type;
        } elseif (function_exists('mime_content_type')) {
            return mime_content_type($file_path);
        } else {
            $file_info = wp_check_filetype($file_path);
            return $file_info['type'];
        }
    }
}