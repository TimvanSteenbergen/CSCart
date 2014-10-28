{include file="common/subheader.tpl" title=__("summary")}

<table class="table table-striped">
<tr>
    <td width="300">{__("polls_total_submited")}:</td>
    <td>
        {if $page_data.poll.summary.total}
            {include file="common/popupbox.tpl" id="poll_statistics_votes_total" href="pages.poll_reports?poll_page_id=`$page_data.page_id`&report=votes" link_text=$page_data.poll.summary.total text=__("polls_total_submited") act="edit"}    
        {else}&mdash;{/if}
    </td>
</tr>
<tr>
    <td width="300">{__("polls_total_completed")}:</td>
    <td>
        {if $page_data.poll.summary.completed}
            {include file="common/popupbox.tpl" id="poll_statistics_votes_completed" href="pages.poll_reports?poll_page_id=`$page_data.page_id`&report=votes&completed=Y" link_text=$page_data.poll.summary.completed text=__("polls_total_completed") act="edit"}
        {else}&mdash;{/if}
    </td>
</tr>
<tr>
    <td width="300">{__("polls_first_submited")}:</td>
    <td>{if $page_data.poll.summary.first}{$page_data.poll.summary.first|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{else}&mdash;{/if}</td>
</tr>
<tr>
    <td width="300">{__("polls_last_submited")}:</td>
    <td>{if $page_data.poll.summary.last}{$page_data.poll.summary.last|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{else}&mdash;{/if}</td>
</tr>
</table>

{include file="common/subheader.tpl" title=__("statistics_by_questions")}

{if $page_data.poll.questions}
    <div class="form-horizontal">
        {foreach from=$page_data.poll.questions item=question}
            <div class="control-group">
                <label class="control-label">{$question.description}:</label>
            
                <div class="controls">
                    {if $question.type == "T"}
                        <table class="table table-middle table-bordered">
                        <tr>
                            <td width="200">{__("polls_answers_with_comments")}</td>
                            <td>
                                {include file="addons/polls/views/pages/components/graph_bar.tpl" value_width=$question.results.ratio bar_width="400"}
                                {if $question.results.count}
                                    {include file="common/popupbox.tpl" id="poll_statistics_votes_q_`$question.item_id`" href="pages.poll_reports?poll_page_id=`$page_data.page_id`&report=votes&item_id=`$question.item_id`&answer_id=0" link_text=$question.results.count text="{__("votes")} - `$question.description`" act="edit"}
                                {else}
                                    0
                                {/if}
                                ({$question.results.ratio|default:"0.00"}%)
                            </td>
                            {if $question.has_comments}
                                <td>
                                    {include file="common/popupbox.tpl" id="poll_statistics_comments_q_`$question.item_id`" href="pages.poll_reports?poll_page_id=`$page_data.page_id`&report=answers&item_id=`$question.item_id`" link_text=__("view_answers") text="{__("comments")} - `$question.description`" act="edit" drop_left=true}
                                </td>
                            {/if}
                        </tr>
                        </table>
                            
                    {else}
                        {foreach from=$question.answers item="answer"}
                            <table class="table table-middle table-bordered">
                            <tr>
                                <td width="200">{$answer.description}</td>
                                <td>{include file="addons/polls/views/pages/components/graph_bar.tpl" value_width=$answer.results.ratio bar_width="400"}
                                    {if $answer.results.count}{include file="common/popupbox.tpl" id="poll_statistics_votes_a_`$answer.item_id`" href="pages.poll_reports?poll_page_id=`$page_data.page_id`&report=votes&item_id=`$question.item_id`&answer_id=`$answer.item_id`" link_text=$answer.results.count text="{__("polls_votes")} - `$answer.description`" act="edit"}{else}0{/if} ({$answer.results.ratio|default:"0.00"}%)
                                </td>
                                {if $answer.has_comments}
                                    <td>
                                        {include file="common/popupbox.tpl" id="poll_statistics_comments_a_`$answer.item_id`" href="pages.poll_reports?poll_page_id=`$page_data.page_id`&report=answers&item_id=`$question.item_id`&answer_id=`$answer.item_id`" link_text=__("view_answers") text="{__("comments")} - `$answer.description`" act="edit" drop_left=true}
                                    </td>
                                {/if}
                            </tr>
                            </table>
                            
                        {/foreach}
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
{else}
    <p>{__("no_data")}</p>
{/if}