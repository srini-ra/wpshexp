<?php
/**
 * Admin Support Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$logger = new TKP_Logger();
$recent_logs = $logger->get_recent_logs(100);
$log_stats = $logger->get_log_stats(7);
$error_handler = new TKP_Error_Handler($logger);
$error_stats = $error_handler->get_error_stats(7);
?>

<div class="wrap">
    <h1><?php _e('Theme Kit Pro - Logs & Support', 'theme-kit-pro'); ?></h1>
    
    <div class="tkp-container">
        <div class="tkp-main-content">
            <!-- System Status -->
            <div class="tkp-card">
                <h2><?php _e('System Status', 'theme-kit-pro'); ?></h2>
                
                <div class="tkp-system-status">
                    <div class="status-grid">
                        <div class="status-item">
                            <div class="status-icon success">✓</div>
                            <div class="status-info">
                                <strong><?php _e('Plugin Version', 'theme-kit-pro'); ?></strong>
                                <span><?php echo TKP_PLUGIN_VERSION; ?></span>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon <?php echo version_compare(PHP_VERSION, '8.0', '>=') ? 'success' : 'warning'; ?>">
                                <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? '✓' : '⚠'; ?>
                            </div>
                            <div class="status-info">
                                <strong><?php _e('PHP Version', 'theme-kit-pro'); ?></strong>
                                <span><?php echo PHP_VERSION; ?></span>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon success">✓</div>
                            <div class="status-info">
                                <strong><?php _e('WordPress Version', 'theme-kit-pro'); ?></strong>
                                <span><?php echo get_bloginfo('version'); ?></span>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon <?php echo ini_get('memory_limit') >= '256M' ? 'success' : 'warning'; ?>">
                                <?php echo ini_get('memory_limit') >= '256M' ? '✓' : '⚠'; ?>
                            </div>
                            <div class="status-info">
                                <strong><?php _e('Memory Limit', 'theme-kit-pro'); ?></strong>
                                <span><?php echo ini_get('memory_limit'); ?></span>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon <?php echo class_exists('ZipArchive') ? 'success' : 'error'; ?>">
                                <?php echo class_exists('ZipArchive') ? '✓' : '✗'; ?>
                            </div>
                            <div class="status-info">
                                <strong><?php _e('ZIP Extension', 'theme-kit-pro'); ?></strong>
                                <span><?php echo class_exists('ZipArchive') ? __('Available', 'theme-kit-pro') : __('Missing', 'theme-kit-pro'); ?></span>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon <?php echo extension_loaded('curl') ? 'success' : 'error'; ?>">
                                <?php echo extension_loaded('curl') ? '✓' : '✗'; ?>
                            </div>
                            <div class="status-info">
                                <strong><?php _e('cURL Extension', 'theme-kit-pro'); ?></strong>
                                <span><?php echo extension_loaded('curl') ? __('Available', 'theme-kit-pro') : __('Missing', 'theme-kit-pro'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Error Logs -->
            <div class="tkp-card">
                <div class="card-header">
                    <h2><?php _e('Recent Logs', 'theme-kit-pro'); ?></h2>
                    <div class="card-actions">
                        <button type="button" class="button" onclick="clearLogs()"><?php _e('Clear Logs', 'theme-kit-pro'); ?></button>
                        <button type="button" class="button" onclick="exportLogs()"><?php _e('Export Logs', 'theme-kit-pro'); ?></button>
                        <button type="button" class="button button-primary" onclick="refreshLogs()"><?php _e('Refresh', 'theme-kit-pro'); ?></button>
                    </div>
                </div>
                
                <div class="tkp-logs-container">
                    <div class="logs-filters">
                        <select id="log-level-filter">
                            <option value=""><?php _e('All Levels', 'theme-kit-pro'); ?></option>
                            <option value="debug"><?php _e('Debug', 'theme-kit-pro'); ?></option>
                            <option value="info"><?php _e('Info', 'theme-kit-pro'); ?></option>
                            <option value="notice"><?php _e('Notice', 'theme-kit-pro'); ?></option>
                            <option value="warning"><?php _e('Warning', 'theme-kit-pro'); ?></option>
                            <option value="error"><?php _e('Error', 'theme-kit-pro'); ?></option>
                            <option value="critical"><?php _e('Critical', 'theme-kit-pro'); ?></option>
                        </select>
                        
                        <input type="text" id="log-search" placeholder="<?php _e('Search logs...', 'theme-kit-pro'); ?>">
                    </div>
                    
                    <div class="logs-list" id="logs-list">
                        <?php if (!empty($recent_logs)): ?>
                            <?php foreach ($recent_logs as $log): ?>
                            <div class="log-entry log-<?php echo esc_attr($log->level); ?>" data-level="<?php echo esc_attr($log->level); ?>">
                                <div class="log-header">
                                    <span class="log-level level-<?php echo esc_attr($log->level); ?>"><?php echo strtoupper($log->level); ?></span>
                                    <span class="log-timestamp"><?php echo esc_html($log->timestamp); ?></span>
                                </div>
                                <div class="log-message"><?php echo esc_html($log->message); ?></div>
                                <?php if (!empty($log->context)): ?>
                                <div class="log-context">
                                    <button type="button" class="toggle-context"><?php _e('Show Context', 'theme-kit-pro'); ?></button>
                                    <div class="context-data" style="display: none;">
                                        <pre><?php echo esc_html(json_encode($log->context, JSON_PRETTY_PRINT)); ?></pre>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-logs">
                                <p><?php _e('No logs found', 'theme-kit-pro'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Troubleshooting Guide -->
            <div class="tkp-card">
                <h2><?php _e('Troubleshooting Guide', 'theme-kit-pro'); ?></h2>
                
                <div class="troubleshooting-sections">
                    <div class="trouble-section">
                        <h3><?php _e('Common Import Issues', 'theme-kit-pro'); ?></h3>
                        <div class="trouble-items">
                            <div class="trouble-item">
                                <h4><?php _e('Import fails with memory error', 'theme-kit-pro'); ?></h4>
                                <p><?php _e('Increase PHP memory limit to at least 512M or try importing smaller content batches.', 'theme-kit-pro'); ?></p>
                                <div class="trouble-actions">
                                    <button type="button" class="button" onclick="checkMemoryLimit()"><?php _e('Check Memory', 'theme-kit-pro'); ?></button>
                                </div>
                            </div>
                            
                            <div class="trouble-item">
                                <h4><?php _e('Images not importing correctly', 'theme-kit-pro'); ?></h4>
                                <p><?php _e('Check if cURL is enabled and your server can access external URLs. Enable localhost mode if testing locally.', 'theme-kit-pro'); ?></p>
                                <div class="trouble-actions">
                                    <button type="button" class="button" onclick="testImageImport()"><?php _e('Test Image Import', 'theme-kit-pro'); ?></button>
                                </div>
                            </div>
                            
                            <div class="trouble-item">
                                <h4><?php _e('Import timeout errors', 'theme-kit-pro'); ?></h4>
                                <p><?php _e('Increase max_execution_time or use batch processing for large imports.', 'theme-kit-pro'); ?></p>
                                <div class="trouble-actions">
                                    <button type="button" class="button" onclick="checkExecutionTime()"><?php _e('Check Timeout', 'theme-kit-pro'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="trouble-section">
                        <h3><?php _e('Export Problems', 'theme-kit-pro'); ?></h3>
                        <div class="trouble-items">
                            <div class="trouble-item">
                                <h4><?php _e('Export creates empty or corrupted files', 'theme-kit-pro'); ?></h4>
                                <p><?php _e('Check file permissions and available disk space. Ensure ZIP extension is properly installed.', 'theme-kit-pro'); ?></p>
                                <div class="trouble-actions">
                                    <button type="button" class="button" onclick="checkDiskSpace()"><?php _e('Check Disk Space', 'theme-kit-pro'); ?></button>
                                </div>
                            </div>
                            
                            <div class="trouble-item">
                                <h4><?php _e('Large exports fail', 'theme-kit-pro'); ?></h4>
                                <p><?php _e('Use selective export to reduce package size or increase server limits.', 'theme-kit-pro'); ?></p>
                                <div class="trouble-actions">
                                    <a href="<?php echo admin_url('admin.php?page=theme-kit-pro-selective'); ?>" class="button"><?php _e('Selective Export', 'theme-kit-pro'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="tkp-sidebar">
            <!-- Log Statistics -->
            <div class="tkp-card">
                <h3><?php _e('Log Statistics (7 days)', 'theme-kit-pro'); ?></h3>
                <div class="log-stats">
                    <?php if (!empty($log_stats)): ?>
                        <?php foreach ($log_stats as $stat): ?>
                        <div class="stat-item">
                            <span class="stat-level level-<?php echo esc_attr($stat->level); ?>"><?php echo ucfirst($stat->level); ?></span>
                            <span class="stat-count"><?php echo $stat->count; ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><?php _e('No logs in the last 7 days', 'theme-kit-pro'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="tkp-card">
                <h3><?php _e('Quick Actions', 'theme-kit-pro'); ?></h3>
                <div class="quick-actions">
                    <button type="button" class="button button-primary full-width" onclick="runCompatibilityCheck()">
                        <?php _e('Run Compatibility Check', 'theme-kit-pro'); ?>
                    </button>
                    <button type="button" class="button full-width" onclick="testSystemRequirements()">
                        <?php _e('Test System Requirements', 'theme-kit-pro'); ?>
                    </button>
                    <button type="button" class="button full-width" onclick="cleanupTempFiles()">
                        <?php _e('Cleanup Temp Files', 'theme-kit-pro'); ?>
                    </button>
                    <button type="button" class="button full-width" onclick="optimizeDatabase()">
                        <?php _e('Optimize Database', 'theme-kit-pro'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Support Information -->
            <div class="tkp-card">
                <h3><?php _e('Get Support', 'theme-kit-pro'); ?></h3>
                <div class="support-info">
                    <p><?php _e('Need help with Theme Kit Pro?', 'theme-kit-pro'); ?></p>
                    
                    <div class="support-links">
                        <a href="https://wpelance.com/support" target="_blank" class="support-link">
                            <span class="dashicons dashicons-sos"></span>
                            <?php _e('Get Support', 'theme-kit-pro'); ?>
                        </a>
                        
                        <a href="https://wpelance.com/docs/theme-kit-pro" target="_blank" class="support-link">
                            <span class="dashicons dashicons-book"></span>
                            <?php _e('Documentation', 'theme-kit-pro'); ?>
                        </a>
                        
                        <a href="https://wpelance.com/contact" target="_blank" class="support-link">
                            <span class="dashicons dashicons-email"></span>
                            <?php _e('Contact Us', 'theme-kit-pro'); ?>
                        </a>
                        
                        <a href="https://wpelance.com" target="_blank" class="support-link">
                            <span class="dashicons dashicons-admin-home"></span>
                            <?php _e('Visit WPelance', 'theme-kit-pro'); ?>
                        </a>
                    </div>
                    
                    <div class="plugin-info">
                        <p><strong><?php _e('Plugin Version:', 'theme-kit-pro'); ?></strong> <?php echo TKP_PLUGIN_VERSION; ?></p>
                        <p><strong><?php _e('Author:', 'theme-kit-pro'); ?></strong> <a href="https://wpelance.com" target="_blank">WPelance</a></p>
                    </div>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="tkp-card">
                <h3><?php _e('System Information', 'theme-kit-pro'); ?></h3>
                <div class="system-info">
                    <div class="info-item">
                        <strong><?php _e('Server:', 'theme-kit-pro'); ?></strong>
                        <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                    </div>
                    <div class="info-item">
                        <strong><?php _e('Database:', 'theme-kit-pro'); ?></strong>
                        <span><?php echo $GLOBALS['wpdb']->db_version(); ?></span>
                    </div>
                    <div class="info-item">
                        <strong><?php _e('Upload Max:', 'theme-kit-pro'); ?></strong>
                        <span><?php echo ini_get('upload_max_filesize'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong><?php _e('Post Max:', 'theme-kit-pro'); ?></strong>
                        <span><?php echo ini_get('post_max_size'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong><?php _e('Max Execution:', 'theme-kit-pro'); ?></strong>
                        <span><?php echo ini_get('max_execution_time'); ?>s</span>
                    </div>
                </div>
                
                <button type="button" class="button full-width" onclick="copySystemInfo()">
                    <?php _e('Copy System Info', 'theme-kit-pro'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Support page JavaScript functions
function clearLogs() {
    if (confirm('<?php _e("Are you sure you want to clear all logs?", "theme-kit-pro"); ?>')) {
        // AJAX call to clear logs
        jQuery.post(ajaxurl, {
            action: 'tkp_clear_logs',
            nonce: tkpAjax.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('<?php _e("Failed to clear logs", "theme-kit-pro"); ?>');
            }
        });
    }
}

function exportLogs() {
    window.open(ajaxurl + '?action=tkp_export_logs&nonce=' + tkpAjax.nonce);
}

function refreshLogs() {
    location.reload();
}

function runCompatibilityCheck() {
    // Show loading
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = '<?php _e("Checking...", "theme-kit-pro"); ?>';
    button.disabled = true;
    
    jQuery.post(ajaxurl, {
        action: 'tkp_compatibility_check',
        check_type: 'general',
        nonce: tkpAjax.nonce
    }, function(response) {
        button.textContent = originalText;
        button.disabled = false;
        
        if (response.success) {
            alert('<?php _e("Compatibility check completed. Check the logs for details.", "theme-kit-pro"); ?>');
            location.reload();
        } else {
            alert('<?php _e("Compatibility check failed", "theme-kit-pro"); ?>');
        }
    });
}

function copySystemInfo() {
    const systemInfo = document.querySelector('.system-info').innerText;
    const pluginInfo = document.querySelector('.plugin-info').innerText;
    const fullInfo = systemInfo + '\n\n' + pluginInfo;
    
    navigator.clipboard.writeText(fullInfo).then(function() {
        alert('<?php _e("System information copied to clipboard", "theme-kit-pro"); ?>');
    });
}

// Log filtering
document.getElementById('log-level-filter').addEventListener('change', function() {
    filterLogs();
});

document.getElementById('log-search').addEventListener('input', function() {
    filterLogs();
});

function filterLogs() {
    const levelFilter = document.getElementById('log-level-filter').value;
    const searchTerm = document.getElementById('log-search').value.toLowerCase();
    const logEntries = document.querySelectorAll('.log-entry');
    
    logEntries.forEach(function(entry) {
        const level = entry.getAttribute('data-level');
        const message = entry.querySelector('.log-message').textContent.toLowerCase();
        
        const levelMatch = !levelFilter || level === levelFilter;
        const searchMatch = !searchTerm || message.includes(searchTerm);
        
        entry.style.display = levelMatch && searchMatch ? 'block' : 'none';
    });
}

// Toggle context display
document.querySelectorAll('.toggle-context').forEach(function(button) {
    button.addEventListener('click', function() {
        const contextData = this.nextElementSibling;
        const isVisible = contextData.style.display !== 'none';
        
        contextData.style.display = isVisible ? 'none' : 'block';
        this.textContent = isVisible ? '<?php _e("Show Context", "theme-kit-pro"); ?>' : '<?php _e("Hide Context", "theme-kit-pro"); ?>';
    });
});
</script>