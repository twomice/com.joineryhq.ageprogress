<?php

/**
 * Updater for ageprogress extension.
 *
 */
class CRM_Ageprogress_Updater {

  private $params = [];
  public $isDoUpdate;

  public function __construct($params) {
    $params['birth_date'] = ['IS NOT NULL' => 1];
    $params['contact_type'] = 'Individual';
    $params['return'] = ["birth_date", "contact_sub_type"];
    $this->params = $params;
    $this->isDoUpdate = $this->isDoUpdate();
  }

  public function doUpdate() {
    $ret = array(
      'processedCount' => 0,
      'updateCount' => 0,
      'errorCount' => 0,
    );
    $contactGet = civicrm_api3('contact', 'get', $this->params);
    $util = CRM_Ageprogress_Util::singleton();
    foreach ($contactGet['values'] as &$contact) {
      $ret['processedCount']++;

      $birthDate = CRM_Utils_Array::value('birth_date', $contact);
      $subTypes = CRM_Utils_Array::value('contact_sub_type', $contact);
      $age = $util->calculateAge($birthDate);

      $altSubTypes = CRM_Ageprogress_Util::alterSubTypes($subTypes, $age);
      if (!CRM_Ageprogress_Util::arrayValuesEqual($subTypes, $altSubTypes)) {
        $contact['ageprogress_processed'] = TRUE;
        $contact['contact_sub_type'] = $altSubTypes;
        try {
          civicrm_api3('contact', 'create', $contact);
          $ret['updateCount']++;
        }
        catch (CiviCRM_API3_Exception $e) {
          $ret['errorCount']++;
          CRM_Core_Error::debug_log_message('Ageprogress: encountered error in contact.create API while updating sub-types for contact ID=' . $contact['id'] . '; API error message: ' . $e->getMessage());
          CRM_Core_Error::debug_var('Ageprogress: contact.create API params', $contact);
        }
      }
    }

    $null = NULL;
    $params = $this->params;
    CRM_Utils_Hook::singleton()->invoke(['params', 'counts'], $params, $ret, $null,
      $null, $null, $null,
      'civicrm_ageprogress_postUpdate'
    );

    return $ret;
  }

  /**
   * Determine whether or not to run a site-wide update now, using its own logic,
   * which may be overridden by hook_civicrm_ageprogress_alterIsDoUpdate
   *
   * @return Boolean
   */
  public function isDoUpdate() {
    // By default, we'll always run the update.
    $isDoUpdate = TRUE;
    // Allow hook implementations to alter that decision.
    $null = NULL;
    $params = $this->params;
    CRM_Utils_Hook::singleton()->invoke(['isDoUpdate', 'params'], $isDoUpdate, $params, $null,
      $null, $null, $null,
      'civicrm_ageprogress_alterIsDoUpdate'
    );
    return $isDoUpdate;
  }

}
