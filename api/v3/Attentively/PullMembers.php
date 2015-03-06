<?php

/**
 * Attentively.PullMembers API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_attentively_pullmembers($params) {
  CRM_Attentively_BAO_Attentively::pullMembers();
}

