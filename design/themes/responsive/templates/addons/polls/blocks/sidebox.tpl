{** block-description:tmpl_polls_side **}

<div class="ty-polls">
{foreach from=$items item=poll}
{if $smarty.request.page_id != $poll.page_id}

    {if $poll.completed}
        <h3 class="ty-poll-header">{__("polls_have_completed")}</h3>
        {if $poll.show_results == "V" || $poll.show_results == "E"}
        <div class="ty-polls-buttons">
            {include file="addons/polls/views/pages/components/poll_results_link.tpl" }
        </div>
        {/if}
    {else}
        <form name="{$form_name|default:"main_login_form"}" action="{""|fn_url}" method="post">
            <input type="hidden" name="page_id" value="{$poll.page_id}" />
            <input type="hidden" name="redirect_url" value="{$config.current_url}" />
            <input type="hidden" name="obj_prefix" value="{$block.block_id}" />
            {if $poll.header}<div class="polls__header">{$poll.header nofilter}</div>{/if}

            {if $poll.questions}
                {foreach from=$poll.questions item="question"}
                <div class="ty-poll">
                    <h3 class="ty-poll__header">{$question.description}{if $question.required == "Y"} <span class="ty-poll__required-question">*</span>{/if}</h3>

                    {if $question.type == "T"}
                        <textarea name="answer_text[{$question.item_id}]" class="ty-poll__textarea"></textarea>
                    {else}

                        <ul class="ty-poll-list">
                            {foreach from=$question.answers item="answer"}
                                <li class="ty-poll-list__item">
                                    {if $question.type == "Q"}
                                        <input type="radio" class="radio" id="var_{$block.block_id}_{$answer.item_id}" name="answer[{$question.item_id}]" value="{$answer.item_id}" />
                                    {else}
                                        <input type="checkbox" id="var_{$block.block_id}_{$answer.item_id}" name="answer[{$question.item_id}][{$answer.item_id}]" value="Y" />
                                    {/if}
                                    <label for="var_{$block.block_id}_{$answer.item_id}">{$answer.description}</label>
                                    {if $answer.type == "O"}
                                        <input type="text" name="answer_more[{$question.item_id}][{$answer.item_id}]" class="ty-poll__input-text ty-input-text" value="" />
                                    {/if}
                                </li>
                            {/foreach}
                        </ul>
                    {/if}
                </div>
                {/foreach}
            {/if}

            {if $poll.footer}<div class="ty-polls__footer">{$poll.footer nofilter}</div>{/if}

            {include file="common/image_verification.tpl" option="use_for_polls"}

            <div class="ty-polls__buttons">
                {if $poll.show_results == "E"}
                    <div class="ty-float-right">
                        {include file="addons/polls/views/pages/components/poll_results_link.tpl" }
                    </div>
                {/if}
                {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("submit") but_name="dispatch[pages.poll_submit]"}
            </div>

        </form>
    {/if}
{/if}
{/foreach}
</div>