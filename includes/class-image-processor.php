<?php
/**
 * Image Processor Class
 * 
 * Handles automatic image import and URL replacement
 */

if (!defined('ABSPATH')) {
    exit;
}

class TKP_Image_Processor {
    
    private $logger;
    private $processed_images = array();
    private $failed_images = array();
    private $placeholder_image = null;
    
    public function __construct($logger) {
        $this->logger = $logger;
        $this->init_placeholder();
    }
    
    /**
     * Initialize placeholder image
     */
    private function init_placeholder() {
        $upload_dir = wp_upload_dir();
        $placeholder_path = $upload_dir['basedir'] . '/theme-kit-pro/placeholder.jpg';
        
        if (!file_exists($placeholder_path)) {
            $this->create_placeholder_image($placeholder_path);
        }
        
        $this->placeholder_image = $upload_dir['baseurl'] . '/theme-kit-pro/placeholder.jpg';
    }
    
    /**
     * Create placeholder image
     */
    private function create_placeholder_image($path) {
        $dir = dirname($path);
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        
        // Create a simple placeholder image
        if (extension_loaded('gd')) {
            $image = imagecreate(800, 600);
            $bg_color = imagecolorallocate($image, 240, 240, 240);
            $text_color = imagecolorallocate($image, 100, 100, 100);
            
            imagestring($image, 5, 300, 290, 'Image Not Available', $text_color);
            imagejpeg($image, $path, 80);
            imagedestroy($image);
        } else {
            // Fallback: copy a default image or create empty file
            file_put_contents($path, '');
        }
    }
    
    /**
     * Process images in content
     */
    public function process_content_images($content, $context = array()) {
        if (empty($content)) {
            return $content;
        }
        
        // Find all image URLs in content
        $pattern = '/(?:src|href|url)\s*=\s*["\']([^"\']*\.(?:jpg|jpeg|png|gif|webp|svg))["\']|url\(["\']?([^"\']*\.(?:jpg|jpeg|png|gif|webp|svg))["\']?\)/i';
        
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $image_url = !empty($match[1]) ? $match[1] : $match[2];
            
            if (empty($image_url)) {
                continue;
            }
            
            // Skip if already processed
            if (isset($this->processed_images[$image_url])) {
                $content = str_replace($image_url, $this->processed_images[$image_url], $content);
                continue;
            }
            
            // Process the image
            $new_url = $this->import_image($image_url, $context);
            
            if ($new_url) {
                $content = str_replace($image_url, $new_url, $content);
                $this->processed_images[$image_url] = $new_url;
            } else {
                // Use placeholder if import failed
                $content = str_replace($image_url, $this->placeholder_image, $content);
                $this->failed_images[] = $image_url;
            }
        }
        
