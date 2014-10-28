<select id="elm_{$elm_id}" name="page_data[form][elements_data][{$num}][element_type]" onchange="fn_check_element_type(this.value, this.id, '{$selectable_elements}');">
    <optgroup label="{__("base")}">
    <option value="{$smarty.const.FORM_SELECT}" {if $element_type == $smarty.const.FORM_SELECT}selected="selected"{/if}>{__("selectbox")}</option>
    <option value="{$smarty.const.FORM_RADIO}" {if $element_type == $smarty.const.FORM_RADIO}selected="selected"{/if}>{__("radiogroup")}</option>
    <option value="{$smarty.const.FORM_MULTIPLE_CB}" {if $element_type == $smarty.const.FORM_MULTIPLE_CB}selected="selected"{/if}>{__("multiple_checkboxes")}</option>
    <option value="{$smarty.const.FORM_MULTIPLE_SB}" {if $element_type == $smarty.const.FORM_MULTIPLE_SB}selected="selected"{/if}>{__("multiple_selectbox")}</option>
    <option value="{$smarty.const.FORM_CHECKBOX}" {if $element_type == $smarty.const.FORM_CHECKBOX}selected="selected"{/if}>{__("checkbox")}</option>
    <option value="{$smarty.const.FORM_INPUT}" {if $element_type == $smarty.const.FORM_INPUT}selected="selected"{/if}>{__("input_field")}</option>
    <option value="{$smarty.const.FORM_TEXTAREA}" {if $element_type == $smarty.const.FORM_TEXTAREA}selected="selected"{/if}>{__("textarea")}</option>
    <option value="{$smarty.const.FORM_HEADER}" {if $element_type == $smarty.const.FORM_HEADER}selected="selected"{/if}>{__("header")}</option>
    <option value="{$smarty.const.FORM_SEPARATOR}" {if $element_type == $smarty.const.FORM_SEPARATOR}selected="selected"{/if}>{__("separator")}</option>
    </optgroup>
    <optgroup label="{__("special")}">
    {hook name="pages:form_elements"}
    <option value="{$smarty.const.FORM_DATE}" {if $element_type == $smarty.const.FORM_DATE}selected="selected"{/if}>{__("date")}</option>
    <option value="{$smarty.const.FORM_EMAIL}" {if $element_type == $smarty.const.FORM_EMAIL}selected="selected"{/if}>{__("email")}</option>
    <option value="{$smarty.const.FORM_NUMBER}" {if $element_type == $smarty.const.FORM_NUMBER}selected="selected"{/if}>{__("number")}</option>
    <option value="{$smarty.const.FORM_PHONE}" {if $element_type == $smarty.const.FORM_PHONE}selected="selected"{/if}>{__("phone")}</option>
    <option value="{$smarty.const.FORM_COUNTRIES}" {if $element_type == $smarty.const.FORM_COUNTRIES}selected="selected"{/if}>{__("countries_list")}</option>
    <option value="{$smarty.const.FORM_STATES}" {if $element_type == $smarty.const.FORM_STATES}selected="selected"{/if}>{__("states_list")}</option>
    <option value="{$smarty.const.FORM_FILE}" {if $element_type == $smarty.const.FORM_FILE}selected="selected"{/if}>{__("file")}</option>
    <option value="{$smarty.const.FORM_REFERER}" {if $element_type == $smarty.const.FORM_REFERER}selected="selected"{/if}>{__("referer")}</option>
    <option value="{$smarty.const.FORM_IP_ADDRESS}" {if $element_type == $smarty.const.FORM_IP_ADDRESS}selected="selected"{/if}>{__("ip_address")}</option>
    {/hook}
    </optgroup>
</select>