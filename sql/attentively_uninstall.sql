DROP TABLE IF EXISTS `civicrm_attentively_member`;

DROP TABLE IF EXISTS `civicrm_attentively_member_network`;

DELETE FROM civicrm_navigation WHERE name = 'Extensions';

DELETE FROM civicrm_navigation WHERE name = 'Attentive.ly';

DELETE FROM civicrm_navigation WHERE name = 'Manage Social Media';
