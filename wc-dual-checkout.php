<?php
/**
 * Plugin Name: WC Dual Checkout
 * Description: Plugin para manejar flujos duales de checkout en WooCommerce
 * Version: 1.0.0
 * Author: Tu Nombre
 * License: MIT
 */

if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('WC_DUAL_CHECKOUT_VERSION', '1.0.0');
define('WC_DUAL_CHECKOUT_PATH', plugin_dir_path(__FILE__));
define('WC_DUAL_CHECKOUT_URL', plugin_dir_url(__FILE__));

// Después de las definiciones de constantes, añade:

// Cargar archivos principales
require_once WC_DUAL_CHECKOUT_PATH . 'includes/class-shipping-handler.php';
require_once WC_DUAL_CHECKOUT_PATH . 'admin/class-admin-settings.php';

// Inicializar clases
function wc_dual_checkout_init() {
    new WC_Dual_Checkout_Shipping_Handler();
    if (is_admin()) {
        new WC_Dual_Checkout_Admin();
    }
}
add_action('plugins_loaded', 'wc_dual_checkout_init');

// Registrar scripts
function wc_dual_checkout_scripts() {
    if (is_checkout()) {
        wp_enqueue_script(
            'wc-dual-checkout',
            WC_DUAL_CHECKOUT_URL . 'assets/js/checkout-handler.js',
            array('jquery'),
            WC_DUAL_CHECKOUT_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'wc_dual_checkout_scripts');