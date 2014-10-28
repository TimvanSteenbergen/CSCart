{*!ACCNT{$_d}NAME{$_d}ACCNTTYPE{$_d}SCD{$_d}EXTRA{$_d}HIDDEN
ACCNT{$_d}{$addons.quickbooks.accnt_tax}{$_d}INC{$_d}{$_d}{$_d}
*}
{******************** EXPORT CUSTOMERS *******************}
!CUST{$_d}NAME{$_d}BADDR1{$_d}BADDR2{$_d}BADDR3{$_d}BADDR4{$_d}BADDR5{$_d}SADDR1{$_d}SADDR2{$_d}SADDR3{$_d}SADDR4{$_d}SADDR5{$_d}PHONE1{$_d}FAXNUM{$_d}EMAIL{$_d}CONT1{$_d}SALUTATION{$_d}COMPANYNAME{$_d}FIRSTNAME{$_d}LASTNAME
{foreach from=$order_users item="order"}
CUST{$_d}{$order.lastname}, {$order.firstname}{$_d}{$order.b_firstname} {$order.b_lastname}{$_d}{$order.b_address} {$order.b_address_2}{$_d}{$order.b_city}{$_d}"{$order.b_state}, {$order.b_zipcode}"{$_d}{$order.b_country_descr}{$_d}{$order.s_firstname} {$order.s_lastname}{$_d}{$order.s_address} {$order.s_address_2}{$_d}{$order.s_city}{$_d}"{$order.s_state}, {$order.s_zipcode}"{$_d}{$order.s_country_descr}{$_d}{$order.phone}{$_d}{$order.fax}{$_d}{$order.email}{$_d}{$order.lastname}, {$order.b_firstname}{$_d}{$order.title}{$_d}{$order.company}{$_d}{$order.firstname}{$_d}{$order.lastname}
{/foreach}

{******************** EXPORT ITEMS *******************}
!INVITEM{$_d}NAME{$_d}INVITEMTYPE{$_d}DESC{$_d}PURCHASEDESC{$_d}ACCNT{$_d}ASSETACCNT{$_d}COGSACCNT{$_d}PRICE{$_d}COST{$_d}TAXABLE
{foreach from=$order_products item="product"}
INVITEM{$_d}{if $product.product_code}{$product.product_code}{else}{$product.product_id}{/if}{$_d}INVENTORY{$_d}{$product.product}{$product.selected_options}{$_d}{$product.product}{$product.selected_options}{$_d}{$addons.quickbooks.accnt_product}{$_d}{$addons.quickbooks.accnt_asset}{$_d}{$addons.quickbooks.accnt_cogs}{$_d}{$product.price}{$_d}0{$_d}N
{/foreach}
{hook name="quickbooks:export_items"}
{/hook}

