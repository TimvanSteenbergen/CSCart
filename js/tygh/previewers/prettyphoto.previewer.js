/* previewer-description:text_prettyphoto */
(function(_, $) {
    $.loadCss(['js/lib/prettyphoto/css/prettyPhoto.css']);
    $.getScript('js/lib/prettyphoto/js/jquery.prettyPhoto.js');
    
    $.cePreviewer('handlers', {
        display: function(elm) {
            var inited = elm.data('inited');
            
            if (inited != true) {
                var image_id = elm.data('caImageId');
                var elms = $('a[data-ca-image-id="' + image_id + '"]');

                // prettyPhoto works with rel attributes by default, so we
                // need to copy data attribute to rel
                if (elms.length == 1) {
                    // use prettyPhoto rel to show single image without gallery tools
                    elms.attr('rel', 'prettyPhoto');
                } else {
                    elms.attr('rel', image_id);
                }
                elms.data('inited', true);
                
                elms.prettyPhoto({
                    keyboard_shortcuts: false,
                    gallery_markup: '',
                    deeplinking: false,
                    social_tools: '',
                    callback: function() {
                        $.popupStack.remove('prettyPhoto');
                    }
                });

                elms.each(function() {
                    $(this).click(function() {
                        $.popupStack.remove('prettyPhoto');
                        $.popupStack.add({
                            name: 'prettyPhoto',
                            close: function() {
                                $.prettyPhoto.close();
                            }
                        });
                    });
                });

                elm.click();

                $(_.doc).keydown(function(e){
                    switch(e.keyCode) {
                        case 37:
                            $.prettyPhoto.changePage('previous');
                            break;
                        case 39:
                            $.prettyPhoto.changePage('next');
                            break;
                    }
                });
            }
        }
    });
}(Tygh, Tygh.$));
