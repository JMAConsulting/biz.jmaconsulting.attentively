--
-- Table structure for table `civicrm_attentively_member`
--

CREATE TABLE IF NOT EXISTS `civicrm_attentively_member` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(10) NOT NULL,
  `contact_id` int(10) NOT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `first_name` varchar(64) DEFAULT NULL,
  `last_name` varchar(64) DEFAULT NULL,
  `age` int(10) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip_code` varchar(12) DEFAULT NULL,
  `metro_area` text,
  `klout_score` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `civicrm_attentively_member_network`
--

CREATE TABLE IF NOT EXISTS `civicrm_attentively_member_network` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `civicrm_attentively_watched_terms`
--

CREATE TABLE IF NOT EXISTS `civicrm_attentively_watched_terms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(255) DEFAULT NULL,
  `nickname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Adding Administration form to navigation menu
--

SELECT @parentId := id FROM `civicrm_navigation` WHERE `name` = 'Administer';

INSERT INTO `civicrm_navigation` (`domain_id`, `label`, `name`,`permission`, `permission_operator`, `parent_id`, `is_active`, `has_separator`, `weight`) VALUES
(1, 'Extensions', 'Extensions', 'access CiviCRM', 'AND', @parentId, 1, 1, -10);

SELECT @parentId := id FROM `civicrm_navigation` WHERE `name` = 'Extensions';

INSERT INTO `civicrm_navigation` (`domain_id`, `label`, `name`, `url`, `permission`, `permission_operator`, `parent_id`, `is_active`, `has_separator`, `weight`) VALUES
(1, 'Attentive.ly', 'Attentive.ly', 'civicrm/auth', 'access CiviCRM', 'AND', @parentId, 1, 1, 1);

SELECT @parentId := id FROM `civicrm_navigation` WHERE `name` = 'Contacts';

INSERT INTO `civicrm_navigation` (`domain_id`, `label`, `name`, `url`, `permission`, `permission_operator`, `parent_id`, `is_active`, `has_separator`, `weight`) VALUES
(1, 'Manage Social Media', 'Manage Social Media', 'https://attentive.ly', 'access CiviCRM', 'AND', @parentId, 1, 2, 1);
