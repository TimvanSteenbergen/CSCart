{script src="js/tygh/tabs.js"}

{if $mailing_list.list_id}
    {assign var="id" value=$mailing_list.list_id}
{else}
    {assign var="id" value=0}
{/if}

<div id="content_group{$id}">
<form action="{""|fn_url}" method="post" name="mailing_lists_form_{$id}" class="form-horizontal form-edit ">
<input type="hidden" name="list_id" value="{$id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_campaign_details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
    <div id="content_tab_campaign_details_{$id}">
    <fieldset>
        <div class="control-group">
            <label for="elm_mailing_list_name_{$id}" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
                <input type="text" name="mailing_list_data[name]" id="elm_mailing_list_name_{$id}" value="{$mailing_list.object}"/>
            </div>
        </div>

        <div class="control-group">
            <label for="elm_mailing_list_from_name_{$id}" class="control-label">{__("from_name")}</label>
            <div class="controls">
                <input type="text" name="mailing_list_data[from_name]" id="elm_mailing_list_from_name_{$id}" value="{$mailing_list.from_name}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label cm-email cm-required" for="elm_mailing_list_from_email_{$id}" >{__("from_email")}</label>
            <div class="controls">
                <input type="text" name="mailing_list_data[from_email]" id="elm_mailing_list_from_email_{$id}" value="{$mailing_list.from_email|default:$settings.Company.company_newsletter_email}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label cm-email cm-required" for="elm_mailing_list_reply_to_{$id}">{__("reply_to")}</label>
            <div class="controls">
                <input type="text" name="mailing_list_data[reply_to]" id="elm_mailing_list_reply_to_{$id}" value="{$mailing_list.reply_to|default:$settings.Company.company_newsletter_email}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_mailing_list_register_autoresponder_{$id}">{__("register_autoresponder")}</label>
            <div class="controls">
                <select name="mailing_list_data[register_autoresponder]" id="elm_mailing_list_register_autoresponder_{$id}">
                    <option value="0">{__("no_autoresponder")}</option>
                    {foreach from=$autoresponders item=a}
                        <option {if $mailing_list.register_autoresponder == $a.newsletter_id}selected="selected"{/if} value="{$a.newsletter_id}">{$a.newsletter}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_mailing_list_show_on_checkout_{$id}">{__("show_on_checkout")}</label>
            <div class="controls">
                <input type="hidden" name="mailing_list_data[show_on_checkout]" value="0" />
                <input type="checkbox" name="mailing_list_data[show_on_checkout]" id="elm_mailing_list_show_on_checkout_{$id}" value="1" {if $mailing_list.show_on_checkout}checked="checked"{/if}/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_mailing_list_show_on_registration_{$id}">{__("show_on_registration")}</label>
            <div class="controls">
                <input type="hidden" name="mailing_list_data[show_on_registration]" value="0" />
                <input type="checkbox" name="mailing_list_data[show_on_registration]" id="elm_mailing_list_show_on_registration_{$id}" value="1" {if $mailing_list.show_on_registration}checked="checked"{/if} />
            </div>
        </div>

        {if $id}
            <div class="control-group">
                <label class="control-label">{__("subscribers")}</label>
                <div class="controls shift-top">
                {$mailing_list.subscribers_num}
                {include file="buttons/button.tpl" but_text=__("add_subscribers") but_href="subscribers.manage?list_id=`$id`" but_role="text"}
                </div>
            </div>
        {/if}

        {include file="common/select_status.tpl" input_name="mailing_list_data[status]" obj_id=$id obj=$mailing_list hidden=true}
    </fieldset>
    </div>
</div>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[mailing_lists.update]" cancel_action="close" save=$id}
</div>
    
</form>

<!--content_group{$id}--></div>
