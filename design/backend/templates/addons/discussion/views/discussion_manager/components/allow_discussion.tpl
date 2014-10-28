<div class="control-group {if $no_hide_input}cm-no-hide-input{/if}">
    <label class="control-label" for="discussion_type">{$title}:</label>
    <div class="controls">
        {assign var="discussion" value=$object_id|fn_get_discussion:$object_type}

        {if "discussion.add"|fn_check_view_permissions}
            <select name="{$prefix}[discussion_type]" id="discussion_type">
                <option {if $discussion.type == "B"}selected="selected"{/if} value="B">{__("communication")} {__("and")} {__("rating")}</option>
                <option {if $discussion.type == "C"}selected="selected"{/if} value="C">{__("communication")}</option>
                <option {if $discussion.type == "R"}selected="selected"{/if} value="R">{__("rating")}</option>
                <option {if $discussion.type == "D" || !$discussion}selected="selected"{/if} value="D">{__("disabled")}</option>
            </select>
        {else}
            <span class="shift-input">{if $discussion.type == "B"}{__("communication")} {__("and")} {__("rating")}{elseif $discussion.type == "C"}{__("communication")}{elseif $discussion.type == "R"}{__("rating")}{else}{__("disabled")}{/if}</span>
        {/if}

    </div>
</div>