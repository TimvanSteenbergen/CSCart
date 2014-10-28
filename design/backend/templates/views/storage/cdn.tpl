
{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="cdn_form" class="form-horizontal form-edit">

{include file="common/subheader.tpl" title=__("general") target="#acc_general"}
<div id="acc_general" class="collapsed in">

    {if $cdn_data.host}

    <div class="control-group">
        <label for="elm_enable_cdn" class="control-label">{__("enable_cdn")}:</label>
        <div class="controls">
            <input type="hidden" name="cdn_data[is_enabled]" value="0" />
            <input type="checkbox" name="cdn_data[is_enabled]" id="elm_enable_cdn" value="1" {if $cdn_data.is_enabled}checked="checked"{/if} {if !$cdn_data.is_active}disabled="disabled"{/if} />
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <span class="shift-input">{__("text_cdn_check", ["[url]" => $cdn_test_url])}</span>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label">{__("status")}:</label>
        <div class="controls">
            <span class="shift-input">
                
                {if $cdn_data.is_active}
                <span class="label btn-info o-status-c">{__("active")}</span>
                {else}
                <span class="label btn-info o-status-i">{__("in_progress")}</span>
                {/if}

            </span>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label">{__("host")}:</label>
        <div class="controls">
            <span class="shift-input">{$cdn_data.host}</span>
        </div>
    </div>

    {/if}

    <div class="control-group">
        <label class="control-label">{__("provider")}:</label>
        <div class="controls">
            <span class="shift-input">Amazon</span>
        </div>
    </div>


    <div class="control-group">
        <label for="elm_cf_key" class="control-label cm-required">{__("key")}:</label>
        <div class="controls">
            <input type="text" name="cdn_data[key]" id="elm_cf_key" size="55" value="{$cdn_data.key}" class="input-large" />
        </div>
    </div>

    <div class="control-group">
        <label for="elm_cf_secret" class="control-label cm-required">{__("secret_key")}:</label>
        <div class="controls">
            <input type="text" name="cdn_data[secret]" id="elm_cf_secret" size="55" value="{$cdn_data.secret}" class="input-large" />
        </div>
    </div>
</div>

{include file="common/subheader.tpl" title=__("extra") target="#acc_extra" meta="collapsed"}
<div id="acc_extra" class="collapse">

    <div class="control-group">
        <label for="elm_cf_cname" class="control-label">{__("cname")}:</label>
        <div class="controls">
            <input type="text" name="cdn_data[cname]" id="elm_cf_cname" size="55" value="{$cdn_data.cname}" class="input-large" />
        </div>
    </div>
</div>


{capture name="buttons"}

    {include file="buttons/save_cancel.tpl" but_name="dispatch[storage.update_cdn]" but_role="submit-link" but_target_form="cdn_form" save=true hide_second_button=true}

{/capture}

</form>

{/capture}
{include file="common/mainbox.tpl" sidebar=$smarty.capture.sidebar title=__("cdn_settings") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

