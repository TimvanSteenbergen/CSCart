{** block-description:products2 **}

{if $block.properties.hide_add_to_cart_button == "Y"}
    {assign var="_show_add_to_cart" value=false}
{else}
    {assign var="_show_add_to_cart" value=true}
{/if}

{include file="blocks/list_templates/products2.tpl" 
products=$items 
columns=$block.properties.number_of_columns 
hide_add_to_cart_button=$block.properties.hide_add_to_cart_button 
obj_prefix="`$block.block_id`000" 
item_number=$block.properties.item_number
show_trunc_name=true 
show_price=true 
show_add_to_cart=$_show_add_to_cart 
show_list_buttons=false
add_to_cart_meta="text-button-add"
but_role="text"}