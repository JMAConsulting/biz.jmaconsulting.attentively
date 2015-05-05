<?php

define('ROWCOUNT', 1000);
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

  static public function pushMembers() {
    $sqlBody = "FROM civicrm_contact c 
        LEFT JOIN civicrm_email e ON e.contact_id = c.id
        LEFT JOIN civicrm_attentively_member_processed m ON m.contact_id = c.id 
        LEFT JOIN civicrm_group_contact gc ON gc.contact_id = c.id
        LEFT JOIN civicrm_group g ON gc.group_id = g.id
        WHERE e.is_primary = 1
        AND m.is_processed IS NULL
        AND e.email IS NOT NULL
        AND c.is_deleted <> 1
        GROUP BY c.id";  // used to determine initial count and to retrieve records and insert processed records
    $count = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM (SELECT COUNT(*) " . $sqlBody . ") as S"); // total members to send
    $settings = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, " AND v.name = 'access_token' ", 'name', FALSE);
    $memberCount = 0; // number of members successfully sent
    $startRow = 0; // next row to send
    $errors = array();
    while ($count > 0) {
      $sqlBodyLimited = $sqlBody . " LIMIT $startRow, " . ROWCOUNT; // will use to insert processed records on success
      $sql = "SELECT c.id, c.first_name, c.last_name, e.email, g.title " . $sqlBodyLimited;
      $contacts = CRM_Core_DAO::executeQuery($sql);
      if ($contacts->N == 0) {
        break;
      }
      $members = array();
      $members['access_token'] = $settings['access_token']; //Not sure why this needs to be done before the members array, but doesn't work if done in the function below. 
      while ($contacts->fetch()) {
        $members['members'][$contacts->id]['contact_id'] =  $contacts->id;
        $members['members'][$contacts->id]['first_name'] =  addslashes($contacts->first_name);
        $members['members'][$contacts->id]['last_name'] =  addslashes($contacts->last_name);
        $members['members'][$contacts->id]['email_address'] =  $contacts->email;
        $members['members'][$contacts->id]['group'] =  addslashes($contacts->title);
      }
      $result = self::getAttentivelyResponse('members_add', $members, TRUE);
      if ($result['success']) {
        $memberCount += $contacts->N;
        $sql = "INSERT INTO civicrm_attentively_member_processed (contact_id, is_processed) 
        SELECT c.id, 1 " . $sqlBodyLimited;
        CRM_Core_DAO::singleValueQuery($sql);
      } else {
        $errors[] = $result['error'];
      }
      $count -= $contacts->N;
      $startRow += $contacts->N;
    }
    return empty($errors) ? $memberCount : array_unique($errors);
  }

  static public function pullMembers() {
    $settings = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, " AND v.name = 'access_token' ", 'name', FALSE);
    $url = self::checkEnvironment();
    $url = $url . 'members';
    $post = 'access_token=' . $settings['access_token'] . '&use_deferred=1';
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, TRUE);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec( $ch );
    $result = get_object_vars(json_decode($response));
    $network = $errors = array();
    // Check the deferred status [This allows us to call all records without pagination]
    if ($result['success'] && $result['deferred_status'] == 'queued') {
      // Call API again until deferred status is ready
      $post = 'access_token=' . $settings['access_token'] . '&deferred_id=' . $result['deferred_id'] . '&use_deferred=1';
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $post); // Set new postfields
      $response = curl_exec( $ch );
      $result = get_object_vars(json_decode($response));
      while ($result['deferred_status'] == 'queued') {
        sleep(10); // Delay execution by 10 seconds to allow list to be refreshed
        $response = curl_exec( $ch );
        $result = get_object_vars(json_decode($response));
      }
    }
    else {
      $errors[] = $result['error'];
    }
    curl_close($ch);
    
    if ($result['success'] && $result['deferred_status'] == 'complete') {
      // Store members
      foreach ($result['members'] as $key => $value) {
        $attContact = array();
        // Add members into CiviCRM if contact_id not returned.
        if (empty($value->contact_id)) {
          $attContact['first_name'] = $value->first_name;
          $attContact['last_name'] = $value->last_name;
          $attContact['email'] = $value->email_address;
          $attContact['contact_type'] = 'Individual';
          $attContact['version'] = 3;
          $dedupeParams = CRM_Dedupe_Finder::formatParams($attContact, 'Individual');
          $dedupeParams['check_permission'] = FALSE;
          $dupes = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual');
          if (count($dupes) == 1) {
            $attContact['contact_id'] = $dupes[0];
          } 
          elseif (count($dupes) > 1) {
            $dao = new CRM_Core_DAO_UFMatch();
            $dao->uf_name = $attContact['email'];
            if ($dao->find(TRUE)) {
              $attContact['contact_id'] = $dao->contact_id;
            }
            else { 
              $attContact['contact_id'] = $dupes[0];
            }
          }
          $contact = civicrm_api( 'Contact', 'create', $attContact );
          if (CRM_Utils_Array::value('id', $contact)) {
            $value->contact_id = $contact['id'];
          }
        }
        if (empty($value->klout_score)) {
          $value->klout_score = 0;
        }
        $sql = "INSERT INTO civicrm_attentively_member (`member_id`, `contact_id`, `email_address`, `first_name`, `last_name`, `age`, `city`, `state`, `zip_code`, `metro_area`, `klout_score`) 
          VALUES ( '{$value->member_id}', '{$value->contact_id}', '{$value->email_address}', %1, %2, '{$value->age}', %3, %4, 
          '{$value->zipcode}', %5, '{$value->klout_score}' ) 
          ON DUPLICATE KEY UPDATE member_id = '{$value->member_id}', email_address = '{$value->email_address}', first_name = %1, last_name = %2,
          age = '{$value->age}', city = %3, state = %4, zip_code = '{$value->zipcode}', metro_area = %5, klout_score = '{$value->klout_score}'";
        $params = array( 
          1 => array($value->first_name, 'String'),
          2 => array($value->last_name, 'String'),
          3 => array($value->city, 'String'),
          4 => array($value->state, 'String'),
          5 => array($value->metro_area, 'String'),
        );
        $dao = CRM_Core_DAO::executeQuery($sql, $params);
        // Store networks
        foreach ($value->networks as $k => $networks) {
          $network[$key][$k]['contact_id'] = $value->contact_id;
          $network[$key][$k]['name'] = $networks->name;
          $network[$key][$k]['url'] = $networks->url;
          $network[$key][$k]['photo'] = $networks->photo;
        }
      }
      foreach ($network as $key => $v) {
        foreach ($v as $value ) {
          $check = "SELECT id FROM civicrm_attentively_member_network WHERE `contact_id` = '{$value['contact_id']}' AND `name` = '{$value['name']}'";
          $flag = CRM_Core_DAO::singleValueQuery($check);
          if ($flag) 
            continue;
          $query = "INSERT INTO civicrm_attentively_member_network (`contact_id`, `name`, `url`, `photo`)
          VALUES ( '{$value['contact_id']}', '{$value['name']}', '{$value['url']}', '{$value['photo']}' )";
          $dao = CRM_Core_DAO::executeQuery($query);
        }
      }
      return count($result['members']);
    }
    else {
      $errors[] = $result['error'];
    }
    return array_unique($errors);
  }

  static public function pullWatchedTerms() {
    $errors = array();
    $result = self::getAttentivelyResponse('watched_terms', NULL);
    $dao = new CRM_Attentively_DAO_AttentivelyWatchedTerms();
    if ($result['success'] && !empty($result['watched_terms'])) {
      foreach ($result['watched_terms'] as $term) {
        $dao->term = $term->term;
        $dao->nickname = $term->nickname;
        $dao->save();
      }
      return count($result['watched_terms']);
    }
    else {
      $errors[] = $result['error'];
    }
    return array_unique($errors);
  }

  static public function getPosts($cid) {
    $posts = array();
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM civicrm_attentively_posts WHERE contact_id = {$cid}");
    while ($dao->fetch()) {
      $posts[$dao->id]['post_network'] = $dao->network;
      $posts[$dao->id]['post_content'] = $dao->post_content;
      $posts[$dao->id]['post_date'] = $dao->post_date;
      $posts[$dao->id]['post_url'] = $dao->post_url;
    }
    return $posts;
  }

  static public function pullPosts() {
    $terms = $errors = array();
    CRM_Attentively_BAO_AttentivelyWatchedTerms::getWatchedTerms($terms);
    foreach ($terms as $term) {
      $allTerms .= $term['term'] . ',';
    }
    if (empty($allTerms)) {
      return array('error' => ts('You must specify watched terms before you can pull posts. Please specify them at Administer >> System Settings >> Option Groups >> Attentive.ly Watched Terms'));
    }
    $period = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, " AND v.name = 'post_period_to_retrieve' ", 'name', FALSE);
    $post = '&period=' . $period['post_period_to_retrieve'] . '&term=' . $allTerms;
    $result = self::getAttentivelyResponse('posts', $post);
 
    if ($result['success']) {
      // Store posts
      foreach ($result['posts'] as $key => $value) {
        $check = CRM_Core_DAO::singleValueQuery("SELECT 1 FROM civicrm_attentively_posts WHERE post_timestamp = {$value->timestamp}");
        if ($check)
          continue;
        // FIXME: This needs to have its own DAO
        $sql = "INSERT INTO civicrm_attentively_posts (`member_id`, `contact_id`, `network`, `post_content`, `post_date`, `post_timestamp`,  `post_url`) 
          VALUES ( '{$value->member_id}', '{$value->contact_id}', '{$value->network}', %1, %2, '{post_timestamp}', %3)";
        $params = array( 
          1 => array($value->post_content, 'String'),
          2 => array(date('Y-m-d H:i:s', strtotime($value->post_date)), 'String'),
          3 => array($value->post_url, 'String'),
        );
        $dao = CRM_Core_DAO::executeQuery($sql, $params);
      }
      return count($result['posts']);
    }
    else {
      $errors[] = $result['error'];
    }
    return array_unique($errors);
  }

  static public function pushWatchedTerms() {
    $errors = array();
    $terms = CRM_Core_OptionGroup::values('attentively_terms', FALSE, FALSE, FALSE, NULL, 'label', FALSE);
    if (empty($terms)) {
      return array('error' => ts('You have not specified any watched terms. Please specify them at Administer >> System Settings >> Option Groups >> Attentive.ly Watched Terms'));
    }
    $terms = '&terms=' . implode(',' , $terms);
    $result = self::getAttentivelyResponse('watched_terms_add', $terms); 
    if ($result['success']) {
      return count($result['parameters']->terms);
    }
    else {
      $errors[] = $result['error'];
    }
    return array_unique($errors);
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
        $email = self::getAttentivelyFromContact($cid, array('email_address'));
        $atts = array(
          'position' => 'static',
          'border-top-left-radius' => '5px',
          'border-top-right-radius' => '5px',            
        );
        $network[$dao->name]['image'] = self::getGravatar($email['email_address'], TRUE, $atts);
      }
      elseif ($dao->name != 'klout') {
        $network[$dao->name]['url'] = $dao->url;
        $network[$dao->name]['image'] = '<img class="network-image" src="' .$config->extensionsURL. '/biz.jmaconsulting.attentively/images/' .$dao->name. '.png" style="width:80px !important; height:80px !important;"/>';
      }
      if ($dao->photo != '' && $dao->name != 'gravatar') {
        $network['gravatar']['image'] = '<img class="photo" src=' . $dao->photo . ' />';
      }
      if ($dao->name == 'klout') {
        $network['gravatar']['url'] = $dao->url;
      }
    }
    return $network;
  }

  static public function getAttentivelyFromContact($cid, $value) {
    if (!$cid) {
      return NULL;
    }
    $members = array();
    $total = count($value);
    $count = 0;
    $sql = "SELECT ". implode(',' , $value) ." FROM civicrm_attentively_member
      WHERE contact_id = {$cid}";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $dao->fetch();
    while ($total) {
      $members[$value[$count]] = $dao->$value[$count];
      $count++;
      $total--;
    }
    return !empty($members) ? $members : NULL;
  }

  static public function getCount($cid) {
    $sql = "SELECT count(id) FROM civicrm_attentively_member_network
      WHERE contact_id = {$cid} and name NOT IN ('klout', 'gravatar')";
    $count = CRM_Core_DAO::singleValueQuery($sql);
    return $count;
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
    $sql = "SELECT name FROM civicrm_attentively_member_network WHERE name NOT IN ('klout', 'gravatar') GROUP BY name";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $networks = array();
    while ($dao->fetch()) {
      $networks[$dao->name] = ucfirst($dao->name);
    }
    return $networks;
  }


  static public function getAttentivelyResponse($url, $postPart, $isMember = FALSE) {
    $settings = CRM_Core_OptionGroup::values('attentively_auth', TRUE, FALSE, FALSE, " AND v.name = 'access_token' ", 'name', FALSE);
    // FIXME: should refactor for all POST fields
    if ($isMember) {
      $post = http_build_query($postPart);
    }
    else {
      $post = 'access_token=' . $settings['access_token'] . $postPart;
    }
    $url = self::checkEnvironment() . $url;
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, TRUE);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    

    $response = curl_exec( $ch );
    return get_object_vars(json_decode($response));
  }
}
