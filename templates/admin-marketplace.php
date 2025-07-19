<?php
/**
 * Admin Marketplace Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$marketplace = new TEP_Marketplace_Integration();
$registered_marketplaces = $marketplace->get_registered_marketplaces();
$distribution_logs = $marketplace->get_distribution_logs(20);
?>

<div class="wrap">
    <h1><?php _e('Theme Exporter Pro - Marketplace Integration', 'theme-exporter-pro'); ?></h1>
    
    <div class="tep-container">
        <div class="tep-main-content">
            <!-- Marketplace Configuration -->
            <div class="tep-card">
                <h2><?php _e('Marketplace Configuration', 'theme-exporter-pro'); ?></h2>
                <p><?php _e('Configure your marketplace accounts for automatic package distribution.', 'theme-exporter-pro'); ?></p>
                
                <form id="tep-marketplace-form" method="post">
                    <?php wp_nonce_field('tep_nonce', 'tep_nonce'); ?>
                    
                    <div class="tep-marketplace-tabs">
                        <nav class="tep-tab-nav">
                            <button type="button" class="tep-tab-button active" data-tab="envato"><?php _e('Envato Market', 'theme-exporter-pro'); ?></button>
                            <button type="button" class="tep-tab-button" data-tab="templatemonster"><?php _e('TemplateMonster', 'theme-exporter-pro'); ?></button>
                            <button type="button" class="tep-tab-button" data-tab="creative-market"><?php _e('Creative Market', 'theme-exporter-pro'); ?></button>
                            <button type="button" class="tep-tab-button" data-tab="custom"><?php _e('Custom Marketplace', 'theme-exporter-pro'); ?></button>
                        </nav>
                        
                        <!-- Envato Market Tab -->
                        <div class="tep-tab-content active" id="envato-tab">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Enable Envato Market', 'theme-exporter-pro'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="marketplaces[envato][enabled]" value="1" <?php checked(isset($registered_marketplaces['envato']['enabled']) && $registered_marketplaces['envato']['enabled']); ?>>
                                            <?php _e('Enable automatic uploads to Envato Market', 'theme-exporter-pro'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="envato_api_key"><?php _e('API Key', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" id="envato_api_key" name="marketplaces[envato][api_key]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['envato']['api_key'] ?? ''); ?>">
                                        <p class="description"><?php _e('Your Envato API personal token', 'theme-exporter-pro'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="envato_username"><?php _e('Username', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="envato_username" name="marketplaces[envato][username]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['envato']['username'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Auto Upload', 'theme-exporter-pro'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="marketplaces[envato][auto_upload]" value="1" <?php checked(isset($registered_marketplaces['envato']['auto_upload']) && $registered_marketplaces['envato']['auto_upload']); ?>>
                                            <?php _e('Automatically upload new packages', 'theme-exporter-pro'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="envato_regular_price"><?php _e('Regular License Price', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="envato_regular_price" name="marketplaces[envato][pricing][regular_license]" class="small-text" value="<?php echo esc_attr($registered_marketplaces['envato']['pricing']['regular_license'] ?? ''); ?>" step="0.01" min="0">
                                        <span>$</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="envato_extended_price"><?php _e('Extended License Price', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="envato_extended_price" name="marketplaces[envato][pricing][extended_license]" class="small-text" value="<?php echo esc_attr($registered_marketplaces['envato']['pricing']['extended_license'] ?? ''); ?>" step="0.01" min="0">
                                        <span>$</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- TemplateMonster Tab -->
                        <div class="tep-tab-content" id="templatemonster-tab">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Enable TemplateMonster', 'theme-exporter-pro'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="marketplaces[templatemonster][enabled]" value="1" <?php checked(isset($registered_marketplaces['templatemonster']['enabled']) && $registered_marketplaces['templatemonster']['enabled']); ?>>
                                            <?php _e('Enable automatic uploads to TemplateMonster', 'theme-exporter-pro'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="tm_api_key"><?php _e('API Key', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" id="tm_api_key" name="marketplaces[templatemonster][api_key]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['templatemonster']['api_key'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="tm_username"><?php _e('Username', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="tm_username" name="marketplaces[templatemonster][username]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['templatemonster']['username'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Auto Upload', 'theme-exporter-pro'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="marketplaces[templatemonster][auto_upload]" value="1" <?php checked(isset($registered_marketplaces['templatemonster']['auto_upload']) && $registered_marketplaces['templatemonster']['auto_upload']); ?>>
                                            <?php _e('Automatically upload new packages', 'theme-exporter-pro'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Creative Market Tab -->
                        <div class="tep-tab-content" id="creative-market-tab">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Enable Creative Market', 'theme-exporter-pro'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="marketplaces[creative_market][enabled]" value="1" <?php checked(isset($registered_marketplaces['creative_market']['enabled']) && $registered_marketplaces['creative_market']['enabled']); ?>>
                                            <?php _e('Enable automatic uploads to Creative Market', 'theme-exporter-pro'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="cm_api_key"><?php _e('API Key', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" id="cm_api_key" name="marketplaces[creative_market][api_key]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['creative_market']['api_key'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="cm_username"><?php _e('Shop Name', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="cm_username" name="marketplaces[creative_market][username]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['creative_market']['username'] ?? ''); ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Custom Marketplace Tab -->
                        <div class="tep-tab-content" id="custom-tab">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Enable Custom Marketplace', 'theme-exporter-pro'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="marketplaces[custom][enabled]" value="1" <?php checked(isset($registered_marketplaces['custom']['enabled']) && $registered_marketplaces['custom']['enabled']); ?>>
                                            <?php _e('Enable custom marketplace integration', 'theme-exporter-pro'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="custom_name"><?php _e('Marketplace Name', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="custom_name" name="marketplaces[custom][name]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['custom']['name'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="custom_api_url"><?php _e('API URL', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="url" id="custom_api_url" name="marketplaces[custom][api_url]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['custom']['api_url'] ?? ''); ?>">
                                        <p class="description"><?php _e('Base URL for your marketplace API', 'theme-exporter-pro'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="custom_api_key"><?php _e('API Key', 'theme-exporter-pro'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password" id="custom_api_key" name="marketplaces[custom][api_key]" class="regular-text" value="<?php echo esc_attr($registered_marketplaces['custom']['api_key'] ?? ''); ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tep-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Save Marketplace Settings', 'theme-exporter-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Package Upload -->
            <div class="tep-card">
                <h2><?php _e('Upload Package to Marketplace', 'theme-exporter-pro'); ?></h2>
                
                <form id="tep-marketplace-upload-form" method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('tep_nonce', 'tep_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="upload_package_file"><?php _e('Package File', 'theme-exporter-pro'); ?></label>
                            </th>
                            <td>
                                <input type="file" id="upload_package_file" name="package_file" accept=".zip" required>
                                <p class="description"><?php _e('Select the theme package ZIP file to upload', 'theme-exporter-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="upload_marketplace"><?php _e('Marketplace', 'theme-exporter-pro'); ?></label>
                            </th>
                            <td>
                                <select id="upload_marketplace" name="marketplace_id" required>
                                    <option value=""><?php _e('Select Marketplace', 'theme-exporter-pro'); ?></option>
                                    <?php foreach ($registered_marketplaces as $id => $marketplace): ?>
                                        <?php if ($marketplace['enabled']): ?>
                                        <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($marketplace['name']); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="upload_package_name"><?php _e('Package Name', 'theme-exporter-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="upload_package_name" name="package_name" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="upload_package_description"><?php _e('Description', 'theme-exporter-pro'); ?></label>
                            </th>
                            <td>
                                <textarea id="upload_package_description" name="package_description" class="large-text" rows="4" required></textarea>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="upload_category"><?php _e('Category', 'theme-exporter-pro'); ?></label>
                            </th>
                            <td>
                                <select id="upload_category" name="category">
                                    <option value="wordpress-themes"><?php _e('WordPress Themes', 'theme-exporter-pro'); ?></option>
                                    <option value="elementor-templates"><?php _e('Elementor Templates', 'theme-exporter-pro'); ?></option>
                                    <option value="gutenberg-blocks"><?php _e('Gutenberg Blocks', 'theme-exporter-pro'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="upload_tags"><?php _e('Tags', 'theme-exporter-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="upload_tags" name="tags" class="regular-text" placeholder="wordpress, theme, responsive">
                                <p class="description"><?php _e('Comma-separated tags', 'theme-exporter-pro'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="tep-actions">
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Upload to Marketplace', 'theme-exporter-pro'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="tep-sidebar">
            <div class="tep-card">
                <h3><?php _e('Distribution Logs', 'theme-exporter-pro'); ?></h3>
                <div class="tep-logs-list">
                    <?php if (!empty($distribution_logs)): ?>
                        <?php foreach ($distribution_logs as $log): ?>
                        <div class="tep-log-item <?php echo $log['success'] ? 'success' : 'error'; ?>">
                            <div class="tep-log-header">
                                <strong><?php echo esc_html($log['package']); ?></strong>
                                <span class="tep-log-date"><?php echo date('M j, H:i', strtotime($log['timestamp'])); ?></span>
                            </div>
                            <div class="tep-log-details">
                                <span class="tep-marketplace"><?php echo esc_html(ucfirst($log['marketplace'])); ?></span>
                                <span class="tep-log-status <?php echo $log['success'] ? 'success' : 'error'; ?>">
                                    <?php echo $log['success'] ? '✓' : '✗'; ?>
                                </span>
                            </div>
                            <div class="tep-log-message"><?php echo esc_html($log['message']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><?php _e('No distribution logs yet', 'theme-exporter-pro'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="tep-card">
                <h3><?php _e('Marketplace Status', 'theme-exporter-pro'); ?></h3>
                <div class="tep-marketplace-status">
                    <?php foreach ($registered_marketplaces as $id => $marketplace): ?>
                    <div class="tep-status-item">
                        <div class="tep-status-indicator <?php echo $marketplace['enabled'] ? 'enabled' : 'disabled'; ?>"></div>
                        <div class="tep-status-info">
                            <strong><?php echo esc_html($marketplace['name']); ?></strong>
                            <span><?php echo $marketplace['enabled'] ? __('Enabled', 'theme-exporter-pro') : __('Disabled', 'theme-exporter-pro'); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>