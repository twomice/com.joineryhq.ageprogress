<?php
use CRM_Ageprogress_ExtensionUtil as E;

class CRM_Ageprogress_BAO_AgeprogressContactType extends CRM_Ageprogress_DAO_AgeprogressContactType {

  /**
   * Create a new AgeprogressContactType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Ageprogress_DAO_AgeprogressContactType|NULL
   */
  // public static function create($params) {
  //   $className = 'CRM_Ageprogress_DAO_AgeprogressContactType';
  //   $entityName = 'AgeprogressContactType';
  //   $hook = empty($params['id']) ? 'create' : 'edit';

  //   CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
  //   $instance = new $className();
  //   $instance->copyValues($params);
  //   $instance->save();
  //   CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

  //   return $instance;
  // }

}
