<?php
/**
 * Elementor Handler Class
 * 
 * Handles Elementor-specific functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_Elementor_Handler {
    
    /**
     * Export Elementor templates
     */
    public function export_templates() {
        if (!class_exists('Elementor\Plugin')) {
            return array();
        }
        
        $templates = array();
        
        // Get Elementor templates
        $elementor_templates = get_posts(array(
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        foreach ($elementor_templates as $template) {
            $template_data = array(
                'title' => $template->post_title,
                'content' => $template->post_content,
                'type' => get_post_meta($template->ID, '_elementor_template_type', true),
                'data' => get_post_meta($template->ID, '_elementor_data', true),
                'page_settings' => get_post_meta($template->ID, '_elementor_page_settings', true),
                'version' => get_post_meta($template->ID, '_elementor_version', true),
                'conditions' => get_post_meta($template->ID, '_elementor_conditions', true),
                'css' => get_post_meta($template->ID, '_elementor_css', true)
            );
            
            $templates[] = $template_data;
        }
        
        // Get pages/posts with Elementor data
        $elementor_posts = get_posts(array(
            'post_type' => array('page', 'post'),
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_key' => '_elementor_data',
            'meta_compare' => 'EXISTS'
        ));
        
        foreach ($elementor_posts as $post) {
            $post_data = array(
                'post_id' => $post->ID,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'elementor_data' => get_post_meta($post->ID, '_elementor_data', true),
                'page_settings' => get_post_meta($post->ID, '_elementor_page_settings', true),
                'version' => get_post_meta($post->ID, '_elementor_version', true),
                'css' => get_post_meta($post->ID, '_elementor_css', true)
            );
            
            $templates[] = $post_data;
        }
        
        return $templates;
    }
    
    /**
     * Import Elementor templates
     */
    public function import_templates($templates) {
        if (!class_exists('Elementor\Plugin')) {
            return array('success' => false, 'message' => __('Elementor is not installed', 'theme-exporter-pro'));
        }
        
        $imported_templates = 0;
        $imported_posts = 0;
        
        foreach ($templates as $template_data) {
            if (isset($template_data['type']) && $template_data['type']) {
                // This is an Elementor template
                $template_id = wp_insert_post(array(
                    'post_title' => $template_data['title'],
                    'post_content' => $template_data['content'],
                    'post_type' => 'elementor_library',
                    'post_status' => 'publish'
                ));
                
                if ($template_id && !is_wp_error($template_id)) {
                    // Import template meta
                    update_post_meta($template_id, '_elementor_template_type', $template_data['type']);
                    update_post_meta($template_id, '_elementor_data', $template_data['data']);
                    
                    if (isset($template_data['page_settings'])) {
                        update_post_meta($template_id, '_elementor_page_settings', $template_data['page_settings']);
                    }
                    
                    if (isset($template_data['version'])) {
                        update_post_meta($template_id, '_elementor_version', $template_data['version']);
                    }
                    
                    if (isset($template_data['conditions'])) {
                        update_post_meta($template_id, '_elementor_conditions', $template_data['conditions']);
                    }
                    
                    if (isset($template_data['css'])) {
                        update_post_meta($template_id, '_elementor_css', $template_data['css']);
                    }
                    
                    $imported_templates++;
                }
            } else if (isset($template_data['post_title']) && isset($template_data['elementor_data'])) {
                // This is a page/post with Elementor data
                $post_id = wp_insert_post(array(
                    'post_title' => $template_data['post_title'],
                    'post_type' => $template_data['post_type'] ?? 'page',
                    'post_status' => 'publish'
                ));
                
                if ($post_id && !is_wp_error($post_id)) {
                    // Import Elementor data
                    update_post_meta($post_id, '_elementor_data', $template_data['elementor_data']);
                    update_post_meta($post_id, '_elementor_edit_mode', 'builder');
                    
                    if (isset($template_data['page_settings'])) {
                        update_post_meta($post_id, '_elementor_page_settings', $template_data['page_settings']);
                    }
                    
                    if (isset($template_data['version'])) {
                        update_post_meta($post_id, '_elementor_version', $template_data['version']);
                    }
                    
                    if (isset($template_data['css'])) {
                        update_post_meta($post_id, '_elementor_css', $template_data['css']);
                    }
                    
                    $imported_posts++;
                }
            }
        }
        
        // Clear Elementor cache
        if (class_exists('Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('%d templates and %d posts imported', 'theme-exporter-pro'), $imported_templates, $imported_posts)
        );
    }
    
    /**
     * Export Elementor global settings
     */
    public function export_global_settings() {
        if (!class_exists('Elementor\Plugin')) {
            return array();
        }
        
        $settings = array();
        
        // Get Elementor settings
        $elementor_settings = get_option('elementor_settings', array());
        if (!empty($elementor_settings)) {
            $settings['elementor_settings'] = $elementor_settings;
        }
        
        // Get Elementor active kit
        $active_kit = get_option('elementor_active_kit', 0);
        if ($active_kit) {
            $settings['active_kit'] = $active_kit;
            $settings['kit_settings'] = get_post_meta($active_kit, '_elementor_page_settings', true);
        }
        
        // Get custom fonts
        $custom_fonts = get_option('elementor_custom_fonts', array());
        if (!empty($custom_fonts)) {
            $settings['custom_fonts'] = $custom_fonts;
        }
        
        return $settings;
    }
    
    /**
     * Import Elementor global settings
     */
    public function import_global_settings($settings) {
        if (!class_exists('Elementor\Plugin')) {
            return false;
        }
        
        // Import Elementor settings
        if (isset($settings['elementor_settings'])) {
            update_option('elementor_settings', $settings['elementor_settings']);
        }
        
        // Import custom fonts
        if (isset($settings['custom_fonts'])) {
            update_option('elementor_custom_fonts', $settings['custom_fonts']);
        }
        
        // Handle active kit
        if (isset($settings['active_kit']) && isset($settings['kit_settings'])) {
            // Create new kit post
            $kit_id = wp_insert_post(array(
                'post_title' => 'Imported Kit',
                'post_type' => 'elementor_library',
                'post_status' => 'publish'
            ));
            
            if ($kit_id && !is_wp_error($kit_id)) {
                update_post_meta($kit_id, '_elementor_template_type', 'kit');
                update_post_meta($kit_id, '_elementor_page_settings', $settings['kit_settings']);
                update_option('elementor_active_kit', $kit_id);
            }
        }
        
        return true;
    }
    
    /**
     * Get Elementor system requirements
     */
    public function get_system_requirements() {
        return array(
            'elementor' => array(
                'name' => 'Elementor',
                'required' => true,
                'slug' => 'elementor'
            ),
            'elementor-pro' => array(
                'name' => 'Elementor Pro',
                'required' => false,
                'slug' => 'elementor-pro'
            )
        );
    }
}