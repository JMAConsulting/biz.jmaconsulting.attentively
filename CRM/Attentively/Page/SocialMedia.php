<?php

require_once 'CRM/Core/Page.php';

class CRM_Attentively_Page_SocialMedia extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('SocialMedia'));
    $session = CRM_Core_Session::singleton();
    $cid = $session->get('view.id');
    $post = array();
    $networkData = CRM_Attentively_BAO_Attentively::getNetworks($cid);
    $kloutScore = CRM_Attentively_BAO_Attentively::getKloutScore($cid);
    $memberID = CRM_Attentively_BAO_Attentively::getMemberID($cid);
    $posts= CRM_Attentively_BAO_Attentively::getPosts($cid);
    if ($posts) {
      foreach ($posts as $key => $value) {
        $post[$key]['network'] = ucfirst($value['post_network']);
        $post[$key]['content'] = $value['post_content'];
        $post[$key]['post_url'] = $value['post_url'];
        $post[$key]['date'] = $value['post_date'];
      }
    }
    $attURL = "https://dashboard.attentive.ly/";

    if ($memberID) {
      $attURL .= "dashboard/contact_detail/{$memberID}";
      $this->assign('memID', $memberID);
    }
    $this->assign('attURL', $attURL);
    $this->assign('posts', $post);
    $this->assign('networkData', $networkData);
    $this->assign('klout', $kloutScore);
    parent::run();
  }
}
