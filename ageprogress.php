<?php

require_once 'ageprogress.civix.php';
use CRM_Ageprogress_ExtensionUtil as E;

/**
 * Implements hook_civicrm_pageRun().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pageRun/
 */
function ageprogress_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Admin_Page_ContactType' && $page->getVar('_action') == CRM_Core_Action::BROWSE) {
    $ageprogressSubTypes = Civi\Api4\AgeprogressContactType::get()
      ->addWhere('is_ageprogress', '=', 1)
      ->execute();
    $jsVars = [
      'isAgeproggressTypesIds' => [],
    ];
    foreach ($ageprogressSubTypes as $ageprogressSubType) {
      $jsVars['isAgeproggressTypesIds'][] = $ageprogressSubType['contact_type_id'];
    }
    if ($jsVars['isAgeproggressTypesIds']) {
      CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.ageprogress', 'js/CRM_Admin_Page_ContactType.js');
      CRM_Core_Resources::singleton()->addVars('ageprogress', $jsVars);
      $text = E::ts(
        'Some contact types are configured for "Sub-Type by Age" processing (marked as <i class="crm-i fa-bolt"></i> in the list below); <a href="%1"> click here for an overview of them</a>.',
        [
          '1' => CRM_Utils_System::url('civicrm/admin/options/subtype/ageprogress', 'reset=1', NULL, NULL, FALSE, FALSE, TRUE),
        ]
      );
      CRM_Core_Session::setStatus($text, E::ts('Sub-Type by Age'), 'info', ['expires' => 0]);
    }
  }
}

/**
 * Implements hook_civicrm_pre().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pre/
 */
