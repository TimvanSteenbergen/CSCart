{if $search.p_ids}
    {assign var="product_ids" value=","|explode:$search.p_ids}
{/if}
    {include file="pickers/products/picker.tpl" data_id="added_products" but_text=__("add") item_ids=$product_ids input_name="p_ids" type="links" no_container=true picker_view=true}
    {assign var="views" value="products"|fn_get_views}
    {if $views}
    {__("or_saved_search")}:&nbsp;
    <select name="product_view_id">
        <option value="0">--</option>
        {foreach from=$views item="f"}
            <option value="{$f.view_id}" {if $search.product_view_id == $f.view_id}selected="selected"{/if}>{$f.name}</option>
        {/foreach}
    </select>
    {/if}