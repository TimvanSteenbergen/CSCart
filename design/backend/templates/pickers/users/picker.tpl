{math equation="rand()" assign="rnd"}
{assign var="data_id" value="`$data_id`_`$rnd`"}
{assign var="view_mode" value=$view_mode|default:"mixed"}

{script src="js/tygh/picker.js"}

{if $item_ids && !$item_ids|is_array}
    {assign var="item_ids" value=","|explode:$item_ids}
{/if}

{assign var="display" value=$display|default:"checkbox"}

{if $view_mode != "list"}

    {include file="views/profiles/components/profiles_scripts.tpl"}

    {if $extra_var}
        {assign var="extra_var" value=$extra_var|escape:url}
    {/if}

    {if $display == "checkbox"}
        {assign var="_but_text" value=__("add_users")}
    {elseif $display == "radio"}
        {assign var="_but_text" value=__("choose")}
    {/if}

    {if $but_text}
        {assign var="_but_text" value=__("select_customer")}
    {/if}

    {if $placement == 'right'}
        <div class="clearfix">
            <div class="pull-right">
    {/if}
        {include file="buttons/button.tpl" but_id="opener_picker_`$data_id`" but_href="profiles.picker?display=`$display`&extra=`$extra_var`&picker_for=`$picker_for`&data_id=`$data_id`&shared_force=`$shared_force`"|fn_url but_text=$_but_text but_role="add" but_target_id="content_`$data_id`" but_meta="cm-dialog-opener `$but_meta`" but_icon=$but_icon}
    {if $placement == 'right'}
        </div></div>
    {/if}

{/if}

{if $view_mode != "button"}
    {if $display != "radio"}
        <input id="u{$data_id}_ids" type="hidden" name="{$input_name}" value="{if $item_ids}{","|implode:$item_ids}{/if}" />

        <table width="100%" class="table table-middle">
        <thead>
        <tr>
            <th width="100%">{__("person_name")}</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="{$data_id}"{if !$item_ids} class="hidden"{/if}>
        {include file="pickers/users/js.tpl" user_id="`$ldelim`user_id`$rdelim`" email="`$ldelim`email`$rdelim`" user_name="`$ldelim`user_name`$rdelim`" holder=$data_id clone=true}
        {if $item_ids}
        {foreach from=$item_ids item="user" name="items"}
            {assign var="user_info" value=$user|fn_get_user_short_info}
            {include file="pickers/users/js.tpl" user_id=$user email=$user_info.email user_name="`$user_info.firstname` `$user_info.lastname`" holder=$data_id first_item=$smarty.foreach.items.first}
        {/foreach}
        {/if}
        </tbody>
        <tbody id="{$data_id}_no_item"{if $item_ids} class="hidden"{/if}>
        <tr class="no-items">
            <td colspan="2"><p>{$no_item_text|default:__("no_items") nofilter}</p></td>
        </tr>
        </tbody>
        </table>
    {/if}
{/if}

{if $view_mode != "list"}
    <div class="hidden" id="content_{$data_id}" title="{$_but_text}">
    </div>
{/if}