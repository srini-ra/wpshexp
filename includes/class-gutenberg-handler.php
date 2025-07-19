<?php
/**
 * Gutenberg Handler Class
 * 
 * Handles Gutenberg-specific functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_Gutenberg_Handler {
    
    /**
     * Export Gutenberg templates
     */
    public function export_templates() {
        $templates = array();
        
        // Get FSE templates (block themes)
        if (wp_is_block_theme()) {
            $fse_templates = get_posts(array(
                'post_type' => 'wp_template',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            foreach ($fse_templates as $template) {
                $template_data = array(
                    'title' => $template->post_title,
                    'content' => $template->post_content,
                    'slug' => $template->post_name,
                    'type' => 'wp_template',
                    'theme' => get_post_meta($template->ID, 'theme', true),
                    'area' => get_post_meta($template->ID, 'area', true),
                    'description' => get_post_meta($template->ID, 'description', true)
                );
                
                $templates[] = $template_data;
            }
            
            // Get template parts
            $template_parts = get_posts(array(
                'post_type' => 'wp_template_part',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            foreach ($template_parts as $template_part) {
                $part_data = array(
                    'title' => $template_part->post_title,
                    'content' => $template_part->post_content,
                    'slug' => $template_part->post_name,
                    'type' => 'wp_template_part',
                    'theme' => get_post_meta($template_part->ID, 'theme', true),
                    'area' => get_post_meta($template_part->ID, 'area', true),
                    'description' => get_post_meta($template_part->ID, 'description', true)
                );
                
                $templates[] = $part_data;
            }
        }
        
        // Get reusable blocks
        $reusable_blocks = get_posts(array(
            'post_type' => 'wp_block',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        foreach ($reusable_blocks as $block) {
            $block_data = array(
                'title' => $block->post_title,
                'content' => $block->post_content,
                'type' => 'wp_block',
                'slug' => $block->post_name
            );
            
            $templates[] = $block_data;
        }
        
        // Get posts and pages with Gutenberg blocks
        $gutenberg_posts = get_posts(array(
            'post_type' => array('page', 'post'),
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        foreach ($gutenberg_posts as $post) {
            // Check if post has blocks
            if (has_blocks($post->post_content)) {
                $post_data = array(
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_type' => $post->post_type,
                    'post_content' => $post->post_content,
                    'type' => 'gutenberg_post',
                    'blocks' => parse_blocks($post->post_content)
                );
                
                $templates[] = $post_data;
            }
        }
        
        return $templates;
    }
    
    /**
     * Import Gutenberg templates
     */
    public function import_templates($templates) {
        $imported_templates = 0;
        $imported_parts = 0;
        $imported_blocks = 0;
        $imported_posts = 0;
        
        foreach ($templates as $template_data) {
            switch ($template_data['type']) {
                case 'wp_template':
                    $template_id = wp_insert_post(array(
                        'post_title' => $template_data['title'],
                        'post_content' => $template_data['content'],
                        'post_name' => $template_data['slug'],
                        'post_type' => 'wp_template',
                        'post_status' => 'publish'
                    ));
                    
                    if ($template_id && !is_wp_error($template_id)) {
                        // Import template meta
                        if (isset($template_data['theme'])) {
                            update_post_meta($template_id, 'theme', $template_data['theme']);
                        }
                        if (isset($template_data['area'])) {
                            update_post_meta($template_id, 'area', $template_data['area']);
                        }
                        if (isset($template_data['description'])) {
                            update_post_meta($template_id, 'description', $template_data['description']);
                        }
                        
                        $imported_templates++;
                    }
                    break;
                    
                case 'wp_template_part':
                    $part_id = wp_insert_post(array(
                        'post_title' => $template_data['title'],
                        'post_content' => $template_data['content'],
                        'post_name' => $template_data['slug'],
                        'post_type' => 'wp_template_part',
                        'post_status' => 'publish'
                    ));
                    
                    if ($part_id && !is_wp_error($part_id)) {
                        // Import template part meta
                        if (isset($template_data['theme'])) {
                            update_post_meta($part_id, 'theme', $template_data['theme']);
                        }
                        if (isset($template_data['area'])) {
                            update_post_meta($part_id, 'area', $template_data['area']);
                        }
                        if (isset($template_data['description'])) {
                            update_post_meta($part_id, 'description', $template_data['description']);
                        }
                        
                        $imported_parts++;
                    }
                    break;
                    
                case 'wp_block':
                    $block_id = wp_insert_post(array(
                        'post_title' => $template_data['title'],
                        'post_content' => $template_data['content'],
                        'post_name' => $template_data['slug'],
                        'post_type' => 'wp_block',
                        'post_status' => 'publish'
                    ));
                    
                    if ($block_id && !is_wp_error($block_id)) {
                        $imported_blocks++;
                    }
                    break;
                    
                case 'gutenberg_post':
                    $post_id = wp_insert_post(array(
                        'post_title' => $template_data['post_title'],
                        'post_content' => $template_data['post_content'],
                        'post_type' => $template_data['post_type'],
                        'post_status' => 'publish'
                    ));
                    
                    if ($post_id && !is_wp_error($post_id)) {
                        $imported_posts++;
                    }
                    break;
            }
        }
        
        return array(
            'success' => true,
            'message' => sprintf(
                __('%d templates, %d template parts, %d reusable blocks, and %d posts imported', 'theme-exporter-pro'),
                $imported_templates,
                $imported_parts,
                $imported_blocks,
                $imported_posts
            )
        );
    }
    
    /**
     * Export Gutenberg global settings
     */
    public function export_global_settings() {
        $settings = array();
        
        // Get global styles
        if (wp_is_block_theme()) {
            $global_styles = get_posts(array(
                'post_type' => 'wp_global_styles',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            foreach ($global_styles as $style) {
                $style_data = array(
                    'title' => $style->post_title,
                    'content' => $style->post_content,
                    'theme' => get_post_meta($style->ID, 'theme', true),
                    'is_global_styles_user_theme_json' => get_post_meta($style->ID, 'is_global_styles_user_theme_json', true)
                );
                
                $settings['global_styles'][] = $style_data;
            }
        }
        
        // Get block editor settings
        $block_editor_settings = get_option('gutenberg-experiments', array());
        if (!empty($block_editor_settings)) {
            $settings['block_editor_settings'] = $block_editor_settings;
        }
        
        return $settings;
    }
    
    /**
     * Import Gutenberg global settings
     */
    public function import_global_settings($settings) {
        // Import global styles
        if (isset($settings['global_styles'])) {
            foreach ($settings['global_styles'] as $style_data) {
                $style_id = wp_insert_post(array(
                    'post_title' => $style_data['title'],
                    'post_content' => $style_data['content'],
                    'post_type' => 'wp_global_styles',
                    'post_status' => 'publish'
                ));
                
                if ($style_id && !is_wp_error($style_id)) {
                    if (isset($style_data['theme'])) {
                        update_post_meta($style_id, 'theme', $style_data['theme']);
                    }
                    if (isset($style_data['is_global_styles_user_theme_json'])) {
                        update_post_meta($style_id, 'is_global_styles_user_theme_json', $style_data['is_global_styles_user_theme_json']);
                    }
                }
            }
        }
        
        // Import block editor settings
        if (isset($settings['block_editor_settings'])) {
            update_option('gutenberg-experiments', $settings['block_editor_settings']);
        }
        
        return true;
    }
    
    /**
     * Get custom block patterns
     */
    public function export_block_patterns() {
        $patterns = array();
        
        // Get registered patterns
        $registered_patterns = WP_Block_Patterns_Registry::get_instance()->get_all_registered();
        
        foreach ($registered_patterns as $pattern_name => $pattern) {
            // Only export custom patterns (not core ones)
            if (strpos($pattern_name, 'custom/') === 0 || strpos($pattern_name, get_stylesheet() . '/') === 0) {
                $patterns[$pattern_name] = $pattern;
            }
        }
        
        return $patterns;
    }
    
    /**
     * Import custom block patterns
     */
    public function import_block_patterns($patterns) {
        $imported_count = 0;
        
        foreach ($patterns as $pattern_name => $pattern) {
            if (register_block_pattern($pattern_name, $pattern)) {
                $imported_count++;
            }
        }
        
        return $imported_count;
    }
    
    /**
     * Get Gutenberg system requirements
     */
    public function get_system_requirements() {
        return array(
            'wordpress' => array(
                'name' => 'WordPress',
                'version' => '5.0+',
                'required' => true
            ),
            'gutenberg' => array(
                'name' => 'Gutenberg Plugin',
                'required' => false,
                'slug' => 'gutenberg'
            )
        );
    }
}