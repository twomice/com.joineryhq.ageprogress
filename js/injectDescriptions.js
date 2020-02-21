(function(ts) {
  CRM.$(function($) {
    // Append any descriptions for bhfe fields.
    for (var i in CRM.vars.ageprogress.descriptions) {
      $('input#' + i + ', select#' + i).after('<div class="description" id="' + i + '-description>'+ CRM.vars.ageprogress.descriptions[i] +'</div>');
    }

  });
}(CRM.ts('com.joineryhq.ageprogress')));