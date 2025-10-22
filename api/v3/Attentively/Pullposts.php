<?php

/**
 * Attentively.PullPosts API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_attentively_pullposts() {
  $result = CRM_Attentively_BAO_Attentively::pullPosts();
  if (!is_array($result)) {
    if ($result) {
      return civicrm_api3_create_success(ts('Total posts fetched from Attentive.ly: ' . $result));
    }
    else {
      return civicrm_api3_create_success(ts('No posts were fetched from Attentive.ly!'));
    }
  }
  else {
    return civicrm_api3_create_error(ts('There was an error fetching posts from Attentive.ly. Error(s): ' . implode(',' , $result)));
  }
}

