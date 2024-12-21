<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Dual_Checkout_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Dual Checkout Settings',
            'Dual Checkout',
            'manage_options',
            'wc-dual-checkout',
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        // Contenido de la página de configuración
        include_once WC_DUAL_CHECKOUT_PATH . 'admin/views/settings-page.php';
    }
}