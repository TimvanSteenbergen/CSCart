{if $items}
<p class="ty-center image-border">
    {foreach from=$items item=image}
        <img src="{$image.image_path}" width="{$image.image_x}" height="{$image.image_y}" alt="{$image.alt}" />
    {/foreach}
</p>
{/if}