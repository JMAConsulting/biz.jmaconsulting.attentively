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
class CRM_Attentively_BAO_Query extends CRM_Contact_BAO_Query_Interface {


  /**
   * static field for all the export/import attentively fields
   *
   * @var array
   * @static
   */
  static $_networkFields = array();


  public function &getFields() {
    if (!self::$_networkFields) {
      self::$_networkFields = array_merge(self::$_networkFields, CRM_Attentively_DAO_AttentivelyMemberNetwork::export());
    }
    return self::$_networkFields;
  }

  /**
   * build select for Attentively
   *
   * @return void
   * @access public
   */
  public function select(&$query) { }

  public function where(&$query) {
    $grouping = NULL;
    foreach (array_keys($query->_params) as $id) {
      if (!CRM_Utils_Array::value(0, $query->_params[$id])) {
        continue;
      }
      if (substr($query->_params[$id][0], 0, 8) == 'network_') {
        if ($query->_mode == CRM_Contact_BAO_QUERY::MODE_CONTACTS) {
          $query->_useDistinct = TRUE;
        }
        $this->whereClauseSingle($query->_params[$id], $query);
      }
    }
  }

  public function whereClauseSingle(&$values, &$query) {
    $fields = $this->getFields();
    list($name, $op, $value, $grouping, $wildcard) = $values;
    switch ($name) {
    case 'network_options':
      $this->networkOptions($values);
      return;

    case 'network_toggle':
    case 'network_operator': // handled above
      return;
      
    case 'network_klout_score_low':      
    case 'network_klout_score_high':
      $query->numberRangeBuilder($values,
        'civicrm_attentively_member', 'network_klout_score', 'klout_score', ts('Klout Score')
      );      
      break;

    default:
        
      if (!isset($fields[$name])) {
        CRM_Core_Session::setStatus(ts(
          'We did not recognize the search field: %1.',
           array(1 => $name)
           )
        );
        return;
      }
    }
  }

  public function from($name, $mode, $side) {
    $from = NULL;
    switch ($name) {
    case 'civicrm_attentively_member_network':
      $from = " $side JOIN civicrm_attentively_member_network ON civicrm_attentively_member_network.contact_id = contact_a.id";
      break;
    case 'civicrm_attentively_member':
      $from = " $side JOIN civicrm_attentively_member ON civicrm_attentively_member.contact_id = contact_a.id";
      break;
    }
    return $from;
  }

  public function buildSearchForm(&$form) {
    $form->addElement('text', 'network_klout_score_low', ts('From'));
    $form->addElement('text', 'network_klout_score_high', ts('To'));

    $networks = CRM_Attentively_BAO_Attentively::getNetworkList();
    $form->add(
      'select',
      'network_options',
      ts('Social Media Accounts'),
      $networks,
      FALSE,
      array(
        'id' => 'network_options',
        'multiple' => 'multiple',
        'title' => ts('- select -'),
      )
    );

    $form->addElement(
      'select',
      'network_operator',
      ts('Operator'),
      array(
        'OR' => ts('OR'),
        'AND' => ts('AND'),
      )
    );

    $options = array(
      1 => ts('Exclude'),
      2 => ts('Include by Social Media Account(s)'),
    );
    $form->addRadio('network_toggle', ts('Social Media Options'), $options);
  }

  public function searchAction(&$row, $id) {}

  public function setTableDependency(&$tables) {
    $tables = array_merge(array('civicrm_attentively_member_network' => 1, 'civicrm_attentively_member' => 1), $tables);
  }

  public function getPanesMapper(&$panes) {
    $panes['Social Media'] = 'civicrm_attentively_member_network';
  }

  public function registerAdvancedSearchPane(&$panes) {
    $panes['Social Media'] = 'social';
  }

  public function buildAdvancedSearchPaneForm(&$form, $type) {
    if ($type  == 'social') {
      $form->add('hidden', 'hidden_social', 1);
      self::buildSearchForm($form);
      $form->setDefaults(array('network_toggle' => 2));
    }
  }

  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {
    if ($type  == 'social') {
      $paneTemplatePathArray['social'] = 'CRM/Attentively/Form/Search/Criteria.tpl';
    }
  }

  public function networkOptions($values) {
    list($name, $op, $value, $grouping, $wildcard) = $values;

    if (empty($value) || !is_array($value)) {
      return;
    }

    // get the operator and toggle values
    $opValues = $this->getWhereValues('network_operator', $grouping);
    $operator = 'OR';
    if ($opValues &&
        strtolower($opValues[2] == 'AND')
        ) {
      $operator = 'AND';
    }

    $toggleValues = $this->getWhereValues('network_toggle', $grouping);
    $compareOP = '!=';
    if ($toggleValues &&
        $toggleValues[2] == 2
        ) {
      $compareOP = '=';
    }

    $clauses = array();
    $qill = array();
    foreach ($value as $dontCare => $pOption) {
      $clauses[] = " ( civicrm_attentively_member_network.name $compareOP '{$pOption}' ) ";
      $field = CRM_Utils_Array::value($pOption, $this->getFields());
      $title = $field ? $field['title'] : $pOption;
      $qill[] = " Social Media $compareOP $title ";
    }

    $this->_where[$grouping][] = '( ' . implode($operator, $clauses) . ' )';
    $this->_qill[$grouping][] = implode($operator, $qill);
  }

  public function getWhereValues($name, $grouping) {
    $result = NULL;
    foreach ($this->_params as $values) {
      if ($values[0] == $name && $values[3] == $grouping) {
        return $values;
      }
    }

    return $result;
  }
}

