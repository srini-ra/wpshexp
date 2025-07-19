<?php
/**
 * Marketplace Integration Class
 * 
 * Handles marketplace integration for package distribution
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_Marketplace_Integration {
    
    private $api_endpoints = array(
        'envato' => 'https://api.envato.com/v3/',
        'templatemonster' => 'https://api.templatemonster.com/v1/',
        'creative_market' => 'https://api.creativemarket.com/v1/',
        'custom' => ''
    );
    
    /**
     * Register marketplace
     */
    public function register_marketplace($marketplace_data) {
        $marketplaces = get_option('tep_marketplaces', array());
        
        $marketplace_id = sanitize_key($marketplace_data['name']);
        
        $marketplaces[$marketplace_id] = array(
            'name' => sanitize_text_field($marketplace_data['name']),
            'api_url' => esc_url_raw($marketplace_data['api_url']),
            'api_key' => sanitize_text_field($marketplace_data['api_key']),
            'api_secret' => sanitize_text_field($marketplace_data['api_secret']),
            'username' => sanitize_text_field($marketplace_data['username']),
            'enabled' => (bool) $marketplace_data['enabled'],
            'auto_upload' => (bool) $marketplace_data['auto_upload'],
            'categories' => array_map('sanitize_text_field', $marketplace_data['categories'] ?? array()),
            'tags' => array_map('sanitize_text_field', $marketplace_data['tags'] ?? array()),
            'pricing' => array(
                'regular_license' => floatval($marketplace_data['pricing']['regular_license'] ?? 0),
                'extended_license' => floatval($marketplace_data['pricing']['extended_license'] ?? 0)
            )
        );
        
        update_option('tep_marketplaces', $marketplaces);
        
        return $marketplace_id;
    }
    
    /**
     * Upload package to marketplace
     */
    public function upload_to_marketplace($package_path, $marketplace_id, $package_data) {
        $marketplaces = get_option('tep_marketplaces', array());
        
        if (!isset($marketplaces[$marketplace_id])) {
            return array('success' => false, 'message' => __('Marketplace not found', 'theme-exporter-pro'));
        }
        
        $marketplace = $marketplaces[$marketplace_id];
        
        switch ($marketplace_id) {
            case 'envato':
                return $this->upload_to_envato($package_path, $marketplace, $package_data);
            case 'templatemonster':
                return $this->upload_to_templatemonster($package_path, $marketplace, $package_data);
            case 'creative_market':
                return $this->upload_to_creative_market($package_path, $marketplace, $package_data);
            default:
                return $this->upload_to_custom_marketplace($package_path, $marketplace, $package_data);
        }
    }
    
    /**
     * Upload to Envato (ThemeForest/CodeCanyon)
     */
    private function upload_to_envato($package_path, $marketplace, $package_data) {
        $api_url = $this->api_endpoints['envato'] . 'market/private/user/upload';
        
        $headers = array(
            'Authorization' => 'Bearer ' . $marketplace['api_key'],
            'Content-Type' => 'multipart/form-data'
        );
        
        $body = array(
            'item_name' => $package_data['name'],
            'item_description' => $package_data['description'],
            'category' => $package_data['category'],
            'tags' => implode(',', $package_data['tags']),
            'price_regular' => $marketplace['pricing']['regular_license'],
            'price_extended' => $marketplace['pricing']['extended_license'],
            'file' => new CURLFile($package_path, 'application/zip', basename($package_path))
        );
        
        $response = $this->make_api_request($api_url, $headers, $body, 'POST');
        
        if ($response && isset($response['success']) && $response['success']) {
            return array(
                'success' => true,
                'message' => __('Package uploaded to Envato successfully', 'theme-exporter-pro'),
                'item_id' => $response['item_id'],
                'preview_url' => $response['preview_url']
            );
        }
        
        return array(
            'success' => false,
            'message' => $response['message'] ?? __('Failed to upload to Envato', 'theme-exporter-pro')
        );
    }
    
    /**
     * Upload to TemplateMonster
     */
    private function upload_to_templatemonster($package_path, $marketplace, $package_data) {
        $api_url = $this->api_endpoints['templatemonster'] . 'products/upload';
        
        $headers = array(
            'Authorization' => 'Bearer ' . $marketplace['api_key'],
            'Content-Type' => 'multipart/form-data'
        );
        
        $body = array(
            'title' => $package_data['name'],
            'description' => $package_data['description'],
            'category_id' => $package_data['category'],
            'tags' => $package_data['tags'],
            'price' => $marketplace['pricing']['regular_license'],
            'package' => new CURLFile($package_path, 'application/zip', basename($package_path))
        );
        
        $response = $this->make_api_request($api_url, $headers, $body, 'POST');
        
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return array(
                'success' => true,
                'message' => __('Package uploaded to TemplateMonster successfully', 'theme-exporter-pro'),
                'product_id' => $response['product_id'],
                'preview_url' => $response['preview_url']
            );
        }
        
        return array(
            'success' => false,
            'message' => $response['message'] ?? __('Failed to upload to TemplateMonster', 'theme-exporter-pro')
        );
    }
    
    /**
     * Upload to Creative Market
     */
    private function upload_to_creative_market($package_path, $marketplace, $package_data) {
        $api_url = $this->api_endpoints['creative_market'] . 'shops/' . $marketplace['username'] . '/products';
        
        $headers = array(
            'Authorization' => 'Bearer ' . $marketplace['api_key'],
            'Content-Type' => 'multipart/form-data'
        );
        
        $body = array(
            'name' => $package_data['name'],
            'description' => $package_data['description'],
            'category' => $package_data['category'],
            'tags' => implode(',', $package_data['tags']),
            'price' => $marketplace['pricing']['regular_license'],
            'file' => new CURLFile($package_path, 'application/zip', basename($package_path))
        );
        
        $response = $this->make_api_request($api_url, $headers, $body, 'POST');
        
        if ($response && isset($response['id'])) {
            return array(
                'success' => true,
                'message' => __('Package uploaded to Creative Market successfully', 'theme-exporter-pro'),
                'product_id' => $response['id'],
                'preview_url' => $response['url']
            );
        }
        
        return array(
            'success' => false,
            'message' => $response['error'] ?? __('Failed to upload to Creative Market', 'theme-exporter-pro')
        );
    }
    
    /**
     * Upload to custom marketplace
     */
    private function upload_to_custom_marketplace($package_path, $marketplace, $package_data) {
        if (empty($marketplace['api_url'])) {
            return array('success' => false, 'message' => __('Custom marketplace API URL not configured', 'theme-exporter-pro'));
        }
        
        $api_url = rtrim($marketplace['api_url'], '/') . '/upload';
        
        $headers = array(
            'Authorization' => 'Bearer ' . $marketplace['api_key'],
            'Content-Type' => 'multipart/form-data'
        );
        
        $body = array(
            'name' => $package_data['name'],
            'description' => $package_data['description'],
            'category' => $package_data['category'],
            'tags' => $package_data['tags'],
            'price' => $marketplace['pricing']['regular_license'],
            'package' => new CURLFile($package_path, 'application/zip', basename($package_path))
        );
        
        $response = $this->make_api_request($api_url, $headers, $body, 'POST');
        
        if ($response && isset($response['success']) && $response['success']) {
            return array(
                'success' => true,
                'message' => __('Package uploaded to custom marketplace successfully', 'theme-exporter-pro'),
                'item_id' => $response['item_id'] ?? '',
                'preview_url' => $response['preview_url'] ?? ''
            );
        }
        
        return array(
            'success' => false,
            'message' => $response['message'] ?? __('Failed to upload to custom marketplace', 'theme-exporter-pro')
        );
    }
    
    /**
     * Make API request
     */
    private function make_api_request($url, $headers, $body, $method = 'GET') {
        $args = array(
            'method' => $method,
            'headers' => $headers,
            'timeout' => 300,
            'sslverify' => true
        );
        
        if ($method === 'POST' && !empty($body)) {
            $args['body'] = $body;
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return $data;
    }
    
    /**
     * Get marketplace categories
     */
    public function get_marketplace_categories($marketplace_id) {
        $marketplaces = get_option('tep_marketplaces', array());
        
        if (!isset($marketplaces[$marketplace_id])) {
            return array();
        }
        
        $marketplace = $marketplaces[$marketplace_id];
        
        switch ($marketplace_id) {
            case 'envato':
                return $this->get_envato_categories($marketplace);
            case 'templatemonster':
                return $this->get_templatemonster_categories($marketplace);
            case 'creative_market':
                return $this->get_creative_market_categories($marketplace);
            default:
                return $this->get_custom_marketplace_categories($marketplace);
        }
    }
    
    /**
     * Get Envato categories
     */
    private function get_envato_categories($marketplace) {
        $api_url = $this->api_endpoints['envato'] . 'market/catalog/categories';
        
        $headers = array(
            'Authorization' => 'Bearer ' . $marketplace['api_key']
        );
        
        $response = $this->make_api_request($api_url, $headers, array(), 'GET');
        
        if ($response && isset($response['categories'])) {
            return $response['categories'];
        }
        
        return array();
    }
    
    /**
     * Sync package status
     */
    public function sync_package_status($marketplace_id, $item_id) {
        $marketplaces = get_option('tep_marketplaces', array());
        
        if (!isset($marketplaces[$marketplace_id])) {
            return false;
        }
        
        $marketplace = $marketplaces[$marketplace_id];
        
        switch ($marketplace_id) {
            case 'envato':
                return $this->get_envato_item_status($marketplace, $item_id);
            case 'templatemonster':
                return $this->get_templatemonster_product_status($marketplace, $item_id);
            case 'creative_market':
                return $this->get_creative_market_product_status($marketplace, $item_id);
            default:
                return $this->get_custom_marketplace_status($marketplace, $item_id);
        }
    }
    
    /**
     * Get sales analytics
     */
    public function get_sales_analytics($marketplace_id, $date_range = '30days') {
        $marketplaces = get_option('tep_marketplaces', array());
        
        if (!isset($marketplaces[$marketplace_id])) {
            return array();
        }
        
        $marketplace = $marketplaces[$marketplace_id];
        
        switch ($marketplace_id) {
            case 'envato':
                return $this->get_envato_analytics($marketplace, $date_range);
            case 'templatemonster':
                return $this->get_templatemonster_analytics($marketplace, $date_range);
            case 'creative_market':
                return $this->get_creative_market_analytics($marketplace, $date_range);
            default:
                return $this->get_custom_marketplace_analytics($marketplace, $date_range);
        }
    }
    
    /**
     * Auto-distribute package
     */
    public function auto_distribute_package($package_path, $package_data) {
        $marketplaces = get_option('tep_marketplaces', array());
        $results = array();
        
        foreach ($marketplaces as $marketplace_id => $marketplace) {
            if ($marketplace['enabled'] && $marketplace['auto_upload']) {
                $result = $this->upload_to_marketplace($package_path, $marketplace_id, $package_data);
                $results[$marketplace_id] = $result;
                
                // Log the result
                $this->log_distribution_result($marketplace_id, $package_data['name'], $result);
            }
        }
        
        return $results;
    }
    
    /**
     * Log distribution result
     */
    private function log_distribution_result($marketplace_id, $package_name, $result) {
        $logs = get_option('tep_distribution_logs', array());
        
        $logs[] = array(
            'timestamp' => current_time('mysql'),
            'marketplace' => $marketplace_id,
            'package' => $package_name,
            'success' => $result['success'],
            'message' => $result['message'],
            'item_id' => $result['item_id'] ?? ''
        );
        
        // Keep only last 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('tep_distribution_logs', $logs);
    }
    
    /**
     * Get distribution logs
     */
    public function get_distribution_logs($limit = 50) {
        $logs = get_option('tep_distribution_logs', array());
        
        return array_slice(array_reverse($logs), 0, $limit);
    }
    
    /**
     * Get registered marketplaces
     */
    public function get_registered_marketplaces() {
        return get_option('tep_marketplaces', array());
    }
    
    /**
     * Remove marketplace
     */
    public function remove_marketplace($marketplace_id) {
        $marketplaces = get_option('tep_marketplaces', array());
        
        if (isset($marketplaces[$marketplace_id])) {
            unset($marketplaces[$marketplace_id]);
            update_option('tep_marketplaces', $marketplaces);
            return true;
        }
        
        return false;
    }
}