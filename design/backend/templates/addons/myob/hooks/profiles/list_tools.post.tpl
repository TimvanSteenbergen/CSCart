{* Profile exporter *}
{if $search.user_type == 'C'}
    <li><a class="cm-process-items cm-submit" data-ca-dispatch="dispatch[myob_export.export_profiles]" data-ca-target-form="userlist_form">{__("export_to_myob")}</a></li>
{/if}