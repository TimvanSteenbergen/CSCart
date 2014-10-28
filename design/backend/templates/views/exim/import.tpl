{capture name="mainbox"}

{capture name="tabsbox"}

{assign var="p_id" value=$pattern.pattern_id}
<div id="content_{$p_id}">

    {if $pattern.notes}
        {capture name="local_notes"}
            {foreach from=$pattern.notes item=note}
                {eval var=__($note)}
                <hr />
            {/foreach}
        {/capture}
    {/if}
    
    {include file="common/subheader.tpl" title=$pattern.name notes=$smarty.capture.local_notes notes_id=$p_id target="#import_fields_`$p_id`"}
    <div id="import_fields_{$p_id}" class="in collapse">
        <p class="p-notice">{__("text_exim_import_notice")}</p>
        {split data=$pattern.export_fields size=5 assign="splitted_fields" simple=true size_is_horizontal=true}
        <table class="table table-striped table-exim">
            <tr>
            {foreach from=$splitted_fields item="fields"}
                <td>
                    <ul class="unstyled">
                    {foreach from=$fields key="field" item="f"}
                        <li>{if $f.required}<strong>{/if}{$field}{if $f.required}</strong>{/if}</li>
                    {/foreach}
                    </ul>
                </td>
            {/foreach}
            </tr>
        </table>
    </div>

    {include file="common/subheader.tpl" title=__("import_options") target="#import_options_`$p_id`"}
    <div id="import_options_{$p_id}" class="in collapse">
    <form action="{""|fn_url}" method="post" name="{$p_id}_manage_layout_form" enctype="multipart/form-data" class="cm-ajax cm-comet form-horizontal form-edit">
    <input type="hidden" name="section" value="{$pattern.section}" />
    <input type="hidden" name="pattern_id" value="{$p_id}" />
    <input type="hidden" name="result_ids" value="content_{$p_id}" />

    {if $pattern.options}
    {foreach from=$pattern.options key=k item=o}
    <div class="control-group">
        <label for="{$k}" class="control-label">
            {__($o.title)}{if $o.description}{include file="common/tooltip.tpl" tooltip=__($o.description)}{/if}:
        </label>
        <div class="controls">
            {if $o.type == "checkbox"}
                <input type="hidden" name="import_options[{$k}]" value="N" />
                <input id="{$k}" class="checkbox" type="checkbox" name="import_options[{$k}]" value="Y" {if $o.default_value == "Y"}checked="checked"{/if} />
            {elseif $o.type == "input"}
                <input id="{$k}" class="input-large" type="text" name="import_options[{$k}]" value="{$o.default_value}" />
            {elseif $o.type == "select"}
                <select name="import_options[{$k}]" id="{$k}">
                    {foreach from=$o.variants key=vk item=vi}
                        <option value="{$vk}" {if $vk == $o.default_value}checked="checked"{/if}>{__($vi)}</option>
                    {/foreach}
                </select>
            {/if}

            {if $o.notes}
                <p class="muted">{$o.notes nofilter}</p>
            {/if}
        </div>
    </div>
    {/foreach}
    {/if}

    <div class="control-group">
        <label class="control-label">{__("csv_delimiter")}:</label>
        <div class="controls">{include file="views/exim/components/csv_delimiters.tpl" name="import_options[delimiter]"}</div>
    </div>

    <div class="control-group">
        <label class="control-label">{__("select_file")}:</label>
        <div class="controls">{include file="common/fileuploader.tpl" var_name="csv_file[0]" prefix=$p_id}</div>
    </div>

    {capture name="buttons"}
        <div class="cm-tab-tools" id="tools_{$p_id}">
            {include file="buttons/button.tpl" but_text=__("import") but_name="dispatch[exim.import]" but_role="submit-link" but_target_form="`$p_id`_manage_layout_form" but_meta="cm-tab-tools"}
            <!--tools_{$p_id}--></div>
    {/capture}

    </form>
    </div>
<!--content_{$p_id}--></div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$p_id}

{/capture}
{include file="common/mainbox.tpl" title=__("import_data") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}