
{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="storage_update_form" class="form-horizontal cm-ajax cm-comet form-edit " enctype="multipart/form-data">

{include file="common/subheader.tpl" title=__("general") target="#acc_general"}
<div id="acc_general" class="collapsed in">

    <div class="control-group">
        <label class="control-label cm-required">{__("status")}</label>
        <div class="controls">
            <label class="radio" for="elm_storage_file"><input type="radio" name="storage_data[storage]" id="elm_storage_file" {if $storage_data.storage == "file"}checked="checked"{/if} value="file" />{__("file")}</label>
            <label class="radio" for="elm_storage_amazon"><input type="radio" name="storage_data[storage]" id="elm_storage_amazon" {if $storage_data.storage == "amazon"}checked="checked"{/if} value="amazon" />Amazon S3</label>
        </div>
    </div>
    <div class="cm-amazon-options {if $storage_data.storage != "amazon"}hidden{/if}">
        <div class="control-group">
            <label for="elm_amazon_region" class="control-label cm-required">{__("region")}:</label>
            <div class="controls">
                <select id="elm_amazon_region" name="storage_data[region]" {if $current_storage == "amazon"}disabled="disabled"{/if}>
                    {foreach from=$amazon_data.regions key="region_addr" item="region"}
                    <option value="{$region_addr}" {if $region_addr == $storage_data.region}selected="selected"{/if}>{$region}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="elm_amazon_key" class="control-label cm-required">{__("key")}:</label>
            <div class="controls">
                <input type="text" name="storage_data[key]" id="elm_amazon_key" size="55" value="{$storage_data.key}" {if $current_storage == "amazon"}disabled="disabled"{/if} class="input-large" />
            </div>
        </div>

        <div class="control-group">
            <label for="elm_amazon_secret" class="control-label cm-required">{__("secret_key")}:</label>
            <div class="controls">
                <input type="text" name="storage_data[secret]" id="elm_amazon_secret" size="55" value="{$storage_data.secret}" {if $current_storage == "amazon"}disabled="disabled"{/if} class="input-large" />
            </div>
        </div>

        <div class="control-group">
            <label for="elm_amazon_bucket" class="control-label cm-required">{__("bucket")}:</label>
            <div class="controls">
                <input type="text" name="storage_data[bucket]" id="elm_amazon_bucket" size="55" value="{$storage_data.bucket}" {if $current_storage == "amazon"}disabled="disabled"{/if} class="input-large" />
            </div>
        </div>        
    </div>
</div>

{* For future usage *}
<div class="hidden">
    {include file="common/subheader.tpl" title=__("additional_options") target="#acc_amazon_additional"}
    <div id="acc_amazon_additional" class="collapsed in">
        <div class="control-group">
            <label for="elm_amazon_host" class="control-label">{__("host")}:</label>
            <div class="controls">
                <input type="text" name="storage_data[host]" id="elm_amazon_host" size="55" value="{$storage_data.host}" class="input-large" disabled="disabled" />
            </div>
        </div>
    </div>
</div>

{capture name="buttons"}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[storage.update_storage]" but_role="submit-link" but_target_form="storage_update_form" save=true}
{/capture}

{literal}
<script>

(function(_, $) {
    $('#elm_storage_file').change(function() {
        $('.cm-amazon-options').switchAvailability(true, true)
    });
    $('#elm_storage_amazon').change(function() {
        $('.cm-amazon-options').switchAvailability(false, true);
    });

    if ($('#elm_storage_file').prop('checked') == true) {
       $('.cm-amazon-options').switchAvailability(true, true); 
    }

}(Tygh, Tygh.$));


</script>
{/literal}

</form>

{/capture}
{include file="common/mainbox.tpl" sidebar=$smarty.capture.sidebar title=__("storage") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

