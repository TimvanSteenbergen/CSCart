{if !$order_status_descr}
    {if !$status_type}{assign var="status_type" value=$smarty.const.STATUSES_ORDER}{/if}
    {assign var="order_status_descr" value=$status_type|fn_get_simple_statuses}
{/if}

{strip}
{if $display == "view"}
    {$order_status_descr.$status}
{elseif $display == "select"}
    {html_options name=$name options=$order_status_descr selected=$status id=$select_id}
{elseif $display == "checkboxes"}
    {html_checkboxes name=$name options=$order_status_descr selected=$status columns=$columns|default:4 assign=_html_checkboxes labels=false}
    {foreach $_html_checkboxes as $item}
    	<label>{$item nofilter}</label>
    {/foreach}
{/if}
{/strip}
