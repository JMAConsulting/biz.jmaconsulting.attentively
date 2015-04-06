<?php

/**
 * Attentively.PushMembers API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_attentively_pushmembers() {
  $result = CRM_Attentively_BAO_Attentively::pushMembers();
  if (!is_array($result)) {
    if ($result) {
      return civicrm_api3_create_success(ts('Total contacts pushed to Attentive.ly: ' . $count));
    }
    else {
      return civicrm_api3_create_success(ts('No contacts were sent to Attentive.ly!'));
    }
  }
  else {
    return civicrm_api3_create_error(ts('There was an error sending contacts to Attentive.ly. Error(s): ' . implode(',' , $result)));
  }
}
