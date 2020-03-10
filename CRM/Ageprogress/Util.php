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
   * @param Array $subTypes One or more contact sub-type names.
   * @param Int $age Age to use in calculating sub-types.
   * @return Array Modified list of contact sub-type names.
   */
  public static function alterSubTypes($subTypes, $age) {
    $subTypes = (array) $subTypes;
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
        // If this sub-type is not current for this contact, remove it.
        // (Note that if it's the final sub-type we'll be adding it below anyway.
        $index = array_search($subTypeName, $subTypes);
        if ($index !== FALSE) {
          unset($subTypes[$index]);
        }
      }
    }
    // If one of the subtypes is current, add it.
    if ($currentSubTypeName) {
      $subTypes[] = $currentSubTypeName;
      // Also remove the Final sub-type, if any.
      if ($finalSubTypeName) {
        $index = array_search($finalSubTypeName, $subTypes);
        if ($index !== FALSE) {
          unset($subTypes[$index]);
        }
      }
    }
    // If not, add the final sub-type (if any).
    elseif ($finalSubTypeName) {
      $subTypes[] = $finalSubTypeName;
    }
    return array_filter(array_unique($subTypes));
  }

  public static function arrayValuesEqual($a, $b) {
    $x = array_values($a);
    $y = array_values($b);

    sort($x);
    sort($y);

    return $x == $y;
  }

}
