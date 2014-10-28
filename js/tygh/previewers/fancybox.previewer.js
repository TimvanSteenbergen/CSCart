/* previewer-description:tmpl_text_fancybox */
(function(_, $) {
    $.loadCss(['js/lib/fancybox/jquery.fancybox-1.3.4.css']);
    $.getScript('js/lib/fancybox/jquery.fancybox-1.3.4.pack.js');

    $.cePreviewer('handlers', {
        display: function(elm) {
            var inited = elm.data('inited');
            
            if (inited != true) {
                var image_id = elm.data('caImageId');
                var elms = $('a[data-ca-image-id="' + image_id + '"]');

                // fancybox works with rel attributes by default, so we
                // need to copy data attribute to rel
                elms.each(function() {
                    $(this).attr('rel', $(this).data('caImageId'));
                });

                elms.data('inited', true);
                
                elms.fancybox({
                    titlePosition: 'inside',
                    titleFormat: function (title, cArray, cIndex, cOpts) {
                        return '<div id="tip7-title">' + (cArray.length > 1 ? ((cIndex + 1) + '/' + cArray.length) : '') + (title && title.length ? '&nbsp;&nbsp;&nbsp;<b>' + title + '</b>' : '' ) + '</div>';
                    },
                    onStart: function() {
                        $.popupStack.add({
                            name: 'fancybox',
                            close: function() {
                                $.fancybox.close();
                            }
                        });
                    },
                    onComplete: function() {
                        $.popupStack.remove('fancybox');
                        $.popupStack.add({
                            name: 'fancybox',
                            close: function() {
                                $.fancybox.close();
                            }
                        });
                    },
                    onClosed: function() {
                        $.popupStack.remove('fancybox');
                    }
                });
                
                elm.click();
            }
        }
    });
}(Tygh, Tygh.$));
