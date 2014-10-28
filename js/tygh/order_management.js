(function(_, $) {

    $.extend({
        selectPaymentMethod: function(payment_id)
        {
            var url = fn_url('order_management.update_payment?payment_id=' + payment_id);

            $.ceAjax('request', url, {
                result_ids: result_ids
            });
        },

        selectShippingMethod: function(shipping_id, supplier_id)
        {
            var url = 'order_management.update_shipping?shipping_id=' + shipping_id;

            if (typeof(supplier_id) != 'undefined') {
                url += '&supplier_id=' + supplier_id;
            }

            url = fn_url(url);

            $.ceAjax('request', url, {
                result_ids: result_ids
            });
        }
    });

    $(document).ready(function(){
        $(_.doc).on('change', '.cm-om-totals input:visible, .cm-om-totals select:visible, .cm-om-totals textarea:visible', function(){
            var is_changed = $('.cm-om-totals').formIsChanged();
            $('.cm-om-totals-price').toggleBy(is_changed);
            $('.cm-om-totals-recalculate').toggleBy(!is_changed);
        });
    });
        
}(Tygh, Tygh.$));
