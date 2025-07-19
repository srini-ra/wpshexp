/**
 * Admin JavaScript for Theme Exporter Pro
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize
    TEP.init();
    
    var TEP = {
        
        // Initialize the plugin
        init: function() {
            this.bindEvents();
            this.setupFileUpload();
            this.checkSystemRequirements();
        },
        
        // Bind events
        bindEvents: function() {
            // Export form submission
            $('#tep-export-form').on('submit', this.handleExport.bind(this));
            
            // Import form submission
            $('#tep-import-form').on('submit', this.handleImport.bind(this));
            
            // File upload change
            $('#package_file').on('change', this.handleFileSelect.bind(this));
            
            // Builder type change
            $('#builder_type').on('change', this.handleBuilderTypeChange.bind(this));
        },
        
        // Setup file upload
        setupFileUpload: function() {
            var uploadArea = $('.tep-upload-area');
            var fileInput = $('#package_file');
            
            // Drag and drop events
            uploadArea.on('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });
            
            uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });
            
            uploadArea.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
                
                var files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    fileInput[0].files = files;
                    fileInput.trigger('change');
                }
            });
        },
        
        // Handle export
        handleExport: function(e) {
            e.preventDefault();
            
            var $form = $(e.target);
            var formData = new FormData($form[0]);
            formData.append('action', 'tep_export_theme');
            
            this.showProgressModal(tepAjax.strings.exporting);
            this.updateProgress(0, 'Preparing export...');
            
            $.ajax({
                url: tepAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    this.hideProgressModal();
                    
                    if (response.success) {
                        this.showMessage('success', response.message);
                        if (response.download_url) {
                            this.downloadFile(response.download_url);
                        }
                    } else {
                        this.showMessage('error', response.message);
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    this.hideProgressModal();
                    this.showMessage('error', 'Export failed: ' + error);
                }.bind(this),
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = (e.loaded / e.total) * 100;
                            this.updateProgress(percentComplete, 'Exporting...');
                        }
                    }.bind(this), false);
                    return xhr;
                }.bind(this)
            });
        },
        
        // Handle import
        handleImport: function(e) {
            e.preventDefault();
            
            var $form = $(e.target);
            var formData = new FormData($form[0]);
            formData.append('action', 'tep_import_theme');
            
            this.showProgressModal(tepAjax.strings.importing);
            this.updateProgress(0, 'Preparing import...');
            
            $.ajax({
                url: tepAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    this.hideProgressModal();
                    
                    if (response.success) {
                        this.showMessage('success', response.message);
                        this.showImportResults(response.results);
                    } else {
                        this.showMessage('error', response.message);
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    this.hideProgressModal();
                    this.showMessage('error', 'Import failed: ' + error);
                }.bind(this),
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percentComplete = (e.loaded / e.total) * 100;
                            this.updateProgress(percentComplete, 'Importing...');
                        }
                    }.bind(this), false);
                    return xhr;
                }.bind(this)
            });
        },
        
        // Handle file selection
        handleFileSelect: function(e) {
            var file = e.target.files[0];
            var $button = $('#tep-import-form button[type="submit"]');
            var $options = $('#tep-import-options');
            
            if (file) {
                // Validate file type
                if (file.type !== 'application/zip' && !file.name.endsWith('.zip')) {
                    this.showMessage('error', 'Please select a valid ZIP file.');
                    $button.prop('disabled', true);
                    return;
                }
                
                // Update upload text
                $('.tep-upload-text p').first().text(file.name);
                
                // Enable import button
                $button.prop('disabled', false);
                
                // Show import options
                $options.slideDown();
                
                // Try to read package info
                this.readPackageInfo(file);
            } else {
                $button.prop('disabled', true);
                $options.slideUp();
            }
        },
        
        // Handle builder type change
        handleBuilderTypeChange: function(e) {
            var builderType = $(e.target).val();
            var $templateCheckbox = $('input[name="export_templates"]');
            
            if (builderType === 'elementor') {
                $templateCheckbox.next('label').text('Export Elementor templates');
            } else if (builderType === 'gutenberg') {
                $templateCheckbox.next('label').text('Export Gutenberg blocks and templates');
            } else {
                $templateCheckbox.next('label').text('Export Elementor templates and Gutenberg blocks');
            }
        },
        
        // Read package info
        readPackageInfo: function(file) {
            // This would require additional libraries to read ZIP files
            // For now, just show placeholder info
            var infoHtml = '<div class="tep-info-list">' +
                '<div class="tep-info-item">' +
                '<strong>File Name:</strong>' +
                '<span>' + file.name + '</span>' +
                '</div>' +
                '<div class="tep-info-item">' +
                '<strong>File Size:</strong>' +
                '<span>' + this.formatFileSize(file.size) + '</span>' +
                '</div>' +
                '<div class="tep-info-item">' +
                '<strong>Last Modified:</strong>' +
                '<span>' + new Date(file.lastModified).toLocaleDateString() + '</span>' +
                '</div>' +
                '</div>';
            
            $('#tep-package-info').html(infoHtml);
        },
        
        // Format file size
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        // Show progress modal
        showProgressModal: function(title) {
            var modal = $('#tep-progress-modal');
            
            if (modal.length === 0) {
                modal = $('<div id="tep-progress-modal" class="tep-modal">' +
                    '<div class="tep-modal-content">' +
                    '<div class="tep-modal-header">' +
                    '<h3></h3>' +
                    '</div>' +
                    '<div class="tep-modal-body">' +
                    '<div class="tep-progress-bar">' +
                    '<div class="tep-progress-fill"></div>' +
                    '</div>' +
                    '<div class="tep-progress-text"></div>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
                
                $('body').append(modal);
            }
            
            modal.find('.tep-modal-header h3').text(title);
            modal.show();
        },
        
        // Hide progress modal
        hideProgressModal: function() {
            $('#tep-progress-modal').hide();
        },
        
        // Update progress
        updateProgress: function(percent, text) {
            $('.tep-progress-fill').css('width', percent + '%');
            $('.tep-progress-text').text(text);
        },
        
        // Show message
        showMessage: function(type, message) {
            var messageHtml = '<div class="tep-message ' + type + '">' + message + '</div>';
            var $container = $('.tep-main-content');
            
            // Remove existing messages
            $container.find('.tep-message').remove();
            
            // Add new message
            $container.prepend(messageHtml);
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(function() {
                    $('.tep-message.success').fadeOut();
                }, 5000);
            }
        },
        
        // Show import results
        showImportResults: function(results) {
            if (!results) return;
            
            var resultsHtml = '<div class="tep-import-results">';
            resultsHtml += '<h3>Import Results:</h3>';
            resultsHtml += '<ul>';
            
            $.each(results, function(key, result) {
                if (result.success) {
                    resultsHtml += '<li class="success">' + result.message + '</li>';
                } else {
                    resultsHtml += '<li class="error">' + result.message + '</li>';
                }
            });
            
            resultsHtml += '</ul></div>';
            
            $('.tep-main-content').append(resultsHtml);
        },
        
        // Download file
        downloadFile: function(url) {
            var link = document.createElement('a');
            link.href = url;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        
        // Check system requirements
        checkSystemRequirements: function() {
            var checks = $('.tep-check-item');
            
            checks.each(function() {
                var $item = $(this);
                var isSuccess = $item.hasClass('success');
                var isError = $item.hasClass('error');
                
                if (isError) {
                    $item.attr('title', 'This requirement is not met. Some features may not work properly.');
                }
            });
        }
    };
    
    // Global TEP object
    window.TEP = TEP;
});