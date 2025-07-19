<?php
/**
 * WooCommerce Handler Class
 * 
 * Handles WooCommerce-specific functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class TEP_WooCommerce_Handler {
    
    /**
     * Export WooCommerce data
     */
    public function export_woocommerce_data($options = array()) {
        if (!class_exists('WooCommerce')) {
            return array();
        }
        
        $wc_data = array();
        
        // Export products
        if (isset($options['export_products']) && $options['export_products']) {
            $wc_data['products'] = $this->export_products();
        }
        
        // Export product categories
        if (isset($options['export_categories']) && $options['export_categories']) {
            $wc_data['categories'] = $this->export_product_categories();
        }
        
        // Export product attributes
        if (isset($options['export_attributes']) && $options['export_attributes']) {
            $wc_data['attributes'] = $this->export_product_attributes();
        }
        
        // Export WooCommerce settings
        if (isset($options['export_settings']) && $options['export_settings']) {
            $wc_data['settings'] = $this->export_wc_settings();
        }
        
        // Export payment gateways
        if (isset($options['export_gateways']) && $options['export_gateways']) {
            $wc_data['payment_gateways'] = $this->export_payment_gateways();
        }
        
        // Export shipping methods
        if (isset($options['export_shipping']) && $options['export_shipping']) {
            $wc_data['shipping'] = $this->export_shipping_methods();
        }
        
        // Export tax settings
        if (isset($options['export_tax']) && $options['export_tax']) {
            $wc_data['tax'] = $this->export_tax_settings();
        }
        
        return $wc_data;
    }
    
    /**
     * Export products
     */
    private function export_products() {
        $products = array();
        
        $product_posts = get_posts(array(
            'post_type' => array('product', 'product_variation'),
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        foreach ($product_posts as $product_post) {
            $product = wc_get_product($product_post->ID);
            
            if (!$product) continue;
            
            $product_data = array(
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'slug' => $product->get_slug(),
                'type' => $product->get_type(),
                'status' => $product->get_status(),
                'featured' => $product->get_featured(),
                'catalog_visibility' => $product->get_catalog_visibility(),
                'description' => $product->get_description(),
                'short_description' => $product->get_short_description(),
                'sku' => $product->get_sku(),
                'price' => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),
                'stock_quantity' => $product->get_stock_quantity(),
                'stock_status' => $product->get_stock_status(),
                'manage_stock' => $product->get_manage_stock(),
                'weight' => $product->get_weight(),
                'dimensions' => array(
                    'length' => $product->get_length(),
                    'width' => $product->get_width(),
                    'height' => $product->get_height()
                ),
                'categories' => wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'slugs')),
                'tags' => wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'slugs')),
                'images' => $this->get_product_images($product),
                'attributes' => $this->get_product_attributes($product),
                'meta_data' => $product->get_meta_data()
            );
            
            // Handle variable products
            if ($product->is_type('variable')) {
                $product_data['variations'] = $this->get_product_variations($product);
            }
            
            $products[] = $product_data;
        }
        
        return $products;
    }
    
    /**
     * Get product images
     */
    private function get_product_images($product) {
        $images = array();
        
        // Featured image
        $image_id = $product->get_image_id();
        if ($image_id) {
            $images['featured'] = wp_get_attachment_url($image_id);
        }
        
        // Gallery images
        $gallery_ids = $product->get_gallery_image_ids();
        foreach ($gallery_ids as $gallery_id) {
            $images['gallery'][] = wp_get_attachment_url($gallery_id);
        }
        
        return $images;
    }
    
    /**
     * Get product attributes
     */
    private function get_product_attributes($product) {
        $attributes = array();
        
        foreach ($product->get_attributes() as $attribute) {
            $attributes[] = array(
                'name' => $attribute->get_name(),
                'options' => $attribute->get_options(),
                'visible' => $attribute->get_visible(),
                'variation' => $attribute->get_variation()
            );
        }
        
        return $attributes;
    }
    
    /**
     * Get product variations
     */
    private function get_product_variations($product) {
        $variations = array();
        
        foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);
            
            if (!$variation) continue;
            
            $variations[] = array(
                'id' => $variation->get_id(),
                'attributes' => $variation->get_variation_attributes(),
                'price' => $variation->get_price(),
                'regular_price' => $variation->get_regular_price(),
                'sale_price' => $variation->get_sale_price(),
                'stock_quantity' => $variation->get_stock_quantity(),
                'sku' => $variation->get_sku(),
                'image' => wp_get_attachment_url($variation->get_image_id())
            );
        }
        
        return $variations;
    }
    
    /**
     * Export product categories
     */
    private function export_product_categories() {
        $categories = array();
        
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false
        ));
        
        foreach ($terms as $term) {
            $categories[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'parent' => $term->parent,
                'image' => wp_get_attachment_url(get_term_meta($term->term_id, 'thumbnail_id', true))
            );
        }
        
        return $categories;
    }
    
    /**
     * Export product attributes
     */
    private function export_product_attributes() {
        $attributes = array();
        
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        
        foreach ($attribute_taxonomies as $attribute) {
            $terms = get_terms(array(
                'taxonomy' => 'pa_' . $attribute->attribute_name,
                'hide_empty' => false
            ));
            
            $attribute_data = array(
                'id' => $attribute->attribute_id,
                'name' => $attribute->attribute_name,
                'label' => $attribute->attribute_label,
                'type' => $attribute->attribute_type,
                'order_by' => $attribute->attribute_orderby,
                'public' => $attribute->attribute_public,
                'terms' => array()
            );
            
            foreach ($terms as $term) {
                $attribute_data['terms'][] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description
                );
            }
            
            $attributes[] = $attribute_data;
        }
        
        return $attributes;
    }
    
    /**
     * Export WooCommerce settings
     */
    private function export_wc_settings() {
        $settings = array();
        
        // General settings
        $settings['general'] = array(
            'store_address' => get_option('woocommerce_store_address'),
            'store_city' => get_option('woocommerce_store_city'),
            'store_postcode' => get_option('woocommerce_store_postcode'),
            'default_country' => get_option('woocommerce_default_country'),
            'currency' => get_option('woocommerce_currency'),
            'currency_pos' => get_option('woocommerce_currency_pos'),
            'thousand_sep' => get_option('woocommerce_thousand_sep'),
            'decimal_sep' => get_option('woocommerce_decimal_sep'),
            'num_decimals' => get_option('woocommerce_price_num_decimals')
        );
        
        // Product settings
        $settings['products'] = array(
            'shop_page_id' => get_option('woocommerce_shop_page_id'),
            'cart_page_id' => get_option('woocommerce_cart_page_id'),
            'checkout_page_id' => get_option('woocommerce_checkout_page_id'),
            'myaccount_page_id' => get_option('woocommerce_myaccount_page_id'),
            'terms_page_id' => get_option('woocommerce_terms_page_id')
        );
        
        return $settings;
    }
    
    /**
     * Export payment gateways
     */
    private function export_payment_gateways() {
        $gateways = array();
        
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        
        foreach ($available_gateways as $gateway) {
            $gateways[] = array(
                'id' => $gateway->id,
                'title' => $gateway->title,
                'description' => $gateway->description,
                'enabled' => $gateway->enabled,
                'settings' => $gateway->settings
            );
        }
        
        return $gateways;
    }
    
    /**
     * Export shipping methods
     */
    private function export_shipping_methods() {
        $shipping = array();
        
        $shipping_zones = WC_Shipping_Zones::get_zones();
        
        foreach ($shipping_zones as $zone) {
            $zone_data = array(
                'id' => $zone['id'],
                'zone_name' => $zone['zone_name'],
                'zone_locations' => $zone['zone_locations'],
                'shipping_methods' => array()
            );
            
            foreach ($zone['shipping_methods'] as $method) {
                $zone_data['shipping_methods'][] = array(
                    'id' => $method->id,
                    'method_id' => $method->get_method_id(),
                    'title' => $method->get_title(),
                    'enabled' => $method->is_enabled(),
                    'settings' => $method->instance_settings
                );
            }
            
            $shipping[] = $zone_data;
        }
        
        return $shipping;
    }
    
    /**
     * Export tax settings
     */
    private function export_tax_settings() {
        $tax = array();
        
        $tax['settings'] = array(
            'calc_taxes' => get_option('woocommerce_calc_taxes'),
            'prices_include_tax' => get_option('woocommerce_prices_include_tax'),
            'tax_based_on' => get_option('woocommerce_tax_based_on'),
            'shipping_tax_class' => get_option('woocommerce_shipping_tax_class'),
            'tax_round_at_subtotal' => get_option('woocommerce_tax_round_at_subtotal')
        );
        
        // Tax classes
        $tax['classes'] = WC_Tax::get_tax_classes();
        
        // Tax rates
        $tax['rates'] = array();
        foreach (WC_Tax::get_tax_rate_classes() as $class) {
            $tax['rates'][$class] = WC_Tax::get_rates_for_tax_class($class);
        }
        
        return $tax;
    }
    
    /**
     * Import WooCommerce data
     */
    public function import_woocommerce_data($wc_data) {
        if (!class_exists('WooCommerce')) {
            return array('success' => false, 'message' => __('WooCommerce is not installed', 'theme-exporter-pro'));
        }
        
        $results = array();
        
        // Import categories first
        if (isset($wc_data['categories'])) {
            $results['categories'] = $this->import_product_categories($wc_data['categories']);
        }
        
        // Import attributes
        if (isset($wc_data['attributes'])) {
            $results['attributes'] = $this->import_product_attributes($wc_data['attributes']);
        }
        
        // Import products
        if (isset($wc_data['products'])) {
            $results['products'] = $this->import_products($wc_data['products']);
        }
        
        // Import settings
        if (isset($wc_data['settings'])) {
            $results['settings'] = $this->import_wc_settings($wc_data['settings']);
        }
        
        return array('success' => true, 'results' => $results);
    }
    
    /**
     * Import products
     */
    private function import_products($products) {
        $imported_count = 0;
        
        foreach ($products as $product_data) {
            $product = new WC_Product_Simple();
            
            $product->set_name($product_data['name']);
            $product->set_slug($product_data['slug']);
            $product->set_description($product_data['description']);
            $product->set_short_description($product_data['short_description']);
            $product->set_sku($product_data['sku']);
            $product->set_price($product_data['price']);
            $product->set_regular_price($product_data['regular_price']);
            $product->set_sale_price($product_data['sale_price']);
            $product->set_stock_quantity($product_data['stock_quantity']);
            $product->set_manage_stock($product_data['manage_stock']);
            $product->set_stock_status($product_data['stock_status']);
            $product->set_featured($product_data['featured']);
            $product->set_catalog_visibility($product_data['catalog_visibility']);
            
            // Set dimensions
            if (isset($product_data['dimensions'])) {
                $product->set_length($product_data['dimensions']['length']);
                $product->set_width($product_data['dimensions']['width']);
                $product->set_height($product_data['dimensions']['height']);
            }
            
            $product->set_weight($product_data['weight']);
            
            $product_id = $product->save();
            
            if ($product_id) {
                // Set categories
                if (isset($product_data['categories'])) {
                    wp_set_object_terms($product_id, $product_data['categories'], 'product_cat');
                }
                
                // Set tags
                if (isset($product_data['tags'])) {
                    wp_set_object_terms($product_id, $product_data['tags'], 'product_tag');
                }
                
                $imported_count++;
            }
        }
        
        return array('imported' => $imported_count, 'total' => count($products));
    }
    
    /**
     * Import product categories
     */
    private function import_product_categories($categories) {
        $imported_count = 0;
        
        foreach ($categories as $category_data) {
            $term = wp_insert_term(
                $category_data['name'],
                'product_cat',
                array(
                    'slug' => $category_data['slug'],
                    'description' => $category_data['description'],
                    'parent' => $category_data['parent']
                )
            );
            
            if (!is_wp_error($term)) {
                $imported_count++;
            }
        }
        
        return array('imported' => $imported_count, 'total' => count($categories));
    }
    
    /**
     * Import product attributes
     */
    private function import_product_attributes($attributes) {
        $imported_count = 0;
        
        foreach ($attributes as $attribute_data) {
            $attribute_id = wc_create_attribute(array(
                'name' => $attribute_data['label'],
                'slug' => $attribute_data['name'],
                'type' => $attribute_data['type'],
                'order_by' => $attribute_data['order_by'],
                'has_archives' => $attribute_data['public']
            ));
            
            if (!is_wp_error($attribute_id)) {
                // Import attribute terms
                foreach ($attribute_data['terms'] as $term_data) {
                    wp_insert_term(
                        $term_data['name'],
                        'pa_' . $attribute_data['name'],
                        array(
                            'slug' => $term_data['slug'],
                            'description' => $term_data['description']
                        )
                    );
                }
                
                $imported_count++;
            }
        }
        
        return array('imported' => $imported_count, 'total' => count($attributes));
    }
    
    /**
     * Import WooCommerce settings
     */
    private function import_wc_settings($settings) {
        // Import general settings
        if (isset($settings['general'])) {
            foreach ($settings['general'] as $key => $value) {
                update_option('woocommerce_' . $key, $value);
            }
        }
        
        // Import product settings
        if (isset($settings['products'])) {
            foreach ($settings['products'] as $key => $value) {
                update_option('woocommerce_' . $key, $value);
            }
        }
        
        return array('success' => true);
    }
}