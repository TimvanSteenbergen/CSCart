{foreach from=$promotions item="promotion" key="promotion_id" name="pfe"}

{if $promotion.name}
    {include file="common/subheader.tpl" title=$promotion.name}

    {foreach from=$order_info.promotions.$promotion_id.bonuses item="bonus" key="bonus_name"}
    {if $bonus_name == "give_coupon"}
    <div class="control-group">
        <label class="control-label">{__("coupon_code")}</label>
        <div class="controls">
            <a href="{"promotions.update?promotion_id=`$bonus.value`&selected_section=conditions"|fn_url}">{$bonus.coupon_code}</a>
        </div>
    </div>
    {/if}
    {/foreach}

    {$promotion.short_description nofilter}
    <p><a href="{"promotions.update?promotion_id=`$promotion_id`"|fn_url}">{__("details")}</a></p>
{else}
    <p>{foreach from=$promotion.bonuses item="bonus" key="bonus_name"}
        {assign var="lvar" value="promotion_bonus_`$bonus_name`"}<span>{__($lvar)}</span>
    {/foreach} ({__("deleted")})</p>
{/if}

{/foreach}