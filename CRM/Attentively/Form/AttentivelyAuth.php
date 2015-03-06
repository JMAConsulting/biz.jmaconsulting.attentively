<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Attentively_Form_AttentivelyAuth extends CRM_Core_Form {
  function buildQuickForm() {

    $this->add('text', "access_token", ts('Access Token'), array(
        'size' => 30, 'maxlength' => 60, 'readonly' => TRUE)
    );
    $this->add('checkbox', "accept", ts('I have read and accepted the terms of conditions'));
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Authorize'),
        'isDefault' => TRUE,
      ),
    ));

    $defaults = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, NULL, 'name', FALSE);
    $this->setDefaults($defaults);
    $this->freeze('access_token');
    parent::buildQuickForm();
  }

  function postProcess() {
    $systemUrl = rawurlencode(CRM_Utils_System::url('civicrm/attentively/callback', NULL, TRUE));
    $redirectUri = "http://attentive.ly.jmaconsulting.biz/civicrm/attentively/request?redirect={$systemUrl}"; // Redirect to JMA instance
    CRM_Utils_System::redirect($redirectUri);
  }
}
