(function(_, $) {

    (function($) {


        // public methods
        var methods = {
            checkType: function() {
                var feature_id = $(this).data('caFeatureId');
                var value = $(this).prop('value');
                var default_value = $('option', $(this)).map(function() {
                    if (this.defaultSelected == true) {
                        return this;
                    }
                }).val();

                var variant_types = ['S', 'M', 'N', 'E'];
                var show_variants = $.inArray(value, variant_types) != -1;

                var show_warning = false;

                if (!$(this).hasClass('cm-new-feature')) {
                    if ($.inArray(value, variant_types) != -1 && $.inArray(default_value, variant_types) == -1) {
                        show_warning = true;
                    }

                    if ($.inArray(value, variant_types) == -1 && $.inArray(default_value, variant_types) != -1) {
                        show_warning = true;
                    }
                }

                $('#warning_feature_change_' + feature_id).toggleBy(!show_warning);

                var t = $('#content_tab_variants_' + feature_id);
                $('#tab_variants_' + feature_id).toggleBy(!show_variants);
                // display/hide images
                var is_extended = (value == 'E');
                $('.cm-extended-feature', t).toggleBy(!is_extended);

                // hide/show extra information line
                $('tr[id^=extra_feature_]', t).toggleBy(!is_extended);
                // switch extra information opener
                $('img[id^=off_extra_feature_]', t).toggleBy(!is_extended);
                $('img[id^=on_extra_feature_]', t).toggleBy(is_extended);
                // switch common extra information opener
                $('img[id^=off_st_]', t).toggleBy(!is_extended);
                $('img[id^=on_st_]', t).toggleBy(is_extended);

                if (value == 'N') {
                    $('.cm-feature-value', t).addClass('cm-value-decimal');
                } else {
                    $('.cm-feature-value', t).removeClass('cm-value-decimal');
                }

            },
            checkGroup: function() {
                var feature_id = $(this).data('caFeatureId');
                var is_defined = $(this).prop('value') != 0;
                $('#tab_categories_' + feature_id).toggleBy(is_defined);
                var display_elms = $('input[type="checkbox"][name^="feature_data[display_on"]', $('#tabs_content_' + feature_id));

                if (is_defined) {
                    var selected = $(this).find(':selected');

                    display_elms.each(function() {
                        var self = $(this);
                        var is_enabled = selected.data('caDisplay' + self.data('caDisplayId')) === 'Y';
                        self.prop('disabled', is_enabled);
                        if (is_enabled) {
                            self.prop('checked', true);
                        }
                    });
                } else {
                    display_elms.prop('disabled', false);
                }
            }
        };

        $.fn.ceProductFeature = function(method) {
            var args = arguments;

            return $(this).each(function(i, elm) {

                if (methods[method]) {
                    return methods[method].apply(this, Array.prototype.slice.call(args, 1));
                } else if (typeof method === 'object' || !method) {
                    return methods.init.apply(this, args);
                } else {
                    $.error('ty.productfeature: method ' + method + ' does not exist');
                }
            });
        };

    })($);

    $(document).ready(function() {
        $(_.doc).on('change', '.cm-feature-type', function(e) {
            $(e.target).ceProductFeature('checkType');
        });
        $(_.doc).on('change', '.cm-feature-group', function(e) {
            $(e.target).ceProductFeature('checkGroup');
        });
    });

}(Tygh, Tygh.$));