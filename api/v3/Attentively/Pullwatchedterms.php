<?php

/**
 * Attentively.PullWatchedTerms API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_attentively_pullwatchedterms() {
  $result = CRM_Attentively_BAO_Attentively::pullWatchedTerms();
  if (!is_array($result)) {
    if ($result) {
      return civicrm_api3_create_success(ts('Total watched terms fetched: ' . $result));
    }
    else {
      return civicrm_api3_create_success(ts('No watched terms were fetched from Attentive.ly!'));
    }
  }
  else {
    return civicrm_api3_create_error(ts('There was an error fetching watched terms from Attentive.ly. Error(s): ' . implode(',' , $result)));
  }
}

