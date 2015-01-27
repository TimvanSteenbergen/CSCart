
{assign var="data_id" value=$data_id|default:"pages_list"}
{math equation="rand()" assign="rnd"}
{assign var="data_id" value="`$data_id`_`$rnd`"}
{assign var="view_mode" value=$view_mode|default:"mixed"}
{if $item_ids && !$item_ids|is_array && $type != "table"}
        {assign var="item_ids" value=","|explode:$item_ids}
{/if}
{assign var="start_pos" value=$start_pos|default:0}

{script src="js/tygh/picker.js"}

{if $view_mode != "list"}

    <div class="clearfix">
        {if $extra_var}
            {assign var="extra_var" value=$extra_var|escape:url}
        {/if}
        
        {if !$no_container}<div class="buttons-container pull-right">{/if}{if $picker_view}[{/if}
            {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="news.picker?display=`$display`&picker_for=`$picker_for`&extra=`$extra_var`&checkbox_name=`$checkbox_name`&data_id=`$data_id`"|fn_url but_text=__("add_news") but_role="add" but_target_id="content_`$data_id`" but_meta="btn pull-right cm-dialog-opener" but_icon="icon-plus"}
        {if $picker_view}]{/if}{if !$no_container}</div>{/if}
        
        <div class="hidden" id="content_{$data_id}" title="{$but_text|default:__("add_news")}">
        </div>
    </div>

{/if}

{if $view_mode != "button"}
    <input id="n{$data_id}_ids" type="hidden" name="{$input_name}" value="{if $item_ids}{","|implode:$item_ids}{/if}" />
    <table width="100%" class="table table-middle">
    <thead>
    <tr>
        {if $positions}<th>{__("position_short")}</th>{/if}
        <th>{__("name")}</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tbody id="{$data_id}"{if !$item_ids} class="hidden"{/if}>
    {include file="addons/news_and_emails/pickers/news/js.tpl" news_id="`$ldelim`news_id`$rdelim`" holder=$data_id input_name=$input_name clone=true hide_link=$hide_link hide_input=true position_field=$positions position="0"}
    {if $item_ids}
    {foreach name="items" from=$item_ids item="p_id"}
        {include file="addons/news_and_emails/pickers/news/js.tpl" news_id=$p_id holder=$data_id input_name=$input_name hide_link=$hide_link hide_input=true first_item=$smarty.foreach.items.first position_field=$positions position=$smarty.foreach.items.iteration+$start_pos}
    {/foreach}
    {/if}
    </tbody>
    <tbody id="{$data_id}_no_item"{if $item_ids} class="hidden"{/if}>
    <tr class="no-items">
        <td colspan="{if $positions}3{else}2{/if}"><p>{$no_item_text|default:__("no_items")}</p></td>
    </tr>
    </tbody>
    </table>
{/if}