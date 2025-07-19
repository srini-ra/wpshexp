<?php
/**
 * Cloud Storage Class
 * 
 * Handles cloud storage integration (Google Drive, Dropbox, etc.)
 */

if (!defined('ABSPATH')) {
    exit;
}

class TKP_Cloud_Storage {
    
    private $logger;
    
    public function __construct($logger) {
        $this->logger = $logger;
    }
    
    /**
     * Upload to Google Drive
     */
    public function upload_to_google_drive($file_path) {
        try {
            // Check if file exists
            if (!file_exists($file_path)) {
                throw new Exception(__('File not found', 'theme-kit-pro'));
            }
            
            // Get Google Drive credentials
            $credentials = $this->get_google_drive_credentials();
            if (!$credentials) {
                throw new Exception(__('Google Drive not configured', 'theme-kit-pro'));
            }
            
            // Get access token
            $access_token = $this->get_google_drive_token($credentials);
            if (!$access_token) {
                throw new Exception(__('Failed to authenticate with Google Drive', 'theme-kit-pro'));
            }
            
            // Upload file
            $result = $this->upload_file_to_google_drive($file_path, $access_token);
            
            if ($result['success']) {
                $this->logger->info('File uploaded to Google Drive successfully', array(
                    'file' => basename($file_path),
                    'file_id' => $result['file_id'],
                    'share_url' => $result['share_url']
                ));
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logger->error('Google Drive upload failed', array(
                'file' => basename($file_path),
                'error' => $e->getMessage()
            ));
            
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Get Google Drive credentials
     */
    private function get_google_drive_credentials() {
        $client_id = get_option('tkp_google_drive_client_id');
        $client_secret = get_option('tkp_google_drive_client_secret');
        $refresh_token = get_option('tkp_google_drive_refresh_token');
        
        if (empty($client_id) || empty($client_secret) || empty($refresh_token)) {
            return false;
        }
        
        return array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token
        );
    }
    
    /**
     * Get Google Drive access token
     */
    private function get_google_drive_token($credentials) {
        $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'client_id' => $credentials['client_id'],
                'client_secret' => $credentials['client_secret'],
                'refresh_token' => $credentials['refresh_token'],
                'grant_type' => 'refresh_token'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return $data['access_token'] ?? false;
    }
    
    /**
     * Upload file to Google Drive
     */
    private function upload_file_to_google_drive($file_path, $access_token) {
        $filename = basename($file_path);
        $file_content = file_get_contents($file_path);
        
        // Create file metadata
        $metadata = array(
            'name' => $filename,
            'parents' => array($this->get_google_drive_folder_id())
        );
        
        // Upload file
        $boundary = uniqid();
        $delimiter = '-------314159265358979323846';
        $close_delim = "\r\n--{$delimiter}--\r\n";
        
        $body = "--{$delimiter}\r\n";
        $body .= "Content-Type: application/json\r\n\r\n";
        $body .= json_encode($metadata) . "\r\n";
        $body .= "--{$delimiter}\r\n";
        $body .= "Content-Type: application/zip\r\n\r\n";
        $body .= $file_content . "\r\n";
        $body .= $close_delim;
        
        $response = wp_remote_post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'multipart/related; boundary="' . $delimiter . '"'
            ),
            'body' => $body,
            'timeout' => 300
        ));
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            throw new Exception('Upload failed with status: ' . $response_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['id'])) {
            throw new Exception('Invalid response from Google Drive');
        }
        
        // Make file shareable
        $share_url = $this->make_google_drive_file_shareable($data['id'], $access_token);
        
        return array(
            'success' => true,
            'message' => __('File uploaded to Google Drive successfully', 'theme-kit-pro'),
            'file_id' => $data['id'],
            'share_url' => $share_url,
            'filename' => $filename
        );
    }
    
    /**
     * Make Google Drive file shareable
     */
    private function make_google_drive_file_shareable($file_id, $access_token) {
        // Create permission for anyone with link
        wp_remote_post("https://www.googleapis.com/drive/v3/files/{$file_id}/permissions", array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode(array(
                'role' => 'reader',
                'type' => 'anyone'
            )),
            'timeout' => 30
        ));
        
        return "https://drive.google.com/file/d/{$file_id}/view?usp=sharing";
    }
    
    /**
     * Get Google Drive folder ID for Theme Kit Pro
     */
    private function get_google_drive_folder_id() {
        $folder_id = get_option('tkp_google_drive_folder_id');
        
        if (empty($folder_id)) {
            // Use root folder
            return 'root';
        }
        
        return $folder_id;
    }
    
    /**
     * Configure Google Drive
     */
    public function configure_google_drive($client_id, $client_secret, $authorization_code) {
        try {
            // Exchange authorization code for tokens
            $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
                'body' => array(
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'code' => $authorization_code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => admin_url('admin.php?page=theme-kit-pro-settings')
                ),
                'timeout' => 30
            ));
            
            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (!isset($data['refresh_token'])) {
                throw new Exception('Failed to get refresh token');
            }
            
            // Save credentials
            update_option('tkp_google_drive_client_id', $client_id);
            update_option('tkp_google_drive_client_secret', $client_secret);
            update_option('tkp_google_drive_refresh_token', $data['refresh_token']);
            
            return array(
                'success' => true,
                'message' => __('Google Drive configured successfully', 'theme-kit-pro')
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Test Google Drive connection
     */
    public function test_google_drive_connection() {
        $credentials = $this->get_google_drive_credentials();
        if (!$credentials) {
            return array(
                'success' => false,
                'message' => __('Google Drive not configured', 'theme-kit-pro')
            );
        }
        
        $access_token = $this->get_google_drive_token($credentials);
        if (!$access_token) {
            return array(
                'success' => false,
                'message' => __('Failed to authenticate with Google Drive', 'theme-kit-pro')
            );
        }
        
        // Test by getting user info
        $response = wp_remote_get('https://www.googleapis.com/drive/v3/about?fields=user', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['user'])) {
            return array(
                'success' => true,
                'message' => sprintf(__('Connected as %s', 'theme-kit-pro'), $data['user']['displayName']),
                'user' => $data['user']
            );
        }
        
        return array(
            'success' => false,
            'message' => __('Failed to verify Google Drive connection', 'theme-kit-pro')
        );
    }
}