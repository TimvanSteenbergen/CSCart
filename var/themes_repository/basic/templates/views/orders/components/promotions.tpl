{include file="common/subheader.tpl" title=__("promotions")}

{foreach from=$promotions item="promotion" name="pfe" key="promotion_id"}
<h5 class="info-field-title">{$promotion.name}</h5>

{foreach from=$order_info.promotions.$promotion_id.bonuses item="bonus"}
{if $bonus.bonus == "give_coupon"}
<div class="control-group">
    <label>{__("coupon_code")}:</label>
    {$bonus.coupon_code}
</div>
{/if}
{/foreach}


<div class="info-field-body">{$promotion.short_description nofilter}</div>
{/foreach}