        return $content;
    }
    
    /**
     * Import single image
     */
    public function import_image($image_url, $context = array()) {
        try {
            // Skip if localhost and remote checks are disabled
            if (get_option('tkp_enable_localhost_mode', false) && $this->is_localhost()) {
                if ($this->is_external_url($image_url)) {
                    $this->logger->info("Skipping external image in localhost mode: {$image_url}");
                    return $this->placeholder_image;
                }
            }
            
            // Validate URL
            if (!$this->is_valid_image_url($image_url)) {
                throw new Exception("Invalid image URL: {$image_url}");
            }
            
            // Check if image already exists in media library
            $existing_id = $this->find_existing_image($image_url);
            if ($existing_id) {
                return wp_get_attachment_url($existing_id);
            }
            
            // Download and import image
            $attachment_id = $this->download_and_import($image_url, $context);
            
            if ($attachment_id) {
                $new_url = wp_get_attachment_url($attachment_id);
                $this->logger->info("Successfully imported image: {$image_url} -> {$new_url}");
                return $new_url;
            }
            
            throw new Exception("Failed to import image");
            
        } catch (Exception $e) {
            $this->logger->warning("Image import failed: {$image_url}", array(
                'error' => $e->getMessage(),
                'context' => $context
            ));
            
            return null;
        }
    }
    
    /**
     * Download and import image to media library
     */
    private function download_and_import($image_url, $context = array()) {
        // Get image data
        $response = wp_remote_get($image_url, array(
            'timeout' => 30,
            'user-agent' => 'Theme Kit Pro/1.0'
        ));
        
        if (is_wp_error($response)) {
            throw new Exception("Failed to download image: " . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            throw new Exception("HTTP error {$response_code} when downloading image");
        }
        
        $image_data = wp_remote_retrieve_body($response);
        if (empty($image_data)) {
            throw new Exception("Empty image data received");
        }
        
        // Validate image data
        if (!$this->is_valid_image_data($image_data)) {
            throw new Exception("Invalid image data");
        }
        
        // Generate filename
        $filename = $this->generate_filename($image_url, $context);
        
        // Upload to media library
        $upload = wp_upload_bits($filename, null, $image_data);
        
        if ($upload['error']) {
            throw new Exception("Upload failed: " . $upload['error']);
        }
        
        // Create attachment
        $attachment = array(
            'post_mime_type' => wp_check_filetype($filename, null)['type'],
            'post_title' => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
        
        if (is_wp_error($attachment_id)) {
            throw new Exception("Failed to create attachment: " . $attachment_id->get_error_message());
        }
        
        // Generate attachment metadata
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        
        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        // Add custom meta for tracking
        update_post_meta($attachment_id, '_tkp_original_url', $image_url);
        update_post_meta($attachment_id, '_tkp_import_context', $context);
        update_post_meta($attachment_id, '_tkp_import_date', current_time('mysql'));
        
        return $attachment_id;
    }
    
    /**
     * Validate image URL
     */
    private function is_valid_image_url($url) {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check file extension
        $path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
        
        return in_array($extension, $allowed_extensions);
    }
    
    /**
     * Validate image data
     */
    private function is_valid_image_data($data) {
        // Check if data looks like an image
        $image_signatures = array(
            'jpg' => "\xFF\xD8\xFF",
            'png' => "\x89\x50\x4E\x47",
            'gif' => "GIF",
            'webp' => "WEBP"
        );
        
        foreach ($image_signatures as $type => $signature) {
            if (strpos($data, $signature) === 0 || strpos($data, $signature) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Find existing image in media library
     */
    private function find_existing_image($image_url) {
        global $wpdb;
        
        // Check by original URL meta
        $attachment_id = $wpdb->get_var($wpdb->prepare("
            SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key = '_tkp_original_url' 
            AND meta_value = %s
        ", $image_url));
        
        if ($attachment_id) {
            return $attachment_id;
        }
        
        // Check by filename
        $filename = basename(parse_url($image_url, PHP_URL_PATH));
        
        $attachment_id = $wpdb->get_var($wpdb->prepare("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'attachment' 
            AND post_title = %s
        ", sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME))));
        
        return $attachment_id;
    }
    
    /**
     * Generate filename for imported image
     */
    private function generate_filename($image_url, $context = array()) {
        $original_filename = basename(parse_url($image_url, PHP_URL_PATH));
        $pathinfo = pathinfo($original_filename);
        
        $name = sanitize_file_name($pathinfo['filename']);
        $extension = strtolower($pathinfo['extension'] ?? 'jpg');
        
        // Add context prefix if available
        $prefix = '';
        if (!empty($context['post_title'])) {
            $prefix = sanitize_file_name(substr($context['post_title'], 0, 20)) . '-';
        } elseif (!empty($context['template_name'])) {
            $prefix = sanitize_file_name(substr($context['template_name'], 0, 20)) . '-';
        }
        
        // Ensure unique filename
        $filename = $prefix . $name . '.' . $extension;
        $upload_dir = wp_upload_dir();
        $counter = 1;
        
        while (file_exists($upload_dir['path'] . '/' . $filename)) {
            $filename = $prefix . $name . '-' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Check if URL is external
     */
    private function is_external_url($url) {
        $site_url = parse_url(site_url(), PHP_URL_HOST);
        $image_host = parse_url($url, PHP_URL_HOST);
        
        return $image_host && $image_host !== $site_url;
    }
    
    /**
     * Check if running on localhost
     */
    private function is_localhost() {
        $localhost_indicators = array('localhost', '127.0.0.1', '::1', '.local', '.test');
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        foreach ($localhost_indicators as $indicator) {
            if (strpos($host, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get processing statistics
     */
    public function get_stats() {
        return array(
            'processed' => count($this->processed_images),
            'failed' => count($this->failed_images),
            'success_rate' => count($this->processed_images) > 0 
                ? round((count($this->processed_images) / (count($this->processed_images) + count($this->failed_images))) * 100, 2)
                : 0,
            'failed_urls' => $this->failed_images
        );
    }
    
    /**
     * Reset processing state
     */
    public function reset() {
        $this->processed_images = array();
        $this->failed_images = array();
    }
    
    /**
     * Optimize imported images
     */
    public function optimize_image($attachment_id) {
        if (!get_option('tkp_enable_image_optimization', true)) {
            return false;
        }
        
        $file_path = get_attached_file($attachment_id);
        
        if (!file_exists($file_path)) {
            return false;
        }
        
        $image_type = wp_check_filetype($file_path)['type'];
        
        // Only optimize JPEG and PNG images
        if (!in_array($image_type, array('image/jpeg', 'image/png'))) {
            return false;
        }
        
        try {
            if (extension_loaded('imagick')) {
                return $this->optimize_with_imagick($file_path, $image_type);
            } elseif (extension_loaded('gd')) {
                return $this->optimize_with_gd($file_path, $image_type);
            }
        } catch (Exception $e) {
            $this->logger->warning("Image optimization failed: {$file_path}", array(
                'error' => $e->getMessage()
            ));
        }
        
        return false;
    }
    
    /**
     * Optimize image with ImageMagick
     */
    private function optimize_with_imagick($file_path, $image_type) {
        $image = new Imagick($file_path);
        
        // Strip metadata
        $image->stripImage();
        
        // Set quality
        $quality = $image_type === 'image/jpeg' ? 85 : 90;
        $image->setImageCompressionQuality($quality);
        
        // Write optimized image
        $image->writeImage($file_path);
        $image->destroy();
        
        return true;
    }
    
    /**
     * Optimize image with GD
     */
    private function optimize_with_gd($file_path, $image_type) {
        if ($image_type === 'image/jpeg') {
            $image = imagecreatefromjpeg($file_path);
            imagejpeg($image, $file_path, 85);
            imagedestroy($image);
            return true;
        } elseif ($image_type === 'image/png') {
            $image = imagecreatefrompng($file_path);
            imagepng($image, $file_path, 6);
            imagedestroy($image);
            return true;
        }
        
        return false;
    }
}