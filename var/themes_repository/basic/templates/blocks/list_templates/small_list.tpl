{if $products}

{if $show_price_values && $settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart" && !$auth.user_id}
{assign var="show_price_values" value="0"}
{else}
{assign var="show_price_values" value="1"}
{/if}

{if !$no_sorting}
    {include file="views/products/components/sorting.tpl"}
{/if}
{if !$no_pagination}
    {include file="common/pagination.tpl"}
{/if}

{if $products|@sizeof < $columns}
{assign var="columns" value=$products|@sizeof}
{/if}
{split data=$products size=$columns|default:"2" assign="splitted_products"}

{assign var="img_width" value="2"}
{assign var="space_width" value="2"}
{math equation="(100 + space_width) / x - space_width - img_width" x=$columns|default:"2" assign="cell_width" space_width=$space_width img_width=$img_width}
{math equation="cell_width + img_width" cell_width=$cell_width img_width=$img_width assign="2_cell_width"}

{if $item_number == "Y"}
    {assign var="cur_number" value=1}
{/if}
<table class="table-width template-small-list">
{foreach from=$splitted_products item="sproducts" name="splitted_products"}
<tr>
{foreach from=$sproducts item="product" name="sproducts"}
    {if $product}
        {assign var="obj_id" value=$product.product_id}
        {assign var="obj_id_prefix" value="`$obj_prefix``$product.product_id`"}
        {include file="common/product_data.tpl" product=$product}
        <td class="{if !$smarty.foreach.splitted_products.last} border-bottom{/if}" style="width: {$cell_width}%;">        
            {assign var="form_open" value="form_open_`$obj_id`"}
            {$smarty.capture.$form_open nofilter}
            {hook name="products:product_small_list"}
            <table class="table-width">
            <tr>
                <td class="center" style="width: {$img_width}%">
                <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{include file="common/image.tpl" image_width="40" image_height="40" obj_id=$obj_id_prefix images=$product.main_pair}</a></td>
                <td class="compact" style="width: {$cell_width}%">
                    {if $item_number == "Y"}{$cur_number}.&nbsp;{math equation="num + 1" num=$cur_number assign="cur_number"}{/if}
                    {assign var="name" value="name_$obj_id"}{$smarty.capture.$name nofilter}

                    <p>
                        {assign var="old_price" value="old_price_`$obj_id`"}
                        {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}&nbsp;{/if}

                        {assign var="price" value="price_`$obj_id`"}
                        {$smarty.capture.$price nofilter}
                    </p>

                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}
                </td>
            </tr>
            </table>
            {/hook}
            {assign var="form_close" value="form_close_`$obj_id`"}
            {$smarty.capture.$form_close nofilter}
        </td>
    {else}
        <td class="valign-top{if !$smarty.foreach.splitted_products.last} border-bottom{/if}" style="width: {$cell_width}%;">&nbsp;</td>
    {/if}
    {if !$smarty.foreach.sproducts.last}
    <td style="width: {$space_width}%">&nbsp;</td>
    {/if}
{/foreach}
</tr>
{/foreach}
</table>

{if !$no_pagination}
    {include file="common/pagination.tpl"}
{/if}

{/if}

{capture name="mainbox_title"}{$title}{/capture}