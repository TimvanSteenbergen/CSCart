{capture name="section"}

<div class="sidebar-row">
    <h6>{__("search")}</h6>
    <form action="{""|fn_url}" name="gift_certificates_search_form" method="get">
    
    {capture name="simple_search"}
        <div class="sidebar-field">
            <label for="sender">{__("gift_cert_from")}:</label>
            <input type="text" name="sender" id="sender" value="{$search.sender}" size="20">
        </div>
    
        <div class="sidebar-field">
            <label for="recipient">{__("gift_cert_to")}:</label>
            <input type="text" name="recipient" id="recipient" value="{$search.recipient}" size="20">
        </div>
    
       <div class="sidebar-field">
            <label for="email">{__("email")}:</label>
            <input type="text" name="email" id="email" value="{$search.email}" size="25">
       </div>
    {/capture}

    {capture name="advanced_search"}
        <div class="group form-horizontal">
            <div class="control-group">
                {include file="common/period_selector.tpl" period=$search.period form_name="gift_certificates_search_form"}
            </div>
        </div>
        
        <div class="group">
            <div class="control-group">
                <label class="control-label">{__("gift_certificate_status")}:</label>
                <div class="controls checkbox-list">
                    {include file="common/status.tpl" status=$search.status display="checkboxes" name="status" status_type=$smarty.const.STATUSES_GIFT_CERTIFICATE}
                </div>
            </div>
        </div>
        
        <div class="group form-horizontal">
            <div class="control-group">
                <label  class="control-label" for="gift_cert_code">{__("gift_cert_code")}:</label>
                <div class="controls">
                    <input type="text" name="gift_cert_code" id="gift_cert_code" value="{$search.gift_cert_code}" size="30">
                </div>
            </div>
        </div>
    {/capture}
    
    {include file="common/advanced_search.tpl" advanced_search=$smarty.capture.advanced_search simple_search=$smarty.capture.simple_search dispatch="gift_certificates.manage" view_type="events"}

    </form>
</div>

{/capture}
{include file="common/section.tpl" section_content=$smarty.capture.section}