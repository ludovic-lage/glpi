<?php
/*
 * @version $Id: document.class.php 9112 2009-10-13 20:17:16Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

/// Location class
class Location extends CommonTreeDropdown {

   // From CommonDBTM
   public $table = 'glpi_locations';
   public $type = 'Location';

   function canCreate() {
      return haveRight('entity_dropdown','w');
   }

   function canView() {
      return haveRight('entity_dropdown','r');
   }

   function getAdditionalFields() {
      global $LANG;

      return array(array('name'  => getForeignKeyFieldForTable($this->table),
                         'label' => $LANG['setup'][75],
                         'type'  => 'parent',
                         'list'  => false),
                   array('name'  => 'building',
                         'label' => $LANG['setup'][99],
                         'type'  => 'text',
                         'list'  => true),
                   array('name'  => 'room',
                         'label' => $LANG['setup'][100],
                         'type'  => 'text',
                         'list'  => true));
   }

   static function getTypeName() {
      global $LANG;

      return $LANG['common'][15];
   }

   /**
    * Get search function for the class
    *
    * @return array of search option
    */
   function getSearchOptions() {
      global $LANG;

      $tab = parent::getSearchOptions();

      $tab[11]['table']         = $this->table;
      $tab[11]['field']         = 'building';
      $tab[11]['linkfield']     = 'building';
      $tab[11]['name']          = $LANG['setup'][99];
      $tab[11]['datatype']      = 'text';

      $tab[12]['table']         = $this->table;
      $tab[12]['field']         = 'room';
      $tab[12]['linkfield']     = 'room';
      $tab[12]['name']          = $LANG['setup'][100];
      $tab[12]['datatype']      = 'text';

      return $tab;
   }

   function defineTabs($ID,$withtemplate) {
      global $LANG;

      $ong=parent::defineTabs($ID,$withtemplate);
      if ($ID>0) {
         $ong[2] = $LANG['networking'][51];
      }

      return $ong;
   }

   /**
    * Display content of Tab
    *
    * @param $ID of the item
    * @param $tab number of the tab
    *
    * @return true if handled (for class stack)
    */
   function showTabContent ($ID, $tab) {
      if ($ID>0 && !parent::showTabContent ($ID, $tab)) {
         switch ($tab) {
            case 2 :
               $this->showNetpoints($ID);
               return true;
            case -1 :
               $this->showNetpoints($ID);
               return false;
         }
      }
      return false;
   }

   // TODO Move this to Netpoint class ?
   /**
    * Print the HTML array of the Netpoint associated to a Location
    *
    *@param $ID of the Location
    *
    *@return Nothing (display)
    *
    **/
    function showNetpoints($ID) {
      global $DB, $CFG_GLPI, $LANG;

      $netpoint = new Netpoint();
      $this->check($ID, 'r');
      $canedit = $this->can($ID, 'w');

      if (isset($_REQUEST["start"])) {
         $start = $_REQUEST["start"];
      } else {
         $start = 0;
      }
      $number = countElementsInTable('`glpi_netpoints`', "`locations_id`='$ID'");

      echo "<br><div class='center'>";

      if ($number < 1) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan>".$LANG['networking'][51]." - ".$LANG['search'][15]."</th></tr>";
      } else {
         printAjaxPager($this->getTreeLink()." - ".$LANG['networking'][51],$start,$number);

         if ($canedit) {
            echo "<form method='post' name='massiveaction_form' id='massiveaction_form' action='".
                   $CFG_GLPI["root_doc"]."/front/massiveaction.php'>";
         }
         echo "<table class='tab_cadre_fixe'><tr>";
         if ($canedit) {
            echo "<th width='10'>&nbsp;</th>";
         }
         echo "<th>".$LANG['common'][16]."</th>"; // Name
         echo "<th>".$LANG['common'][25]."</th>"; // Comment
         echo "</tr>\n";

         $crit = array('locations_id' => $ID,
                       'ORDER'        => 'name',
                       'START'        => $start,
                       'LIMIT'        => $_SESSION['glpilist_limit']);

         initNavigateListItems('Netpoint', $this->getTypeName()."= ".$this->fields['name']);
         foreach ($DB->request('glpi_netpoints', $crit) as $data) {
            addToNavigateListItems('Netpoint',$data["id"]);
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<input type='checkbox' name='item[".$data["id"]."]' value='1'>";
            }
            echo "<td><a href='".$netpoint->getFormURL();
            echo '?id='.$data['id']."'>".$data['name']."</a></td>";
            echo "<td>".$data['comment']."</td>";
            echo "</tr>\n";
         }
         echo "</table>\n";
         if ($canedit) {
            openArrowMassive("massiveaction_form", true);
            echo "<input type='hidden' name='itemtype' value='Netpoint'>";
            echo "<input type='hidden' name='action' value='delete'>";
            closeArrowMassive('massiveaction', $LANG['buttons'][6]);

            echo "</form>\n";
         }
      }
      if ($canedit) {
         // Minimal form for quick input.
         echo "<form action='".$netpoint->getFormURL()."' method='post'>";
         echo "<br><table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2 center'><td class='b'>".$LANG['common'][87]."</td>";
         echo "<td>".$LANG['common'][16]."&nbsp;: ";
         autocompletionTextField("name",$this->table,"name");
         echo "<input type='hidden' name='entities_id' value='".$_SESSION['glpiactive_entity']."'>";
         echo "<input type='hidden' name='locations_id' value='$ID'></td>";
         echo "<td><input type='submit' name='add' value=\"".
              $LANG['buttons'][8]."\" class='submit'></td>";
         echo "</tr>\n";
         echo "</table></form>\n";

         // Minimal form for massive input.
         echo "<form action='".$netpoint->getFormURL()."' method='post'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2 center'><td class='b'>".$LANG['common'][87]."</td>";
         echo "<td>".$LANG['common'][16]."&nbsp;: ";
         echo "<input type='text' maxlength='100' size='10' name='_before'>";
         dropdownInteger('_from', 0, 0, 400);
         echo "-->";
         dropdownInteger('_to', 0, 0, 400);
         echo "<input type='text' maxlength='100' size='10' name='_after'><br>";
         echo "<input type='hidden' name='entities_id' value='".$_SESSION['glpiactive_entity']."'>";
         echo "<input type='hidden' name='locations_id' value='$ID'></td>";
         echo "<input type='hidden' name='_method' value='addMulti'></td>";
         echo "<td><input type='submit' name='execute' value=\"".
              $LANG['buttons'][8]."\" class='submit'></td>";
         echo "</tr>\n";
         echo "</table></form>\n";
      }
      echo "</div>\n";
   }
}

?>