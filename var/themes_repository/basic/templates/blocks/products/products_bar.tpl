{** block-description:products_bar **}

{if $block.properties.hide_add_to_cart_button == "Y"}
    {assign var="_show_add_to_cart" value=false}
{else}
    {assign var="_show_add_to_cart" value=true}
{/if}
{include file="blocks/list_templates/products_bar.tpl"
    title_block=$block.name
    products=$items    
    no_sorting="Y" 
    obj_prefix="`$block.block_id`000"
    show_trunc_name=true  
    show_old_price=true 
    show_price=true       
    show_clean_price=true
    show_add_to_cart=$_show_add_to_cart|default:true
    but_role=action
}