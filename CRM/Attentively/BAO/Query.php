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


  function &getFields() {
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
  function select(&$query) {
    $this->_params = $query->_params;
    if(  CRM_Contact_BAO_Query::componentPresent($query->_returnProperties, 'network_')) {
      $fields = $this->getFields();
      foreach ($fields as $fldName => $params) {
        if (CRM_Utils_Array::value($fldName, $query->_returnProperties)) {
          $query->_select[$fldName]  = "{$params['where']} as $fldName";
          $query->_element[$fldName] = 1;
          list($tableName, $dnc) = explode('.', $params['where'], 2);
          $query->_tables[$tableName]  = $query->_whereTables[$tableName] = 1;
        }
      }

      if (CRM_Utils_Array::value('network_name', $query->_returnProperties)) {
        $query->_select['network_name'] = "civicrm_attentively_member_network.name as network_name";
        $query->_element['network_name'] = 1;
      }

      if (CRM_Utils_Array::value('network_klout', $query->_returnProperties)) {
        $query->_select['network_klout'] = "civicrm_attentively_member.klout_score as network_klout";
        $query->_element['network_klout'] = 1;
      }

      if (CRM_Utils_Array::value('network_url', $query->_returnProperties)) {
        $query->_select['network_url'] = "civicrm_attentively_member_network.url as network_url";
        $query->_element['network_url'] = 1;
      }
    }
  }

  function where(&$query) {
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

  function whereClauseSingle(&$values, &$query) {
    $fields = $this->getFields();
    list($name, $op, $value, $grouping, $wildcard) = $values;
    switch ($name) {
    case 'network_options':
      $this->networkOptions($values);
      return;

    case 'network_toggle':
    case 'network_operator': // handled above
      return;

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

  function from($name, $mode, $side) {
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

  /**
   * getter for the qill object
   *
   * @return string
   * @access public
   */
  function qill() {
    return (isset($this->_qill)) ? $this->_qill : "";
  }

  static function defaultReturnProperties() {
    $properties = array(
                        'contact_type' => 1,
                        'contact_sub_type' => 1,
                        'sort_name' => 1,
                        'display_name' => 1,
                        'network_name' => 1,
                        'network_url' => 1,
                        );
    return $properties;
  }

  static function buildSearchForm(&$form) {


    $form->addElement('text', 'klout_score_low', ts('From'));
    $form->addElement('text', 'klout_score_high', ts('To'));

    $networks = CRM_Attentively_BAO_Attentively::getNetworkList();
    $form->add('select',
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

    $form->addElement('select',
                      'network_operator',
                      ts('Operator'),
                      array('OR' => ts('OR'),
                            'AND' => ts('AND'),
                            )
                      );

    $options = array(
                     1 => ts('Exclude'),
                     2 => ts('Include by Social Media Account(s)'),
                     );
    $form->addRadio('network_toggle', ts('Social Media Options'), $options);
  }

  function searchAction(&$row, $id) {}

  function setTableDependency(&$tables) {
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
    }
  }

  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {
    if ($type  == 'social') {
      $paneTemplatePathArray['social'] = 'CRM/Attentively/Form/Search/Criteria.tpl';
    }
  }

  function networkOptions($values) {
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

  function &getWhereValues($name, $grouping) {
    $result = NULL;
    foreach ($this->_params as $values) {
      if ($values[0] == $name && $values[3] == $grouping) {
        return $values;
      }
    }

    return $result;
  }
}

