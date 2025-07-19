<?php
/**
 * Admin Export Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Theme Exporter Pro - Export', 'theme-exporter-pro'); ?></h1>
    
    <div class="tep-container">
        <div class="tep-main-content">
            <div class="tep-card">
                <h2><?php _e('Export Theme Package', 'theme-exporter-pro'); ?></h2>
                <p><?php _e('Create a complete theme package with templates, demo content, and required plugins.', 'theme-exporter-pro'); ?></p>
                
                <form id="tep-export-form" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('tep_nonce', 'tep_nonce'); ?>
                    
                    <!-- Package Information -->
                    <div class="tep-section">
                        <h3><?php _e('Package Information', 'theme-exporter-pro'); ?></h3>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="package_name"><?php _e('Package Name', 'theme-exporter-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="package_name" name="package_name" class="regular-text" value="<?php echo esc_attr(get_stylesheet()); ?>" required>
                                    <p class="description"><?php _e('Enter a name for your theme package', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="package_description"><?php _e('Package Description', 'theme-exporter-pro'); ?></label>
                                </th>
                                <td>
                                    <textarea id="package_description" name="package_description" class="large-text" rows="3"><?php echo esc_textarea(wp_get_theme()->get('Description')); ?></textarea>
                                    <p class="description"><?php _e('Describe your theme package', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="builder_type"><?php _e('Builder Type', 'theme-exporter-pro'); ?></label>
                                </th>
                                <td>
                                    <select id="builder_type" name="builder_type" required>
                                        <option value="elementor"><?php _e('Elementor', 'theme-exporter-pro'); ?></option>
                                        <option value="gutenberg"><?php _e('Gutenberg', 'theme-exporter-pro'); ?></option>
                                        <option value="both"><?php _e('Both Elementor & Gutenberg', 'theme-exporter-pro'); ?></option>
                                    </select>
                                    <p class="description"><?php _e('Select the page builder type for your templates', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Export Options -->
                    <div class="tep-section">
                        <h3><?php _e('Export Options', 'theme-exporter-pro'); ?></h3>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Theme Files', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="export_theme" value="1" checked>
                                        <?php _e('Export child theme files', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Include all theme files (style.css, functions.php, templates, etc.)', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Templates', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="export_templates" value="1" checked>
                                        <?php _e('Export page templates', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Export Elementor templates, Gutenberg blocks, and reusable templates', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Customizer Settings', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="export_customizer" value="1" checked>
                                        <?php _e('Export customizer settings', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Include theme customizer settings and theme mods', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Demo Content', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="export_content" value="1" checked>
                                        <?php _e('Export demo content', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Include posts, pages, and other content for demonstration', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Required Plugins', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="export_plugins" value="1" checked>
                                        <?php _e('Export plugin list', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Include list of required plugins for automatic installation', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Widgets', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="export_widgets" value="1" checked>
                                        <?php _e('Export widget settings', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Include widget configurations and sidebar assignments', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Export Button -->
                    <div class="tep-actions">
                        <button type="submit" class="button button-primary button-large">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Theme Package', 'theme-exporter-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="tep-sidebar">
            <div class="tep-card">
                <h3><?php _e('Export Information', 'theme-exporter-pro'); ?></h3>
                <div class="tep-info-list">
                    <div class="tep-info-item">
                        <strong><?php _e('Current Theme:', 'theme-exporter-pro'); ?></strong>
                        <span><?php echo wp_get_theme()->get('Name'); ?></span>
                    </div>
                    <div class="tep-info-item">
                        <strong><?php _e('Theme Version:', 'theme-exporter-pro'); ?></strong>
                        <span><?php echo wp_get_theme()->get('Version'); ?></span>
                    </div>
                    <div class="tep-info-item">
                        <strong><?php _e('WordPress Version:', 'theme-exporter-pro'); ?></strong>
                        <span><?php echo get_bloginfo('version'); ?></span>
                    </div>
                    <div class="tep-info-item">
                        <strong><?php _e('Active Plugins:', 'theme-exporter-pro'); ?></strong>
                        <span><?php echo count(get_option('active_plugins', array())); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="tep-card">
                <h3><?php _e('System Check', 'theme-exporter-pro'); ?></h3>
                <div class="tep-system-check">
                    <div class="tep-check-item <?php echo class_exists('ZipArchive') ? 'success' : 'error'; ?>">
                        <span class="dashicons <?php echo class_exists('ZipArchive') ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
                        <?php _e('ZIP Extension', 'theme-exporter-pro'); ?>
                    </div>
                    <div class="tep-check-item <?php echo is_writable(wp_upload_dir()['basedir']) ? 'success' : 'error'; ?>">
                        <span class="dashicons <?php echo is_writable(wp_upload_dir()['basedir']) ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
                        <?php _e('Upload Directory Writable', 'theme-exporter-pro'); ?>
                    </div>
                    <div class="tep-check-item <?php echo class_exists('Elementor\Plugin') ? 'success' : 'warning'; ?>">
                        <span class="dashicons <?php echo class_exists('Elementor\Plugin') ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                        <?php _e('Elementor Plugin', 'theme-exporter-pro'); ?>
                    </div>
                    <div class="tep-check-item success">
                        <span class="dashicons dashicons-yes"></span>
                        <?php _e('Gutenberg Support', 'theme-exporter-pro'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div id="tep-progress-modal" class="tep-modal" style="display: none;">
    <div class="tep-modal-content">
        <div class="tep-modal-header">
            <h3><?php _e('Exporting Theme Package', 'theme-exporter-pro'); ?></h3>
        </div>
        <div class="tep-modal-body">
            <div class="tep-progress-bar">
                <div class="tep-progress-fill"></div>
            </div>
            <div class="tep-progress-text">
                <?php _e('Preparing export...', 'theme-exporter-pro'); ?>
            </div>
        </div>
    </div>
</div>