<?php
/**
 * Admin Selective Export Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$selective_exporter = new TEP_Selective_Exporter();
$available_content = $selective_exporter->get_available_content();
$content_stats = $selective_exporter->get_content_statistics();
?>

<div class="wrap">
    <h1><?php _e('Theme Exporter Pro - Selective Export', 'theme-exporter-pro'); ?></h1>
    
    <div class="tep-container">
        <div class="tep-main-content">
            <div class="tep-card">
                <h2><?php _e('Selective Content Export', 'theme-exporter-pro'); ?></h2>
                <p><?php _e('Choose specific content to include in your theme package export.', 'theme-exporter-pro'); ?></p>
                
                <form id="tep-selective-export-form" method="post">
                    <?php wp_nonce_field('tep_nonce', 'tep_nonce'); ?>
                    
                    <!-- Content Selection Tabs -->
                    <div class="tep-content-tabs">
                        <nav class="tep-tab-nav">
                            <button type="button" class="tep-tab-button active" data-tab="posts"><?php _e('Posts & Pages', 'theme-exporter-pro'); ?></button>
                            <button type="button" class="tep-tab-button" data-tab="media"><?php _e('Media', 'theme-exporter-pro'); ?></button>
                            <button type="button" class="tep-tab-button" data-tab="taxonomies"><?php _e('Categories & Tags', 'theme-exporter-pro'); ?></button>
                            <button type="button" class="tep-tab-button" data-tab="users"><?php _e('Users', 'theme-exporter-pro'); ?></button>
                            <button type="button" class="tep-tab-button" data-tab="menus"><?php _e('Menus', 'theme-exporter-pro'); ?></button>
                            <?php if (class_exists('WooCommerce')): ?>
                            <button type="button" class="tep-tab-button" data-tab="woocommerce"><?php _e('WooCommerce', 'theme-exporter-pro'); ?></button>
                            <?php endif; ?>
                        </nav>
                        
                        <!-- Posts & Pages Tab -->
                        <div class="tep-tab-content active" id="posts-tab">
                            <?php if (isset($available_content['post_types'])): ?>
                                <?php foreach ($available_content['post_types'] as $post_type => $data): ?>
                                <div class="tep-content-section">
                                    <div class="tep-section-header">
                                        <label class="tep-select-all">
                                            <input type="checkbox" class="tep-select-all-checkbox" data-target="posts-<?php echo esc_attr($post_type); ?>">
                                            <strong><?php echo esc_html($data['label']); ?></strong>
                                            <span class="tep-count">(<?php echo $data['count']; ?>)</span>
                                        </label>
                                    </div>
                                    <div class="tep-content-list">
                                        <?php foreach ($data['posts'] as $post): ?>
                                        <label class="tep-content-item">
                                            <input type="checkbox" name="posts[]" value="<?php echo $post['id']; ?>" class="posts-<?php echo esc_attr($post_type); ?>">
                                            <div class="tep-item-info">
                                                <?php if ($post['featured_image']): ?>
                                                <img src="<?php echo esc_url($post['featured_image']); ?>" alt="" class="tep-item-thumb">
                                                <?php endif; ?>
                                                <div class="tep-item-details">
                                                    <strong><?php echo esc_html($post['title']); ?></strong>
                                                    <span class="tep-item-meta"><?php echo date('M j, Y', strtotime($post['date'])); ?> • <?php echo esc_html($post['status']); ?></span>
                                                </div>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Media Tab -->
                        <div class="tep-tab-content" id="media-tab">
                            <?php if (isset($available_content['media'])): ?>
                            <div class="tep-content-section">
                                <div class="tep-section-header">
                                    <label class="tep-select-all">
                                        <input type="checkbox" class="tep-select-all-checkbox" data-target="media-files">
                                        <strong><?php echo esc_html($available_content['media']['label']); ?></strong>
                                        <span class="tep-count">(<?php echo $available_content['media']['count']; ?>)</span>
                                    </label>
                                </div>
                                <div class="tep-media-grid">
                                    <?php foreach ($available_content['media']['files'] as $file): ?>
                                    <label class="tep-media-item">
                                        <input type="checkbox" name="media[]" value="<?php echo $file['id']; ?>" class="media-files">
                                        <div class="tep-media-preview">
                                            <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
                                            <img src="<?php echo esc_url($file['url']); ?>" alt="<?php echo esc_attr($file['title']); ?>">
                                            <?php else: ?>
                                            <div class="tep-file-icon"><?php echo strtoupper(pathinfo($file['filename'], PATHINFO_EXTENSION)); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tep-media-info">
                                            <strong><?php echo esc_html($file['title'] ?: $file['filename']); ?></strong>
                                            <span class="tep-file-size"><?php echo esc_html($file['size']); ?></span>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Taxonomies Tab -->
                        <div class="tep-tab-content" id="taxonomies-tab">
                            <?php if (isset($available_content['taxonomies'])): ?>
                                <?php foreach ($available_content['taxonomies'] as $taxonomy => $data): ?>
                                <div class="tep-content-section">
                                    <div class="tep-section-header">
                                        <label class="tep-select-all">
                                            <input type="checkbox" class="tep-select-all-checkbox" data-target="terms-<?php echo esc_attr($taxonomy); ?>">
                                            <strong><?php echo esc_html($data['label']); ?></strong>
                                            <span class="tep-count">(<?php echo $data['count']; ?>)</span>
                                        </label>
                                    </div>
                                    <div class="tep-content-list">
                                        <?php foreach ($data['terms'] as $term): ?>
                                        <label class="tep-content-item">
                                            <input type="checkbox" name="terms[<?php echo esc_attr($taxonomy); ?>][]" value="<?php echo $term['id']; ?>" class="terms-<?php echo esc_attr($taxonomy); ?>">
                                            <div class="tep-item-info">
                                                <div class="tep-item-details">
                                                    <strong><?php echo esc_html($term['name']); ?></strong>
                                                    <span class="tep-item-meta"><?php echo $term['count']; ?> posts</span>
                                                </div>
                                            </div>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Users Tab -->
                        <div class="tep-tab-content" id="users-tab">
                            <?php if (isset($available_content['users'])): ?>
                            <div class="tep-content-section">
                                <div class="tep-section-header">
                                    <label class="tep-select-all">
                                        <input type="checkbox" class="tep-select-all-checkbox" data-target="user-list">
                                        <strong><?php echo esc_html($available_content['users']['label']); ?></strong>
                                        <span class="tep-count">(<?php echo $available_content['users']['count']; ?>)</span>
                                    </label>
                                </div>
                                <div class="tep-content-list">
                                    <?php foreach ($available_content['users']['users'] as $user): ?>
                                    <label class="tep-content-item">
                                        <input type="checkbox" name="users[]" value="<?php echo $user['id']; ?>" class="user-list">
                                        <div class="tep-item-info">
                                            <div class="tep-item-details">
                                                <strong><?php echo esc_html($user['display_name']); ?></strong>
                                                <span class="tep-item-meta"><?php echo esc_html($user['email']); ?> • <?php echo esc_html($user['role']); ?></span>
                                            </div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Menus Tab -->
                        <div class="tep-tab-content" id="menus-tab">
                            <?php if (isset($available_content['menus'])): ?>
                            <div class="tep-content-section">
                                <div class="tep-section-header">
                                    <label class="tep-select-all">
                                        <input type="checkbox" class="tep-select-all-checkbox" data-target="menu-list">
                                        <strong><?php echo esc_html($available_content['menus']['label']); ?></strong>
                                        <span class="tep-count">(<?php echo $available_content['menus']['count']; ?>)</span>
                                    </label>
                                </div>
                                <div class="tep-content-list">
                                    <?php foreach ($available_content['menus']['menus'] as $menu): ?>
                                    <label class="tep-content-item">
                                        <input type="checkbox" name="menus[]" value="<?php echo $menu['id']; ?>" class="menu-list">
                                        <div class="tep-item-info">
                                            <div class="tep-item-details">
                                                <strong><?php echo esc_html($menu['name']); ?></strong>
                                                <span class="tep-item-meta"><?php echo $menu['count']; ?> items</span>
                                            </div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- WooCommerce Tab -->
                        <?php if (class_exists('WooCommerce')): ?>
                        <div class="tep-tab-content" id="woocommerce-tab">
                            <div class="tep-content-section">
                                <h3><?php _e('WooCommerce Data', 'theme-exporter-pro'); ?></h3>
                                <div class="tep-wc-options">
                                    <label class="tep-checkbox-label">
                                        <input type="checkbox" name="wc_products" value="1">
                                        <?php _e('Products', 'theme-exporter-pro'); ?>
                                    </label>
                                    <label class="tep-checkbox-label">
                                        <input type="checkbox" name="wc_categories" value="1">
                                        <?php _e('Product Categories', 'theme-exporter-pro'); ?>
                                    </label>
                                    <label class="tep-checkbox-label">
                                        <input type="checkbox" name="wc_attributes" value="1">
                                        <?php _e('Product Attributes', 'theme-exporter-pro'); ?>
                                    </label>
                                    <label class="tep-checkbox-label">
                                        <input type="checkbox" name="wc_settings" value="1">
                                        <?php _e('WooCommerce Settings', 'theme-exporter-pro'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Export Options -->
                    <div class="tep-section">
                        <h3><?php _e('Export Options', 'theme-exporter-pro'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="selective_package_name"><?php _e('Package Name', 'theme-exporter-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="selective_package_name" name="package_name" class="regular-text" value="<?php echo esc_attr(get_stylesheet() . '-selective'); ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Include Theme Files', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="include_theme" value="1" checked>
                                        <?php _e('Include theme files with selected content', 'theme-exporter-pro'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Export Button -->
                    <div class="tep-actions">
                        <button type="submit" class="button button-primary button-large">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Selected Content', 'theme-exporter-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="tep-sidebar">
            <div class="tep-card">
                <h3><?php _e('Content Statistics', 'theme-exporter-pro'); ?></h3>
                <div class="tep-stats-list">
                    <?php if (isset($content_stats['post_types'])): ?>
                        <?php foreach ($content_stats['post_types'] as $post_type => $stats): ?>
                        <div class="tep-stat-item">
                            <strong><?php echo esc_html($stats['label']); ?>:</strong>
                            <span><?php echo $stats['total']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (isset($content_stats['media'])): ?>
                    <div class="tep-stat-item">
                        <strong><?php _e('Media Files', 'theme-exporter-pro'); ?>:</strong>
                        <span><?php echo $content_stats['media']['total']; ?> (<?php echo $content_stats['media']['size']; ?>)</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($content_stats['users'])): ?>
                    <div class="tep-stat-item">
                        <strong><?php _e('Users', 'theme-exporter-pro'); ?>:</strong>
                        <span><?php echo $content_stats['users']['total']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tep-card">
                <h3><?php _e('Selection Summary', 'theme-exporter-pro'); ?></h3>
                <div id="tep-selection-summary">
                    <p><?php _e('No items selected', 'theme-exporter-pro'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>