<?php
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
';
  
// check module installed and set up
	if (! defined('MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY') && strlen(MODULE_HEADER_TAGS_FRAUDLABSPRO_API_KEY)) exit;

// check if the customer is logged on and in checkout
	if (!tep_session_is_registered('customer_id') && tep_session_is_registered('payment')) {
		exit;
	}

// avoid hack attempts during the checkout procedure by checking the internal cartID
	if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
		if ($cart->cartID != $cartID) {
			exit;
		}
	}

	include_once('includes/classes/order.php');
// work on the assumption that payment modules which create an order before payment create a session variable called cart_something
// which is a concatenation of cartID-orderID
	$order_id = '';
	foreach ($_SESSION as $key => $value) {
		if (substr($key,0,strlen('cart_')) == 'cart_' && strpos($value,'-')) {
			$order_id = substr($value,strpos($value,'-')+1);
			$log_record .= '     order_id            : ' . $order_id .'
';
			break;
		}
	}  
  
	function flp_get_country_iso_2($country_name) {
		$query = tep_db_query("select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_name = '" . $country_name . "'");
		$row = tep_db_fetch_array($query);
		return $row['countries_iso_code_2'];
	}
  
	$order = new order($order_id); // if order id set, read from database otherwise load from cart
	
	$flpData['first_name'] = $order->customer['name'];
	
	$flpData['bill_city'] = $order->billing['city'];
	$flpData['bill_state'] = $order->billing['state'];
	$flpData['bill_zip_code'] = $order->billing['postcode'];
	$flpData['bill_country'] = flp_get_country_iso_2($order->billing['country']);
	$flpData['user_phone'] = $order->customer['telephone'];
	
	$flpData['ship_addr'] = $order->delivery['street_address'] . $order->delivery['suburb'];
	$flpData['ship_city'] = $order->delivery['city'];
	$flpData['ship_state'] = $order->delivery['state'];
	$flpData['ship_zip_code'] = $order->delivery['postcode'];
	$flpData['ship_country'] = flp_get_country_iso_2($order->delivery['country']);
	
	$flpData['email'] = $order->customer['email_address'];
	$flpData['email_domain'] = substr($order->customer['email_address'],strpos ($order->customer['email_address'], '@') + 1);
	//$flpData['email_hash'] = hash_string($order->customer['email_address']);
	
	$flpData['bin_no'] = substr(str_replace(array('-',' '), '', $order->info['cc_number']), 0, 6);
	$flpData['user_order_id'] = $order_id;
	$flpData['amount'] = $order->info['total'];
	$flpData['quantity'] = sizeof($order->products);
	$flpData['currency'] = $order->info['currency'];

	$flpData['payment_mode'] = $payment; // payment class should be in session variable
	
	$ip = $_SERVER['REMOTE_ADDR'];
	if(isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)){
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	}
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)){
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	
	$flpData['ip'] = $ip;

	if (isset($_COOKIE['flp_checksum'])) $flpData['flp_checksum'] = $_COOKIE['flp_checksum']; // as long as agent script is loaded!

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
			tep_session_register('flpro_id');
			
			break;
		}
	}
	