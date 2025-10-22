<?php

/**
 * Attentively.PushWatchedTerms API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_attentively_pushwatchedterms() {
  $result = CRM_Attentively_BAO_Attentively::pushWatchedTerms();
  
  if (!is_array($result)) {
    if ($result) {
      return civicrm_api3_create_success(ts('Total watched terms pushed: ' . $result));
    }
    else {
      return civicrm_api3_create_success(ts('No watched terms were pushed to Attentive.ly!'));
    }
  }
  else {
    return civicrm_api3_create_error(ts('There was an error pushing watched terms to Attentive.ly. Error(s): ' . implode(',' , $result)));
  }
}

