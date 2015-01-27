{script src="js/addons/image_zoom/cloudzoom.js"}

<script type="text/javascript">
//<![CDATA[
(function(_, $) {

    $.ceEvent('on', 'ce.commoninit', function(context) {
        context.find('.cm-previewer').each(function(){

            var elm = $(this).find('img');

            if(elm.data('CloudZoom') == undefined) {
                elm.attr('data-cloudzoom', 'zoomImage: "' + $(this).prop('href') + '"')
                    .CloudZoom({
                        tintColor: '{$addons.image_zoom.cz_tint_color_picker|default:"#ffffff"}',
                        tintOpacity: {$addons.image_zoom.cz_opacity|default:0.6},
                        animationTime: {$addons.image_zoom.cz_animation_time|default:200},
                        easeTime: {$addons.image_zoom.cz_ease_time|default:200},
                        zoomFlyOut: {if $addons.image_zoom.cz_zoom_fly_out == 'Y'}true{else}false{/if},
                        zoomSizeMode: '{$addons.image_zoom.cz_zoom_size_mode|default:"zoom"}',
                        captionPosition: '{$addons.image_zoom.cz_caption_position|default:"bottom"}',
                        {if $addons.image_zoom.cz_zoom_position == 'inside'}zoomOffsetX: 0,{/if}
                        zoomPosition: '{$addons.image_zoom.cz_zoom_position|default:3}',
                        autoInside: 767
                });
            }

        });
    });

}(Tygh, Tygh.$));
//]]>
</script>
