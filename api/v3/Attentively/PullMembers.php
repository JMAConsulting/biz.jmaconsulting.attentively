<?php

/**
 * Attentively.PullMembers API
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_attentively_pullmembers() {
  $result = CRM_Attentively_BAO_Attentively::pullMembers();
  if (!is_array($result)) {
    if ($result) {
      if ($result) {
        return civicrm_api3_create_success(ts('Total members fetched from Attentive.ly: ' . $result));
      }
    }
    else {
      return civicrm_api3_create_success(ts('No contacts were pulled from Attentive.ly!'));
    }
  }
  else {
    return civicrm_api3_create_error(ts('There was an error pulling contacts from Attentive.ly. Error(s): ' . implode(',' , $result)));
  }
}

