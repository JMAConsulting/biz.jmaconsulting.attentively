<?php

/**
 * Attentively.PullPosts API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_attentively_pullposts() {
  $count = CRM_Attentively_BAO_Attentively::pullPosts();
  if ($count) {
    return civicrm_api3_create_success(ts('Total posts fetched from Attentive.ly: ' . $count));
  }
  else {
    return civicrm_api3_create_error(ts('Error while fetching posts from Attentive.ly'));
  }
}

