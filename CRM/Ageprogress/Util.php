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

  public static function nativeCalculateAge($contact) {
    $birthDate = CRM_Utils_Array::value('birth_date', $contact);
    $age = CRM_Utils_Date::calculateAge($birthDate);
    return CRM_Utils_Array::value('years', $age, 0);
  }

  public function calculateAge($contact) {
    $age = call_user_func($this->ageCalcCallback, $contact);
    return $age;
  }

  /**
   * Remove from and add to the given array of sub-types, based on the given age
   * and the configuration of sub-types having is_ageprogress.
   *
   * @param Array $subTypesOfContact One or more contact sub-type names; typically
   *   this is the set of existing sub-types for a certain contact.
   * @param Int $age Age to use in calculating sub-types.
   * @return Array Modified list of contact sub-type names.
   */
  public static function alterSubTypes($subTypesOfContact, $age) {
    $subTypesOfContact = (array) $subTypesOfContact;
    $ageprogressSubTypes = self::calculateAgeprogressSubTypes($age);

    foreach ($ageprogressSubTypes as $ageprogressSubType => $isAgeAppropriate) {
      // If it's age appropriate, add it.
      if ($isAgeAppropriate) {
        $subTypesOfContact[] = $ageprogressSubType;
      }
      // Otherwise, remove it.
      else {
        $index = array_search($ageprogressSubType, $subTypesOfContact);
        if ($index !== FALSE) {
          unset($subTypesOfContact[$index]);
        }
      }
    }
    return array_filter(array_unique($subTypesOfContact));
  }

  /**
   * For a given age, get an array of age-progress-managed sub-types; for each
   * subtype, indicate whether it IS or IS NOT appropriate to the age.
   *
   * @param Integer $age
   * @return Array
   *   One-dimensional array keyed to sub-type name, with boolean
   *   indicating whether it is or is not appropriate to the age. Example:
   *   $age = 3;
   *   array(
   *     'child' => TRUE,
   *     'youth' => FALSE,
   *     'adult' => FALSE,
   *   );
   *   Only one sub-type should be TRUE; all others should be FALSE.
   */
  public static function calculateAgeprogressSubTypes($age = 0) {
    $ret = [];
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
      if (!$currentSubTypeName && $ageprogressSubType['ageprogress_max_age'] >= $age) {
        $currentSubTypeName = $subTypeName;
        $ret[$subTypeName] = TRUE;
      }
      else {
        $ret[$subTypeName] = FALSE;
      }
    }
    if ($finalSubTypeName) {
      if (empty(array_filter($ret))) {
        $ret[$finalSubTypeName] = TRUE;
      }
      else {
        $ret[$finalSubTypeName] = FALSE;
      }
    }
    return $ret;
  }

  public static function arrayValuesEqual($a, $b) {
    $x = array_values($a);
    $y = array_values($b);

    sort($x);
    sort($y);

    return $x == $y;
  }

}
