{literal}
<script type="text/javascript">
    function fn_check_element_type(elm, id, selectable_elements)
    {
        var $ = Tygh.$;
        var elem_id = id.replace('elm_', 'box_element_variants_');
        $('#' + elem_id).toggleBy(selectable_elements.indexOf(elm) == -1);

        // Hide description box for separator
        $('#descr_' + id).toggleBy((elm == 'D'));
        $('#hr_' + id).toggleBy((elm != 'D'));

        $('#req_' + id).prop('disabled', (elm == 'D' || elm == 'H'));
    }

    function fn_go_check_element_type(id, selectable_elements)
    {
        var $ = Tygh.$;
        var id = id || '';

        var c = parseInt(id.replace('add_elements', '').replace('_', ''));
        c = (isNaN(c))? 1 : c++;
        var c_id = c.toString();
        $('#elm_add_variants_' + c_id).trigger('change');
    }
</script>
{/literal}

{assign var="allow_save" value=true}
{if "ULTIMATE"|fn_allowed_for}
    {assign var="allow_save" value=$page_data|fn_allow_save_object:"pages"}
{/if}

<table class="table hidden-inputs table-middle">
<thead>
    <tr>
        <th width="5%">{__("position_short")}</th>
        <th width="30%">{__("name")}</th>
        <th width="30%">{__("type")}</th>
        <th width="5%">{__("required")}</th>
        <th width="10%">&nbsp;</th>
        <th width="10%" class="right">{__("status")}</th>
    </tr>
</thead>
{foreach from=$elements item="element" name="fe_e"}
{assign var="num" value=$smarty.foreach.fe_e.iteration}
<tbody class="cm-row-item cm-row-status-{$element.status|lower}">
<tr>
    <td class="nowrap">
        <span id="on_box_element_variants_{$element.element_id}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combination-options-{$id}"><span class="exicon-expand"></span></span>
        <span id="off_box_element_variants_{$element.element_id}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand hidden cm-combination-options-{$id}"><span class="exicon-collapse"></span> </span>
        <input type="hidden" name="page_data[form][elements_data][{$num}][element_id]" value="{$element.element_id}" />
        <input class="input-micro" type="text" size="3" name="page_data[form][elements_data][{$num}][position]" value="{$element.position}" /></td>
    <td>
        <input id="descr_elm_{$element.element_id}" class="{if $element.element_type == $smarty.const.FORM_SEPARATOR}hidden{/if}" type="text" name="page_data[form][elements_data][{$num}][description]" value="{$element.description}" />
        <hr id="hr_elm_{$element.element_id}" width="100%" {if $element.element_type != $smarty.const.FORM_SEPARATOR}class="hidden"{/if} /></td>
    <td>
        {include file="addons/form_builder/views/pages/components/element_types.tpl" element_type=$element.element_type elm_id=$element.element_id}</td>
    <td class="center">
        <input type="hidden" name="page_data[form][elements_data][{$num}][required]" value="N" />
        <input id="req_elm_{$element.element_id}" type="checkbox" {if "HD"|strstr:$element.element_type}disabled="disabled"{/if} name="page_data[form][elements_data][{$num}][required]" value="Y" {if $element.required == "Y"}checked="checked"{/if} class="checkbox" /></td>
    <td>
        <div class="hidden-tools">
            {include file="buttons/multiple_buttons.tpl" only_delete="Y"}
        </div>
    </td>
    <td class="right">
        {include file="common/select_popup.tpl" id=$element.element_id prefix="elm" status=$element.status hidden="" object_id_name="element_id" table="form_options" non_editable=!$allow_save}
    </td>
