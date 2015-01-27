{assign var="show_discussion" value="discussion_manager"|fn_check_permissions:'manage':'admin'}
{if "MULTIVENDOR"|fn_allowed_for && !$runtime.company_id || !"MULTIVENDOR"|fn_allowed_for}
    {if "ULTIMATE"|fn_allowed_for && $runtime.company_id || "MULTIVENDOR"|fn_allowed_for}
        {if $show_discussion}
        <div class="statistics-box communication">
            <h2>
                <span class="float-right hidden">
                    <i class="icon-minus cm-tooltip" title="{__("hide")}"></i>
                    <i class="icon-remove cm-tooltip" title="{__("close")}"></i>
                </span>
                {__("latest_reviews")}
            </h2>
            
            <div class="statistics-body">
                <div id="stats_discussion">
                    {if $latest_posts}
                        {foreach from=$latest_posts item=post}
                            {assign var="o_type" value=$post.object_type}
                            {assign var="object_name" value=$discussion_objects.$o_type}
                            {assign var="review_name" value="discussion_title_$object_name"}
                            
                            <div class="{cycle values=" ,manage-post"} posts">
                                <div class="clear">
                                    {if $post.type == "R" || $post.type == "B"}
                                        <div class="float-left">
                                            {include file="addons/discussion/views/discussion_manager/components/stars.tpl" stars=$post.rating}
                                        </div>
                                    {/if}
                                    
                                    <div class="float-right">
                                    <a class="tool-link valign" href="{$post.object_data.url|fn_url}">{__("edit")}</a>
                                    {include file="buttons/button.tpl" but_role="delete_item" but_href="index.delete_post?post_id=`$post.post_id`" but_meta="cm-ajax cm-confirm" but_target_id="stats_discussion"}
                                    </div>
                                    
                                    {__($object_name)}:&nbsp;<a href="{$post.object_data.url|fn_url}">{$post.object_data.description|truncate:70}</a>
                                    <span class="lowercase">&nbsp;{__("comment_by")}</span>&nbsp;{$post.name}
                                </div>
                            
                                {if $post.type == "C" || $post.type == "B"}
                                    <div class="scroll-x">{$post.message}</div>
                                {/if}
                                
                                <div class="clear">
                                <div class="float-left"><span>{__("ip_address")}:</span>&nbsp;{$post.ip_address}</div>
                                {include file="addons/discussion/views/index/components/dashboard_status.tpl"}
                                </div>
                            </div>
                        {/foreach}
                    {else}
                        <p class="no-items">{__("no_items")}</p>
                    {/if}
                <!--stats_discussion--></div>
            </div>
        </div>
        {/if}
    {/if}
{/if}