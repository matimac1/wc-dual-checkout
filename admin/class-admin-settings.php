<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Dual_Checkout_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }

    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Dual Checkout',
            'Dual Checkout',
            'manage_options',
            'wc-dual-checkout',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Inicializar configuraciones
     */
    public function init_settings() {
        register_setting(
            'wc_dual_checkout_options',
            'wc_dual_checkout_settings'
        );

        add_settings_section(
            'wc_dual_checkout_general',
            'Configuración General',
            array($this, 'render_section_info'),
            'wc_dual_checkout'
        );

        // Habilitar/Deshabilitar botón Pagar Ahora
        add_settings_field(
            'enable_buy_now',
            'Botón Pagar Ahora',
            array($this, 'render_enable_buy_now_field'),
            'wc_dual_checkout',
            'wc_dual_checkout_general'
        );

        // Texto personalizado para el botón
        add_settings_field(
            'buy_now_text',
            'Texto del botón',
            array($this, 'render_buy_now_text_field'),
            'wc_dual_checkout',
            'wc_dual_checkout_general'
        );
    }

    /**
     * Renderizar página de configuración
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        require_once WC_DUAL_CHECKOUT_PATH . 'admin/views/settings-page.php';
    }

    /**
     * Renderizar información de la sección
     */
    public function render_section_info() {
        echo '<p>Configura las opciones para el funcionamiento del plugin.</p>';
    }

    /**
     * Renderizar campo de habilitar/deshabilitar
     */
    public function render_enable_buy_now_field() {
        $options = get_option('wc_dual_checkout_settings');
        $value = isset($options['enable_buy_now']) ? $options['enable_buy_now'] : '1';
        ?>
        <label>
            <input type="checkbox" name="wc_dual_checkout_settings[enable_buy_now]" 
                   value="1" <?php checked('1', $value); ?>>
            Habilitar botón "Pagar Ahora" en productos
        </label>
        <?php
    }

    /**
     * Renderizar campo de texto del botón
     */
    public function render_buy_now_text_field() {
        $options = get_option('wc_dual_checkout_settings');
        $value = isset($options['buy_now_text']) ? $options['buy_now_text'] : 'Pagar Ahora';
        ?>
        <input type="text" name="wc_dual_checkout_settings[buy_now_text]" 
               value="<?php echo esc_attr($value); ?>" class="regular-text">
        <p class="description">Texto que se mostrará en el botón de compra rápida</p>
        <?php
    }
}