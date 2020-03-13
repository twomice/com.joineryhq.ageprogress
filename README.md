# CiviCRM: Sub-type progress by age (com.joineryhq.ageprogress)

This extension will automatically progress contacts through a series of sub-types
based on age, according to configurable rules. It provides a daily scheduled job
to process changes, and performs updates on any new contact or when birth date
is changed.

## Usage

This extension provides additional settings on the Edit Contact Type form. These 
settings are used by the scheduled daily job, and whenever an individual contact's
birth date is changed.

![Screenshot](/images/screenshot.png)

## Developer hooks
This extension provides the following hooks:
* hook_civicrm_ageprogress_alterAgeCalcMethod(&$callback): specify an alternate method
  or function for age calculation (default is CiviCRM's native method based
  strictly on date of birth).

  Callbacks should have this function signature:
  ```php
  /**
   * Example custom callback to calculate age.
   *  .
   * @param $contact
   *   Array of contact properties, as returned by the Contact.getsingle API (v3). This should
   *   contain both the 'birthdate' and 'id' properties, but of course you can retrieve more
   *   (or more recent) values if needed.
   */
  function mycallback($contact) {
    $birthDate = CRM_Utils_Array::value('birth_date', $contact);
    $customValue = CRM_Utils_Array::value('custom_123', $contact);
    $age = longExampleFunctionToAdjustAgeBasedOnCustomFieldValue($birthDate, $custom123);
    return $age;
  }
  ```
* hook_civicrm_ageprogress_alterIsDoUpdate(&$isDoUpdate, $apiParams): alter the decision to
  perform updates at the current time.
  * $isDoUpdate: boolean, passed by reference;
  * $apiParams: array of parameters passed to the Contact.ageprogress API.
* hook_civicrm_ageprogress_postUpdate($apiParams, &$return): additional actions
  to perform after contact.ageprogress updates.
  * $apiParams: a copy of the API parameters that were used in the contact.ageprogress
    API.
  * $returnArray: an array of results to be returned by the contact.ageprogress API.
    Results are concatenated into the Contact.ageprogress API results (visible, for
    exmaple, in Scheduled Job logs). Contact.ageprogress defines these values;
    hook implmentations may alter, remove, or define additional values:
    * processedCount: (Integer) number of total contacts processed.
    * updateCount: (Integer) number of total contacts having their sub-type changed.
    * errorCount: (Integer) number of contacts for which errors were encountere
      when attempting to change sub-types.

The extension is licensed under [GPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* CiviCRM 5.0

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.joineryhq.ageprogress@https://github.com/twomice/com.joineryhq.ageprogress/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/twomice/com.joineryhq.ageprogress.git
cv en ageprogress
```
