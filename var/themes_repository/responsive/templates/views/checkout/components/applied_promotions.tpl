<div id="applied_promotions">
    <span class="ty-strong">{__("text_applied_promotions")}</span>
    <ul>
    {foreach from=$cart.applied_promotions item="promotion"}
        <li>
            {if $promotion.short_description|strip_tags|trim}
                <a id="sw_promo_description_{$promotion.promotion_id}"class="cm-combination ty-dashed-link">{$promotion.name nofilter}</a>
                <div id="promo_description_{$promotion.promotion_id}" class="ty-wysiwyg-content hidden">{$promotion.short_description nofilter}</div>
            {else}
                {$promotion.name nofilter}
            {/if}
        </li>
    {/foreach}
    </ul>
<!--applied_promotions--></div>