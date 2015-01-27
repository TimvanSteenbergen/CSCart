{if $newsletter.newsletter_id}
    {assign var="id" value=$newsletter.newsletter_id}
{else}
    {assign var="id" value=0}
{/if}

<script type="text/javascript">
(function(_, $) {
    $(document).ready(function() {
        $(_.doc).on('click', '#elm_newsletter_load_template', function() {
            if ($("#elm_newsletter_template").val() != '0') {
                $.ceAjax('request', 
                    '{"newsletters.render?template_id="|fn_url nofilter}' + $("#elm_newsletter_template").val(), {
                        callback: function(data) {
                            $("#elm_newsletter_descr_html").ceEditor("val", $("#elm_newsletter_descr_html").ceEditor("val") + data['template']);
                        }
                    }
                )
            }
        });
    });
}(Tygh, Tygh.$));
</script>


{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="newsletters_form" class="form-horizontal form-edit ">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="newsletter_id" value="{$id}" />
<input type="hidden" name="newsletter_data[type]" value="{$newsletter_type}" />
<input type="hidden" name="dispatch" value="" />

{notes}
    {foreach from=$placeholders item=p_descr key=p}
        {$p}: {__($p_descr)}
    {/foreach}
{/notes}

<div class="control-group">
    <label class="control-label cm-required" for="elm_newsletter_subject">{__("subject")}</label>
    <div class="controls">
        <input type="text" name="newsletter_data[newsletter]" id="elm_newsletter_subject" value="{$newsletter.newsletter}" size="40" class="input-large" />
    </div>
</div>

{if $newsletter_type == $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
<div class="control-group">
    <label class="control-label" for="elm_newsletter_subject_multiple">{__("more_subjects")}</label>
    <div class="controls">
        <textarea name="newsletter_data[newsletter_multiple]" id="elm_newsletter_subject_multiple" class="input-large">{$newsletter.newsletter_multiple}</textarea>
    </div>
</div>
{/if}

<div class="control-group">
    <label class="control-label" for="elm_newsletter_descr_html">{__("body_html")}</label>
    <div class="controls">
        <textarea id="elm_newsletter_descr_html" name="newsletter_data[body_html]" cols="35" rows="8" class="cm-wysiwyg input-large">{$newsletter.body_html}</textarea>
    <p>{include file="buttons/button.tpl" but_text=__("preview") but_name="dispatch[newsletters.preview_html]" but_meta="cm-new-window"}</p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_newsletter_template">{__("template")}</label>
    <div class="controls">
    <select name="newsletter_data[template]" id="elm_newsletter_template">
        <option value="0">{__("no_template")}</option>
        {foreach from=$newsletter_templates item=template}
        <option {if $newsletter.template == $template.newsletter_id}selected="selected"{/if} value="{$template.newsletter_id}">{$template.newsletter}</option>
        {/foreach}
    </select>
    {include file="buttons/button.tpl" but_text=__("load") but_name="dispatch[newsletters.test_send]" but_id="elm_newsletter_load_template" but_meta="cm-no-submit"}
    </div>
</div>


{if $newsletter_type == $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
    <div class="control-group">
        <label class="control-label" for="elm_newsletter_campaigns">{__("campaign")}</label>
        <div class="controls">
        <select name="newsletter_data[campaign_id]" id="elm_newsletter_campaigns">
            <option value="0">{__("none")}</option>
            {foreach from=$newsletter_campaigns item=campaign}
                <option {if $newsletter.campaign_id == $campaign.campaign_id}selected="selected"{/if} value="{$campaign.campaign_id}">{$campaign.object}</option>
            {/foreach}
        </select>
        </div>
    </div>
{/if}

{include file="common/select_status.tpl" input_name="newsletter_data[status]" id="elm_newsletter_status" obj=$newsletter items_status="news"|fn_get_predefined_statuses}

{if $newsletter_type == $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
{include file="common/subheader.tpl" title=__("send_to") target="#acc_send"}
<div id="acc_send" class="collapse in">
    <div class="control-group">
        <label class="control-label">{__("mailing_lists")}</label>
        <div class="controls">
        {foreach from=$mailing_lists item="list"}
            <label class="checkbox">
                <input type="checkbox" value="{$list.list_id}" name="newsletter_data[mailing_lists][]" {if $list.list_id|in_array:$newsletter.mailing_lists}checked="checked"{/if} />
                    {$list.object}
                    {if "ULTIMATE"|fn_allowed_for}
                    {assign var="companies" value="\n"|implode:$list.shared_for_companies}
                    {include file="common/tooltip.tpl" tooltip=$companies}
                    {/if}
            </label>
        {/foreach}
        </div>
    </div>

    <div class="control-group">
        <label class="control-label">{__("users")}</label>
        <div class="controls">
            {include file="pickers/users/picker.tpl" but_text=__("add_recipients_from_users") data_id="return_users" but_meta="btn" input_name="newsletter_data[users]" item_ids=$newsletter.users placement="right"}
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_newsletter_abandoned_type">{__("customers_with_abandoned")}</label>
        <div class="controls">
        <select id="elm_newsletter_abandoned_type" name="newsletter_data[abandoned_type]">
            <option value="cart" {if $newsletter.abandoned_type == "cart"}selected="selected"{/if}>{__("cart")}</option>
            <option value="wishlist" {if $newsletter.abandoned_type == "wishlist"}selected="selected"{/if}>{__("wishlist")}</option>
            <option value="both" {if $newsletter.abandoned_type == "both"}selected="selected"{/if}>{__("cart_or_wishlist")}</option>
        </select>
        {if "ULTIMATE"|fn_allowed_for}
        &nbsp;{__("for")}&nbsp;
            {include file="views/companies/components/company_field.tpl" name="newsletter_data[abandoned_company_id]" no_wrap=true meta="droptop"}
        {/if}
        &nbsp;{__("which_is")}
        <input class="input-small" type="text" name="newsletter_data[abandoned_days]" value="{$newsletter.abandoned_days}" />
        {__("days_old")}
        </div>
    </div>
</div>
{/if}

{if $newsletter_links}
<div class="control-group">
    <label class="control-label">{__("clicks")}</label>
    <div class="controls">
    <table class="table">
        <tr>
            <th>{__("url")}</th>
            <th>{__("campaign")}</th>
            <th>{__("clicks")}</th>
        </tr>
    {foreach from=$newsletter_links item=link}
        <tr>
            <td>{$link.url}</td>
            <td>{$newsletter_campaigns[$link.campaign_id].object}</td>
            <td>{$link.clicks}</td>
        </tr>
    {/foreach}
    </table>
    </div>
</div>
{/if}

{if $newsletter_type != $smarty.const.NEWSLETTER_TYPE_TEMPLATE}
    <div class="control-group">
        <label class="control-label" for="elm_newsletter_test_send">{__("send_to_test_email")}</label>
        <div class="controls">
            <div class="input-append">
                <input type="text" name="test_email" id="elm_newsletter_test_send" value="" class="input-medium" />
                {include file="buttons/button.tpl" but_text=__("send") but_name="dispatch[newsletters.test_send]" but_id="test_send_button" but_meta="cm-ajax"}
            </div>
        </div>
    </div>
{/if}

</form>

{capture name="buttons"}
{if $newsletter_type == $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
    {include file="buttons/button.tpl" but_text=__("save_and_send") but_name="dispatch[newsletters.send]" but_role="submit-link" but_target_form="newsletters_form" allow_href=true}
{/if}
{include file="buttons/save.tpl" but_name="dispatch[newsletters.update]" but_role="submit-link" but_target_form="newsletters_form"}
{/capture}
{/capture}

{if $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
    {assign var="object_name" value=__("newsletter")|lower}
{elseif $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_TEMPLATE}
    {assign var="object_name" value=__("newsletter_template")|lower}
{elseif $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_AUTORESPONDER}
    {assign var="object_name" value=__("newsletter_autoresponder")|lower}
{/if}

{capture name="sidebar"}
    {capture name="content_sidebar"}
        <ul class="nav nav-list">
            <li><a href="{"newsletters.add?type=N"|fn_url}">{__("add_newsletter")}</a></li>
            <li><a href="{"newsletters.add?type=T"|fn_url}">{__("add_template")}</a></li>
            <li><a href="{"newsletters.add?type=A"|fn_url}">{__("add_autoresponder")}</a></li>
        </ul>
    {/capture}
    {include file="common/sidebox.tpl" content=$smarty.capture.content_sidebar title=__("menu")}
{/capture}

{if !$id}
    {include file="common/mainbox.tpl" title="{__("new")}: `$object_name`" content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
{else}
    {include file="common/mainbox.tpl" title="{__("editing")} `$object_name`:&nbsp;`$newsletter.newsletter`" content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
{/if}
