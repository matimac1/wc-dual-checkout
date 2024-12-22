<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Dual_Checkout_Buy_Now_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        // Registro del tag dinámico para Elementor
        add_action('elementor/dynamic_tags/register', array($this, 'register_buy_now_dynamic_tag'));
        
        // Procesamiento de compra directa
        add_action('template_redirect', array($this, 'process_buy_now'), 5);
        
        // Mantener costos de envío
        add_action('init', array($this, 'maintain_shipping_costs_for_direct_buy'));
        
        // Manejo de la sesión y el carrito
        add_action('woocommerce_before_calculate_totals', array($this, 'save_initial_shipping_cost'), 1);
        add_action('woocommerce_checkout_order_processed', array($this, 'clear_buy_now_session'));
        
        // Deshabilitar mini carrito
        if (isset($_GET['buy-now'])) {
            add_action('wp_footer', array($this, 'disable_cart_fragments_for_buy_now'));
            add_filter('woocommerce_widget_cart_is_hidden', array($this, 'disable_mini_cart_for_buy_now'), 10);
        }
    }

    /**
     * Registrar tag dinámico para Elementor
     */
    public function register_buy_now_dynamic_tag($dynamic_tags_manager) {
        require_once WC_DUAL_CHECKOUT_PATH . 'includes/elementor/class-buy-now-url-tag.php';
        $dynamic_tags_manager->register(new Buy_Now_URL());
    }

    /**
     * Procesar compra directa
     */
    public function process_buy_now() {
        if (!isset($_GET['buy-now']) || !isset($_GET['product'])) {
            return;
        }

        $product_id = absint($_GET['product']);
        
        if (!wp_verify_nonce($_GET['_wpnonce'], 'buy_now_' . $product_id)) {
            return;
        }

        // Limpiar carrito y añadir producto
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart($product_id, 1);

        // Marcar la sesión como compra directa
        if (WC()->session) {
            WC()->session->set('is_buy_now_purchase', 'yes');
        }

        // Prevenir mini carrito
        remove_action('woocommerce_add_to_cart', 'woocommerce_add_to_cart_message');
        remove_action('woocommerce_add_to_cart', 'wc_add_to_cart_message');
        
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }

    /**
     * Mantener costos de envío para compra directa
     */
    public function maintain_shipping_costs_for_direct_buy() {
        if (WC()->session && WC()->session->get('is_buy_now_purchase') === 'yes') {
            add_filter('woocommerce_shipping_get_state', function($state) {
                if (WC()->session && ($saved_city = WC()->session->get('chosen_shipping_city'))) {
                    return $saved_city;
                }
                return $state;
            }, 999);
        }
    }

    /**
     * Guardar costo inicial de envío
     */
    public function save_initial_shipping_cost() {
        if (
            WC()->session && 
            WC()->session->get('is_buy_now_purchase') === 'yes' && 
            !WC()->session->get('chosen_shipping_cost')
        ) {
            foreach (WC()->shipping->get_packages() as $package) {
                if (!empty($package['rates'])) {
                    $first_rate = reset($package['rates']);
                    WC()->session->set('chosen_shipping_cost', $first_rate->cost);
                    break;
                }
            }
        }
    }

    /**
     * Deshabilitar fragmentos del carrito
     */
    public function disable_cart_fragments_for_buy_now() {
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            $(document.body).off('added_to_cart');
            $(document.body).off('wc_fragments_loaded');
        });
        </script>
        <?php
    }

    /**
     * Deshabilitar mini carrito
     */
    public function disable_mini_cart_for_buy_now($enable) {
        return false;
    }

    /**
     * Limpiar sesión después de procesar la orden
     */
    public function clear_buy_now_session($order_id) {
        if (WC()->session) {
            WC()->session->set('is_buy_now_purchase', 'no');
            WC()->session->set('chosen_shipping_cost', null);
        }
    }
}