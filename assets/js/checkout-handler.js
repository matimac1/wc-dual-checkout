jQuery(function($) {
    var selectedMethod = null;
    
    // Guardar método seleccionado
    $(document.body).on('change', 'input[name^="shipping_method"]', function() {
        selectedMethod = $('input[name^="shipping_method"]:checked').val();
    });
    
    // Restaurar selección después de actualización
    $(document.body).on('updated_checkout', function() {
        if (selectedMethod) {
            $('input[name^="shipping_method"][value="' + selectedMethod + '"]').prop('checked', true);
        }
    });
});