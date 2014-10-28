{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
<div class="control-group">
    <label class="control-label" for="elm_tag">{__("tag")}</label>
    <div class="controls">
    <input id="elm_tag" type="text" name="tag" value="{$search.tag}" onfocus="this.select();"/>
    </div>
</div>
{/if}