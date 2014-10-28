{if $order_info.fraud_checking}
{capture name="tools_list"}
        <dl class='dl-horizontal dl-horizontal-small'>
            <dt>{__("ip_address")}:</dt>
            <dd>{$order_info.ip_address}</dd>

            {if $order_info.fraud_checking.B}
                <dt>{__("reason")}:</dt>
                <dd>
                    {foreach from=$order_info.fraud_checking.B item=item}
                        {__($item)}<br>
                    {/foreach}
                </dd>
            {/if}

            {if $order_info.fraud_checking.G}
            <dt>{__("reason")}:</dt>
            <dd>
                {foreach from=$order_info.fraud_checking.G item=item}
                    {__($item)}<br>
                {/foreach}
            </dd>
            {/if}

            <dt>{__("risk_factor")}:</dt>
            <dd><strong>{$order_info.fraud_checking.risk_factor|default:__("not_available")}</strong></dd>

            <dt>{__("decision")}:</dt>
            <dd>
                {if $order_info.fraud_checking.risk_factor > $addons.anti_fraud.anti_fraud_risk_factor}
                    {__("anti_fraud_order_not_approved")}.
                {else}
                    {__("anti_fraud_order_approved")}.
                {/if}
            </dd>

        </dl>
{/capture}

{capture name="but_text"}
    {__("fraud_risk")}{if $order_info.fraud_checking.risk_factor}: {$order_info.fraud_checking.risk_factor}{/if}
{/capture}

<a class="cm-popover btn hand" data-toggle="popover" data-placement="bottom" data-content="{$smarty.capture.tools_list nofilter}" title="" data-original-title="{__("fraud_checking")}">{$smarty.capture.but_text}</a>

{/if}