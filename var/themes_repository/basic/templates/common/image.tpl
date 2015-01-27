{strip}

{if $capture_image}
    {capture name="image"}
{/if}

{if !$obj_id}
    {math equation="rand()" assign="obj_id"}
{/if}

{$image_data=$images|fn_image_to_display:$image_width:$image_height}
{$generate_image=$image_data.generate_image && !$external}


{if $show_detailed_link}
    <a id="det_img_link_{$obj_id}" {if $image_data.detailed_image_path && $image_id}data-ca-image-id="{$image_id}"{/if} class="{$link_class} {if $image_data.detailed_image_path}cm-previewer previewer{/if}" {if $image_data.detailed_image_path}href="{$image_data.detailed_image_path}" title="{$images.detailed.alt}"{/if}>
{/if}
{if $image_data.image_path}
    <img class="{$valign} {$class} {if $generate_image}spinner{/if} pict"  {if $obj_id && !$no_ids}id="det_img_{$obj_id}"{/if} {if $generate_image}data-ca-image-path="{$image_data.image_path}"{/if} src="{if $generate_image}{$images_dir}/icons/spacer.gif{else}{$image_data.image_path}{/if}" width="{$image_data.width}" height="{$image_data.height}" alt="{$image_data.alt}" title="{$image_data.alt}" {if $image_onclick}onclick="{$image_onclick}"{/if} />
{else}
    <div class="no-image" style="width: {$image_width|default:$image_height}px; height: {$image_height|default:$image_width}px;"><i class="icon-image" title="{__("no_image")}"></i></div>
{/if}
{if $show_detailed_link}
    {if $images.detailed_id}
        <span class="view-larger-image"></span>
    {/if}
</a>
{/if}

{if $capture_image}
    {/capture}
    {capture name="icon_image_path"}
        {$image_data.image_path}
    {/capture}
    {capture name="detailed_image_path"}
        {$image_data.detailed_image_path}
    {/capture}
{/if}

{/strip}
