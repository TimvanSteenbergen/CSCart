<div class="ty-wysiwyg-content">
{foreach from=$promotions key="promotion_id" item="promotion"}
    {hook name="promotions:list_item"}
        {include file="common/subheader.tpl" title=$promotion.name}
        {$promotion.detailed_description|default:$promotion.short_description nofilter}
    {/hook}
{foreachelse}
    <p>{__("text_no_active_promotions")}</p>
{/foreach}
</div>

{capture name="mainbox_title"}{__("active_promotions")}{/capture}