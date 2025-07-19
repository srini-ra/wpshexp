<?php
/**
 * Admin Import Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Theme Exporter Pro - Import', 'theme-exporter-pro'); ?></h1>
    
    <div class="tep-container">
        <div class="tep-main-content">
            <div class="tep-card">
                <h2><?php _e('Import Theme Package', 'theme-exporter-pro'); ?></h2>
                <p><?php _e('Import a complete theme package with templates, demo content, and required plugins.', 'theme-exporter-pro'); ?></p>
                
                <form id="tep-import-form" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('tep_nonce', 'tep_nonce'); ?>
                    
                    <!-- Package Upload -->
                    <div class="tep-section">
                        <h3><?php _e('Select Package File', 'theme-exporter-pro'); ?></h3>
                        
                        <div class="tep-upload-area">
                            <input type="file" id="package_file" name="package_file" accept=".zip" required>
                            <div class="tep-upload-text">
                                <span class="dashicons dashicons-upload"></span>
                                <p><?php _e('Select a theme package file (.zip)', 'theme-exporter-pro'); ?></p>
                                <p class="description"><?php _e('Maximum file size: ', 'theme-exporter-pro'); ?><?php echo wp_max_upload_size(); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Import Options -->
                    <div class="tep-section" id="tep-import-options" style="display: none;">
                        <h3><?php _e('Import Options', 'theme-exporter-pro'); ?></h3>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Theme Files', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="import_theme" value="1" checked>
                                        <?php _e('Import and activate theme', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Install theme files and activate the theme', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Required Plugins', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="install_plugins" value="1" checked>
                                        <?php _e('Install required plugins', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Automatically install and activate required plugins', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Customizer Settings', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="import_customizer" value="1" checked>
                                        <?php _e('Import customizer settings', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Apply theme customizer settings and theme mods', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Demo Content', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="import_content" value="1" checked>
                                        <?php _e('Import demo content', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Import posts, pages, and other demo content', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Templates', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="import_templates" value="1" checked>
                                        <?php _e('Import page templates', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Import Elementor templates, Gutenberg blocks, and reusable templates', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Widgets', 'theme-exporter-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="import_widgets" value="1" checked>
                                        <?php _e('Import widget settings', 'theme-exporter-pro'); ?>
                                    </label>
                                    <p class="description"><?php _e('Apply widget configurations and sidebar assignments', 'theme-exporter-pro'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Import Button -->
                    <div class="tep-actions">
                        <button type="submit" class="button button-primary button-large" disabled>
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Import Theme Package', 'theme-exporter-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="tep-sidebar">
            <div class="tep-card">
                <h3><?php _e('Package Information', 'theme-exporter-pro'); ?></h3>
                <div id="tep-package-info">
                    <p><?php _e('Select a package file to view information', 'theme-exporter-pro'); ?></p>
                </div>
            </div>
            
            <div class="tep-card">
                <h3><?php _e('Import Notes', 'theme-exporter-pro'); ?></h3>
                <div class="tep-notes">
                    <p><strong><?php _e('Important:', 'theme-exporter-pro'); ?></strong></p>
                    <ul>
                        <li><?php _e('Make sure to backup your current site before importing', 'theme-exporter-pro'); ?></li>
                        <li><?php _e('Plugin installation may take several minutes', 'theme-exporter-pro'); ?></li>
                        <li><?php _e('Some plugins may require manual configuration', 'theme-exporter-pro'); ?></li>
                        <li><?php _e('Demo content will be added to existing content', 'theme-exporter-pro'); ?></li>
                    </ul>
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
                    <div class="tep-check-item <?php echo current_user_can('install_plugins') ? 'success' : 'error'; ?>">
                        <span class="dashicons <?php echo current_user_can('install_plugins') ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
                        <?php _e('Plugin Installation Permissions', 'theme-exporter-pro'); ?>
                    </div>
                    <div class="tep-check-item <?php echo current_user_can('switch_themes') ? 'success' : 'error'; ?>">
                        <span class="dashicons <?php echo current_user_can('switch_themes') ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
                        <?php _e('Theme Installation Permissions', 'theme-exporter-pro'); ?>
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
            <h3><?php _e('Importing Theme Package', 'theme-exporter-pro'); ?></h3>
        </div>
        <div class="tep-modal-body">
            <div class="tep-progress-bar">
                <div class="tep-progress-fill"></div>
            </div>
            <div class="tep-progress-text">
                <?php _e('Preparing import...', 'theme-exporter-pro'); ?>
            </div>
        </div>
    </div>
</div>