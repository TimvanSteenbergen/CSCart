(function(_, $) {

    function split( val )
    {
        return val.split( /,\s*/ );
    }
    
    function extractLast(term)
    {
        return split(term).pop();
    }

    function init()
    {
        $('#my_tags').tagit({
            allowSpaces: true,
            placeholderText: _.tr('addons_tags_add_a_tag'),
            allowDuplicates: true,
            fieldName: $("#object_name").val(),
            autocomplete: {
                source: function( request, response ) {
                    $.ceAjax('request', fn_url('tags.list?q=' + encodeURIComponent(extractLast(request.term))), {callback: function(data) {
                        response(data.autocomplete);
                    }});
                }
            },
            afterTagAdded: function(event, ui) {
                if (!ui.duringInitialization) {
                    var params = {
                        callback: function(data) {
                            var tag = data.tag_name.toString().toLowerCase();
                            if (tag.indexOf(ui.tagLabel.toLowerCase()) == -1) {
                                ui.tag.remove();
                            }
                        }
                    };
                    var t_name = encodeURIComponent(ui.tagLabel);
                    var o_id = $("#object_id").val();
                    var o_type = $("#object_type").val();
                    if (Tygh.area == 'C') {
                        $.ceAjax('request', fn_url('tags.update?tag=' + t_name + '&object_id=' + o_id + '&object_type=' + o_type), params);
                    }
                }
            },
            beforeTagRemoved: function(event, ui) {
                var params = {
                    callback: function(data) {
                        if (Tygh.area == 'C') {
                            var tag = data.tag_name.toString().toLowerCase();
                            if (typeof(ui.tagLabel) != 'undefined' && tag.indexOf(ui.tagLabel.toLowerCase()) == -1) {
                                ui.tag.remove();
                            }
                        }
                    }
                };
                var t_name = encodeURIComponent(ui.tagLabel);
                var o_id = $("#object_id").val();
                var o_type = $("#object_type").val();

                if (Tygh.area == 'C') {
                    $.ceAjax('request', fn_url('tags.delete?tag=' + t_name + '&object_id=' + o_id + '&object_type=' + o_type), params);
                } else {
                    ui.tag.remove();
                }

                return false;
            }
        });
    }

    $(document).ready(function(){
        if (!('tagit' in $.fn)) {
            $.getScript('js/addons/tags/lib/tag-it/tag-it.js', function() {
                init();
            });

            return false;
        }

        init();
    });
}(Tygh, Tygh.$));
