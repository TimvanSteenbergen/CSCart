
{if !"ULTIMATE:FREE"|fn_allowed_for}
{if $usergroup_ids !== ""}
{assign var="ug_ids" value=","|explode:$usergroup_ids}
{/if}

{hook name="usergroups:select_usergroups"}
<input type="hidden" name="{$name}" value="0" {$input_extra}/>
{capture name="usergroups_list"}
{$usergroups_default=fn_get_default_usergroups()}
{foreach from=$usergroups_default item=usergroup}
    {if $select_mode}<li><a>
    {else}<label class="checkbox {if !$list_mode}inline{/if}" for="{$id}_{$usergroup.usergroup_id}">
    {/if}
    <input type="checkbox" name="{$name}[]" id="{$id}_{$usergroup.usergroup_id}"{if ($ug_ids && $usergroup.usergroup_id|in_array:$ug_ids) || (!$ug_ids && $usergroup.usergroup_id == $smarty.const.USERGROUP_ALL)} checked="checked"{/if} {if (!$ug_ids || ($ug_ids && $ug_ids|count == 1 && $usergroup.usergroup_id|in_array:$ug_ids)) && $usergroup.usergroup_id == $smarty.const.USERGROUP_ALL} disabled="disabled"{/if} value="{$usergroup.usergroup_id}" {$input_extra} onclick="fn_switch_default_box(this, '{$id}', {$smarty.const.USERGROUP_ALL});" />
    {$usergroup.usergroup}

    {if $select_mode}</a></li>
    {else}</label>
    {/if}
{/foreach}

{foreach from=$usergroups item=usergroup}
    {if $select_mode}<li><a>
    {else}<label class="checkbox {if !$list_mode}inline{/if}" for="{$id}_{$usergroup.usergroup_id}">
    {/if}
        
        <input type="checkbox" name="{$name}[]" id="{$id}_{$usergroup.usergroup_id}"{if $ug_ids && $usergroup.usergroup_id|in_array:$ug_ids} checked="checked"{/if} value="{$usergroup.usergroup_id}" {$input_extra} onclick="fn_switch_default_box(this, '{$id}', {$smarty.const.USERGROUP_ALL});" />
        {$usergroup.usergroup}

    {if $select_mode}</a></li>
    {else}</label>
    {/if}

{/foreach}
{/capture}
{/hook}

{if $select_mode}
    <div class="btn-group">
    <a class="btn-text dropdown-toggle" data-toggle="dropdown">
    {if $ug_ids}
        {assign var="ug_count" value=$ug_ids|count}
    {else}
        {assign var="ug_count" value=$ug_ids|count}
    {/if}
        <i class="exicon-usergroup"></i>
            {$title} <span class="cm-ug-amount">({$ug_count})</span>
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        {$smarty.capture.usergroups_list nofilter}
    </ul>
    </div>
{else}
    {$smarty.capture.usergroups_list nofilter}
{/if}

{if !"SMARTY_USERGROUPS_LOADED"|defined}
    {assign var="tmp" value="SMARTY_USERGROUPS_LOADED"|define:true}
    <script type="text/javascript">
        {literal}
        function fn_switch_default_box(holder, prefix, default_id)
        {
            var $ = Tygh.$;
            var p = $(holder).parents(':not(li,a,label,ul):first');

            var default_box = $('input[id^=' + prefix + '_' + default_id + ']', p);
            var checked_groups = $('input[id^=' + prefix + '_][type=checkbox]:checked', p).not(default_box).not(holder).length + (holder.checked ? 1 : 0);

            default_box.prop('disabled', (checked_groups == 0));
            if (checked_groups == 0) {
                default_box.prop('checked', true);
            }

            fn_calculate_usergroups(p);
            return true;
        }

        function fn_calculate_usergroups(holder)
        {
            var $ = Tygh.$;
            if ($(holder).length) {
                var note = $('.cm-ug-amount', $(holder));
            } else {
                var note = $('.cm-ug-amount');
            }

            note.each(function(){
                var p = $(this).parents(':not(li,a,label,ul):first');
                var total_checked = $('input[type=checkbox]:checked', p).length;
                $(this).html('(' + total_checked + ')');
            });

        }
        {/literal}
    </script>
{/if}

{/if}