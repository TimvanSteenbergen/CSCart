<div class="ty-orders-promotion">
{include file="common/subheader.tpl" title=__("promotions")}

{foreach from=$promotions item="promotion" name="pfe" key="promotion_id"}
<h5 class="ty-orders-promotion__title">{$promotion.name}</h5>

    {foreach from=$order_info.promotions.$promotion_id.bonuses item="bonus"}
    {if $bonus.bonus == "give_coupon"}
    <div class="ty-control-group">
        <label class="ty-orders-promotion__coupon-title">{__("coupon_code")}:</label>
        {$bonus.coupon_code}
    </div>
    {/if}
    {/foreach}

<div class="ty-orders-promotion__description">{$promotion.short_description nofilter}</div>
{/foreach}
</div>