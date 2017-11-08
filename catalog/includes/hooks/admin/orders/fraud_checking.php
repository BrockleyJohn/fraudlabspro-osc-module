<?php
/*
  $Id$

  add fraud checking details to admin / orders.php using hooks
  incorporates fraudlabs pro and other stuff
	
	author: John Ferguson @BrockleyJohn john@sewebsites.net

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

	// 2.3.4BS Edge compatibility
	if (!defined('DIR_WS_CLASSES')) define('DIR_WS_CLASSES','includes/classes/');
	if (!defined('DIR_WS_IMAGES')) define('DIR_WS_IMAGES','images/');
	if (!defined('FILENAME_ORDERS')) define('FILENAME_ORDERS','orders.php');
	
	class hook_admin_orders_fraud_checking {
	
		function listen_orderAction() {
			if ( !class_exists('hook_admin_orders_fraud_checking_action') ) {
				include(DIR_FS_CATALOG . 'includes/modules/hooks/admin/orders_fraud_checking_action.php');
			}
			
			$hook = new hook_admin_orders_fraud_checking_action();
			
			return $hook->execute();
		}
		
		function listen_orderTab() {
			if ( !class_exists('hook_admin_orders_fraud_checking_tab') ) {
				include(DIR_FS_CATALOG . 'includes/modules/hooks/admin/orders_fraud_checking_tab.php');
			}
			
			$hook = new hook_admin_orders_fraud_checking_tab();
			
			return $hook->execute();
		}
	}
