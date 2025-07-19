<?php
/**
 * Theme Preview Class
 * 
 * Handles theme preview functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_Theme_Preview {
    
    /**
     * Generate theme preview
     */
    public function generate_theme_preview($theme_package_path) {
        $preview_data = array();
        
        // Extract package temporarily
        $temp_dir = $this->extract_package_for_preview($theme_package_path);
        
        if (!$temp_dir) {
            return false;
        }
        
        // Load manifest
        $manifest = $this->load_manifest($temp_dir);
        
        if (!$manifest) {
            $this->cleanup_temp_dir($temp_dir);
            return false;
        }
        
        // Generate screenshots
        $preview_data['screenshots'] = $this->generate_screenshots($temp_dir, $manifest);
        
        // Get theme information
        $preview_data['theme_info'] = $this->get_theme_info($temp_dir, $manifest);
        
        // Get template information
        $preview_data['templates'] = $this->get_template_info($temp_dir, $manifest);
        
        // Get plugin requirements
        $preview_data['plugins'] = $this->get_plugin_requirements($temp_dir, $manifest);
        
        // Get demo content info
        $preview_data['demo_content'] = $this->get_demo_content_info($temp_dir, $manifest);
        
        // Cleanup
        $this->cleanup_temp_dir($temp_dir);
        
        return $preview_data;
    }
    
    /**
     * Extract package for preview
     */
    private function extract_package_for_preview($package_path) {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/theme-exporter-pro/preview/' . uniqid();
        
        wp_mkdir_p($temp_dir);
        
        $zip = new ZipArchive();
        
        if ($zip->open($package_path) !== TRUE) {
            return false;
        }
        
        $zip->extractTo($temp_dir);
        $zip->close();
        
        return $temp_dir;
    }
    
    /**
     * Load manifest
     */
    private function load_manifest($temp_dir) {
        $manifest_file = $temp_dir . '/manifest.json';
        
        if (!file_exists($manifest_file)) {
            return false;
        }
        
        return json_decode(file_get_contents($manifest_file), true);
    }
    
    /**
     * Generate screenshots
     */
    private function generate_screenshots($temp_dir, $manifest) {
        $screenshots = array();
        
        // Look for existing screenshots in theme directory
        $theme_dir = $temp_dir . '/theme';
        
        if (file_exists($theme_dir)) {
            $screenshot_files = glob($theme_dir . '/screenshot.*');
            
            foreach ($screenshot_files as $screenshot) {
                $screenshots[] = array(
                    'type' => 'theme_screenshot',
                    'path' => $screenshot,
                    'url' => $this->copy_to_preview_dir($screenshot)
                );
            }
        }
        
        // Generate template previews
        if (isset($manifest['installation_steps']['import_templates'])) {
            $screenshots = array_merge($screenshots, $this->generate_template_previews($temp_dir, $manifest));
        }
        
        return $screenshots;
    }
    
    /**
     * Generate template previews
     */
    private function generate_template_previews($temp_dir, $manifest) {
        $previews = array();
        
        // Elementor templates
        if (file_exists($temp_dir . '/elementor-templates.json')) {
            $elementor_templates = json_decode(file_get_contents($temp_dir . '/elementor-templates.json'), true);
            
            foreach ($elementor_templates as $template) {
                if (isset($template['type']) && $template['type'] === 'page') {
                    $previews[] = array(
                        'type' => 'elementor_template',
                        'title' => $template['title'],
                        'preview_url' => $this->generate_elementor_preview($template)
                    );
                }
            }
        }
        
        // Gutenberg templates
        if (file_exists($temp_dir . '/gutenberg-templates.json')) {
            $gutenberg_templates = json_decode(file_get_contents($temp_dir . '/gutenberg-templates.json'), true);
            
            foreach ($gutenberg_templates as $template) {
                if (isset($template['type']) && $template['type'] === 'wp_template') {
                    $previews[] = array(
                        'type' => 'gutenberg_template',
                        'title' => $template['title'],
                        'preview_url' => $this->generate_gutenberg_preview($template)
                    );
                }
            }
        }
        
        return $previews;
    }
    
    /**
     * Generate Elementor preview
     */
    private function generate_elementor_preview($template) {
        // This would integrate with Elementor's preview system
        // For now, return a placeholder
        return admin_url('admin.php?page=theme-exporter-pro&preview=elementor&template=' . urlencode($template['title']));
    }
    
    /**
     * Generate Gutenberg preview
     */
    private function generate_gutenberg_preview($template) {
        // This would integrate with Gutenberg's preview system
        // For now, return a placeholder
        return admin_url('admin.php?page=theme-exporter-pro&preview=gutenberg&template=' . urlencode($template['title']));
    }
    
    /**
     * Copy file to preview directory
     */
    private function copy_to_preview_dir($file_path) {
        $upload_dir = wp_upload_dir();
        $preview_dir = $upload_dir['basedir'] . '/theme-exporter-pro/previews';
        
        wp_mkdir_p($preview_dir);
        
        $filename = basename($file_path);
        $dest_path = $preview_dir . '/' . uniqid() . '_' . $filename;
        
        if (copy($file_path, $dest_path)) {
            return $upload_dir['baseurl'] . '/theme-exporter-pro/previews/' . basename($dest_path);
        }
        
        return false;
    }
    
    /**
     * Get theme information
     */
    private function get_theme_info($temp_dir, $manifest) {
        $theme_info = array();
        
        if (isset($manifest['package_info'])) {
            $theme_info = $manifest['package_info'];
        }
        
        // Try to get additional info from style.css
        $style_css = $temp_dir . '/theme/style.css';
        
        if (file_exists($style_css)) {
            $theme_data = $this->parse_theme_header($style_css);
            $theme_info = array_merge($theme_info, $theme_data);
        }
        
        return $theme_info;
    }
    
    /**
     * Parse theme header
     */
    private function parse_theme_header($style_css) {
        $theme_data = array();
        
        $file_content = file_get_contents($style_css);
        
        // Extract theme information from header comment
        preg_match('/Theme Name:\s*(.+)/i', $file_content, $matches);
        if (isset($matches[1])) {
            $theme_data['theme_name'] = trim($matches[1]);
        }
        
        preg_match('/Description:\s*(.+)/i', $file_content, $matches);
        if (isset($matches[1])) {
            $theme_data['description'] = trim($matches[1]);
        }
        
        preg_match('/Version:\s*(.+)/i', $file_content, $matches);
        if (isset($matches[1])) {
            $theme_data['version'] = trim($matches[1]);
        }
        
        preg_match('/Author:\s*(.+)/i', $file_content, $matches);
        if (isset($matches[1])) {
            $theme_data['author'] = trim($matches[1]);
        }
        
        return $theme_data;
    }
    
    /**
     * Get template information
     */
    private function get_template_info($temp_dir, $manifest) {
        $template_info = array();
        
        // Count Elementor templates
        if (file_exists($temp_dir . '/elementor-templates.json')) {
            $elementor_templates = json_decode(file_get_contents($temp_dir . '/elementor-templates.json'), true);
            $template_info['elementor'] = count($elementor_templates);
        }
        
        // Count Gutenberg templates
        if (file_exists($temp_dir . '/gutenberg-templates.json')) {
            $gutenberg_templates = json_decode(file_get_contents($temp_dir . '/gutenberg-templates.json'), true);
            $template_info['gutenberg'] = count($gutenberg_templates);
        }
        
        return $template_info;
    }
    
    /**
     * Get plugin requirements
     */
    private function get_plugin_requirements($temp_dir, $manifest) {
        $plugins = array();
        
        if (file_exists($temp_dir . '/required-plugins.json')) {
            $plugins = json_decode(file_get_contents($temp_dir . '/required-plugins.json'), true);
        }
        
        return $plugins;
    }
    
    /**
     * Get demo content information
     */
    private function get_demo_content_info($temp_dir, $manifest) {
        $demo_info = array();
        
        if (file_exists($temp_dir . '/demo-content.json')) {
            $demo_content = json_decode(file_get_contents($temp_dir . '/demo-content.json'), true);
            
            $demo_info['posts'] = 0;
            $demo_info['pages'] = 0;
            $demo_info['other'] = 0;
            
            foreach ($demo_content as $content) {
                switch ($content['post_type']) {
                    case 'post':
                        $demo_info['posts']++;
                        break;
                    case 'page':
                        $demo_info['pages']++;
                        break;
                    default:
                        $demo_info['other']++;
                        break;
                }
            }
        }
        
        return $demo_info;
    }
    
    /**
     * Cleanup temporary directory
     */
    private function cleanup_temp_dir($temp_dir) {
        if (file_exists($temp_dir)) {
            $this->recursive_rmdir($temp_dir);
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
    
    /**
     * Create preview gallery
     */
    public function create_preview_gallery($screenshots) {
        $gallery_html = '<div class="tep-preview-gallery">';
        
        foreach ($screenshots as $screenshot) {
            $gallery_html .= '<div class="tep-preview-item">';
            $gallery_html .= '<img src="' . esc_url($screenshot['url']) . '" alt="' . esc_attr($screenshot['title'] ?? 'Preview') . '">';
            $gallery_html .= '<div class="tep-preview-overlay">';
            $gallery_html .= '<h4>' . esc_html($screenshot['title'] ?? 'Preview') . '</h4>';
            $gallery_html .= '<p>' . esc_html($screenshot['type']) . '</p>';
            $gallery_html .= '</div>';
            $gallery_html .= '</div>';
        }
        
        $gallery_html .= '</div>';
        
        return $gallery_html;
    }
}