<input type="hidden" name="shipping_id" value="{$id}" />
<input type="hidden" name="selected_section" value="shipping_charges" />

<script type="text/javascript">
    (function(_, $) {
        $(function() {
            var tab = $('#dashboard-shipping');
            $('a[data-toggle="tab"]', tab).on('shown', function(e) {
            localStorage.setItem('tab-active', $(e.target).attr('href'));
          });

          var lastTab = localStorage.getItem('tab-active');
          if (lastTab) {
              $('a[href='+ lastTab +']', tab).click();
          }
      });
    }(Tygh, Tygh.$));
</script>

<div class="dashboard-shipping tabs cm-j-tabs" data-ca-width="760" id="dashboard-shipping">
    {if $shipping.rate_calculation == "M"}
    <p>{__("show_rate_for_destination")}:</p>
    <ul class="nav nav-pills" id="dashboard-shipping-tab-menu">
        {foreach from=$shipping.rates item="rate" name="rates"}
            <li class="cm-js {if $smarty.foreach.rates.first} active{/if}">
                <a href="#destination_{$rate.destination_id}" data-toggle="tab">{$rate.destination}{if $rate.rate_defined}(+){/if}</a>
            </li>
        {/foreach}
    </ul>
    {/if}
    <div class="tab-content">

        {foreach from=$shipping.rates item="rate_data" name="rates"}

        {if $shipping.rate_calculation == "M"}
            {assign var="destination_id" value="{$rate_data.destination_id}"}
        {else}
            {assign var="destination_id" value="0"}
        {/if}

        <div class="tab-pane{if $smarty.foreach.rates.first} active{/if}" id="destination_{$destination_id}">
            {include file="common/subheader.tpl" title=__("cost_dependences") meta="clear"}

            <table class="table table-middle">
                <thead>
                <tr class="cm-first-sibling">
                    <th width="1%">{include file="common/check_items.tpl" check_target="cost-`$destination_id`"}</th>
                    <th width="30%">{__("products_cost")}</th>
                    <th width="15%">{__("rate_value")}</th>
                    <th width="15%">{__("type")}</th>
                    <th>{hook name="shippings:cost_dependences_head"}{/hook}</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                {foreach from=$rate_data.rate_value.C item="rate" key="k" name="rdf"}
                    <tr>
                        <td>
                            <input type="checkbox" name="delete_rate_data[{$destination_id}][C][{$k}]" value="Y" {if $smarty.foreach.rdf.first}disabled="disabled"{/if} class="checkbox cm-item-cost-{$destination_id} cm-item" /></td>
                        <td class="nowrap">
                            {__("more_than")}&nbsp;
                            {if $smarty.foreach.rdf.first}
                                <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][C][0][amount]" value="0" />
                                &nbsp;{include file="common/price.tpl" value="0"}
                            {else}
                                {include file="common/price.tpl" value="{$k}" view="input" input_name="shipping_data[rates][`$destination_id`][rate_value][C][`$k`][amount]" class="input-small input-hidden"}
                            {/if}
                        </td>
                        <td>
                            <input type="text" name="shipping_data[rates][{$destination_id}][rate_value][C][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][value]" size="5" value="{$rate.value|default:"0"}" class="input-small input-hidden" /></td>
                        <td>
                            <select class="input-medium" name="shipping_data[rates][{$destination_id}][rate_value][C][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][type]">
                                <option value="F" {if $rate.type == "F"}selected="selected"{/if}>{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                                <option value="P" {if $rate.type == "P"}selected="selected"{/if}>{__("percent")} (%)</option>
                            </select></td>
                        <td>{hook name="shippings:cost_dependences_body"}{/hook}</td>
                        <td class="nowrap right">
                            {capture name="tools_items"}

                                {if !$smarty.foreach.rdf.first && $shipping|fn_allow_save_object:"shippings"}
                                    <a class="cm-confirm cm-tooltip" href="{"shippings.delete_rate_value?rate_type=C&amount=`$k`&shipping_id=`$id`&destination_id=`$destination_id`&rate_id=`$rate_data.rate_id`"|fn_url}" title="{__("delete")}"><i class="icon-trash"></i></a>
                                {else}
                                    <span class="icon-trash undeleted-element" {__("delete")}></span>
                                {/if}
                            {/capture}
                            <div class="hidden-tools">
                                {if $smarty.foreach.rdf.first}
                                    {include file="buttons/remove_item.tpl" but_class="cm-delete-row"}
                                {else}
                                    {include file="buttons/remove_item.tpl" only_delete='Y' but_class="cm-delete-row"}
                                {/if}

                            </div>
                        </td>
                    </tr>
                    {foreachelse}
                    <tr class="no-items">
                        <td colspan="6">
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][C][0][amount]" value="0" />
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][C][0][value]" value="0" />
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][C][0][type]" value="F" />
                            <p>{__("no_items")}</p></td>
                    </tr>
                {/foreach}

                {if !$hide_for_vendor}
                    <tr id="box_add_rate_celm_cost_{$destination_id}">
                        <td>
                            <input type="checkbox" disabled="disabled" value="Y" class="checkbox cm-item-cost cm-item" /></td>
                        <td>
                            {__("more_than")}&nbsp;{include file="common/price.tpl" value="" view="input" input_name="shipping_data[add_rates][{$destination_id}][rate_value][C][0][amount]" class="input-small input-hidden"}</td>
                        <td>
                            <input type="text" name="shipping_data[add_rates][{$destination_id}][rate_value][C][0][value]" size="5" value="" class="input-small input-hidden" /></td>
                        <td>
                            <select class="input-medium" name="shipping_data[add_rates][{$destination_id}][rate_value][C][0][type]">
                                <option value="F">{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                                <option value="P">{__("percent")} (%)</option>
                            </select></td>
                        <td>
                            {hook name="shippings:cost_dependences_new"}
                            {/hook}</td>
                        <td class="right"> <div class="hidden-tools">{include file="buttons/multiple_buttons.tpl" item_id="add_rate_celm_cost_`$destination_id`" tag_level=3}</div></td>
                    </tr>
                {/if}

            </table>


            {include file="common/subheader.tpl" title=__("weight_dependences") meta="clear"}

            <table class="table table-middle">
                <thead>
                <tr class="cm-first-sibling">
                    <th width="1%">{include file="common/check_items.tpl" check_target="weight-`$destination_id`"}</th>
                    <th width="30%">{__("products_weight")}</th>
                    <th width="15%">{__("rate_value")}</th>
                    <th width="15%">{__("type")}</th>
                    <th width="10%">{__("per", ["[object]" => $settings.General.weight_symbol])}</th>
                    <th>{hook name="shippings:weight_dependences_head"}{/hook}</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                {foreach from=$rate_data.rate_value.W item="rate" key="k" name="rdf"}
                    <tr>
                        <td>
                            <input type="checkbox" name="delete_rate_data[{$destination_id}][W][{$k}]" value="Y" {if $smarty.foreach.rdf.first}disabled="disabled"{/if} class="checkbox cm-item-weight-{$destination_id} cm-item" /></td>
                        <td class="nowrap">
                            {__("more_than")}&nbsp;
                            {if $smarty.foreach.rdf.first}
                                <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][W][0][amount]" value="0" />&nbsp;&nbsp;0 {$settings.General.weight_symbol}
                            {else}
                                <input type="text" name="shipping_data[rates][{$destination_id}][rate_value][W][{$k}][amount]" data-p-sign="s" data-a-sign=" {$settings.General.weight_symbol nofilter}" size="5" value="{$k}" class="cm-numeric input-small input-hidden" />
                            {/if}

                        </td>
                        <td>
                            <input type="text" name="shipping_data[rates][{$destination_id}][rate_value][W][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][value]" size="5" value="{$rate.value|default:"0"}" class="input-small input-hidden" /></td>
                        <td>
                            <select class="input-medium" name="shipping_data[rates][{$destination_id}][rate_value][W][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][type]">
                                <option value="F" {if $rate.type == "F"}selected="selected"{/if}>{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                                <option value="P" {if $rate.type == "P"}selected="selected"{/if}>{__("percent")} (%)</option>
                            </select></td>
                        <td>
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][W][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][per_unit]" value="N" />
                            <input type="checkbox" name="shipping_data[rates][{$destination_id}][rate_value][W][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][per_unit]" value="Y" {if $rate.per_unit == "Y"}checked="checked"{/if} class="checkbox" />
                        </td>
                        <td>{hook name="shippings:weight_dependences_body"}{/hook}</td>
                        <td class="nowrap right">
                            {capture name="tools_items"}

                                {if !$smarty.foreach.rdf.first && $shipping|fn_allow_save_object:"shippings"}
                                    <a class="cm-confirm cm-tooltip" href="{"shippings.delete_rate_value?rate_type=W&amount=`$k`&shipping_id=`$id`&destination_id=`$destination_id`&rate_id=`$rate_data.rate_id`"|fn_url}" title="{__("delete")}"><i class="icon-trash"></i></a>
                                {else}
                                    <span class="icon-trash undeleted-element" {__("delete")}></span>
                                {/if}
                            {/capture}
                            <div class="hidden-tools">
                                {if $smarty.foreach.rdf.first}
                                    {include file="buttons/remove_item.tpl" but_class="cm-delete-row"}
                                {else}
                                    {include file="buttons/remove_item.tpl" only_delete='Y' but_class="cm-delete-row"}
                                {/if}

                            </div>
                        </td>
                    </tr>
                    {foreachelse}
                    <tr class="no-items">
                        <td colspan="6">
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][W][0][amount]" value="0" />
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][W][0][value]" value="0" />
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][W][0][type]" value="F" />
                            <p>{__("no_items")}</p></td>
                    </tr>
                {/foreach}

                {if !$hide_for_vendor}
                    <tr id="box_add_rate_celm_weight_{$destination_id}">
                        <td>
                            <input type="checkbox" disabled="disabled" value="Y" class="checkbox cm-item-weight cm-item" /></td>
                        <td>
                            {__("more_than")}&nbsp; <input type="text" name="shipping_data[add_rates][{$destination_id}][rate_value][W][0][amount]" data-p-sign="s" data-a-sign=" {$settings.General.weight_symbol nofilter}" size="5" value="" class="cm-numeric input-small input-hidden" /></td>
                        <td>
                            <input type="text" name="shipping_data[add_rates][{$destination_id}][rate_value][W][0][value]" size="5" value="" class="input-small input-hidden" /></td>
                        <td>
                            <select class="input-medium" name="shipping_data[add_rates][{$destination_id}][rate_value][W][0][type]">
                                <option value="F">{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                                <option value="P">{__("percent")} (%)</option>
                            </select></td>
                        <td>
                            <input type="hidden" name="shipping_data[add_rates][{$destination_id}][rate_value][W][0][per_unit]" value="N" />
                            <input type="checkbox" name="shipping_data[add_rates][{$destination_id}][rate_value][W][0][per_unit]" value="Y" class="checkbox" />
                        </td>
                        <td>
                            {hook name="shippings:weight_dependences_new"}
                            {/hook}</td>
                        <td class="right"> <div class="hidden-tools">{include file="buttons/multiple_buttons.tpl" item_id="add_rate_celm_weight_`$destination_id`" tag_level=3}</div></td>
                    </tr>
                {/if}

            </table>


            {include file="common/subheader.tpl" title=__("items_dependences") meta="clear"}
            <table class="table table-middle">
                <thead>
                <tr class="cm-first-sibling">
                    <th width="1%">{include file="common/check_items.tpl" check_target="items-`$destination_id`"}</th>
                    <th width="30%">{__("products_amount")}</th>
                    <th width="15%">{__("rate_value")}</th>
                    <th width="15%">{__("type")}</th>
                    <th width="10%">{__("per_item")}</th>
                    <th>{hook name="shippings:items_dependences_head"}{/hook}</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                {foreach from=$rate_data.rate_value.I item="rate" key="k" name="rdf"}
                    <tr>
                        <td>
                            <input type="checkbox" name="delete_rate_data[{$destination_id}][I][{$k}]" value="Y" {if $smarty.foreach.rdf.first}disabled="disabled"{/if} class="checkbox cm-item-items-{$destination_id} cm-item" /></td>
                        <td class="nowrap">
                            {__("more_than")}&nbsp;
                            {if $smarty.foreach.rdf.first}
                                <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][I][0][amount]" value="0" />&nbsp;&nbsp;0 {__("items")}
                            {else}
                                <input type="text" name="shipping_data[rates][{$destination_id}][rate_value][I][{$k}][amount]" data-v-min="0" data-v-max="999999" data-p-sign="s" data-a-sign=" {__("items")}" size="5" value="{$k}" class="cm-numeric input-small input-hidden" />
                            {/if}
                        </td>
                        <td>
                            <input type="text" name="shipping_data[rates][{$destination_id}][rate_value][I][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][value]" size="5" value="{$rate.value|default:"0"}" class="input-small input-hidden" /></td>
                        <td>
                            <select class="input-medium" name="shipping_data[rates][{$destination_id}][rate_value][I][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][type]">
                                <option value="F" {if $rate.type == "F"}selected="selected"{/if}>{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                                <option value="P" {if $rate.type == "P"}selected="selected"{/if}>{__("percent")} (%)</option>
                            </select></td>
                        <td>
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][I][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][per_unit]" value="N" />
                            <input type="checkbox" name="shipping_data[rates][{$destination_id}][rate_value][I][{if $smarty.foreach.rdf.first}0{else}{$k}{/if}][per_unit]" value="Y" class="checkbox" {if $rate.per_unit == "Y"}checked="checked"{/if} />
                        </td>
                        <td>{hook name="shippings:items_dependences_body"}{/hook}</td>
                        <td class="nowrap right">
                            {capture name="tools_items"}

                                {if !$smarty.foreach.rdf.first && $shipping|fn_allow_save_object:"shippings"}
                                    <a class="cm-confirm cm-tooltip" href="{"shippings.delete_rate_value?rate_type=I&amount=`$k`&shipping_id=`$id`&destination_id=`$destination_id`&rate_id=`$rate_data.rate_id`"|fn_url}" title="{__("delete")}"><i class="icon-trash"></i></a>
                                {else}
                                    <span class="icon-trash undeleted-element" {__("delete")}></span>
                                {/if}
                            {/capture}
                            <div class="hidden-tools">
                                {if $smarty.foreach.rdf.first}
                                    {include file="buttons/remove_item.tpl" but_class="cm-delete-row"}
                                {else}
                                    {include file="buttons/remove_item.tpl" only_delete='Y' but_class="cm-delete-row"}
                                {/if}

                            </div>
                        </td>
                    </tr>
                    {foreachelse}
                    <tr class="no-items">
                        <td colspan="6">
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][I][0][amount]" value="0" />
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][I][0][value]" value="0" />
                            <input type="hidden" name="shipping_data[rates][{$destination_id}][rate_value][I][0][type]" value="F" />
                            <p>{__("no_items")}</p></td>
                    </tr>
                {/foreach}

                {if !$hide_for_vendor}
                    <tr id="box_add_rate_celm_items_{$destination_id}">
                        <td>
                            <input type="checkbox" disabled="disabled" value="Y" class="checkbox cm-item-items cm-item" /></td>
                        <td>
                            {__("more_than")}&nbsp; <input type="text" name="shipping_data[add_rates][{$destination_id}][rate_value][I][0][amount]" data-v-min="0" data-v-max="999999" data-p-sign="s" data-a-sign=" {__("items")}" size="5" value="" class="cm-numeric input-small input-hidden" /></td>
                        <td>
                            <input type="text" name="shipping_data[add_rates][{$destination_id}][rate_value][I][0][value]" size="5" value="" class="input-small input-hidden" /></td>
                        <td>
                            <select class="input-medium" name="shipping_data[add_rates][{$destination_id}][rate_value][I][0][type]">
                                <option value="F">{__("absolute")} ({$currencies.$primary_currency.symbol nofilter})</option>
                                <option value="P">{__("percent")} (%)</option>
                            </select></td>
                        <td>
                            <input type="hidden" name="shipping_data[add_rates][{$destination_id}][rate_value][I][0][per_unit]" value="N" />
                            <input type="checkbox" name="shipping_data[add_rates][{$destination_id}][rate_value][I][0][per_unit]" value="Y" class="checkbox" />
                        </td>
                        <td>
                            {hook name="shippings:items_dependences_new"}
                            {/hook}</td>
                        <td class="right">
                            <div class="hidden-tools">
                                {include file="buttons/multiple_buttons.tpl" item_id="add_rate_celm_items_`$destination_id`" tag_level=3}
                            </div>
                        </td>
                    </tr>
                {/if}

            </table>

        </div>

        {/foreach}
    </div>
</div>


