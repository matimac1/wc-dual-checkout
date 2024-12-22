<?php
if (!defined('ABSPATH')) {
    exit;
}

class Buy_Now_URL extends \Elementor\Core\DynamicTags\Tag {
    public function get_name() {
        return 'buy-now-url';
    }

    public function get_title() {
        return 'URL Comprar Ahora';
    }

    public function get_group() {
        return 'woocommerce';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::URL_CATEGORY];
    }

    public function render() {
        $product_id = get_the_ID();
        $url = wp_nonce_url(
            add_query_arg(
                array(
                    'buy-now' => 'yes',
                    'product' => $product_id,
                    'quantity' => 1,
                    'redirect' => 'checkout'
                ),
                wc_get_checkout_url()
            ),
            'buy_now_' . $product_id
        );
        
        echo esc_url($url);
    }
}