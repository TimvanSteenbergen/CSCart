{** block-description:original **}
{foreach from=$items item="banner" key="key"}
    {if $banner.type == "G" && $banner.main_pair.image_id}
    <div class="ty-banner__image-wrapper">
        {if $banner.url != ""}<a href="{$banner.url|fn_url}" {if $banner.target == "B"}target="_blank"{/if}>{/if}
        {include file="common/image.tpl" images=$banner.main_pair}
        {if $banner.url != ""}</a>{/if}
    </div>
    {else}
        <div class="ty-wysiwyg-content">
            {$banner.description nofilter}
        </div>
    {/if}
{/foreach}