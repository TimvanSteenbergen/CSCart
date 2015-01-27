<div id="sl_settings_block">

{foreach from=$sl_provider_templates item="sl_map_template" key="sl_map_provider" name="sl_providers"}
<div class="control-group setting-wide {if $addons.store_locator.map_provider != $sl_map_provider} hidden{/if}" id="settings_container_{$sl_map_provider}">
        {include file=$sl_map_template}
</div>
{/foreach}

</div>

<script type="text/javascript">
Tygh.$(document).ready(function(){$ldelim}
var $ = Tygh.$;

{literal}
$(':input[id$=map_provider]').on('change', function() {
    var selected_map_provider = $(':input[id$=map_provider]').val();

    $('[id^=settings_container_]').addClass('hidden');
    $('#settings_container_' + selected_map_provider).removeClass('hidden');
});
{/literal}
{$rdelim});
</script>