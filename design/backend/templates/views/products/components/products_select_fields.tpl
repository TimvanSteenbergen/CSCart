<input type="hidden" name="selected_fields[object]" value="product" />
{math equation="ceil(n/c)" assign="rows" n=$selected_fields|count c=$columns|default:"5"}

{split data=$selected_fields|sort_by:"text" size=$rows assign="splitted_selected_fields" vertical_delimition=false size_is_horizontal=true}

<table cellpadding="10" width="100%">
<tr valign="top">
    {foreach from=$splitted_selected_fields item="sfs"}
        <td>
        <ul class="unstyled">
            {foreach from=$sfs item="sf" name="foreach_sfs"}
                <li>
                    {if $sf}
                        {if $sf.disabled == "Y"}<input type="hidden" value="Y" name="selected_fields{$sf.name}" />{/if}
                        <label class="checkbox" for="elm_{$sf.name|md5}"><input type="checkbox" value="Y" name="selected_fields{$sf.name}" id="elm_{$sf.name|md5}" checked="checked" {if $sf.disabled == "Y"}disabled="disabled"{/if} class="cm-item-s" />
                        {$sf.text}</label>
                    {/if}
                </li>
            {/foreach}
        </ul>
        </td>
    {/foreach}
</tr></table>
<p>
{include file="common/check_items.tpl" check_target="s" style="links"}
</p>