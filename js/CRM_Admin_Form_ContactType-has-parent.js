(function(ts) {
  CRM.$(function($) {
    // Give the bhfe elements table an id so we can handle it later.
    $('input#is_ageprogress').closest('table').attr('id', 'bhfe_table');

    var trIsActive = $('input#is_active').closest('tr');
    // remove the 'nowrap' class because it breaks the layout.
    $('table#bhfe_table td').removeClass('nowrap');
    // Move all bhfe table rows into the main table aftrer 'is active'
    $('table#bhfe_table tr').insertAfter(trIsActive);

//    // Set change hanler for 'is_multiple', and go ahead and run it to start with.
//    $('input#is_multiple_registrations').change(isMultipleRegistrationsChange);
//    isMultipleRegistrationsChange();
//
//    // Set change hanler for 'is_primary_atending' radios.
//    $('input[name="is_primary_attending"]').change(isPrimaryAttendingChange);

    // Remove the bhfe table, which should be empty by now.
    $('table#bhfe_table').remove();

  });
}(CRM.ts('com.joineryhq.ageprogress')));