<select name="{$name}" {if $select_class}class="{$select_class}"{/if}>
    {if !$hide_root}
    <option value="0" {if $id == 0}selected="selected"{/if}>- {$root_text|default:__("all_categories")} -</option>
    {/if}
    {foreach from=0|fn_get_plain_categories_tree:false item="cat" name="cat"}
        {if $cat.store}
            {if !$smarty.foreach.cat.first}
                </optgroup>
            {/if}
            <optgroup label="{$cat.category}">
            {assign var="close_optgroup" value=true}
        {else}
            <option value="{$cat.category_id}" {if $cat.disabled}disabled="disabled"{/if} {if $id == $cat.category_id}selected="selected"{/if} title="{$cat.category}">{$cat.category|truncate:25:"...":true|escape|indent:$cat.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
        {/if}
    {/foreach}
    {if $close_optgroup}
        </optgroup>
    {/if}
</select>