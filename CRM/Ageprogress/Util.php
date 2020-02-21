<?php

/**
 * Utilities for ageprogress extension
 *
 */
class CRM_Ageprogress_Util {

  private static $_singleton = NULL;

  private $ageCalcCallback = 'self::nativeCalculateAge';

  public function __construct() {
    $callback = $this->ageCalcCallback;
    $null = NULL;
    $callback = 'self::nativeCalculateAge';
    CRM_Utils_Hook::singleton()->invoke(['callback'], $callback, $null, $null,
      $null, $null, $null,
      'civicrm_ageprogress_alterAgeCalcMethod'
    );
    $this->ageCalcCallback = $callback;
  }

  public static function singleton(CRM_Ageprogress_Utils $instance = NULL) {
    if ($instance !== NULL) {
      self::$_singleton = $instance;
    }
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Ageprogress_Util();
    }
    return self::$_singleton;
  }

  public static function nativeCalculateAge($birthDate) {
    $age = CRM_Utils_Date::calculateAge($birthDate);
    return $age['years'];
  }

  public function calculateAge($birthDate) {
    $age = call_user_func($this->ageCalcCallback, $birthDate);
    return $age;
  }

  /**
   * Remove from and add to the given array of sub-types, based on the given age
   * and the configuration of sub-types having is_ageprogress.
   *
   * @param Array $subTypes One or more contact sub-type names.
   * @param Int $age Age to use in calculating sub-types.
   * @return Array Modified list of contact sub-type names.
   */
  public static function alterSubTypes($subTypes, $age) {
    $ageprogressSubTypes = Civi\Api4\AgeprogressContactType::get()
      ->addWhere('is_ageprogress', '=', 1)
      ->addOrderBy('ageprogress_max_age', 'ASC')
      ->setChain([
        'contact_type' => ['ContactType', 'get', ['where' => [['id', '=', '$contact_type_id']]], 0],
      ])
      ->execute();
    $finalSubTypeName = NULL;
    $currentSubTypeName = NULL;
    foreach ($ageprogressSubTypes as $ageprogressSubType) {
      $subTypeName = $ageprogressSubType['contact_type']['name'];
      if ($ageprogressSubType['is_ageprogress_final']) {
        $finalSubTypeName = $subTypeName;
        continue;
      }
      if (!$currentSubTypeName & $ageprogressSubType['ageprogress_max_age'] >= $age) {
        $currentSubTypeName = $subTypeName;
      }
      else {
        $index = array_search($subTypeName, $subTypes);
        unset($subTypes[$index]);
      }
    }
    // If one of the subtypes is current, add it.
    if ($currentSubTypeName) {
      $subTypes[] = $currentSubTypeName;
      $index = array_search($finalSubTypeName, $subTypes);
      unset($subTypes[$index]);
    }
    // If not, add the final sub-type (if any).
    elseif ($finalSubTypeName) {
      $subTypes[] = $finalSubTypeName;
    }
    return array_unique($subTypes);
  }

}
