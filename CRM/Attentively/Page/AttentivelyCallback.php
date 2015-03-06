<?php

require_once 'CRM/Core/Page.php';

class CRM_Attentively_Page_AttentivelyCallback extends CRM_Core_Page {
  function run() {
    if ($_POST['access_token']) {
      $values['access_token'] = $_POST['access_token'];
      CRM_Attentively_BAO_Attentively::updateAttentivelyAuth($values);
      return TRUE;
    }
    else {
      if (CRM_Attentively_BAO_Attentively::checkAttentivelyAuth() != 'none') {
        CRM_Core_Session::setStatus(
          ts('Access Token obtained successfully!'),
          ts('Complete'), 'success');
      }
      else {
        CRM_Core_Session::setStatus(
          ts('There was an error obtaining the Access Token'),
          ts('Error'), 'error');
      }
      
    }
    parent::run();
  }
}
