<div class="statistic-list pull-right clearfix" id="balance_total">
    <table>
        <tr>
            <td>{__("balance_carried_forward")}:</td>
            <td class="{if $total.BCF > 0}text-error{else}text-success{/if}">{include file="common/price.tpl" value=$total.BCF}</td>
        </tr>
        <tr>
            <td>{__("sales_period_total")}:</td>
            <td class="text-success">{include file="common/price.tpl" value=$total.NO}</td>
        </tr>
        <tr>
            <td>{__("total_period_payout")}:</td>
            <td>{include file="common/price.tpl" value=$total.TPP}</td>
        </tr>
        <tr>
            <td>{__("total_amount_due")}:</td>
            <td>{include file="common/price.tpl" value=$total.LPM}</td>
        </tr>
        <tr>
            <td>{__("total_unpaid_balance")}:</td>
            <td class="{if $total.TOB > 0}text-error{else}text-success{/if}">{include file="common/price.tpl" value=$total.TOB}</td>
        </tr>
    </table>
<!--balance_total--></div>