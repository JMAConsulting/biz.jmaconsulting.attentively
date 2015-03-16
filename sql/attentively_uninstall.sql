DROP TABLE IF EXISTS `civicrm_attentively_member`;

DROP TABLE IF EXISTS `civicrm_attentively_member_network`;

DROP TABLE IF EXISTS `civicrm_attentively_watched_terms`;

DROP TABLE IF EXISTS `civicrm_attentively_posts`;

DELETE FROM civicrm_navigation WHERE name = 'Extensions';

DELETE FROM civicrm_navigation WHERE name = 'Attentive.ly';

DELETE FROM civicrm_navigation WHERE name = 'Manage Social Media';

DELETE FROM civicrm_option_group WHERE name = 'attentively_auth';

DELETE FROM civicrm_option_group WHERE name = 'attentively_terms';
