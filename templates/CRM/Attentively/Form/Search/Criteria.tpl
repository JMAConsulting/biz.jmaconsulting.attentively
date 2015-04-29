{*
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
*}

<table class="form-layout">	
<tr>
  <td>
    <label><b>Klout Score</b></label>
  </td>
</tr>
<tr>
  <td>
    <label>{$form.network_klout_score_low.label}</label> <br />
    {$form.network_klout_score_low.html}
  </td>
  <td>
    {$form.network_klout_score_high.label}<br />
    {$form.network_klout_score_high.html}
   </td>
   <td>
      <table class="form-layout-compressed">
      <tr>
        <td>
            {$form.network_toggle.html}
        </td>
      </tr>
      <tr>
        <td>
            {$form.network_options.html}
        </td>
        <td style="vertical-align:middle">
            <div id="network-operator-wrapper">{$form.network_operator.html}</div>
        </td>
      </tr>
      </table>
      {literal}
        <script type="text/javascript">
          cj("select#network_options").crmasmSelect();
          cj("select#network_options").change(function() {
            var items = cj(this).siblings('ul.crmasmList').find('li').length;
            if (items > 1) {
              cj('#network-operator-wrapper').show();
            } else {
              cj('#network-operator-wrapper').hide();
            }
          }).change();
        </script>
      {/literal}
    </td>
</tr>
</table>
