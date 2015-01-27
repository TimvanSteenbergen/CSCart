{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

    {assign var="r_url" value=$config.current_url|escape:url}
    <div class="items-container cm-sortable {if !""|fn_allow_save_object:"":true} cm-hide-inputs{/if}"
         data-ca-sortable-table="currencies" data-ca-sortable-id-name="currency_id" id="manage_currencies_list">
        {if $currencies_data}
            <table class="table table-middle table-objects table-striped">
                <tbody>
                {foreach from=$currencies_data item="currency"}
                    {if $currency.is_primary == "Y"}
                        {assign var="_href_delete" value=""}
                    {else}
                        {assign var="_href_delete" value="currencies.delete?currency_id=`$currency.currency_id`"}
                    {/if}
                    {assign var="currency_details" value="<span>`$currency.currency_code`</span>, {__("currency_rate")}: <span>`$currency.coefficient`</span>, {__("currency_sign")}: <span>`$currency.symbol`</span>"}

                    {include file="common/object_group.tpl"
                        id=$currency.currency_id
                        text=$currency.description
                        details=$currency_details
                        href="currencies.update?currency_id=`$currency.currency_id`&return_url=$r_url"
                        href_delete=$_href_delete
                        delete_data=$currency.currency_code
                        delete_target_id="manage_currencies_list"
                        header_text="{__("editing_currency")}: `$currency.description`"
                        table="currencies"
                        object_id_name="currency_id"
                        status=$currency.status
                        additional_class="cm-sortable-row cm-sortable-id-`$currency.currency_id`"
                        no_table=true
                        non_editable=$runtime.company_id
                        is_view_link=true
                        draggable=true
                        hidden=true
                        update_controller="currencies"
                        st_result_ids="manage_currencies_list"}
                {/foreach}
                </tbody>
            </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
    <!--manage_currencies_list--></div>
    
    <div class="buttons-container">
        {capture name="extra_tools"}
            {hook name="currencies:import_rates"}{/hook}
        {/capture}
    </div>
    {if ""|fn_allow_save_object:"":true}
        {capture name="adv_buttons"}
            {capture name="add_new_picker"}
                {include file="views/currencies/update.tpl" currency=[]}
            {/capture}

            {include file="common/popupbox.tpl" id="add_new_currency" text=__("new_currency") content=$smarty.capture.add_new_picker title=__("add_currency") act="general" icon="icon-plus"}
        {/capture}
    {/if}
{/capture}

{capture name="sidebar"}
    <div class="sidebar-row">
        <h6>{__("exchange_rate")}</h6>
        <ul class="unstyled currencies-rate" id="currencies_stock_exchange">
        </ul>
    </div>
    <a href="http://finance.yahoo.com/" class="currencies-powered" target="_blank" title="Yahoo finance"></a>
    <script type="text/javascript">

        var exchangeRate = {

            primary_currency: "{$primary_currency}",

            init: function() {

                // Check if primary_currency is valid else use USD as default value
                this.getRate(this.primary_currency, 'USD', "exchangeRate.getAllCurrency");

                $.ceEvent('on', 'ce.form_confirm', function(elm) {
                    var code = elm.data('caParams');

                    if(code !== 'EUR' && code !== 'GBP' && code !== 'CHF') {
                        $('#currencies_stock_exchange li:contains("' + code + '")').remove();
                    }
                });
            },

            getAllCurrency: function(data){
                var self = this;
                var currencies = ['USD','EUR','GBP','CHF'];

                if(parseFloat(data.query.results.row.rate, 10) == 0) {
                    this.primary_currency = 'USD';
                }

                {foreach from=$currencies_data item="currency"}
                    {if $currency.currency_code != "EUR" && $currency.currency_code != "GBP" && $currency.currency_code != "CHF" && $currency.currency_code != "USD"}
                        currencies.push("{$currency.currency_code}");
                    {/if}
                {/foreach}

                $.each(currencies, function(index, value) {
                    self.getRate(value, self.primary_currency);
                });
            },

            getRate: function (from, to, callback) {
                var script = document.createElement('script');
                callback = callback || "exchangeRate.parseExchangeRate";

                script.setAttribute('src', "http://query.yahooapis.com/v1/public/yql?q=select%20rate%2Cname%20from%20csv%20where%20url%3D'http%3A%2F%2Fdownload.finance.yahoo.com%2Fd%2Fquotes%3Fs%3D" + from + to + "%253DX%26f%3Dl1n'%20and%20columns%3D'rate%2Cname'&format=json&callback=" + callback);
                document.body.appendChild(script);
            },

            parseExchangeRate: function(data) {

                var name = Tygh.$.trim(data.query.results.row.name.split('to')[0]);
                var rate = parseFloat(data.query.results.row.rate, 10);
                var container = Tygh.$('#currencies_stock_exchange');

                if(rate !== 0 && name != this.primary_currency) {
                    function asc_sort(a, b){
                        return ($(b).text()) < ($(a).text()) ? 1 : -1;
                    }
                    container.append('<li>' + name + ' / '+ this.primary_currency +' <span class="pull-right muted">'+ rate +'</span></li>');
                    container.find('li').sort(asc_sort).appendTo(container);
                }
            }
        };

        exchangeRate.init();
    </script>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="currencies:manage_tools_list"}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{include file="common/mainbox.tpl" title=__("currencies") content=$smarty.capture.mainbox sidebar=$smarty.capture.sidebar select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