{******************** EXPORT ORDERS *******************}
!TRNS{$_d}TRNSTYPE{$_d}DATE{$_d}ACCNT{$_d}NAME{$_d}CLASS{$_d}AMOUNT{$_d}DOCNUM{$_d}MEMO{$_d}ADDR1{$_d}ADDR2{$_d}ADDR3{$_d}ADDR4{$_d}ADDR5{$_d}PAID{$_d}SHIPVIA{$_d}SADDR1{$_d}SADDR2{$_d}SADDR3{$_d}SADDR4{$_d}SADDR5{$_d}TOPRINT
!SPL{$_d}TRNSTYPE{$_d}DATE{$_d}ACCNT{$_d}NAME{$_d}CLASS{$_d}AMOUNT{$_d}DOCNUM{$_d}MEMO{$_d}PRICE{$_d}QNTY{$_d}INVITEM{$_d}TAXABLE{$_d}EXTRA
!ENDTRNS
{foreach from=$orders item="order"}
TRNS{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}Accounts Receivable{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}{$order.total}{$_d}{$order.order_id}{$_d}Website Order: {$order.details|replace:"\r":" "|replace:"\n":" "|replace:"\t":" "}{$_d}{$order.b_firstname} {$order.b_lastname}{$_d}{$order.b_address} {$order.b_address_2}{$_d}"{$order.b_city}, {$order.b_state} {$order.b_zipcode}"{$_d}{$order.b_countryname}{$_d}{$_d}{if ($order.status == "P" || $order.status == "C")}Y{else}N{/if}{$_d}{$_d}{$order.s_firstname} {$order.s_lastname}{$_d}{$order.s_address} {$order.s_address_2}{$_d}"{$order.s_city}, {$order.s_state} {$order.s_zipcode}"{$_d}{$order.s_countryname}{$_d}{$_d}Y
{*********  PRODUCTS  **********}
{foreach from=$order.products item="item"}
{strip}
{assign var="_tax" value=0}
{assign var="p_id" value=$item.cart_id}
{************* calculate tax for product *********}
{foreach from=$order.taxes item=tax}
{foreach from=$tax.applies item=tax_value key=cart_id}
{if ($tax_value > 0) && ($item.cart_id==$cart_id|substr:2) && $tax.price_includes_tax == "Y"}
{math equation="tax + tax2" tax=$_tax|default:"0" tax2=$tax_value|default:"0" assign="_tax"}
{/if}
{/foreach}
{/foreach}
{************* \calculate tax for product *********}
{math equation="price*amount - tax" price=$item.price|default:"0" amount=$item.amount|default:"1" tax=$_tax|default:"0" assign="p_subtotal"}
{if $order_products.$p_id.product_code}
    {assign var="p_code" value=$order_products.$p_id.product_code}
{else}
    {assign var="p_code" value=$order_products.$p_id.product_id}
{/if}
SPL{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}{$addons.quickbooks.accnt_product}{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}-{$p_subtotal|fn_format_price}{$_d}{$order.order_id}{$_d}{$order_products.$p_id.product}{$order_products.$p_id.selected_options}{$_d}{$item.price|fn_format_price}{$_d}-{$item.amount}{$_d}{$p_code}{$_d}N{$_d}{/strip}
{*** product discount ***
{if $item.discount != 0}
SPL{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}{$addons.quickbooks.accnt_discount}{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}{$item.discount}{$_d}{$order.order_id}{$_d}DISCOUNT FOR {$p_code}{$_d}-{$item.discount}{$_d}-1{$_d}DISCOUNT FOR {$p_code}{$_d}N{$_d}
{/if*}
{*** product TAXES ***}
{foreach from=$order.taxes item=tax}
{foreach from=$tax.applies item=tax_value key=cart_id}
{if ($tax_value > 0) && ($item.cart_id==$cart_id|substr:2)}
SPL{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}{$addons.quickbooks.accnt_tax}{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}-{$tax_value|fn_format_price}{$_d}{$order.order_id}{$_d}{$tax.description} {include file="common/modifier.tpl" mod_value=$tax.rate_value mod_type=$tax.rate_type} FOR {$p_code}{$_d}{$_tax|fn_format_price}{$_d}-1{$_d}TAX{$_d}N{$_d}
{/if}
{/foreach}
{/foreach}
{/foreach}
{*********  GIFT CERTIFICATES  **********}
{hook name="quickbooks:export_gift_certificates"}
{/hook}
{**********  DISCOUNT  **********}
{if $order.subtotal_discount > 0}
SPL{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}{$addons.quickbooks.accnt_discount}{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}{$order.subtotal_discount}{$_d}{$order.order_id}{$_d}DISCOUNT{$_d}-{$order.subtotal_discount}{$_d}-1{$_d}DISCOUNT{$_d}N{$_d}
{/if}
{*********  PAID BY REWARD POINTS  **********}
{if $order.points_info.in_use.cost > 0}
SPL{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}{$addons.quickbooks.accnt_discount}{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}{$order.points_info.in_use.cost}{$_d}{$order.order_id}{$_d}DISCOUNT{$_d}-{$order.points_info.in_use.cost}{$_d}-1{$_d}DISCOUNT{$_d}N{$_d}
{/if}
{*********  PAYMENT BY GIFT CERTIFICATE  **********}
{hook name="quickbooks:export_payments"}
{/hook}
{*********  SHIPPING  **********}
{if $order.shipping_cost > 0}
{assign var="ship_name" value=""}
{foreach from=$order.shipping item=ship}
{if $ship_name}{assign var="ship_name" value="$ship_name; "}{/if}
{assign var="ship_name" value="$ship_name`$ship.shipping`"}
{/foreach}
SPL{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}{$addons.quickbooks.accnt_shipping}{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}-{$order|@fn_order_shipping_cost}{$_d}{$order.order_id}{$_d}{$ship_name}{$_d}{$order|@fn_order_shipping_cost}{$_d}-1{$_d}SHIPPING{$_d}N{$_d}
{/if}
{*********  SURCHARGE  **********} 
{if $order.payment_surcharge > 0} 
SPL{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}{$addons.quickbooks.accnt_surcharge}{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}-{$order.payment_surcharge}{$_d}{$order.order_id}{$_d}Payment processor surcharge{$_d}{$order.payment_surcharge}{$_d}-1{$_d}SURCHARGE{$_d}N{$_d}
{/if}
{********** AUTO TAX  ************}
{if !$order.taxes}
SPL{$_d}INVOICE{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}{$addons.quickbooks.accnt_tax}{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$addons.quickbooks.trns_class}{$_d}0{$_d}{$order.order_id}{$_d}TAX{$_d}{$_d}{$_d}{$_d}N{$_d}AUTOSTAX
{/if}
ENDTRNS
{/foreach}
{******************** PROCCESSED ORDERS *******************}

!TRNS{$_d}TRNSTYPE{$_d}DATE{$_d}ACCNT{$_d}NAME{$_d}AMOUNT{$_d}PAYMETH{$_d}DOCNUM
!SPL{$_d}TRNSTYPE{$_d}DATE{$_d}ACCNT{$_d}NAME{$_d}AMOUNT{$_d}DOCNUM
!ENDTRNS
{foreach from=$orders item="order"}
{if ($order.status == "P" || $order.status == "C")}
TRNS{$_d}PAYMENT{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}Undeposited Funds{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}{$order.total}{$_d}{$order.payment_method.payment}{$_d}{$order.order_id}
SPL{$_d}PAYMENT{$_d}{$order.timestamp|date_format:"%m/%d/%Y"}{$_d}Accounts Receivable{$_d}{$order.b_lastname}, {$order.b_firstname}{$_d}-{$order.total}{$_d}{$order.order_id}
ENDTRNS
{/if}
{/foreach}