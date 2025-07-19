// WordPress Plugin Manager JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeTabs();
    initializeFileExplorer();
    initializeTestRunner();
    initializeDeployment();
});

// Tab Management
function initializeTabs() {
    const navTabs = document.querySelectorAll('.nav-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    navTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            navTabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        });
    });
}

// File Explorer
function initializeFileExplorer() {
    const fileItems = document.querySelectorAll('.file-item.file');
    const previewContent = document.querySelector('.preview-content');
    const filePath = document.querySelector('.file-path');

    fileItems.forEach(item => {
        item.addEventListener('click', function() {
            const fileName = this.getAttribute('data-file');
            
            // Remove active class from all file items
            fileItems.forEach(f => f.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Update file path
            filePath.textContent = `theme-exporter-pro/${fileName}`;
            
            // Show file preview
            showFilePreview(fileName);
        });
    });
}

function showFilePreview(fileName) {
    const previewContent = document.querySelector('.preview-content');
    
    // Sample file content for demonstration
    const fileContents = {
        'theme-exporter.php': `<?php
/**
 * Plugin Name: Theme Exporter Pro
 * Description: Export child themes as packages with templates, plugins, and demo data
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TEP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TEP_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Main plugin class
class ThemeExporterPro {
    // Plugin initialization code...
}`,
        'class-exporter.php': `<?php
/**
 * Theme Exporter Class
 * Handles the export functionality for theme packages
 */

class TEP_Exporter {
    
    private $temp_dir;
    private $upload_dir;
    private $export_data;
    
    public function __construct() {
        // Constructor code...
    }
    
    public function export_theme_package($options) {
        // Export logic...
    }
}`,
        'class-importer.php': `<?php
/**
 * Theme Importer Class
 * Handles the import functionality for theme packages
 */

class TEP_Importer {
    
    public function import_theme_package($files, $options) {
        // Import logic...
    }
}`,
        'admin.css': `/**
 * Admin CSS for Theme Exporter Pro
 */

.tep-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.tep-main-content {
    flex: 1;
}

.tep-sidebar {
    width: 300px;
    flex-shrink: 0;
}`,
        'admin.js': `/**
 * Admin JavaScript for Theme Exporter Pro
 */

jQuery(document).ready(function($) {
    'use strict';
    
    var TEP = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Event binding code...
        }
    };
    
    TEP.init();
});`,
        'readme.txt': `=== Theme Exporter Pro ===
Contributors: yourname
Tags: theme, export, import, elementor, gutenberg
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Export child themes as complete packages with templates, plugins, and demo data.

== Description ==

Theme Exporter Pro is a powerful WordPress plugin that allows you to export child themes as complete packages...`
    };
    
    const content = fileContents[fileName] || 'File content not available for preview.';
    
    previewContent.innerHTML = `
        <div style="padding: 1rem; font-family: 'Courier New', monospace; font-size: 0.875rem; line-height: 1.5; background: #f8f9fa; color: #2d3748; white-space: pre-wrap; height: 100%; overflow: auto;">${content}</div>
    `;
}

// Test Runner
function initializeTestRunner() {
    // Test runner is initialized but tests are simulated
    console.log('Test runner initialized');
}

function runTests() {
    showLoadingState('Running all tests...');
    
    setTimeout(() => {
        hideLoadingState();
        showNotification('All tests completed successfully!', 'success');
    }, 3000);
}

function runUnitTests() {
    showLoadingState('Running unit tests...');
    
    setTimeout(() => {
        hideLoadingState();
        showNotification('Unit tests completed successfully!', 'success');
    }, 2000);
}

function runIntegrationTests() {
    showLoadingState('Running integration tests...');
    
    setTimeout(() => {
        hideLoadingState();
        showNotification('Integration tests completed successfully!', 'success');
    }, 2500);
}

function runPerformanceTests() {
    showLoadingState('Running performance tests...');
    
    setTimeout(() => {
        hideLoadingState();
        showNotification('Performance tests completed successfully!', 'success');
    }, 3500);
}

// Deployment Functions
function initializeDeployment() {
    console.log('Deployment system initialized');
}

function deployToWordPress() {
    showLoadingState('Preparing WordPress submission...');
    
    setTimeout(() => {
        hideLoadingState();
        showNotification('Ready for WordPress Plugin Directory submission!', 'success');
    }, 2000);
}

function createGitHubRelease() {
    showLoadingState('Creating GitHub release...');
    
    setTimeout(() => {
        hideLoadingState();
        showNotification('GitHub release created successfully!', 'success');
        
        // Update deployment status
        const githubStatus = document.querySelector('.deployment-card:nth-child(2) .deployment-status');
        githubStatus.textContent = 'Deployed';
        githubStatus.classList.remove('success');
        githubStatus.classList.add('success');
    }, 1500);
}

function createPrivatePackage() {
    showLoadingState('Creating private package...');
    
    setTimeout(() => {
        hideLoadingState();
        showNotification('Private package created successfully!', 'success');
        
        // Trigger download
        const link = document.createElement('a');
        link.href = 'data:text/plain;charset=utf-8,Theme Exporter Pro - Private Package Created';
        link.download = 'theme-exporter-pro-private.zip';
        link.click();
    }, 2000);
}

// Utility Functions
function showLoadingState(message) {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loading-overlay';
    loadingOverlay.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
            <div style="background: white; padding: 2rem; border-radius: 0.5rem; text-align: center; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <div style="width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top: 3px solid #4299e1; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div>
                <p style="margin: 0; color: #4a5568; font-weight: 500;">${message}</p>
            </div>
        </div>
    `;
    document.body.appendChild(loadingOverlay);
}

function hideLoadingState() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div style="position: fixed; top: 20px; right: 20px; background: ${type === 'success' ? '#48bb78' : '#4299e1'}; color: white; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 1000; max-width: 300px;">
            <p style="margin: 0; font-weight: 500;">${message}</p>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Plugin Analytics (simulated)
function trackEvent(eventName, eventData) {
    console.log(`Event tracked: ${eventName}`, eventData);
}

// Initialize analytics tracking
document.addEventListener('click', function(e) {
    if (e.target.matches('.nav-tab')) {
        trackEvent('tab_clicked', { tab: e.target.getAttribute('data-tab') });
    }
    
    if (e.target.matches('.btn')) {
        trackEvent('button_clicked', { button: e.target.textContent.trim() });
    }
});

// Plugin updater simulation
function checkForUpdates() {
    setTimeout(() => {
        showNotification('Plugin is up to date!', 'success');
    }, 1000);
}

// Auto-check for updates on page load
setTimeout(checkForUpdates, 5000);

// Export functions for global access
window.runTests = runTests;
window.runUnitTests = runUnitTests;
window.runIntegrationTests = runIntegrationTests;
window.runPerformanceTests = runPerformanceTests;
window.deployToWordPress = deployToWordPress;
window.createGitHubRelease = createGitHubRelease;
window.createPrivatePackage = createPrivatePackage;