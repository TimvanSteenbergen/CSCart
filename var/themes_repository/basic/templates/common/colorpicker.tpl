<div class="input-prepend">
    {$cp_id = $cp_id|default:"cp_`$cp_name`"}
    <input type="text" maxlength="7" name="{$cp_name}" id="{$cp_id}" value="{$cp_value}" {if $cp_storage}data-ca-storage="{$cp_storage}"{/if} class="cm-colorpicker {if $cp_class}{$cp_class}{/if}" />
</div>