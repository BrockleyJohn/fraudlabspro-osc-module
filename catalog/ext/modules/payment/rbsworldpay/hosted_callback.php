<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require ('includes/application_top.php');

  if ( !defined('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS') || (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_STATUS  != 'True') ) {
    exit;
  }

  include('includes/languages/' . basename($_POST['M_lang']) . '/modules/payment/rbsworldpay_hosted.php');
  include('includes/modules/payment/rbsworldpay_hosted.php');

  $rbsworldpay_hosted = new rbsworldpay_hosted();

  $error = false;
  $cancelled = false;

  if ( !isset($_GET['installation']) || ($_GET['installation'] != MODULE_PAYMENT_RBSWORLDPAY_HOSTED_INSTALLATION_ID) ) {
    $error = true;
  } elseif ( !isset($_POST['installation']) || ($_POST['installation'] != MODULE_PAYMENT_RBSWORLDPAY_HOSTED_INSTALLATION_ID) ) {
    $error = true;
  } elseif ( tep_not_null(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CALLBACK_PASSWORD) && (!isset($_POST['callbackPW']) || ($_POST['callbackPW'] != MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CALLBACK_PASSWORD)) ) {
    $error = true;
  } elseif ( !isset($_POST['transStatus']) || ($_POST['transStatus'] != 'Y') ) {
    if ($_POST['transStatus'] == 'C') {
		  $cancelled = true;
    } else {
      $error = true;
    }
  } elseif ( !isset($_POST['M_hash']) || !isset($_POST['M_sid']) || !isset($_POST['M_cid']) || !isset($_POST['cartId']) || !isset($_POST['M_lang']) || !isset($_POST['amount']) || ($_POST['M_hash'] != md5($_POST['M_sid'] . $_POST['M_cid'] . $_POST['cartId'] . $_POST['M_lang'] . number_format($_POST['amount'], 2) . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_MD5_PASSWORD)) ) {
    $error = true;
  }

  if ( $error == false ) {
    $order_query = tep_db_query("select orders_id, orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$_POST['cartId'] . "' and customers_id = '" . (int)$_POST['M_cid'] . "'");

    if (!tep_db_num_rows($order_query)) {
      $error = true;
    }
  }

  if ( $error == true ) {
    $rbsworldpay_hosted->sendDebugEmail();

    exit;
  }
	
if ($cancelled == false) {
// fraudlabspro 
// populate settings from elsewhere...
	$customer_id = (int)$_POST['M_cid'];
	$payment = 'rbsworldpay_hosted';
	$order_id = (int)$_POST['cartId'];
	$session_id = $_POST['M_sid'];
	
  $log_record = date('d-m-Y H:i:s') . '
';  
  $log_record .= '
  API Key                : ' . MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY . '
  customer_id registered : ' . (tep_session_is_registered('customer_id')? 'yes' : 'no') . '
  customer_id            : ' . $customer_id . '
  payment registered     : ' . (tep_session_is_registered('payment')? 'yes' : 'no') . '
  payment                : ' . $payment . '
  cart->cartID set       : ' . (isset($cart->cartID)? 'yes' : 'no') . '
  cart->cartID           : ' . $cart->cartID . '
  cartID  registered     : ' . (tep_session_is_registered('cartID')? 'yes' : 'no') . '
  cartID                 : ' . $cartID . '
  orderID                 : ' . $order_id . '
';
$conf_id = 0; $conf_array = array();
$confirm_query = tep_db_query('select * from sew_order_confirm where order_id = ' . (int)$order_id . ' order by confirm_time desc');
if (tep_db_num_rows($confirm_query)) {
	$conf_rec = tep_db_fetch_array($confirm_query);
	if ($conf_rec['session_id'] == $_POST['M_sid'] && $conf_rec['payment_method'] == $payment && $conf_rec['customer_id'] == $customer_id) {
		$log_record .= '  matching confirm record - total: ' . $conf_rec['order_total'] . ' - authorised amount: ' . $_POST['authCost'] . "\n";
		$ip = $conf_rec['ip_address'];
		$cookie = $conf_rec['device_checksum'];
		$conf_id = $conf_rec['confirm_id'];
	} else {
		$log_record .= '  confirm record - bad match with record id ' . $conf_rec['confirm_id'] . ' : ' . "\n    confirmation " . $conf_rec['session_id'] . ' - callback: ' . $_POST['M_sid'] . "\n    confirmation " . $conf_rec['payment_method'] . ' - callback: ' . $payment . "\n    confirmation " . $conf_rec['customer_id'] . ' - callback: ' . $customer_id . "\n";
	}
} else {
	$log_record .= '
  CONFIRM RECORD NOT FOUND
';
}
file_put_contents('fp-api.log',$log_record, FILE_APPEND);
$log_record = '';

if ( defined('MODULE_HEADER_TAGS_FRAUDLABSPRO_STATUS') && MODULE_HEADER_TAGS_FRAUDLABSPRO_STATUS == 'True' && defined('MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY') && strlen(MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY) ) {
  
function flp_get_country_iso_2($country_name) {
	$query = tep_db_query("select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_name = '" . $country_name . "'");
	$row = tep_db_fetch_array($query);
	return $row['countries_iso_code_2'];
}
	include_once('includes/classes/order.php');
	$order = new order((int)$_POST['cartId']); // 
	
	$flpData['first_name'] = $order->customer['name'];
	if (isset($_POST['name'])) {
		$conf_array['cardholder'] = $_POST['name'];
		if ($_POST['name'] != $order->customer['name']) {
			$conf_array['name_mismatch'] = 1;
		}
	}
	
	$flpData['bill_addr'] = $order->billing['street_address'] . ( strlen($order->billing['suburb']) ? ', ' . $order->billing['suburb'] : '');
	$flpData['bill_city'] = $order->billing['city'];
	$flpData['bill_state'] = $order->billing['state'];
	$flpData['bill_zip_code'] = $order->billing['postcode'];
	$flpData['bill_country'] = flp_get_country_iso_2($order->billing['country']['title']);
	$flpData['user_phone'] = $order->customer['telephone'];
	
	$flpData['ship_addr'] = $order->delivery['street_address'] . ( strlen($order->delivery['suburb']) ? ', ' . $order->delivery['suburb'] : '');
	$flpData['ship_city'] = $order->delivery['city'];
	$flpData['ship_state'] = $order->delivery['state'];
	$flpData['ship_zip_code'] = $order->delivery['postcode'];
	$flpData['ship_country'] = flp_get_country_iso_2($order->delivery['country']['title']);
	
	$flpData['email'] = $order->customer['email_address'];
	$flpData['email_domain'] = substr($order->customer['email_address'],strpos ($order->customer['email_address'], '@') + 1);
	//$flpData['email_hash'] = hash_string($order->customer['email_address']);
	
	$flpData['bin_no'] = substr(str_replace(array('-',' '), '', $order->info['cc_number']), 0, 6);
	
	if (isset($_POST['AVS']) && is_numeric($_POST['AVS']) && strlen($_POST['AVS']) == 4) {
		$avs_encode = array(
			'A' => array('4','2'),
			'B' => array('1','2'),
			'C' => array('4','4'),
			'D' => array('2','2'),
			'E' => array('0','0'),
			'G' => array('0','0'),
			'I' => array('1','1'),
			'M' => array('2','2'),
			'N' => array('4','4'),
			'P' => array('2','1'),
			'R' => array('0','0'),
			'S' => array('0','0'),
			'U' => array('0','0'),
			'W' => array('2','4'),
			'X' => array('2','2'),
			'Y' => array('2','2'),
			'Z' => array('2','4'),
		);
		
		foreach ($avs_encode as $code => $values) {
			if ($_POST['AVS'][1] == $values[0] && $_POST['AVS'][2] == $values[1]) {
				$flpData['avs_result'] = $code;
				break;
			}
		}
		
		switch ($_POST['AVS'][0]) {
			case '2' :
				$flpData['cvv_result'] = 'M';
				break;
			case '4' :
			case '8' :
				$flpData['cvv_result'] = 'N';
				break;
			case '1' :
				$flpData['cvv_result'] = 'P';
				break;
			case '0' :
				$flpData['cvv_result'] = 'S';
				break;
		}
		
		$conf_array['country_match'] = $_POST['AVS'][3];

	}

	$flpData['user_order_id'] = $order_id;
	$flpData['amount'] = filter_var($order->info['total'],FILTER_SANITIZE_NUMBER_FLOAT);
	$flpData['quantity'] = sizeof($order->products);
	$flpData['currency'] = $order->info['currency'];

	$flpData['payment_mode'] = $payment; // payment class should be in session variable
	
	$flpData['ip'] = $ip;

	if (strlen($cookie)) $flpData['flp_checksum'] = $cookie; // as long as agent script is loaded!

	$flpData['key'] = MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY;
	$flpData['format'] = 'json';
	$flpData['last_name'] = " ";
	
	$flpData['source'] = 'oscommerce';
	$flpData['source_version'] = '1.3.1';
	
	$queries = '';
	foreach($flpData as $key=>$value){
		$queries .= $key . '=' . urlencode($value) . '&';
	}
	
	$log_record .= '  call params : ' . print_r($flpData,true) .'
';
	$url = 'https://api.fraudlabspro.com/v1/order/screen?' . rtrim($queries, '&');
	
	for($i=0; $i<3; $i++){
		$response = @file_get_contents($url);
	    $log_record .= '  response : ' . $response .'
';
	
		if(is_null($json = json_decode($response)) === FALSE){
			$sql_data_array = array(
				'is_country_match' => $json->is_country_match,
				'is_high_risk_country' => $json->is_high_risk_country,
				'distance_in_km' => $json->distance_in_km,
				'distance_in_mile' => $json->distance_in_mile,
				'ip_address' => $flpData['ip'],
				'ip_country' => $json->ip_country,
				'ip_continent' => $json->ip_continent,
				'ip_region' => $json->ip_region,
				'ip_city' => $json->ip_city,
				'ip_latitude' => $json->ip_latitude,
				'ip_longitude' => $json->ip_longitude,
				'ip_timezone' => $json->ip_timezone,
				'ip_elevation' => $json->ip_elevation,
				'ip_domain' => $json->ip_domain,
				'ip_mobile_mnc' => $json->ip_mobile_mnc,
				'ip_mobile_mcc' => $json->ip_mobile_mcc,
				'ip_mobile_brand' => $json->ip_mobile_brand,
				'ip_netspeed' => $json->ip_netspeed,
				'ip_isp_name' => $json->ip_isp_name,
				'ip_usage_type' => $json->ip_usage_type,
				'is_free_email' => $json->is_free_email,
				'is_new_domain_name' => $json->is_new_domain_name,
				'is_proxy_ip_address' => $json->is_proxy_ip_address,
				'is_bin_found' => $json->is_bin_found,
				'is_bin_country_match' => $json->is_bin_country_match,
				'is_bin_name_match' => $json->is_bin_name_match,
				'is_bin_phone_match' => $json->is_bin_phone_match,
				'is_bin_prepaid' => $json->is_bin_prepaid,
				'is_address_ship_forward' => $json->is_address_ship_forward,
				'is_bill_ship_city_match' => $json->is_bill_ship_city_match,
				'is_bill_ship_state_match' => $json->is_bill_ship_state_match,
				'is_bill_ship_country_match' => $json->is_bill_ship_country_match,
				'is_bill_ship_postal_match' => $json->is_bill_ship_postal_match,
				'is_ip_blacklist' => $json->is_ip_blacklist,
				'is_email_blacklist' => $json->is_email_blacklist,
				'is_credit_card_blacklist' => $json->is_credit_card_blacklist,
				'is_device_blacklist' => $json->is_device_blacklist,
				'is_user_blacklist' => $json->is_user_blacklist,
				'user_order_id' => $json->user_order_id,
				'user_order_memo' => $json->user_order_memo,
				'fraudlabspro_score' => $json->fraudlabspro_score,
				'fraudlabspro_distribution' => $json->fraudlabspro_distribution,
				'fraudlabspro_status' => $json->fraudlabspro_status,
				'fraudlabspro_id' => $json->fraudlabspro_id,
				'fraudlabspro_version' => $json->fraudlabspro_version,
				'fraudlabspro_error_code' => $json->fraudlabspro_error_code,
				'fraudlabspro_message' => $json->fraudlabspro_message,
				'fraudlabspro_credits' => $json->fraudlabspro_credits,
				'order_id' => $order_id,
//				'api_key' => $flpData['key']
			);
	
			tep_db_perform('fraudlabs_pro', $sql_data_array);
			
            $flpro_id = tep_db_insert_id();
			
			break;
		}
	}
	if ($conf_id) {
		if ($flpro_id) {
			$conf_array['flp_id'] = $flpro_id;
		}
		if (count($conf_array)) {
			tep_db_perform('sew_order_confirm', $conf_array, 'update', 'confirm_id = \'' . (int)$conf_id . '\'');
		}
	}
	
	file_put_contents('fp-api.log',$log_record, FILE_APPEND);

}
  
  $rbsworldpay_hosted->sendDebugEmail();

  $order = tep_db_fetch_array($order_query);

  if ($order['orders_status'] == MODULE_PAYMENT_RBSWORLDPAY_HOSTED_PREPARE_ORDER_STATUS_ID) {
    $order_status_id = (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID);

    tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status_id . "', last_modified = now() where orders_id = '" . (int)$order['orders_id'] . "'");

    $sql_data_array = array('orders_id' => $order['orders_id'],
                            'orders_status_id' => $order_status_id,
                            'date_added' => 'now()',
                            'customer_notified' => '0',
                            'comments' => '');

    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		
		$order['orders_status'] = $order_status_id;
  }
	
	$checked = false; $OK = true;

  $trans_result = 'WorldPay: Transaction Verified (Callback)' . "\n" .
                  'Transaction ID: ' . $_POST['transId'];

  if (MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TESTMODE == 'True') {
    $trans_result .= "\n" . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_WARNING_DEMO_MODE;
  }
	
	if (isset($_POST['wafMerchMessage'])) {
	  $checked = true;
		if ($_POST['wafMerchMessage'] == 'waf.warning') {
			$trans_result .= "\n\n" . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_WAF . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_WAF_WARNING ."\n";
			$OK = false;
		} elseif ($_POST['wafMerchMessage'] == 'waf.caution') {
			$trans_result .= "\n\n" . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_WAF . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_WAF_CAUTION ."\n";
			$OK = false;
		}
	}
	
	if (isset($_POST['AVS']) && is_numeric($_POST['AVS']) && strlen($_POST['AVS']) == 4) {
	  $checked = true;
    $valid_result = array(0,1,2,4,8);
		$avs = array(MODULE_PAYMENT_RBSWORLDPAY_HOSTED_AVS_CVV, MODULE_PAYMENT_RBSWORLDPAY_HOSTED_AVS_POSTCODE, MODULE_PAYMENT_RBSWORLDPAY_HOSTED_AVS_ADDRESS, MODULE_PAYMENT_RBSWORLDPAY_HOSTED_AVS_COUNTRY);
		for ($i = 0, $n = count($avs); $i < $n ; $i++) {
		  if (in_array(substr($_POST['AVS'],$i,1),$valid_result)) {
			  $trans_result .= "\n" . $avs[$i] . constant('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_AVS_'.substr($_POST['AVS'],$i,1));
				if (substr($_POST['AVS'],$i,1) <> 2) $OK = false; 
			} else {
			  $OK = false;
			}
		}
	}

	if (isset($_POST['authentication']) && strlen($_POST['authentication']) == strlen('ARespH.card.authentication.n') && substr($_POST['authentication'],0,-1) == 'ARespH.card.authentication.') {
		$checked = true;
		$valid_result = array(0,1,6,7,9);
		if (in_array(substr($_POST['authentication'],-1),$valid_result)) {
			$trans_result .= "\n" . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ARESPH . constant('MODULE_PAYMENT_RBSWORLDPAY_HOSTED_ARESPH_'.substr($_POST['authentication'],-1));
			if (substr($_POST['authentication'],-1) <> 0) $OK = false; 
		} else {
			$OK = false;
		}
	}
  $sql_data_array = array('orders_id' => $order['orders_id'],
                          'orders_status_id' => MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TRANSACTIONS_ORDER_STATUS_ID,
                          'date_added' => 'now()',
                          'customer_notified' => '0',
                          'comments' => $trans_result);

  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
	
	if ($checked && $OK && MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CHECKED_ORDER_STATUS_ID > 0 && $order['orders_status'] <>  MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CHECKED_ORDER_STATUS_ID) {
    tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . MODULE_PAYMENT_RBSWORLDPAY_HOSTED_CHECKED_ORDER_STATUS_ID . "', last_modified = now() where orders_id = '" . (int)$order['orders_id'] . "'");
	}
}
$url = tep_href_link(($cancelled ? 'checkout_payment.php' : 'checkout_process.php'), tep_session_name() . '=' . $_POST['M_sid'] . '&hash=' . $_POST['M_hash'], 'SSL', false);
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>
<meta http-equiv="refresh" content="3; URL=<?php echo $url; ?>">
<style>
 body {font-family:Geneva, Arial, Helvetica, sans-serif;}
</style>
</head>
<body>
<h1><?php echo STORE_NAME; ?></h1>

<p><?php echo ($cancelled ? MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_CANCEL_TRANSACTION : MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_SUCCESSFUL_TRANSACTION); ?></p>

<form action="<?php echo tep_href_link($url); ?>" method="post" target="_top">
  <p><input type="submit" value="<?php echo sprintf(($cancelled ? MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_RETURN_BUTTON : MODULE_PAYMENT_RBSWORLDPAY_HOSTED_TEXT_CONTINUE_BUTTON), addslashes(STORE_NAME)); ?>" /></p>
</form>

<p>&nbsp;</p>

<WPDISPLAY ITEM=banner>

</body>
</html>

<?php
  tep_session_destroy();
?>
