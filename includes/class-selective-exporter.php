<?php
/**
 * Selective Exporter Class
 * 
 * Handles selective content export functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_Selective_Exporter {
    
    /**
     * Get available content for selective export
     */
    public function get_available_content() {
        $content = array();
        
        // Get posts by type
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $post_type) {
            $posts = get_posts(array(
                'post_type' => $post_type->name,
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            if (!empty($posts)) {
                $content['post_types'][$post_type->name] = array(
                    'label' => $post_type->label,
                    'count' => count($posts),
                    'posts' => array()
                );
                
                foreach ($posts as $post) {
                    $content['post_types'][$post_type->name]['posts'][] = array(
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'date' => $post->post_date,
                        'status' => $post->post_status,
                        'featured_image' => get_the_post_thumbnail_url($post->ID, 'thumbnail')
                    );
                }
            }
        }
        
        // Get taxonomies
        $taxonomies = get_taxonomies(array('public' => true), 'objects');
        
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy->name,
                'hide_empty' => false
            ));
            
            if (!empty($terms) && !is_wp_error($terms)) {
                $content['taxonomies'][$taxonomy->name] = array(
                    'label' => $taxonomy->label,
                    'count' => count($terms),
                    'terms' => array()
                );
                
                foreach ($terms as $term) {
                    $content['taxonomies'][$taxonomy->name]['terms'][] = array(
                        'id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                        'count' => $term->count
                    );
                }
            }
        }
        
        // Get media files
        $media = get_posts(array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'inherit'
        ));
        
        if (!empty($media)) {
            $content['media'] = array(
                'label' => 'Media Files',
                'count' => count($media),
                'files' => array()
            );
            
            foreach ($media as $file) {
                $content['media']['files'][] = array(
                    'id' => $file->ID,
                    'title' => $file->post_title,
                    'filename' => basename(get_attached_file($file->ID)),
                    'mime_type' => $file->post_mime_type,
                    'url' => wp_get_attachment_url($file->ID),
                    'size' => size_format(filesize(get_attached_file($file->ID)))
                );
            }
        }
        
        // Get users
        $users = get_users();
        
        if (!empty($users)) {
            $content['users'] = array(
                'label' => 'Users',
                'count' => count($users),
                'users' => array()
            );
            
            foreach ($users as $user) {
                $content['users']['users'][] = array(
                    'id' => $user->ID,
                    'login' => $user->user_login,
                    'email' => $user->user_email,
                    'display_name' => $user->display_name,
                    'role' => implode(', ', $user->roles)
                );
            }
        }
        
        // Get menus
        $menus = wp_get_nav_menus();
        
        if (!empty($menus)) {
            $content['menus'] = array(
                'label' => 'Navigation Menus',
                'count' => count($menus),
                'menus' => array()
            );
            
            foreach ($menus as $menu) {
                $content['menus']['menus'][] = array(
                    'id' => $menu->term_id,
                    'name' => $menu->name,
                    'slug' => $menu->slug,
                    'count' => $menu->count
                );
            }
        }
        
        return $content;
    }
    
    /**
     * Export selected content
     */
    public function export_selected_content($selections) {
        $export_data = array();
        
        // Export selected posts
        if (isset($selections['posts']) && !empty($selections['posts'])) {
            $export_data['posts'] = $this->export_selected_posts($selections['posts']);
        }
        
        // Export selected terms
        if (isset($selections['terms']) && !empty($selections['terms'])) {
            $export_data['terms'] = $this->export_selected_terms($selections['terms']);
        }
        
        // Export selected media
        if (isset($selections['media']) && !empty($selections['media'])) {
            $export_data['media'] = $this->export_selected_media($selections['media']);
        }
        
        // Export selected users
        if (isset($selections['users']) && !empty($selections['users'])) {
            $export_data['users'] = $this->export_selected_users($selections['users']);
        }
        
        // Export selected menus
        if (isset($selections['menus']) && !empty($selections['menus'])) {
            $export_data['menus'] = $this->export_selected_menus($selections['menus']);
        }
        
        return $export_data;
    }
    
    /**
     * Export selected posts
     */
    private function export_selected_posts($post_ids) {
        $posts = array();
        
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            
            if (!$post) continue;
            
            $post_data = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'excerpt' => $post->post_excerpt,
                'type' => $post->post_type,
                'status' => $post->post_status,
                'date' => $post->post_date,
                'modified' => $post->post_modified,
                'slug' => $post->post_name,
                'parent' => $post->post_parent,
                'menu_order' => $post->menu_order,
                'meta' => get_post_meta($post->ID),
                'terms' => array(),
                'featured_image' => get_post_thumbnail_id($post->ID)
            );
            
            // Get post terms
            $taxonomies = get_object_taxonomies($post->post_type);
            foreach ($taxonomies as $taxonomy) {
                $terms = wp_get_post_terms($post->ID, $taxonomy);
                if (!empty($terms) && !is_wp_error($terms)) {
                    $post_data['terms'][$taxonomy] = wp_list_pluck($terms, 'slug');
                }
            }
            
            $posts[] = $post_data;
        }
        
        return $posts;
    }
    
    /**
     * Export selected terms
     */
    private function export_selected_terms($term_selections) {
        $terms = array();
        
        foreach ($term_selections as $taxonomy => $term_ids) {
            foreach ($term_ids as $term_id) {
                $term = get_term($term_id, $taxonomy);
                
                if (!$term || is_wp_error($term)) continue;
                
                $terms[] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description,
                    'taxonomy' => $term->taxonomy,
                    'parent' => $term->parent,
                    'meta' => get_term_meta($term->term_id)
                );
            }
        }
        
        return $terms;
    }
    
    /**
     * Export selected media
     */
    private function export_selected_media($media_ids) {
        $media = array();
        
        foreach ($media_ids as $media_id) {
            $attachment = get_post($media_id);
            
            if (!$attachment || $attachment->post_type !== 'attachment') continue;
            
            $file_path = get_attached_file($media_id);
            $file_url = wp_get_attachment_url($media_id);
            
            $media[] = array(
                'id' => $attachment->ID,
                'title' => $attachment->post_title,
                'description' => $attachment->post_content,
                'caption' => $attachment->post_excerpt,
                'alt_text' => get_post_meta($media_id, '_wp_attachment_image_alt', true),
                'filename' => basename($file_path),
                'mime_type' => $attachment->post_mime_type,
                'url' => $file_url,
                'file_path' => $file_path,
                'meta' => wp_get_attachment_metadata($media_id)
            );
        }
        
        return $media;
    }
    
    /**
     * Export selected users
     */
    private function export_selected_users($user_ids) {
        $users = array();
        
        foreach ($user_ids as $user_id) {
            $user = get_user_by('id', $user_id);
            
            if (!$user) continue;
            
            $users[] = array(
                'id' => $user->ID,
                'login' => $user->user_login,
                'email' => $user->user_email,
                'display_name' => $user->display_name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'description' => $user->description,
                'roles' => $user->roles,
                'meta' => get_user_meta($user->ID)
            );
        }
        
        return $users;
    }
    
    /**
     * Export selected menus
     */
    private function export_selected_menus($menu_ids) {
        $menus = array();
        
        foreach ($menu_ids as $menu_id) {
            $menu = wp_get_nav_menu_object($menu_id);
            
            if (!$menu) continue;
            
            $menu_items = wp_get_nav_menu_items($menu_id);
            
            $menu_data = array(
                'id' => $menu->term_id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'items' => array()
            );
            
            foreach ($menu_items as $item) {
                $menu_data['items'][] = array(
                    'id' => $item->ID,
                    'title' => $item->title,
                    'url' => $item->url,
                    'target' => $item->target,
                    'description' => $item->description,
                    'classes' => $item->classes,
                    'xfn' => $item->xfn,
                    'menu_order' => $item->menu_order,
                    'parent' => $item->menu_item_parent,
                    'object' => $item->object,
                    'object_id' => $item->object_id,
                    'type' => $item->type
                );
            }
            
            $menus[] = $menu_data;
        }
        
        return $menus;
    }
    
    /**
     * Get content statistics
     */
    public function get_content_statistics() {
        $stats = array();
        
        // Post type statistics
        $post_types = get_post_types(array('public' => true), 'objects');
        foreach ($post_types as $post_type) {
            $count = wp_count_posts($post_type->name);
            $stats['post_types'][$post_type->name] = array(
                'label' => $post_type->label,
                'published' => $count->publish,
                'draft' => $count->draft,
                'total' => $count->publish + $count->draft
            );
        }
        
        // Media statistics
        $media_count = wp_count_posts('attachment');
        $stats['media'] = array(
            'total' => $media_count->inherit,
            'size' => $this->get_media_total_size()
        );
        
        // User statistics
        $user_count = count_users();
        $stats['users'] = array(
            'total' => $user_count['total_users'],
            'by_role' => $user_count['avail_roles']
        );
        
        return $stats;
    }
    
    /**
     * Get total media size
     */
    private function get_media_total_size() {
        global $wpdb;
        
        $upload_dir = wp_upload_dir();
        $total_size = 0;
        
        $attachments = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment'");
        
        foreach ($attachments as $attachment) {
            $file_path = get_attached_file($attachment->ID);
            if (file_exists($file_path)) {
                $total_size += filesize($file_path);
            }
        }
        
        return size_format($total_size);
    }
}