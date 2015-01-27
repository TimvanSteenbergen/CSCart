(function(_, $) {

(function($){
    
    function _popup_switch_search(to_popup)
    {
        var placeholder = to_popup ? $('#simple_search_popup') : $('#simple_search_common');
        $('#simple_search').appendTo(placeholder);
    }
    
    var methods = {
            
        init_popup_search: function(adv_popup_id) {

            $('#' + adv_popup_id).on({
                dialogbeforeclose: function( event, ui ) {
                    _popup_switch_search(false);
                },
                dialogcreate: function( event, ui ) {
                    _popup_switch_search(true);
                },
                dialogopen: function( event, ui ) {
                    _popup_switch_search(true);
                }
            });

            _popup_switch_search(false);
        },

        check_views: function(input_id, views_id) {
            var match = true;
            var sbm_button = $('input[type=submit]:first', $('#' + input_id).parents('form:first'));
            $('.cm-view-name', $('#' + views_id)).each(function() {
                if ($(this).text().toLowerCase() == $('#' + input_id).val().toLowerCase()) {
                    match = confirm(_.tr('object_exists'));
                    if (match) {
                        $('<input type="hidden" name="update_view_id" value="' + $(this).data('caViewId') + '" />').appendTo($('#' + input_id).parent());
                    }
                    return false;
                }
            });
            if (match) {
                sbm_button.prop('name', sbm_button.prop('name').substr(0, sbm_button.prop('name').length - 1) + '.save_view]');
                sbm_button.trigger('click');
            }
        }
    };

    $.ceAdvancedSearch = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            $.error('ty.advancedSearch: method ' +  method + ' does not exist');
        }
    };

})($);

$(document).ready(function() {
    if ($('#adv_search').length) {
        $.ceAdvancedSearch('init_popup_search', 'adv_search');
    }
    $('#adv_search_save').on('click', function() {
        $.ceAdvancedSearch('check_views', 'view_name', 'views');
    });
});
    
}(Tygh, Tygh.$));