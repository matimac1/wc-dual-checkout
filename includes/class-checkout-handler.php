<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Dual_Checkout_Checkout_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        // Modificación de campos
        add_filter('woocommerce_checkout_fields', array($this, 'modify_checkout_fields'), 20);
        
        // Texto personalizado para envío directo
        add_action('wp_footer', array($this, 'custom_shipping_text_and_state'));
        add_filter('woocommerce_ship_to_different_address_checked', '__return_false', 999);
        add_filter('woocommerce_shipping_method_default', '__return_empty_string', 999);
        
        // Personalización del checkout en compra directa
        if (isset($_GET['buy-now'])) {
            add_filter('woocommerce_get_checkout_url', array($this, 'add_buy_now_parameter'));
        }
    }

    /**
     * Modificar campos del checkout
     */
    public function modify_checkout_fields($fields) {
        // Modificar campos de estado/ciudad
        if (isset($fields['billing']['billing_state'])) {
            $fields['billing']['billing_state']['label'] = 'Ciudad';
            $fields['billing']['billing_state']['placeholder'] = 'Selecciona tu ciudad';
        }

        if (isset($fields['shipping']['shipping_state'])) {
            $fields['shipping']['shipping_state']['label'] = 'Ciudad';
            $fields['shipping']['shipping_state']['placeholder'] = 'Selecciona la ciudad de envío';
        }

        // Modificar campos de dirección
        if (isset($fields['billing']['billing_address_1'])) {
            $fields['billing']['billing_address_1']['label'] = 'Referencias de ubicación';
            $fields['billing']['billing_address_1']['placeholder'] = 'Describe cómo llegar';
        }

        if (isset($fields['shipping']['shipping_address_1'])) {
            $fields['shipping']['shipping_address_1']['label'] = 'Referencias de ubicación';
            $fields['shipping']['shipping_address_1']['placeholder'] = 'Describe cómo llegar';
        }

        return $fields;
    }

    /**
     * Personalizar texto y comportamiento del envío
     */
    public function custom_shipping_text_and_state() {
        if (!is_checkout()) {
            return;
        }

        $is_direct_buy = isset($_GET['buy-now']) && $_GET['buy-now'] === 'yes';
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var isDirect = <?php echo $is_direct_buy ? 'true' : 'false'; ?>;
            var directText = '¿Dónde desea recibir su pedido?';
            var normalText = '¿Desea enviar a una dirección diferente?';
            
            function updateText() {
                var textToUse = isDirect ? directText : normalText;
                $('#ship-to-different-address label span').text(textToUse);
                $('#ship-to-different-address-checkbox').prop('checked', false);
            }
            
            // Cambiar texto inicial
            updateText();
            
            // Mantener después de actualizaciones
            $(document.body).on('updated_checkout', updateText);
        });
        </script>
        
        <style>
            #ship-to-different-address {
                margin: 20px 0;
            }
            #ship-to-different-address label {
                font-weight: normal;
                color: #333;
            }
        </style>
        <?php
    }

    /**
     * Añadir parámetro de compra directa al URL
     */
    public function add_buy_now_parameter($url) {
        return add_query_arg('buy-now', 'yes', $url);
    }
}