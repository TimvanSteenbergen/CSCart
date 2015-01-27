{if $question_data}
    {assign var="id" value=$question_data.item_id}
{else}
    {assign var="id" value="0"}
{/if}

{if $page_data|fn_allow_save_object:"pages"}
    {assign var="allow_edit" value=true}
{else}
    {assign var="allow_edit" value=false}
{/if}

<div id="content_group{$id}">

<form action="{""|fn_url}" method="post" name="question_form{$id}" class="form-horizontal{if !$allow_edit} cm-hide-inputs{/if}">
<input type="hidden" name="item_id" value="{$id}" />
<input type="hidden" name="page_id" value="{$question_data.page_id|default:$smarty.request.page_id}" />
<input type="hidden" name="selected_section" value="poll_questions" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        {if !$question_data || $question_data.type != "T"}
        <li id="conf_{$id}" class="cm-js"><a>{__("answers")}</a></li>
        {/if}
    </ul>
</div>

<div class="cm-tabs-content">
    <div id="content_details_{$id}">

        <div class="control-group">
            <label for="descr_{$id}" class="cm-required control-label">{__("question_text")}:</label>
            <div class="controls">
                <input type="text" name="question_data[description]" id="descr_{$id}" value="{$question_data.description}">
            </div>
        </div>

        <div class="control-group">
            <label for="pos_{$id}" class="control-label">{__("position")}:</label>
            <div class="controls">
                <input type="text" name="question_data[position]" id="pos_{$id}" value="{$question_data.position}">
            </div>
        </div>

        <div class="control-group">
            <label for="type_{$id}" class="cm-required control-label">{__("type")}:</label>
            <div class="controls">
                <select name="question_data[type]" id="type_{$id}" {if !$question_data}onchange="Tygh.$('#conf_{$id}').toggleBy(this.value == 'T');"{/if}>
                        {if !$question_data || $question_data.type == "Q" || $question_data.type == "M"}
                        <option value="Q" {if $question_data.type == "Q"}selected="selected"{/if}>{__("select_single_type")}</option>
                        <option value="M" {if $question_data.type == "M"}selected="selected"{/if}>{__("select_one_or_more_type")}</option>
                        {/if}
                        {if !$question_data || $question_data.type == "T"}
                        <option value="T" {if $question_data.type == "T"}selected="selected"{/if}>{__("text_answer_type")}</option>
                        {/if}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="req_{$id}" class="control-label">{__("required")}:</label>
            <div class="controls">
                <label for="" class="checkbox"><input type="checkbox" name="question_data[required]" id="req_{$id}" value="Y" {if $question_data.required == "Y"}checked="checked"{/if}></label>
                <input type="hidden" name="question_data[required]" value="N">
            </div>
        </div>

    </div>

    {if !$question_data || $question_data.type != "T"}
    <div class="hidden" id="content_conf_{$id}">

        <table class="table table-middle">
        <thead>
            <tr>
                <th width="10%">{__("position_short")}</th>
                <th width="50%">{__("answer_text")}</th>
                <th width="20%" class="center nowrap">{__("text_box")}</th>
                <th width="20%">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        {if $question_data.answers}
        {foreach from=$question_data.answers item="answer"}
        <tr id="box_answer_{$answer.item_id}">
            <td>
                <input type="text" name="question_data[answers][{$answer.item_id}][position]" size="3" value="{$answer.position}" class="input-micro"></td>
            <td>
                <input type="text" name="question_data[answers][{$answer.item_id}][description]" size="75" value="{$answer.description}" class="input-xxlarge"></td>
            <td class="center">
                <input type="hidden" name="question_data[answers][{$answer.item_id}][type]" value="A">
                <input type="checkbox" name="question_data[answers][{$answer.item_id}][type]" value="O" class="checkbox"{if $answer.type == "O"} checked="checked"{/if}></td>
            <td>
                {include file="buttons/multiple_buttons.tpl" item_id="answer_`$answer.item_id`" only_delete="Y"}</td>
        </tr>
        {/foreach}
        {/if}

        {if $allow_edit}        
            <tr id="box_new_answer_{$id}">
                <td>
                    <input type="text" name="question_data[new_answers][0][position]" size="3" value="" class="input-micro"></td>
                <td>
                    <input type="text" name="question_data[new_answers][0][description]" size="75" value="" class="input-xxlarge" /></td>
                <td class="center">
                    <input type="hidden" name="question_data[new_answers][0][type]" value="A" />
                    <input type="checkbox" name="question_data[new_answers][0][type]" value="O" class="checkbox"{if $answer.type == "O"} checked="checked"{/if} /></td>
                <td>
                    {include file="buttons/multiple_buttons.tpl" item_id="new_answer_`$id`"}</td>
            </tr>
        {/if}

        </tbody>
        </table>

    </div>
    {/if}

</div>

<div class="buttons-container">
    {if "ULTIMATE"|fn_allowed_for && !$allow_edit}
        {assign var="hide_first_button" value=true}
    {/if}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[pages.update_question]" cancel_action="close" save=$id hide_first_button=$hide_first_button}
</div>

</form>
<!--content_group{$id}--></div>