function ageprogress_civicrm_pre($op, $objectName, $id, &$params) {
  // On Create or Edit of any Inividual, modify sub-types based on age.
  if ($objectName == 'Individual'
    && (
      $op == 'create'
      || $op == 'edit'
    )
  ) {
    if (CRM_Utils_Array::value('ageprogress_processed', $params)) {
      // If this contact has already been processed for ageprogress sub-types
      // (as is done in contact.ageprogress API, via CRM_Ageprogress_Updater::doUpdate())
      // we'll just accept $params at face value, in order to avoid repeating
      // the calculation of the sub-types. So nothing to do here; return.
      return;
    }
    // If we're still here, we'll need to calculate the sub-types by age.
    // Get birthdate from params if given.
    $birthDate = CRM_Utils_Array::value('birth_date', $params);
    if (!$birthDate && $id) {
      // If there is no birthdate, and this is an existing contact, try to get
      // their birthdate from the contat record.
      $contact = civicrm_api3('contact', 'getSingle', ['id' => $id]);
      $birthDate = CRM_Utils_Array::value('birth_date', $contact);
    }
    // If we can't get the birthdate from params or from id, we just don't have
    // it, so nothing to do. But if we do have it, calculate age and adjust
    // sub-types.
    if ($birthDate) {
      $util = CRM_Ageprogress_Util::singleton();
      $age = $util->calculateAge($params);
      $params['contact_sub_type'] = CRM_Ageprogress_Util::alterSubTypes($params['contact_sub_type'], $age);
    }
  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm/
 */
function ageprogress_civicrm_buildForm($formName, &$form) {
  // Get a list of the injected fields for this form.
  $fieldNames = _ageprogress_buildForm_fields($formName, $form);
  if (!empty($fieldNames)) {
    _ageprogress_add_bhfe($fieldNames, $form);
  }

  if ($formName == 'CRM_Admin_Form_ContactType') {
    $contactTypeId = $form->getVar('_id');
    $contactTypeSettings = _ageprogressGetContactTypeSettings($contactTypeId);
    $defaults = [];
    if (!empty($contactTypeSettings)) {
      foreach ($fieldNames as $fieldName) {
        $defaults[$fieldName] = $contactTypeSettings[$fieldName];
      }
    }
    $form->setDefaults($defaults);
  }
};

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postProcess/
 */
function ageprogress_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_ContactType') {
    if ($form->getVar('_parentId')) {
      // Here we need to save all of our injected fields on this form.

      // Get a list of the injected fields for this form.
      $fieldNames = _ageprogress_buildForm_fields($formName);

      $contactTypeId = $form->getVar('_id');

      // Get the existing settings record for this subtype, if any.
      $ageprogressContactTypeGet = \Civi\Api4\AgeprogressContactType::get()
        ->addWhere('contact_type_id', '=', $contactTypeId)
        ->execute()
        ->first();
      // If existing record wasn't found, we'll create.
      if (empty($ageprogressContactTypeGet)) {
        $ageprogressContactType = \Civi\Api4\AgeprogressContactType::create()
          ->addValue('contact_type_id', $contactTypeId);
      }
      // If it was found, we'll just update it.
      else {
        $ageprogressContactType = \Civi\Api4\AgeprogressContactType::update()
          ->addWhere('id', '=', $ageprogressContactTypeGet['id']);
      }
      // Whether create or update, add the values of our injected fields.
      foreach ($fieldNames as $fieldName) {
        $ageprogressContactType->addValue($fieldName, $form->_submitValues[$fieldName]);
      }
      // Create/update settings record.
      $ageprogressContactType
        ->execute();
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function ageprogress_civicrm_config(&$config) {
  _ageprogress_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function ageprogress_civicrm_xmlMenu(&$files) {
  _ageprogress_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function ageprogress_civicrm_install() {
  _ageprogress_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function ageprogress_civicrm_postInstall() {
  _ageprogress_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function ageprogress_civicrm_uninstall() {
  _ageprogress_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function ageprogress_civicrm_enable() {
  _ageprogress_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function ageprogress_civicrm_disable() {
  _ageprogress_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function ageprogress_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _ageprogress_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function ageprogress_civicrm_managed(&$entities) {
  _ageprogress_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function ageprogress_civicrm_caseTypes(&$caseTypes) {
  _ageprogress_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function ageprogress_civicrm_angularModules(&$angularModules) {
  _ageprogress_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function ageprogress_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _ageprogress_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function ageprogress_civicrm_entityTypes(&$entityTypes) {
  _ageprogress_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function ageprogress_civicrm_themes(&$themes) {
  _ageprogress_civix_civicrm_themes($themes);
}

/**
 * Add injected elements to $form (if provided), and in any case return a list
 * of the injected fields for $formName.
 *
 * @param type $formName
 * @param type $form
 * @return string
 */
function _ageprogress_buildForm_fields($formName, &$form = NULL) {
  $fieldNames = [];
  $descriptions = [];
  if ($formName == 'CRM_Admin_Form_ContactType') {
    if ($form !== NULL) {
      // Only do this for sub-types of Individual.
      if ($form->getVar('_parentId') == 1) {
        $form->addElement('checkbox', 'is_ageprogress', E::ts('Progress by age'));
        $descriptions['is_ageprogress'] = E::ts('Should this sub-type be included in "Sub-Type by Age" processing?');

        $form->addElement('checkbox', 'is_ageprogress_final', E::ts('Final sub-type'));
        $descriptions['is_ageprogress_final'] = E::ts('Is this the final sub-type in the progression? (e.g., for "Adult" sub-type). If checked, contacts who match no other sub-types will be finally moved into this sub-type.');

        $form->addElement('text', 'ageprogress_max_age', E::ts('Maximum age'));
        $descriptions['ageprogress_max_age'] = E::ts('Contacts calculated to be above this age will be automatically removed from this sub-type.');
        $form->addRule('ageprogress_max_age', E::ts('Maximum age should be a positive number'), 'positiveInteger');
        $form->addFormRule('_ageprogress_CRM_Admin_Form_ContactType_formRule', $form);

        CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.ageprogress', 'js/CRM_Admin_Form_ContactType-has-parent.js');
      }
    }
    $fieldNames = [
      'is_ageprogress',
      'is_ageprogress_final',
      'ageprogress_max_age',
    ];
  }

  // Use JS to inject any form element descriptions.
  if (!empty($descriptions)) {
    CRM_Core_Resources::singleton()->addVars('ageprogress', ['descriptions' => $descriptions]);
    CRM_Core_Resources::singleton()->addScriptFile('com.joineryhq.ageprogress', 'js/injectDescriptions.js');

  }
  return $fieldNames;
}

function _ageprogress_add_bhfe(array $elementNames, CRM_Core_Form &$form) {
  $bhfe = $form->get_template_vars('beginHookFormElements');
  if (!$bhfe) {
    $bhfe = [];
  }
  foreach ($elementNames as $elementName) {
    $bhfe[] = $elementName;
  }
  $form->assign('beginHookFormElements', $bhfe);
}

/**
 * Shorthand to retrieve settings per contact type
 *
 */
function _ageprogressGetContactTypeSettings($contactTypeId) {
  static $contactTypeSettings = [];
  if (!in_array($contactTypeId, $contactTypeSettings)) {
    // Add fields to manage "primary is attending" for this registration.
    $contactTypeSettings[$contactTypeId] = \Civi\Api4\AgeprogressContactType::get()
      ->addWhere('contact_type_id', '=', $contactTypeId)
      ->execute()
      ->first();
  }

  return $contactTypeSettings[$contactTypeId];
}

/**
 * Validation rules for the CRM_Admin_Form_ContactType form, as called for by
 * HTML_Quickform::addFormRule().
 *
 * @param array $submitValues
 * @param array $submitFiles
 * @param object $form
 * @return array
 *   list of errors to be posted back to the form
 */
function _ageprogress_CRM_Admin_Form_ContactType_formRule($submitValues, $submitFiles, $form) {
  $errors = array();

  // Only bother if is_ageprogress is true.
  if ($submitValues['is_ageprogress']) {
    $contactTypeId = $form->getVar('_id');
    // Find any other contact type that has the same 'max_age' setting.
    $otherMaxAgeType = \Civi\Api4\AgeprogressContactType::get()
      ->addWhere('contact_type_id', '!=', $contactTypeId)
      ->addWhere('is_ageprogress', '=', 1)
      ->addWhere('ageprogress_max_age', '=', $submitValues['ageprogress_max_age'])
      ->execute()
      ->first();
    if ($otherMaxAgeType) {
      // If found, generate an error. Get some info about the other type so
      // we can report something useful about it.
      $otherContactType = Civi\Api4\ContactType::get()
        ->addWhere('id', '=', $otherMaxAgeType['contact_type_id'])
        ->execute()
        ->first();
      $errors['ageprogress_max_age'] = E::ts('The maxiumum age must be unique; the value "%1" is already in use in the Contact Type: %2', [
        '1' => $submitValues['ageprogress_max_age'],
        '2' => $otherContactType['label'],
      ]);
    }

    // If this is 'final', find any other already set to 'final'
    if (CRM_Utils_Array::value('is_ageprogress_final', $submitValues, 0)) {
      $otherFinalType = \Civi\Api4\AgeprogressContactType::get()
        ->addWhere('contact_type_id', '!=', $contactTypeId)
        ->addWhere('is_ageprogress', '=', 1)
        ->addWhere('is_ageprogress_final', '=', 1)
        ->execute()
        ->first();
      if ($otherFinalType) {
        // If found, generate an error. Get some info about the other type so
        // we can report something useful about it.
        $otherContactType = Civi\Api4\ContactType::get()
          ->addWhere('id', '=', $otherFinalType['contact_type_id'])
          ->execute()
          ->first();
        $errors['is_ageprogress_final'] = E::ts('Only one contact type can marked "final sub-type"; this setting is already in use in the Contact Type: %1', [
          '2' => $otherContactType['label'],
        ]);
      }
    }
  }
  return empty($errors) ? TRUE : $errors;
}
