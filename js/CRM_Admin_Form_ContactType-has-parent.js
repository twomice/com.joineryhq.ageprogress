(function(ts) {
  CRM.$(function($) {

    var isAgeprogressChange = function isAgeprogressChange() {
      var finalTr = $('#is_ageprogress_final').closest('tr');
      var maxAgeTr = $('#ageprogress_max_age').closest('tr');
      if ($('#is_ageprogress').is(':checked')) {
        finalTr.show();
        isAgeprogressFinalChange();
      }
      else {
        finalTr.hide();
        maxAgeTr.hide();
      }
    };

    var isAgeprogressFinalChange = function isAgeprogressFinalChange() {
      var tr = $('#ageprogress_max_age').closest('tr');
      if ($('#is_ageprogress_final').is(':checked')) {
        tr.hide();
      }
      else {
        tr.show();
      }
    };
    // Give the bhfe elements table an id so we can handle it later.
    $('input#is_ageprogress').closest('table').attr('id', 'bhfe_table');

    var trIsActive = $('input#is_active').closest('tr');
    // remove the 'nowrap' class because it breaks the layout.
    $('table#bhfe_table td').removeClass('nowrap');
    // Move all bhfe table rows into the main table aftrer 'is active'
    $('table#bhfe_table tr').insertAfter(trIsActive);

    // Remove the bhfe table, which should be empty by now.
    $('table#bhfe_table').remove();

    // Set change hanler for 'is_ageprogress_final'.
    $('input#is_ageprogress_final').change(isAgeprogressFinalChange);

    // Set change hanler for 'is_ageprogress', and go ahead and run it to start with.
    $('input#is_ageprogress').change(isAgeprogressChange);
    isAgeprogressChange();

  });
}(CRM.ts('com.joineryhq.ageprogress')));