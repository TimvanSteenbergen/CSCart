{include file="common/letter_header.tpl"}

{__("hello")},<br /><br />

<strong>{__("payment_details")}</strong>:<br />
{__("sales_period")}: {$payment.start_date} - {$payment.end_date}<br />
{__("amount")}: {include file="common/price.tpl" value=$payment.amount}<br />
{__("payment_method")}: {$payment.payment_method}<br />
{__("comments")}: {$payment.comments}<br />

{include file="common/letter_footer.tpl"}