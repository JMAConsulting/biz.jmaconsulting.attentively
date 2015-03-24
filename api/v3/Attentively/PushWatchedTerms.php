<?php

/**
 * Attentively.PushWatchedTerms API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_attentively_pushwatchedterms() {
  $count = CRM_Attentively_BAO_Attentively::pushWatchedTerms();
  if ($count) {
    return civicrm_api3_create_success(ts('Total terms pushed to Attentive.ly: ' . $count));
  }
  else {
    return civicrm_api3_create_error(ts('No terms were sent to Attentive.ly!'));
  }
}

