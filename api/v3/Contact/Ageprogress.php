<?php
use CRM_Ageprogress_ExtensionUtil as E;

/**
 * Contact.Ageprogress API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_contact_Ageprogress_spec(&$spec) {
  _civicrm_api3_contact_get_spec($spec);
  $spec['is_force'] = [
    'options' => [
      '1' => 'Yes',
      '0' => 'No',
    ],
  ];
}

/**
 * Contact.Ageprogress API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_contact_Ageprogress($params) {
  $updater = new CRM_Ageprogress_Updater($params);
  if ($updater->isDoUpdate) {
    $updateResults = $updater->doUpdate();

    $returnString = "";
    foreach ($updateResults as $label => $value) {
      $returnString .= "{$label}: $value; ";
    }

    if ($updateResults['errorCount']) {
      $returnString .= E::ts('ERRORS DETECTED: check CiviCRM log file for error details.');
    }
  }
  else {
    $returnString = E::ts('It is not time to run updates; no action was taken.');
  }
  return civicrm_api3_create_success($returnString, $params, 'Contact', 'Ageprogress');
}
