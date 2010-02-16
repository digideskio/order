<?php
/*
 * @version $Id: HEADER 1 2009-09-21 14:58 Tsmr $
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

// ----------------------------------------------------------------------
// Original Author of file: NOUH Walid & Benjamin Fontan
// Purpose of file: plugin order v1.1.0 - GLPI 0.72
// ----------------------------------------------------------------------
 */

function plugin_order_install() {
   global $DB;

   include_once(GLPI_ROOT."/plugins/order/inc/profile.class.php");

   if (TableExists("glpi_plugin_order_detail")) {
      if (!FieldExists("glpi_plugin_order_detail","discount")) { // version 1.1.0
      
         $DB->runFile(GLPI_ROOT ."/plugins/order/sql/update-1.1.0.sql");
         
         /* Update en 1.1.0 */

         $query = "SELECT `name` FROM `glpi_dropdown_plugin_order_taxes` ";
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         if ($number) {
            while ($data=$DB->fetch_array($result)) {
               $findme   = ',';
               if(strpos($data["name"], $findme)) {
                  $name= str_replace(',', '.', $data["name"]);
                  $query = "UPDATE `glpi_dropdown_plugin_order_taxes`
                        SET `name` = '".$name."'
                        WHERE `name`= '".$data["name"]."'";
                  $DB->query($query) or die($DB->error());
               }
            }
         }

         if (FieldExists("glpi_plugin_order","numordersupplier")) {
            $query = "SELECT `numordersupplier`,`numbill`,`ID` FROM `glpi_plugin_order` ";
            $result = $DB->query($query);
            $number = $DB->numrows($result);
            if ($number) {
               while ($data=$DB->fetch_array($result)) {
                  $query = "INSERT INTO  `glpi_plugin_order_suppliers`
                        (`ID`, `FK_order`, `numorder`, `numbill`) VALUES
                     (NULL, '".$data["ID"]."', '".$data["numordersupplier"]."', '".$data["numbill"]."') ";
                  $DB->query($query) or die($DB->error());
               }
            }

            if (FieldExists('glpi_plugin_order', 'numordersupplier')) {
               $query = "ALTER TABLE `glpi_plugin_order` DROP `numordersupplier`";
               $DB->query($query) or die($DB->error());
            }

            if (FieldExists('glpi_plugin_order', 'numbill')) {
               $query = "ALTER TABLE `glpi_plugin_order` DROP `numbill`";
               $DB->query($query) or die($DB->error());
            }
         }
      }
      
      //Post 1.1.0
      $DB->runFile(GLPI_ROOT ."/plugins/order/sql/update-1.2.0.sql");
      
      $query = "SELECT `suppliers_id`,`id` FROM `glpi_plugin_order_orders` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data=$DB->fetch_array($result)) {
            $query = "UPDATE `glpi_plugin_order_orders_suppliers`
                  SET `suppliers_id` = '".$data["suppliers_id"]."'
                  WHERE `plugin_order_orders_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
      
      $query = "SELECT `entities_id`,`is_recursive`,`id` FROM `glpi_plugin_order_orders` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data=$DB->fetch_array($result)) {
            $query = "UPDATE `glpi_plugin_order_orders_suppliers`
                  SET `entities_id` = '".$data["entities_id"]."',`is_recursive` = '".$data["is_recursive"]."'
                  WHERE `plugin_order_orders_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
      
      $query = "SELECT `entities_id`,`is_recursive`,`id` FROM `glpi_plugin_order_orders` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data=$DB->fetch_array($result)) {
            $query = "UPDATE `glpi_plugin_order_orders_suppliers`
                  SET `entities_id` = '".$data["entities_id"]."',`is_recursive` = '".$data["is_recursive"]."'
                  WHERE `plugin_order_orders_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
      
      $query = "SELECT `entities_id`,`is_recursive`,`id` FROM `glpi_plugin_order_references` ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data=$DB->fetch_array($result)) {
            $query = "UPDATE `glpi_plugin_order_references_suppliers`
                  SET `entities_id` = '".$data["entities_id"]."',`is_recursive` = '".$data["is_recursive"]."'
                  WHERE `plugin_order_references_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
      
      $query_="SELECT *
            FROM `glpi_plugin_order_profiles` ";
      $result_=$DB->query($query_);
      if ($DB->numrows($result_)>0) {

         while ($data=$DB->fetch_array($result_)) {
            $query="UPDATE `glpi_plugin_order_profiles`
                  SET `profiles_id` = '".$data["id"]."'
                  WHERE `id` = '".$data["id"]."';";
            $result=$DB->query($query);

         }
      }
      
      $query="ALTER TABLE `glpi_plugin_order_profiles`
               DROP `name` ;";
      $result=$DB->query($query);

      Plugin::migrateItemType(
         array(3150=>'PluginOrderOrder',
               3151=>'PluginOrderReference',
               3152=>'PluginOrderReference_Supplier',
               3153=>'PluginOrderBudget',
               3154=>'PluginOrderOrder_Supplier',
               3155=>'PluginOrderReception'),
         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
         array("glpi_plugin_order_orders_items", "glpi_plugin_order_references"));

   }
   if (!TableExists("glpi_plugin_order_orders")) { // not installed
      $DB->runFile(GLPI_ROOT ."/plugins/order/sql/empty-1.2.0.sql");
   }

   PluginOrderProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_order_uninstall() {
	global $DB;

	/* drop all the plugin tables */
	$tables = array (
		"glpi_plugin_order_orders",
		"glpi_plugin_order_orders_items",
		"glpi_plugin_order_profiles",
		"glpi_plugin_order_ordertaxes",
		"glpi_plugin_order_orderpayments",
		"glpi_plugin_order_references",
		"glpi_plugin_order_references_suppliers",
		"glpi_plugin_order_configs",
		"glpi_plugin_order_budgets",
      "glpi_plugin_order_orders_suppliers",
      "glpi_plugin_order_others",
      "glpi_plugin_order_othertypes",
      "glpi_plugin_order_deliverystates"
	);

	foreach ($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old tables
	$tables = array (
		"glpi_plugin_order",
		"glpi_plugin_order_detail",
		"glpi_plugin_order_device",
		"glpi_plugin_order_profiles",
		"glpi_dropdown_plugin_order_status",
		"glpi_dropdown_plugin_order_taxes",
		"glpi_dropdown_plugin_order_payment",
		"glpi_plugin_order_references",
		"glpi_plugin_order_references_manufacturers",
		"glpi_plugin_order_config",
		"glpi_plugin_order_budgets",
      "glpi_plugin_order_suppliers"
	);

	foreach ($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
	$in = "IN (" . implode(',', array (
		"'PluginOrderOrder'",
		"'PluginOrderReference'",
		"'PluginOrderReference_Supplier'",
		"'PluginOrderBudget'"
	)) . ")";

	$tables = array (
      "glpi_displaypreferences",
		"glpi_documents_items",
		"glpi_bookmarks",
		"glpi_logs"
	);

	foreach ($tables as $table) {
		$query = "DELETE FROM `$table` WHERE (`itemtype` " . $in." ) ";
		$DB->query($query);
	}

	return true;
}

/* define dropdown tables to be manage in GLPI : */
function plugin_order_getDropdown() {
	/* table => name */
	global $LANG;

	$plugin = new Plugin();
	if ($plugin->isActivated("order"))
		return array (
			'PluginOrderOrderTaxe' => $LANG['plugin_order'][25],
			'PluginOrderOrderPayment' => $LANG['plugin_order'][32],
			'PluginOrderOtherType' => $LANG['plugin_order'][9],
			'PluginOrderDeliveryState' => $LANG['plugin_order']['status'][3]
		);
	else
		return array ();
}

/* define dropdown relations */
function plugin_order_getDatabaseRelations() {
	$plugin = new Plugin();
	if ($plugin->isActivated("order"))
		return array (
			"glpi_plugin_order_orderpayments" => array (
				"glpi_plugin_order_orders" => "plugin_order_orderpayments_id"
			),
			"glpi_plugin_order_ordertaxes" => array (
				"glpi_plugin_order_orders" => "plugin_order_ordertaxes_id"
			),
			"glpi_plugin_order_deliverystates" => array (
				"glpi_plugin_order_orders_items" => "plugin_order_deliverystates_id"
			),
			"glpi_plugin_order_orders" => array (
				"glpi_plugin_order_orders_items" => "plugin_order_orders_id",
				"glpi_plugin_order_orders_suppliers" => "plugin_order_orders_id"
			),
			"glpi_plugin_order_references" => array (
				"glpi_plugin_order_orders_items" => "plugin_order_references_id",
				"glpi_plugin_order_references_suppliers" => "plugin_order_references_id"
			),
			"glpi_entities" => array (
				"glpi_plugin_order_orders" => "entities_id",
				"glpi_plugin_order_references" => "entities_id",
				"glpi_plugin_order_others" => "entities_id"
			),
			"glpi_budgets" => array (
				"glpi_plugin_order_orders" => "budgets_id"
			),
			"glpi_plugin_order_othertypes" => array (
				"glpi_plugin_order_others" => "othertypes_id"
			),
			"glpi_suppliers" => array (
				"glpi_plugin_order_orders" => "suppliers_id",
				"glpi_plugin_order_orders_suppliers" => "suppliers_id",
				"glpi_plugin_order_references_suppliers" => "suppliers_id"
			),
			"glpi_manufacturers" => array (
				"glpi_plugin_order_references" => "manufacturers_id"
			),
			"glpi_contacts" => array (
				"glpi_plugin_order_orders" => "contacts_id"
			),
			"glpi_locations" => array (
				"glpi_plugin_order_orders" => "locations_id"
			),
			"glpi_profiles" => array (
				"glpi_plugin_order_profiles" => "profiles_id"
			)
		);
	else
		return array ();
}

////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_order_getAddSearchOptions($itemtype) {
   global $LANG;

   $sopt = array();
   if (plugin_order_haveRight("order","r")) {
      if (in_array($itemtype, PluginOrderOrder_Item::getClasses(true))) {
         $sopt[3160]['table']         = 'glpi_plugin_order_orders';
         $sopt[3160]['field']         = 'name';
         $sopt[3160]['linkfield']     = '';
         $sopt[3160]['name']          = $LANG['plugin_order'][39];
         $sopt[3160]['forcegroupby']  = true;
         $sopt[3160]['datatype']      = 'itemlink';
         $sopt[3160]['itemlink_type'] = 'PluginOrderOrder';

         $sopt[3161]['table']        = 'glpi_plugin_order_orders';
         $sopt[3161]['field']        = 'num_order';
         $sopt[3161]['linkfield']    = '';
         $sopt[3161]['name']         = $LANG['plugin_order'][0];
         $sopt[3161]['forcegroupby'] =  true;
         $sopt[3161]['datatype']      = 'itemlink';
         $sopt[3161]['itemlink_type'] = 'PluginOrderOrder';
      }
   }
   return $sopt;
}

function plugin_order_forceGroupBy($type){

	return true;
	switch ($type){
		case 'PluginOrderOrder':
			return true;
			break;

	}
	return false;
}

function plugin_order_addSelect($type, $ID, $num) {

	$searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

	if ($table == "glpi_plugin_order_references" && $num!=0)
		return "`$table`.`itemtype`, `$table`.`$field` AS `ITEM_$num`, ";
	else
		return "";

}

function plugin_order_addLeftJoin($type,$ref_table,$new_table,$linkfield,
                                       &$already_link_tables) {
   
	switch ($new_table){
		case "glpi_plugin_order_orders" : // From items
			$out= " LEFT JOIN `glpi_plugin_order_orders_items` ON (`$ref_table`.`id` = `glpi_plugin_order_orders_items`.`items_id` AND `glpi_plugin_order_orders_items`.`itemtype` = '$type') ";
			$out.= " LEFT JOIN `glpi_plugin_order_orders` ON (`glpi_plugin_order_orders`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_orders_id`) ";
			return $out;
			break;
      case "glpi_budgets" : // From order list
			$out= " LEFT JOIN `glpi_budgets` ON (`glpi_plugin_order_orders`.`budgets_id` = `glpi_budgets`.`id`) ";
			return $out;
			break;
		case "glpi_contacts" : // From order list
			$out= " LEFT JOIN `glpi_contacts` ON (`glpi_plugin_order_orders`.`contacts_id` = `glpi_contacts`.`id`) ";
			return $out;
			break;
	}

	return "";
}
/* display custom fields in the search */
function plugin_order_giveItem($type, $ID, $data, $num) {
	global $CFG_GLPI, $LANG;

	$searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   $PluginOrderReference = new PluginOrderReference;
   $PluginOrderOrder = new PluginOrderOrder;

	switch ($table . '.' . $field) {
		/* display associated items with order */
		case "glpi_plugin_order_orders.states_id" :
			return $PluginOrderOrder->getDropdownStatus($data["ITEM_" . $num]);
         break;
		case "glpi_plugin_order_references.types_id" :
         if (file_exists(GLPI_ROOT."/inc/".strtolower($data["itemtype"])."type.class.php"))
            return Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Type"), $data["ITEM_" . $num]);
         else
            return " ";
         break;
		case "glpi_plugin_order_references.models_id" :
         if (file_exists(GLPI_ROOT."/inc/".strtolower($data["itemtype"])."model.class.php"))
            return Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Model"), $data["ITEM_" . $num]);
         else
            return " ";
         break;
		case "glpi_plugin_order_references.templates_id" :
			if (!$data["ITEM_" . $num])
				return " ";
			else
				return $PluginOrderReference->getTemplateName($data["itemtype"], $data["ITEM_" . $num]);
         break;
	}
	return "";
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_order_MassiveActions($type) {
	global $LANG;
	
	switch ($type) {
		case 'PluginOrderOrder' :
			return array (
				// GLPI core one
				"plugin_order_transfert" => $LANG['buttons'][48],

				
			);
			break;
	}
	return array ();
}

function plugin_order_MassiveActionsDisplay($options=array()) {
	global $LANG;
	
	switch ($options['itemtype']) {
		case 'PluginOrderOrder' :
			switch ($options['action']) {
				// No case for add_document : use GLPI core one
				case "plugin_order_transfert" :
					Dropdown::show('Entity');
					echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . $LANG['buttons'][2] . "\" >";
					break;
			}
			break;
	}
	return "";
}

function plugin_order_MassiveActionsProcess($data) {
	global $LANG, $DB;

	switch ($data['action']) {
		case "plugin_order_transfert" :
			if ($data['itemtype'] == 'PluginOrderOrder') {
				foreach ($data["item"] as $key => $val) {
					if ($val == 1) {
						$PluginOrderOrder = new PluginOrderOrder();
						$PluginOrderOrder->transfer($key,$data['entities_id']); 
					}
				}
			}
			break;
	}
}

/* hook done on purge item case */
function plugin_item_purge_order($item) {

	$temp = new PluginOrderOrder_Item();
   $temp->clean(array('itemtype' => get_class($item),
                         'items_id' => $item->getField('id')));
                         
   return true;
}

// Define headings added by the plugin
function plugin_get_headings_order($item,$withtemplate) {
   global $LANG;

   $type = get_Class($item);
   if ($type == 'Profile') {
      if ($item->getField('id') && $item->getField('interface')!='helpdesk') {
         return array(1 => $LANG['plugin_order']['title'][1]);
      }
   } else if (in_array($type, PluginOrderOrder_Item::getClasses(true)) || $type == 'Supplier' || $type == 'Budget') {
      if ($item->getField('id') && !$withtemplate) {
         // Non template case
         return array(1 => $LANG['plugin_order']['title'][1]);
      }
   } else if ($type == 'Notification') {
      if ($item->getField('id')) {
         return array(1 => $LANG['plugin_order']['title'][1]);
      }
   }
   return false;
}

// Define headings actions added by the plugin
function plugin_headings_actions_order($item) {

   if (in_array(get_class($item),PluginOrderOrder_Item::getClasses(true))||
		get_class($item)=='Profile' || 
		get_class($item)=='Supplier' || 
		get_class($item)=='Budget' || 
		get_class($item)=='Notification') {
		return array(
			1 => "plugin_headings_order",
		);
	}
	return false;
}

/* action heading */
function plugin_headings_order($item) {
	global $CFG_GLPI;
  
   $PluginOrderProfile=new PluginOrderProfile();
   $PluginOrderMailingSetting = new PluginOrderMailingSetting();
   $PluginOrderOrder_Item = new PluginOrderOrder_Item();
   $PluginOrderReference = new PluginOrderReference();
   $PluginOrderBudget = new PluginOrderBudget();
   $PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();
   $PluginOrderSurveySupplier = new PluginOrderSurveySupplier();
   
	switch (get_class($item)) {
      case 'Profile' :
         if (!$PluginOrderProfile->getFromDBByProfile($item->getField('id')))
            $PluginOrderProfile->createAccess($item->getField('id'));
         $PluginOrderProfile->showForm($item->getField('id'), array('target' => $CFG_GLPI["root_doc"]."/plugins/order/front/profile.form.php"));
         break;
      case 'Notification' :
         $PluginOrderMailingSetting->showFormMailing($CFG_GLPI["root_doc"]."/plugins/order/front/mailing.setting.php");
         break;
      case 'Supplier' :
         $PluginOrderReference->showReferencesFromSupplier($item->getField('id'));
         $PluginOrderOrder_Supplier->showDeliveries($item->getField('id'));
         $PluginOrderSurveySupplier->showGlobalNotation($item->getField('id'));
         break;
      case 'Budget' :
         $PluginOrderBudget->getAllOrdersByBudget($_POST["id"]);
         break;
      default :
         if (in_array(get_class($item), PluginOrderOrder_Item::getClasses(true))) {
            $PluginOrderOrder_Item->showPluginFromItems(get_class($item),$item->getField('id'));
         }
         break;
   }
}

?>