<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>WC Dual Checkout - Configuración</h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('wc_dual_checkout_options');
        do_settings_sections('wc_dual_checkout');
        submit_button();
        ?>
    </form>

    <div class="wc-dual-checkout-info">
        <h2>Información</h2>
        <p>Este plugin gestiona dos flujos de compra en WooCommerce:</p>
        <ul>
            <li>Flujo tradicional del carrito</li>
            <li>Flujo rápido con "Pagar Ahora"</li>
        </ul>
        <p>Versión actual: <?php echo WC_DUAL_CHECKOUT_VERSION; ?></p>
    </div>
</div>