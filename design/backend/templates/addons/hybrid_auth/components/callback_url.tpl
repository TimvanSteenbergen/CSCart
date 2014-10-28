{assign var='provider_name' value=$providers_schema[$provider]['provider']}
<div class="control-group">
	<label class="control-label">{__('hybrid_auth.callback_url')}: </label>
	<div class="controls">
		<input type="text" class="span8" readonly="readonly" value="{"auth.process?hauth_done={$provider_name}"|fn_url:"C"}" onclick="this.select()">
	</div>
</div>