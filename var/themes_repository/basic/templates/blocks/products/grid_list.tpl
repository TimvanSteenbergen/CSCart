{** block-description:products_grid **}

{if $block.properties.hide_add_to_cart_button == "Y"}
    {assign var="_show_add_to_cart" value=false}
{else}
    {assign var="_show_add_to_cart" value=true}
{/if}

{include file="blocks/list_templates/products_grid.tpl"
    products=$items
    columns=$block.properties.number_of_columns 
    no_sorting="Y" 
    obj_prefix="`$block.block_id`000" 
    item_number=$block.properties.item_number 
    no_pagination=true
    show_name=true       
    show_sku=true       
    show_rating=true       
    show_features=true 
    show_prod_descr=true       
    show_old_price=true 
    show_price=true       
    show_clean_price=true       
    show_list_discount=true
    show_discount_label=true       
    show_product_amount=true
    show_qty=true      
    show_min_qty=true       
    show_product_edp=true 
    show_add_to_cart=$_show_add_to_cart|default:true       
    show_list_buttons=false      
    show_descr=true       
    but_role=action
    separate_buttons=true}