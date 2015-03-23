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
  $count = CRM_Attentively_BAO_Attentively::pullMembers();
  if ($count) {
    return civicrm_api3_create_success(ts('Total members fetched from Attentive.ly: ' . $count));
  }
  else {
    return civicrm_api3_create_error(ts('Error while fetching members from Attentive.ly'));
  }
}

