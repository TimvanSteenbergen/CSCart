<div class="ty-polls">
{if $poll.completed || ($poll.show_results == "E" && $smarty.request.action == "results")}
    {if $poll.show_results == "N"}
        <p>{__("text_you_have_already_filled_this_poll")}</p>
        {if $poll.results}<p>{$poll.results nofilter}</p>{/if}
    {else}
        {if $poll.results}
            <p>{$poll.results nofilter}</p>
        {/if}
        {if $poll.questions}
            <div class="ty-ty-polls__results">
                {foreach from=$poll.questions item="question"}
                    <div class="ty-polls__results-item">
                        <h3 class="ty-poll__header">{$question.description}</h3>
                        {if $question.type == "T"}
                            <div class="ty-poll__txt-answer">{__("polls_answers_with_comments")}</div>
                            {include file="addons/polls/views/pages/components/graph_bar.tpl" value_width=$question.results.ratio color=$_color count=$question.results.count ratio=$question.results.ratio}
                        {else}
                            {foreach from=$question.answers item=answer}
                            {if $answer.results.max_ratio}
                                {assign var="_color" value="1"}
                            {else}
                                {assign var="_color" value=""}
                            {/if}
                                {include file="addons/polls/views/pages/components/graph_bar.tpl" value_width=$answer.results.ratio color=$_color count=$answer.results.count ratio=$answer.results.ratio answer_description=$answer.description}
                            {/foreach}
                        {/if}
                    </div>
                {/foreach}
                <div class="ty-polls__total">{__("polls_total_votes")}: {$poll.summary.total}</div>
            </div>
        {/if}
    {/if}
    {if !$smarty.request.action == "results"}
        <div class="ty-polls__buttons buttons-container">
            {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("continue_shopping") but_href=""|fn_url}
        </div>
    {/if}
{else}
    <form name="{$form_name|default:"main_login_form"}" action="{""|fn_url}" method="post">
        <input type="hidden" name="page_id" value="{$poll.page_id}" />
        <input type="hidden" name="obj_prefix" value="{$obj_prefix}" />
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />

        {if $poll.has_required_questions}{__("text_mandatory_fields")}{/if}
        
        {if $page.description}<div class="ty-polls__description">{$page.description nofilter}</div>{/if}

        {if $poll.header}<div class="ty-polls__header">{$poll.header nofilter}</div>{/if}

        {if $poll.questions}
            {foreach from=$poll.questions item="question"}
                <div class="ty-poll">
                <h3 class="ty-poll__header">{$question.description}{if $question.required == "Y"}&nbsp;<span class="ty-poll__required-question">*</span>{/if}</h3>

                    {if $question.type == "T"}
                        <textarea name="answer_text[{$question.item_id}]" class="ty-poll__textarea" cols="81" rows="10"></textarea>
                    {else}

                        <div class="ty-poll-list">
                        {foreach from=$question.answers item="answer"}
                            <div class="ty-poll-list__item">
                                {if $question.type == "Q"}
                                    <input type="radio" name="answer[{$question.item_id}]" value="{$answer.item_id}" id="var_{$obj_prefix}{$answer.item_id}" class="radio" />
                                {else}
                                    <input type="checkbox" name="answer[{$question.item_id}][{$answer.item_id}]" value="Y" id="var_{$obj_prefix}{$answer.item_id}" />
                                {/if}
                                <label for="var_{$obj_prefix}{$answer.item_id}">{$answer.description}</label>
                                {if $answer.type == "O"}<input type="text" name="answer_more[{$question.item_id}][{$answer.item_id}]" class="ty-input-text ty-poll__input-text" />{/if}
                            </div>
                        {/foreach}
                        </div>
                    {/if}
                </div>
            {/foreach}
        {/if}

        {if $poll.footer}<div class="ty-polls__footer">{$poll.footer nofilter}</div>{/if}

        {include file="common/image_verification.tpl" option="use_for_polls"}

        <div class="ty-polls__buttons buttons-container">
            {if $poll.show_results == "E"}
                <div class="ty-float-right">
                    {include file="addons/polls/views/pages/components/poll_results_link.tpl" }
                </div>
            {/if}
            {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("submit") but_name="dispatch[pages.poll_submit]" }
        </div>
    </form>
{/if}
</div>