<?php
/**
 * Plugin Name: WC Dual Checkout
 * Description: Plugin para manejar flujos duales de checkout en WooCommerce
 * Version: 1.0.1
 * Author: matimac
 * License: MIT
 */

if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('WC_DUAL_CHECKOUT_VERSION', '1.0.0');
define('WC_DUAL_CHECKOUT_PATH', plugin_dir_path(__FILE__));
define('WC_DUAL_CHECKOUT_URL', plugin_dir_url(__FILE__));

// Verificar que WooCommerce está activo
function wc_dual_checkout_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            ?>
            <div class="error">
                <p>WC Dual Checkout requiere que WooCommerce esté instalado y activado.</p>
            </div>
            <?php
        });
        return false;
    }
    return true;
}

// Cargar archivos principales
function wc_dual_checkout_load_files() {
    require_once WC_DUAL_CHECKOUT_PATH . 'includes/class-shipping-handler.php';
    require_once WC_DUAL_CHECKOUT_PATH . 'includes/class-checkout-handler.php';
    require_once WC_DUAL_CHECKOUT_PATH . 'includes/class-buy-now-handler.php';
}

// Inicializar el plugin
function wc_dual_checkout_init() {
    if (!wc_dual_checkout_check_woocommerce()) {
        return;
    }

    wc_dual_checkout_load_files();

    // Inicializar clases
    new WC_Dual_Checkout_Shipping_Handler();
    new WC_Dual_Checkout_Checkout_Handler();
    new WC_Dual_Checkout_Buy_Now_Handler();

    // Cargar admin si estamos en el panel de administración
    if (is_admin()) {
        require_once WC_DUAL_CHECKOUT_PATH . 'admin/class-admin-settings.php';
        new WC_Dual_Checkout_Admin();
    }
}

add_action('plugins_loaded', 'wc_dual_checkout_init');

// Registrar scripts
function wc_dual_checkout_enqueue_scripts() {
    if (is_checkout()) {
        wp_enqueue_script(
            'wc-dual-checkout-js',
            WC_DUAL_CHECKOUT_URL . 'assets/js/checkout-handler.js',
            array('jquery'),
            WC_DUAL_CHECKOUT_VERSION,
            true
        );
    }
}

add_action('wp_enqueue_scripts', 'wc_dual_checkout_enqueue_scripts');

// Registrar estilos
function wc_dual_checkout_enqueue_styles() {
    if (is_checkout()) {
        wp_enqueue_style(
            'wc-dual-checkout-css',
            WC_DUAL_CHECKOUT_URL . 'assets/css/checkout-styles.css',
            array(),
            WC_DUAL_CHECKOUT_VERSION
        );
        
        // Añadir clase si es compra directa
        if (isset($_GET['buy-now']) && $_GET['buy-now'] === 'yes') {
            add_filter('body_class', function($classes) {
                $classes[] = 'wc-buy-now-active';
                return $classes;
            });
        }
    }
}

add_action('wp_enqueue_scripts', 'wc_dual_checkout_enqueue_styles');