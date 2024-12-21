<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Dual_Checkout_Shipping_Handler {
    public function __construct() {
        add_filter('woocommerce_package_rates', array($this, 'manage_shipping_methods'), 999);
    }

    public function manage_shipping_methods($rates) {
        if (!$rates) {
            return $rates;
        }
        foreach ($rates as $rate_id => $rate) {
            if (is_object($rate)) {
                if (strpos($rate_id, 'local_pickup') !== false) {
                    $rates[$rate_id]->cost = 0;
                    $rates[$rate_id]->label = 'Recogida local (Paso a buscar)';
                } elseif (strpos($rate_id, 'flat_rate') !== false) {
                    $rates[$rate_id]->label = 'Envío a domicilio';
                }
            }
        }
        return $rates;
    }
}