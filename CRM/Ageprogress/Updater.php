<?php

/**
 * Updater for ageprogress extension.
 *
 */
class CRM_Ageprogress_Updater {

  var $params;
  var $isDoUpdate;

  public function __construct($params) {
    $params['birth_date'] = ['IS NOT NULL' => 1];
    $this->params = $params;
    $this->isDoUpdate = $this->isDoUpdate();
  }

  public function doUpdate() {
    $contactGet = civicrm_api3('contact', 'get', $this->params);
    foreach ($contactGet['values'] as $contact) {

    }
    return $contactGet['count'];
  }

  private function nativeIsDoUpdate() {
    return TRUE;
  }

  /**
   * Determine whether or not to run a site-wide update now, using its own logic,
   * which may be overridden by hook_civicrm_ageprogress_alterIsDoUpdate
   *
   * @return Boolean
   */
  public function isDoUpdate() {
    $isDoUpdate = $this->nativeIsDoUpdate();
    $null = NULL;
    $params = $this->params;
    CRM_Utils_Hook::singleton()->invoke(['isDoUpdate', 'params'], $isDoUpdate, $params, $null,
      $null, $null, $null,
      'civicrm_ageprogress_alterIsDoUpdate'
    );

    return $isDoUpdate;
  }
}
