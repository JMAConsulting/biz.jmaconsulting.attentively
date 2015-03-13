<?php

/**
 * Attentively.PullWatchedTerms API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_attentively_pullwatchedterms() {
  $count = CRM_Attentively_BAO_Attentively::pullWatchedTerms();
  
  if ($count) {
    return civicrm_api3_create_success(ts('Total watched terms fetched: ' . $count));
  }
  else {
    return civicrm_api3_create_error(ts('Error while fetching watched terms'));
  }
}

