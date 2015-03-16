UPDATE civicrm_navigation SET is_active = 0 WHERE name = 'Extensions';

UPDATE civicrm_navigation SET is_active = 0 WHERE name = 'Attentive.ly';

UPDATE civicrm_navigation SET is_active = 0 WHERE name = 'Manage Social Media';

UPDATE civicrm_option_group SET is_active = 0 WHERE name = 'attentively_auth';

UPDATE civicrm_option_group SET is_active = 0 WHERE name = 'attentively_terms';
