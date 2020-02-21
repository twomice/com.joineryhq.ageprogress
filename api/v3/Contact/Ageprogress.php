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
    $ret = $updater->doUpdate();
  }
  else {
    $ret = 'nopers';
  }

  $returnValues = $ret;
//
//  if (!CRM_Ageprogress_Utils::isDoUpdate()) {
//
//  }
//  $contactGet = civicrm_api3('contact', 'get', $params);
//  foreach ($contactGet['values'] as $contactc) {
//  }

  return civicrm_api3_create_success($returnValues, $params, 'Contact', 'Ageprogress');
}
