{if $page_type == $smarty.const.PAGE_TYPE_POLL && $page_data.page_id}
    <div class="cm-hide-save-button" id="content_poll_questions">

    {script src="js/tygh/tabs.js"}

    <div class="items-container">
        {if !"ULTIMATE"|fn_allowed_for || $page_data|fn_allow_save_object:"pages"}
            <div class="pull-right">
                {capture name="add_new_picker"}
                    {include file="addons/polls/views/pages/update_question.tpl" question_data=[]}
                {/capture}
                {include file="common/popupbox.tpl" id="add_new_question" text=__("new_question") content=$smarty.capture.add_new_picker link_text=__("add_question") act="general"}
            </div>
        {/if}

        <div class="clearfix"><br><br>
            <table class="table table-middle">
            {foreach from=$questions key="k" item="q"}
                {if !"ULTIMATE"|fn_allowed_for || $page_data|fn_allow_save_object:"pages"}
                    {include file="common/object_group.tpl"
                        id=$q.item_id
                        text=$q.description
                        href="pages.update_question?item_id=`$q.item_id`"
                        object_id_name="item_id"
                        table="poll_questions"
                        href_delete="pages.delete_question?item_id=`$q.item_id`"
                        delete_target_id="content_poll_questions"
                        header_text="{__("editing_question")}: `$q.description`"
                        no_table="true"
                        nostatus=true
                    }
                {else}
                    {include file="common/object_group.tpl"
                        id=$q.item_id
                        text=$q.description
                        href="pages.update_question?item_id=`$q.item_id`"
                        object_id_name="item_id"
                        table="poll_questions"
                        header_text="{__("editing_question")}: `$q.description`"
                        no_table="true"
                        nostatus=true
                    }
                {/if}
            {/foreach}
            </table>
            
            {if !$questions}
                <p>{__("no_data")}</p>
            {/if}
            
        </div>
    </div>
    <!--content_poll_questions--></div>

    <div id="content_poll_statistics" class="cm-hide-save-button cm-track">
        {include file="addons/polls/views/pages/components/statistics.tpl"}
    </div>

{/if}