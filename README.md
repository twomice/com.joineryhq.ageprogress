# CiviCRM: Sub-type progress by age (com.joineryhq.ageprogress)

This extension will automatically progress contacts through a series of sub-types 
based on age, according to configurable rules. It provides a daily scheduled job 
to process changes, and performs updates on any new contact or when birth date 
is changed. It also provides the following hooks:
* civicrm_ageprogress_alterAgeCalcMethod($callback): specify an alternate method 
  or function for age calculation (default is CiviCRM's native method based 
  strictly on date of birth).
* hook_civicrm_ageprogress_alterIsDoUpdate($isDoUpdate): alter the decision to 
  perform updates at the current time.
* TBD: define additional actions to perform upon update completion.

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
cv dl com.joineryhq.ageprogress@https://github.com/FIXME/com.joineryhq.ageprogress/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/com.joineryhq.ageprogress.git
cv en ageprogress
```

## Usage

(* FIXME: Where would a new user navigate to get started? What changes would they see? *)

## Known Issues

(* FIXME *)
