<script type="text/javascript">

    {if $redirect_url}
        var url = '{$redirect_url}';
        opener.location.href = url.replace(/\&amp;/g,'&');
    {else}
        opener.location.reload();
    {/if}

    close();

</script>