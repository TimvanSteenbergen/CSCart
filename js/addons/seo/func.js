(function($, _) {
    $(_.doc).on('keyup', '.cm-seo-check-changed', function(){
        var self = $(this);
        if (self.prop('defaultValue') !== '') {
            self.parent().find('.cm-seo-check-changed-block').switchAvailability(self.val() == self.prop('defaultValue'), true);
        }
    });


    $(document).ready(function() {

        var title_len = 60;
        var descr_len = 145;

        var title_elm = $('.cm-seo-srs-title');
        if (!title_elm.length) {
            return false;
        }

        var price_elm = $('#sec_elm_seo_srs_price');
        var description_elm = $('.cm-seo-srs-description');

        $('#product_description_product').change(function() {
            title_elm.text(format($(this).val(), title_len));
        });

        $('#elm_price_price').change(function() {
            price_elm.text($(this).val());
        });

        $('#elm_product_full_descr').ceEditor('change', function(html) {
            description_elm.text(format(html, descr_len));
        });

        $('#elm_product_short_descr').ceEditor('change', function(html) {
            if (!$('#elm_product_full_descr').ceEditor('val')) {
                description_elm.text(format(html, descr_len));
            }
        });
    });

    function format(str, len)
    {
        str = fn_strip_tags(str);
        return str.substr(0, len) + ' ...';
    }

}(Tygh.$, Tygh));