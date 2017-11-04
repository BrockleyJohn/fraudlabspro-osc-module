<?php
/*************************************************************************
  sew_ajax
	an ajax handler which pulls in scripts depending on action
  
	Author: John Ferguson @BrockleyJohn john@sewebsites.net

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/
 
  require('includes/application_top.php');
  include('includes/languages/' . $language . '/sew_ajax.php');

	$action = (isset($_POST['action']) ? $_POST['action'] : $_GET['action']);
//  $action = (isset($_POST['action']) ? $_POST['action'] : '');
  $log_record = date('d-m-Y H:i:s') . '
  processing action      : ' . $action . '
';  

  $result = array('action' => $action);
  
  if (strlen($action) && file_exists('includes/modules/ajax/' . $action  . '.php')) {
		if (file_exists('includes/languages/' . $language . '/sew_ajax/' . $action  . '.php')) include('includes/languages/' . $language . '/sew_ajax/' . $action  . '.php');
		include('includes/modules/ajax/' . $action  . '.php');
	} else {
		$result['status'] = 'fail';
		$result['error'] = sprintf(UNHANDLED_ACTION,$action);
	}

  file_put_contents('fp-api.log',$log_record, FILE_APPEND);
	echo json_encode($result);

  require('includes/application_bottom.php');