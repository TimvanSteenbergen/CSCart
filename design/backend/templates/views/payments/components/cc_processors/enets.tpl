{assign var="r_url" value="http"|fn_payment_url:"enets.php"}
<p>{__("text_enets_notice", ["[r_url]" => "<span>`$r_url`</span>"])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="merchantid">{__("merchant_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchantid]" id="merchantid" value="{$processor_params.merchantid}"  size="60">
    </div>
</div>