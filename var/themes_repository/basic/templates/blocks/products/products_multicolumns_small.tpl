{** block-description:tmpl_multicolumns_small **}

{script src="js/tygh/exceptions.js"}

{if $block.properties.hide_add_to_cart_button == "Y"}
    {assign var="_show_add_to_cart" value=false}
{else}
    {assign var="_show_add_to_cart" value=true}
{/if}

{include file="blocks/list_templates/small_list.tpl" 
products=$items 
columns=$block.properties.number_of_columns 
form_prefix="block_manager" 
no_sorting="Y" 
no_pagination="Y" 
obj_prefix="`$block.block_id`000" 
item_number=$block.properties.item_number 
show_trunc_name=true 
show_price=true 
show_add_to_cart=$_show_add_to_cart
add_to_cart_meta="text-button-add"
show_list_buttons=false
but_role="text"}