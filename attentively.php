<?php

define('ENV', 1); // Set ENV to 1 for production API (https://api.attentive.ly) or 0 for test API (http://apidev.attentive.ly)
define('EXT_NAME', basename(__DIR__));

require_once 'attentively.civix.php';

/**
 * Implements hook_civicrm_config().
 */
function attentively_civicrm_config(&$config) {
  _attentively_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 */
function attentively_civicrm_xmlMenu(&$files) {
  _attentively_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 */
function attentively_civicrm_install() {
  CRM_Core_Session::singleton()->set('authEnabled', TRUE);
  return _attentively_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function attentively_civicrm_uninstall() {
  return _attentively_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 */
function attentively_civicrm_enable() {
  $config = CRM_Core_Config::singleton();
  CRM_Core_Session::singleton()->set('authEnabled', TRUE);
  CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, $config->extensionsDir . EXT_NAME . '/sql/attentively_enable.sql');
  return _attentively_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 */
function attentively_civicrm_disable() {
  $config = CRM_Core_Config::singleton();
  CRM_Utils_File::sourceSQLFile(CIVICRM_DSN, $config->extensionsDir . EXT_NAME . '/sql/attentively_disable.sql');
  return _attentively_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 */
function attentively_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _attentively_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function attentively_civicrm_managed(&$entities) {
  return _attentively_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_tabs().
 */
function attentively_civicrm_tabs(&$tabs, $contactID) {

  $url = CRM_Utils_System::url('civicrm/contact/view/scores',
         "reset=1&snippet=1&force=1&cid=$contactID");
  $count = CRM_Attentively_BAO_Attentively::getCount($contactID);
  $tabs[] = array(
    'id'    => 'socialMedia',
    'url'   => $url,
    'title' => 'Social Media',
    'weight' => 85,
    'count' => $count,
  );
}

/**
 * Implements hook_civicrm_queryObjects().
 */
function attentively_civicrm_queryObjects(&$queryObjects, $type) {
  if ($type == 'Contact') {
    $queryObjects[] = new CRM_Attentively_BAO_Query();
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function attentively_civicrm_pageRun(&$page) {
  if (get_class($page) == 'CRM_Admin_Page_Extensions' && CRM_Core_Session::singleton()->get('authEnabled')) {
    CRM_Core_Session::singleton()->set('authEnabled', FALSE);
    if (CRM_Attentively_BAO_Attentively::checkAttentivelyAuth() == 'none') {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/auth', "reset=1"));
    }
  }
}