</tr>
<tr id="box_element_variants_{$element.element_id}" class="{if !$selectable_elements|substr_count:$element.element_type}hidden{/if} row-more row-gray hidden">
    <td>&nbsp;</td>
    <td colspan="4">
        <table class="table table-middle">
        <thead>
            <tr class="cm-first-sibling">
                <th width="5%" class="left">{__("position_short")}</th>
                <th>{__("name")}</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        {foreach from=$element.variants item=var key="vnum"}
        <tr class="cm-first-sibling cm-row-item">
            <td>
                <input type="hidden" name="page_data[form][elements_data][{$num}][variants][{$vnum}][element_id]" value="{$var.element_id}" />
                <input class="input-micro" size="3" type="text" name="page_data[form][elements_data][{$num}][variants][{$vnum}][position]" value="{$var.position}" /></td>
            <td><input type="text" class="span7" name="page_data[form][elements_data][{$num}][variants][{$vnum}][description]" value="{$var.description}" /></td>
            <td>
                <div class="hidden-tools">
                    {include file="buttons/multiple_buttons.tpl" only_delete="Y"}
                </div>
            </td>
        </tr>
        {/foreach}
        {math equation="x + 1" assign="vnum" x=$vnum|default:0}
        <tr id="box_elm_variants_{$element.element_id}" class="cm-row-item cm-elm-variants">
            <td><input class="input-micro" size="3" type="text" name="page_data[form][elements_data][{$num}][variants][{$vnum}][position]" /></td>
            <td><input type="text" class="span7" name="page_data[form][elements_data][{$num}][variants][{$vnum}][description]" /></td>
            <td>
                <div class="hidden-tools">
                    {include file="buttons/multiple_buttons.tpl" item_id="elm_variants_`$element.element_id`" tag_level="5"}
                </div>
            </td>
        </tr>
        </table>
    </td>
    <td>&nbsp;</td>
</tr>
</tbody>
{/foreach}

{math equation="x + 1" assign="num" x=$num|default:0}
<tbody class="cm-row-item cm-row-status-a" id="box_add_elements">
<tr class="no-border">
    <td class="right">
        <input class="input-micro" size="3" type="text" name="page_data[form][elements_data][{$num}][position]" value="" /></td>
    <td>
        <input id="descr_elm_add_variants" type="text" name="page_data[form][elements_data][{$num}][description]" value="" />
        <hr id="hr_elm_add_variants" class="hidden" /></td>
    <td>
        {include file="addons/form_builder/views/pages/components/element_types.tpl" element_type="" elm_id="add_variants"}</td>
    <td class="center">
        <input type="hidden" name="page_data[form][elements_data][{$num}][required]" value="N" />
        <input id="req_elm_add_variants" type="checkbox" name="page_data[form][elements_data][{$num}][required]" value="Y" checked="checked" class="checkbox" /></td>
    <td class="left">
        <div class="hidden-tools">
            {include file="buttons/multiple_buttons.tpl" item_id="add_elements" on_add="fn_go_check_element_type();"}
        </div>
    </td>
    <td class="right">
        {include file="common/select_status.tpl" input_name="page_data[form][elements_data][`$num`][status]" display="popup"}
    </td>
</tr>
<tr id="box_element_variants_add_variants" class="row-more row-gray">
    <td>&nbsp;</td>
    <td colspan="4">
        <table class="table table-middle">
        <thead>
            <tr class="cm-first-sibling">
                <th width="5%" class="left">{__("position_short")}</th>
                <th>{__("description")}</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tr id="box_elm_variants_add_variants" class="cm-row-item cm-elm-variants">
            <td><input class="input-micro" size="3" type="text" name="page_data[form][elements_data][{$num}][variants][0][position]" /></td>
            <td><input class="span7" type="text" name="page_data[form][elements_data][{$num}][variants][0][description]" /></td>
            <td>
                <div class="hidden-tools">
                    {include file="buttons/multiple_buttons.tpl" item_id="elm_variants_add_variants" tag_level="5"}
                </div>
            </td>
        </tr>
        </table>
    </td>
    <td>&nbsp;</td>
</tr>
</tbody>


</table>
