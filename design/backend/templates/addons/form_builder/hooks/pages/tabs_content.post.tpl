{if $page_type == $smarty.const.PAGE_TYPE_FORM}
<div id="content_build_form">

    <div class="control-group">
        <label for="form_submit_text" class="control-label">{__("form_submit_text")}:</label>
        {assign var="form_submit_const" value=$smarty.const.FORM_SUBMIT}
        <div class="controls">
            <textarea id="form_submit_text" class="cm-wysiwyg input-textarea-long" rows="5" cols="50" name="page_data[form][general][{$form_submit_const}]" rows="5">{$form.$form_submit_const}</textarea>
        </div>
        
    </div>

    <div class="control-group">
        <label for="form_recipient" class="cm-required control-label">{__("email_to")}:</label>
        {assign var="form_recipient_const" value=$smarty.const.FORM_RECIPIENT}
        <div class="controls">
            <input id="form_recipient" class="input-text" type="text" name="page_data[form][general][{$form_recipient_const}]" value="{$form.$form_recipient_const}">
        </div>
    </div>

    <div class="control-group">
        <label for="form_is_secure" class="control-label">{__("form_is_secure")}:</label>
        {assign var="form_secure_const" value=$smarty.const.FORM_IS_SECURE}
        <div class="controls">
                <input type="hidden" name="page_data[form][general][{$smarty.const.FORM_IS_SECURE}]" value="N">
                <span class="checkbox">
                    <input type="checkbox" id="form_is_secure" value="Y" {if $form.$form_secure_const == "Y"}checked="checked"{/if} name="page_data[form][general][{$form_secure_const}]">
                </span>
        </div>
    </div>

    {include file="addons/form_builder/views/pages/components/pages_form_elements.tpl"}

</div>
{/if}