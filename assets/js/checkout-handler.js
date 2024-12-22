(function($) {
    'use strict';

    var WCDualCheckout = {
        // Variables para mantener el estado
        selectedMethod: '',
        
        // Inicialización
        init: function() {
            this.bindEvents();
            this.initializeState();
        },

        // Vincular eventos
        bindEvents: function() {
            $(document.body).on('change', 'select[name^="shipping_state"]', this.handleStateChange.bind(this));
            $(document.body).on('change', 'input[name^="shipping_method"]', this.handleMethodChange.bind(this));
            $(document.body).on('updated_checkout', this.handleCheckoutUpdate.bind(this));
            $(document.body).on('init_checkout', this.initializeState.bind(this));
        },

        // Manejar cambio de ciudad/estado
        handleStateChange: function(e) {
            var state = $(e.target).val();
            
            if (!state) {
                $('input[name^="shipping_method"]').prop('checked', false);
                this.selectedMethod = '';
            }
            
            this.updatePriceDisplay();
            $(document.body).trigger('update_checkout');
        },

        // Manejar cambio de método de envío
        handleMethodChange: function(e) {
            var $target = $(e.target);
            var shippingState = $('select[name^="shipping_state"]').val();
            
            if (!shippingState) {
                $target.prop('checked', false);
                return false;
            }
            
            this.selectedMethod = $target.val();
            this.updatePriceDisplay();
            $(document.body).trigger('update_checkout');
        },

        // Manejar actualización del checkout
        handleCheckoutUpdate: function() {
            var shippingState = $('select[name^="shipping_state"]').val();
            
            if (this.selectedMethod && shippingState) {
                $('input[name^="shipping_method"][value="' + this.selectedMethod + '"]')
                    .prop('checked', true);
            }
            
            this.updatePriceDisplay();
        },

        // Actualizar visualización de precios
        updatePriceDisplay: function() {
            var shippingState = $('select[name^="shipping_state"]').val();
            
            $('.shipping_method').each(function() {
                var $label = $(this).parent();
                var $priceAmount = $label.find('.woocommerce-Price-amount');
                var $currencySymbol = $label.find('.woocommerce-Price-currencySymbol');
                
                if (!shippingState || !$(this).is(':checked')) {
                    $priceAmount.hide();
                    $currencySymbol.hide();
                } else {
                    $priceAmount.show();
                    $currencySymbol.show();
                }
            });
        },

        // Inicializar estado
        initializeState: function() {
            this.updatePriceDisplay();
            
            // Si es compra directa, desmarcar métodos inicialmente
            if (window.location.href.indexOf('buy-now=yes') > -1) {
                $('input[name^="shipping_method"]').prop('checked', false);
            }
        }
    };

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        WCDualCheckout.init();
    });

})(jQuery);