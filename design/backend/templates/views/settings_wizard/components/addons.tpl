{foreach from=$wizard_addons_list item="addon"}
	<table class="table table-addons table-wizard">
	    <tr>
	        <td class="addon-icon">
	            <div class="bg-icon">
	                {if $addon.has_icon}
	                    <img src="{$images_dir}/addons/{$addon.addon_name}/icon.png" width="38" height="38" border="0" alt="{$addon.name}" title="{$addon.name}" >
	                {/if}
	            </div>
	        </td>
	        <td width="95%">
	            <div class="object-group-link-wrap">
	                <span class="unedited-element block">{$addon.name}</span><br>
	                <span class="row-status object-group-details">{$addon.description}</span>
	            </div>
	        </td>
	        <td width="5%">
	            <input type="hidden" name="addons[{$addon.addon_name}]" value="N">
	            <label for="addon_{$addon.addon_name}" class="checkbox">
	                <input id="addon_{$addon.addon_name}" type="checkbox" name="addons[{$addon.addon_name}]" value="Y">
	                {__("install")}
	            </label>     
	        </td>
	    </tr>
	</table>
{/foreach}