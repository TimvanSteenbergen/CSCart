<select name="export_options[output]" id="output">
    <option value="D" {if $value == "D"}selected="selected"{/if}>{__("direct_download")}</option>
    <option value="C" {if $value == "C"}selected="selected"{/if}>{__("screen")}</option>
    {if !$runtime.company_id || !empty($runtime.simple_ultimate)}
        <option value="S" {if $value == "S"}selected="selected"{/if}>{__("server")}</option>
    {/if}
</select>