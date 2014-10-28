(function(_, $) {

(function($){

    function _check_status_switch(data, params)
    {
        if (params.obj) {
            var $ = Tygh.$;
            var status = (data.return_status) ? data.return_status : params.status;
            var s_elm = $(params.obj).parents('.cm-statuses:first');
            
            s_elm.children('[class^="cm-status-"]').hide();
            s_elm.children('.cm-status-' + status.toLowerCase()).show();
        }

    }
    
    var methods = {
        status_switch: function(elm) {
            var jelm = $(elm);
                
            var data = {
                obj: jelm,
                status: jelm.data('caStatus'),
                callback: _check_status_switch
            };
                
            var href = fn_url('tools.update_status?table=discussion_posts&id_name=post_id&id=' + jelm.data('caPostId') + '&status=' + jelm.data('caStatus'));
            $.ceAjax('request', href, data);
        }
    };
    
    $.extend({
        ceDiscussion: function(method) {
            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else {
                $.error('ty.discussion: method ' +  method + ' does not exist');
            }
        }
    });
    
})($);


$(document).ready(function() {
    $(_.doc).on('click', '.cm-status-switch', function (e) {
        $.ceDiscussion('status_switch', e.target);
    });
});

}(Tygh, Tygh.$));