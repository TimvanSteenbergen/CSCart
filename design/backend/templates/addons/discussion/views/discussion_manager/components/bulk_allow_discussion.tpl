{assign var="discussion" value=$object_id|fn_get_discussion:$object_type}
<select name="{$prefix}[{$object_id}][discussion_type]">
    <option {if $discussion.type == "B"}selected="selected"{/if} value="B">{__("communication")} {__("and")} {__("rating")}</option>
    <option {if $discussion.type == "C"}selected="selected"{/if} value="C">{__("communication")}</option>
    <option {if $discussion.type == "R"}selected="selected"{/if} value="R">{__("rating")}</option>
    <option {if $discussion.type == "D" || !$discussion}selected="selected"{/if} value="D">{__("disabled")}</option>
</select>