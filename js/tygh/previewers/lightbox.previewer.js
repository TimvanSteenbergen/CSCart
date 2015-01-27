/* previewer-description:text_light_box */
(function(_, $) {
    $.loadCss(['js/lib/lightbox/css/jquery.lightbox-0.5.css']);
    $.getScript('js/lib/lightbox/js/jquery.lightbox-0.5.min.js');
    
    $.cePreviewer('handlers', {
        display: function(elm) {
                    
            var inited = elm.data('inited');
            if (inited != true) {
                var image_id = elm.data('caImageId');
                var elms = $('a[data-ca-image-id="' + image_id + '"]');
                elms.data('inited', true);
                
                elms.lightBox({
                    imageLoading: _.current_location + '/js/lib/lightbox/images/lightbox-ico-loading.gif',
                    imageBtnPrev: _.current_location + '/js/lib/lightbox/images/lightbox-btn-prev.gif',
                    imageBtnNext: _.current_location + '/js/lib/lightbox/images/lightbox-btn-next.gif',
                    imageBtnClose: _.current_location + '/js/lib/lightbox/images/lightbox-btn-close.gif',
                    imageBlank: _.current_location + '/js/lib/lightbox/images/lightbox-blank.gif',
                    keyToClose: String.fromCharCode(27).toLowerCase() // workaround to fix bug with esc key in lightbox
                });
                
                elm.click();
            }
        }
    });
}(Tygh, Tygh.$));
