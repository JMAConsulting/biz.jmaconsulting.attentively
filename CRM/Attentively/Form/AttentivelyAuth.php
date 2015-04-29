<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Attentively_Form_AttentivelyAuth extends CRM_Core_Form {
  function buildQuickForm() {

    $this->add('hidden', "access_token", ts('Access Token'));
    
    $defaults = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, NULL, 'name', FALSE);
    $buttonText = 'Authorize and Connect Attentive.ly';
    $extraParams = array();
    if (!empty($defaults['access_token']) && $defaults['access_token'] != 'none') {
      $buttonText = 'Reconnect to Attentive.ly';
      $defaults['accept'] = TRUE;
      $extraParams = array('disabled' => 'disabled');      
    }
    $this->addElement('checkbox', "accept", ts('I have read and accepted the terms and conditions'), NULL, $extraParams);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts($buttonText),
        'isDefault' => TRUE,
      ),
    ));

    $this->setDefaults($defaults);
    if (empty($extraParams)) {
      $this->addFormRule(array('CRM_Attentively_Form_AttentivelyAuth', 'formRule'), $this);
    }
    parent::buildQuickForm();
  }

  /**
   * form rule
   *
   * @param array $fields  the input form values
   * @param array $files   the uploaded files if any
   * @param array $options additional user data
   *
   * @return true if no errors, else array of errors
   * @access public
   * @static
   */
  static function formRule($fields, $files, $self) {
    $errors = array();
  
    if (!CRM_Utils_Array::value('accept', $fields)) {
      $errors['accept'] = ts('Please accept the terms and conditions before proceeding.');
    }
    return empty($errors) ? TRUE : $errors;
  }

  function postProcess() {
    $systemUrl = rawurlencode(CRM_Utils_System::url('civicrm/attentively/callback', 'access_token=', TRUE, NULL, TRUE, TRUE, FALSE));
    $redirectUri = "http://attentive.ly.jmaconsulting.biz/civicrm/attentively/request?redirect={$systemUrl}"; // Redirect to JMA instance
    CRM_Utils_System::redirect($redirectUri);
  }
}
