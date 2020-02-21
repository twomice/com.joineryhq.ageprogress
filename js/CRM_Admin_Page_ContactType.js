(function(ts) {
  CRM.$(function($) {

    for (var i in CRM.vars.ageprogress.isAgeproggressTypesIds) {
      var id = CRM.vars.ageprogress.isAgeproggressTypesIds[i];
      $('tr#contact_type-'+ id + ' td.crm-contactType-parent').append('<span>&nbsp; <i class="crm-i fa-bolt" title="Has &quot;Age Progress&quot; settings"></i></span>');
    }

  });
}(CRM.ts('com.joineryhq.ageprogress')));