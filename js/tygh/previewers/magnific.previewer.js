/* previewer-description:text_magnific */
(function(_, $) {
    $.loadCss(['js/lib/magnificpopup/css/magnific-popup.css']);
    $.getScript('js/lib/magnificpopup/js/jquery.magnific-popup.min.js');
   
    $.cePreviewer('handlers', {
        display: function(elm) {

            var inited = elm.data('inited');
            
            if (inited != true) {
                var image_id = elm.data('caImageId');
                var elms = $('a[data-ca-image-id="' + image_id + '"]');
                var hasImagePreview = elms.find('img').length ? true : false;

                elms.data('inited', true);
                
                elms.magnificPopup({
                    type: 'image',
                    mainClass: 'mfp-with-zoom',
                    midClick: true,
                    tLoading: '',
                    gallery: {
                        enabled: true,
                        navigateByImgClick: true,
                        preload: [0,1]
                    },
                    zoom: {
                        enabled: hasImagePreview,
                        duration: 300,
                        easing: 'ease-in-out'
                    }
                });

                elm.click();
            }
        }
    });
}(Tygh, Tygh.$));