<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Attentively_BAO_Attentively {
  
  static public function checkEnvironment() {
    if (ENV) {
      $url = 'https://api.attentive.ly/';
    }
    else {
      $url = 'http://apidev.attentive.ly/';
    }
    return $url;
  }
  
  static public function updateAttentivelyAuth($params) {
    $accessToken = CRM_Utils_Array::value('access_token', $params);
    if ($accessToken) {
      CRM_Core_DAO::singleValueQuery("UPDATE civicrm_option_value SET value = '{$accessToken}' WHERE name = 'access_token'");
    }
  } 
  
  static public function checkAttentivelyAuth() {
    return CRM_Core_DAO::singleValueQuery("SELECT value FROM civicrm_option_value WHERE name = 'access_token'");
  }
  
  /**
   *
   * @params   array   array of name/value pairs of contact info
   * @return boolean  success or failure
   * $Id$
   *
   */
  static public function pushMembers($params = NULL) {
    $settings = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, NULL, 'name', FALSE);
    $url = self::checkEnvironment();
    $url = $url . 'members_add';
    // Retrieve only necessary fields
    if (empty($params)) { // No contacts specified so attempt to push all contacts 
      $params = array( 
        'return.first_name' => 1,
        'return.last_name' => 1,
        'return.email' => 1,
        'rowCount' => 1000,
        'sequential' => 1,
      );
    }
    $contacts = civicrm_api3('Contact', 'get', $params);
    foreach ($contacts['values'] as $key => $values) {
      if (!CRM_Utils_Array::value('email', $values)) {
        continue;
      }
      $members[$key]['contact_id'] =  $values['id'];
      $members[$key]['first_name'] =  $values['first_name'];
      $members[$key]['last_name'] =  $values['last_name'];
      $members[$key]['email_address'] =  $values['email'];
    }
    $object = json_encode(json_decode(json_encode($members), FALSE));
    $post = 'access_token=' . $settings['access_token'] . '&members=' . $object;
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, TRUE);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    
    $response = curl_exec( $ch );
    $result = get_object_vars(json_decode($response));
  }

  static public function pullMembers() {
    $settings = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, NULL, 'name', FALSE);
    $url = self::checkEnvironment();
    $url = $url . 'members';
    $post = 'access_token=' . $settings['access_token'];
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, TRUE);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec( $ch );
    $result = get_object_vars(json_decode($response));
    $network = array();
    if ($result['success']) {
      // Store members
      foreach ($result['members'] as $key => $value) {
        $sql = "INSERT INTO civicrm_attentively_member (`member_id`, `contact_id`, `email_address`, `first_name`, `last_name`, `age`, `city`, `state`, `zip_code`, `metro_area`, `klout_score`) 
          VALUES ( '{$value->member_id}', '{$value->contact_id}', '{$value->email_address}', '{$value->first_name}', '{$value->last_name}', '{$value->age}', '{$value->city}', '{$value->state}', 
          '{$value->zip_code}', '{$value->metro_area}', '{$value->klout_score}' ) 
          ON DUPLICATE KEY UPDATE member_id = '{$value->member_id}', email_address = '{$value->email_address}', first_name = '{$value->first_name}', last_name = '{$value->last_name}',
          age = '{$value->age}', city = '{$value->city}', state = '{$value->state}', zip_code = '{$value->zip_code}', metro_area = '{$value->metro_area}', klout_score = '{$value->klout_score}'";
        $dao = CRM_Core_DAO::executeQuery($sql);
        // Store networks
        foreach ($value->networks as $key => $networks) {
          $network[$key]['contact_id'] = $value->contact_id;
          $network[$key]['name'] = $networks->name;
          $network[$key]['url'] = $networks->url;
        }
      }
      foreach ($network as $key => $value) {
        $query = "INSERT INTO civicrm_attentively_member_network (`contact_id`, `name`, `url`)
          VALUES ( '{$value['contact_id']}', '{$value['name']}', '{$value['url']}' )";
        $dao = CRM_Core_DAO::executeQuery($query);
      }
    }
  }

  static public function pullPosts($terms) {
    if (empty($terms)) {
      return;
    }
    $settings = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, NULL, 'name', FALSE);
    $url = self::checkEnvironment();
    $url = $url . 'posts';
    $post = 'access_token=' . $settings['access_token'] . '&period=' . $settings['post_period_to_retrieve'] . '&term=' . $terms . '&include_member_info=TRUE';
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, TRUE);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec( $ch );
    $result = get_object_vars(json_decode($response));
    return $result;
  }

  static public function pushWatchedTerms($terms) {
    if (empty($terms)) {
      return;
    }
    $settings = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, NULL, 'name', FALSE);
    $url = self::checkEnvironment();
    $url = $url . 'watched_terms_add';
    $post = 'access_token=' . $settings['access_token'] . '&terms=' . $terms;
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, TRUE);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec( $ch );
    $result = get_object_vars(json_decode($response));
    return $result;
  }

  static public function getNetworks($cid) {
    if (!$cid) {
      return NULL;
    }
    $config = CRM_Core_Config::singleton();
    $sql = "SELECT * FROM civicrm_attentively_member_network
      WHERE contact_id = {$cid}";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      if ($dao->name == 'gravatar') {
        $email = self::getAttentivelyEmail($cid);
        $network[$dao->name]['url'] = $dao->url;
        $atts = array(
          'position' => 'static',
          'border-top-left-radius' => '5px',
          'border-top-right-radius' => '5px',            
        );
        $network[$dao->name]['image'] = self::getGravatar($email, TRUE, $atts);
      }
      else {
        $network[$dao->name]['url'] = $dao->url;
        $network[$dao->name]['image'] = '<img class="network-image" src="' .$config->extensionsURL. '/biz.jmaconsulting.attentively/images/' .$dao->name. '.png" style="width:80px !important; height:80px !important;"/>';
      }
    }
    return $network;
  }

  static public function getKloutScore($cid) {
    if (!$cid) {
      return NULL;
    }
    $sql = "SELECT klout_score FROM civicrm_attentively_member
      WHERE contact_id = {$cid}";
    $klout = CRM_Core_DAO::singleValueQuery($sql);
    return $klout;
  }

  static public function getMemberID($cid) {
    if (!$cid) {
      return NULL;
    }
    $sql = "SELECT member_id FROM civicrm_attentively_member
      WHERE contact_id = {$cid}";
    $id = CRM_Core_DAO::singleValueQuery($sql);
    return $id;
  }

  static public function getCount($cid) {
    $sql = "SELECT count(id) FROM civicrm_attentively_member_network
      WHERE contact_id = {$cid}";
    $count = CRM_Core_DAO::singleValueQuery($sql);
    return $count;
  } 
  
  static public function getAttentivelyEmail($cid) {
    if (!$cid) {
      return NULL;
    }
    $sql = "SELECT email_address FROM civicrm_attentively_member
      WHERE contact_id = {$cid}";
    $email = CRM_Core_DAO::singleValueQuery($sql);
    return $email;
  }

  /**
   * Get either a Gravatar URL or complete image tag for a specified email address.
   *
   * @param string $email The email address
   * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
   * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
   * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
   * @param boole $img True to return a complete IMG tag False for just the URL
   * @param array $atts Optional, additional key/value attributes to include in the IMG tag
   * @return String containing either just a URL or a complete image tag
   *
   */
  function getGravatar($email, $img = FALSE, $atts = array(), $s = 80, $d = 'mm', $r = 'g') {
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";
    if ( $img ) {
      $url = '<img class="gravatar-image" src="' . $url . '"';
      foreach ( $atts as $key => $val )
        $url .= ' ' . $key . '="' . $val . '"';
      $url .= ' />';
    }
    return $url;
  }
  
  static public function getNetworkList() {
    $sql = "SELECT name FROM civicrm_attentively_member_network GROUP BY name";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $networks[$dao->name] = ucfirst($dao->name);
    }
    return $networks;
  }
}