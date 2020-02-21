<?php
use CRM_Ageprogress_ExtensionUtil as E;

class CRM_Ageprogress_Page_AgeprogressTypes extends CRM_Core_Page {

  public function run() {
    CRM_Core_Session::singleton()->pushUserContext('civicrm/admin/options/subtype/ageprogress?reset=1');
    $ageprogressSubTypes = Civi\Api4\AgeprogressContactType::get()
      ->addWhere('is_ageprogress', '=', 1)
      ->addOrderBy('is_ageprogress_final', 'ASC')
      ->addOrderBy('ageprogress_max_age', 'ASC')
      ->setChain([
        'contact_type' => ['ContactType', 'get', ['where' => [['id', '=', '$contact_type_id']]], 0],
      ])
      ->execute();

    $this->assign('rows', $ageprogressSubTypes);

    parent::run();
  }

}
