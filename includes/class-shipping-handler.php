<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Dual_Checkout_Shipping_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        // Carrito al checkout
        add_filter('woocommerce_ship_to_different_address_checked', '__return_false');
        add_filter('woocommerce_package_rates', array($this, 'preserve_shipping_cost'), 100, 2);
        add_action('woocommerce_cart_updated', array($this, 'update_persistent_cart'));

        // Manejo de estados de envío
        add_filter('woocommerce_shipping_get_state', array($this, 'maintain_shipping_state'), 999);
        add_action('woocommerce_before_calculate_totals', array($this, 'force_shipping_state'), 1);
        add_action('woocommerce_checkout_update_order_review', array($this, 'handle_state_update'), 1);
        add_action('woocommerce_calculated_shipping', array($this, 'save_chosen_city'));

        // Costos de envío
        add_filter('woocommerce_shipping_rate_cost', array($this, 'handle_shipping_cost'), 100, 2);
        add_filter('woocommerce_package_rates', array($this, 'modify_shipping_labels'), 100);
    }

    /**
     * Preservar costo de envío del carrito al checkout
     */
    public function preserve_shipping_cost($rates, $package) {
        if (!is_checkout()) {
            return $rates;
        }

        $chosen_method = WC()->session->get('chosen_shipping_methods');
        $current_rate = isset($chosen_method[0]) ? $chosen_method[0] : '';
        
        if ($current_rate && isset($rates[$current_rate])) {
            $current_cost = $rates[$current_rate]->cost;
            foreach ($rates as $rate_id => $rate) {
                $rates[$rate_id]->cost = $current_cost;
            }
        }

        return $rates;
    }

    /**
     * Actualizar carrito persistente
     */
    public function update_persistent_cart($cart_updated) {
        if (is_checkout() && WC()->session) {
            $chosen_method = WC()->session->get('chosen_shipping_methods');
            if (!empty($chosen_method)) {
                WC()->session->set('previous_shipping_methods', $chosen_method);
            }
        }
        return $cart_updated;
    }

    /**
     * Mantener estado de envío
     */
    public function maintain_shipping_state($state) {
        if (WC()->session && ($saved_city = WC()->session->get('chosen_shipping_city'))) {
            return $saved_city;
        }
        return $state;
    }

    /**
     * Forzar estado de envío
     */
    public function force_shipping_state() {
        if (WC()->session && ($saved_city = WC()->session->get('chosen_shipping_city'))) {
            WC()->customer->set_shipping_state($saved_city);
        }
    }

    /**
     * Manejar actualización de estado
     */
    public function handle_state_update() {
        if (WC()->session && ($saved_city = WC()->session->get('chosen_shipping_city'))) {
            WC()->customer->set_shipping_state($saved_city);
            WC()->cart->calculate_shipping();
        }
    }

    /**
     * Guardar ciudad elegida
     */
    public function save_chosen_city() {
        if (isset($_POST['calc_shipping_state'])) {
            $city = sanitize_text_field($_POST['calc_shipping_state']);
            WC()->session->set('chosen_shipping_city', $city);
        }
    }

   /**
 * Manejar costo de envío
 */
public function handle_shipping_cost($cost, $rate) {
    // Si es recogida local, siempre es 0
    if (strpos($rate->get_id(), 'local_pickup') !== false) {
        return 0;
    }

    $shipping_state = WC()->checkout->get_value('shipping_state');
    $chosen_methods = WC()->session->get('chosen_shipping_methods');
    $chosen_method = isset($chosen_methods[0]) ? $chosen_methods[0] : '';
    
    // Por defecto, el costo es 0
    if (empty($shipping_state)) {
        return 0;
    }

    // Solo aplicar costo si este método está seleccionado
    if ($rate->get_id() !== $chosen_method) {
        return 0;
    }

    // Obtener costo específico de la ciudad si existe
    $city_cost = $this->get_city_shipping_cost($shipping_state);
    if ($city_cost !== null) {
        return $city_cost;
    }

    // Si no hay costo específico, devolver el costo base solo si el método está seleccionado
    return $chosen_method === $rate->get_id() ? $cost : 0;
}

/**
 * Obtener costo de envío específico para una ciudad
 */
private function get_city_shipping_cost($city) {
    // Aquí va la lógica para obtener el costo específico de la ciudad
    // Por ahora retornamos null para usar el costo base
    return null;
}

    /**
     * Modificar etiquetas de envío
     */
    public function modify_shipping_labels($rates) {
        if (!$rates) return $rates;
        
        foreach ($rates as $rate_id => $rate) {
            if (strpos($rate_id, 'local_pickup') !== false) {
                $rates[$rate_id]->label = 'Recogida local (Paso a buscar)';
            } elseif (strpos($rate_id, 'flat_rate') !== false) {
                $rates[$rate_id]->label = 'Envío a domicilio';
            }
        }
        
        return $rates;
    }
}