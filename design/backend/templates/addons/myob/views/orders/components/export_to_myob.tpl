{foreach from=$orders item=order}
{foreach from=$order.products item=item}
"{$order.lastname}","{$order.firstname}","{$order.s_address}","{$order.s_address_2}","{$order.s_city} {$order.s_state_descr}","{$order.s_country_descr} {$order.s_zipcode}",Y,W{$order.order_id|string_format:"%07d"},{$order.order_date},,,P,{$item.product_code},{$item.amount},"{$item.prod_opts_description}",{$item.price},{$item.subtotal},{$item.discount},{$item.total},{$item.total},,,,,,,,GST,,,,,,,,,,I,AUD,,1,0,0,0,0,{$item.paid_amount},{$order.payment_method.payment},,,,,,,,,,,,WEB-{$order.user_id|string_format:"%011d"},,
{/foreach}
{/foreach}