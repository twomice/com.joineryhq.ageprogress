<div class="crm-content-block crm-block">
  {if $rows}
  <div>
    {strip}
    <table id="options" class="display">
    <thead>
    <tr>
        <th>{ts}Contact Type{/ts}</th>
        <th>{ts}Final sub-type?{/ts}</th>
        <th>{ts}Maximum age{/ts}</th>
        <th></th>
    </tr>
    </thead>
    {foreach from=$rows item=row}
      {assign var='contact_type_id' value=$row.contact_type_id}

      <tr id="contact_type-{$row.id}" class="{cycle values="odd-row,even-row"} crm-contactType crm-entity {if NOT $row.contact_type.is_active} disabled{/if}">
        <td class="crm-contactType-label">{ts}{$row.contact_type.label}{/ts}</td>
        <td class="crm-contactType-is_final">{if $row.is_ageprogress_final}<img src="{$config->resourceBase}i/check.gif" alt="{ts}Yes{/ts}" />{/if}</td>
        <td class="crm-contactType-max_age">{$row.ageprogress_max_age}</td>
        <td class="crm-contactType-actions"><a href="{crmURL p='civicrm/admin/options/subtype' q="action=update&reset=1&id=$contact_type_id"}" class="action-item crm-hover-button crm-popup">{ts}Edit{/ts}</a></td>
    </tr>
    {/foreach}
    </table>
    {/strip}
  </div>
  {else}
    <div class="messages status no-popup">
      <img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/>
      {ts}None found.{/ts}
    </div>
  {/if}
</div>
<script type="text/javascript">
  {literal}
// http://civicrm.org/licensing
// Adds ajaxy behavior to a simple CiviCRM page
CRM.$(function($) {
  var active = 'a.button, a.action-item:not(.crm-enable-disable), a.crm-popup';
  $('#crm-main-content-wrapper')
    // Widgetize the content area
    .crmSnippet()
    // Open action links in a popup
    .off('.crmLivePage')
    .on('click.crmLivePage', active, CRM.popup)
    .on('crmPopupFormSuccess.crmLivePage', active, CRM.refreshParent);
});
    
  {/literal}
    
</script>